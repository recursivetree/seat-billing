<?php

namespace Denngarr\Seat\Billing\Jobs;

use Denngarr\Seat\Billing\Helpers\TaxCode;
use Denngarr\Seat\Billing\Helpers\TaxProcessingHelper;
use Denngarr\Seat\Billing\Models\TaxInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Seat\Eveapi\Models\RefreshToken;

class ProcessTaxPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TaxProcessingHelper;

    private $journal_entry;

    public function __construct($journal_entry)
    {
        $this->journal_entry = $journal_entry;
    }

    public function tags()
    {
        return ["seat-billing", "tax",];
    }

    public function middleware(): array
    {
        $token = RefreshToken::find($this->journal_entry->first_party_id);
        return [(new WithoutOverlapping($token->user->id ?? rand()))->releaseAfter(10)];
    }

    public function handle(){
        $token = RefreshToken::find($this->journal_entry->first_party_id);
        if($token === null) return;

        //make sure this is a transaction
        if($this->journal_entry->ref_type !== "player_donation") return;

        $tax_code = TaxCode::decodeTaxCode($this->journal_entry->reason);
        if(!$tax_code) return;

        $invoices = $tax_code->getTaxInvoices($token->user->id);

        // filter out invoices that can't be covered by this payment because they belong to different corporations
        $corporation_id = $this->journal_entry->second_party_id;
        $invoices = $invoices->filter(function ($invoice) use ($corporation_id) {
            return $invoice->isValidReceiverCorporation($corporation_id);
        });

        if($invoices->isEmpty()){
            $this->handleOverPayment($token->user->id,"billing::tax.tax_no_matching_invoice",(int)$this->journal_entry->amount,$corporation_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);
            return;
        }

        $remaining = $this->coverInvoices($invoices,(int)$this->journal_entry->amount);

        //overpayment
        if($remaining > 0){
            $this->handleOverPayment($token->user->id,"billing::tax.too_much_tax_paid", $remaining,$corporation_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);
        }
    }
}