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
use Seat\Eveapi\Jobs\Middleware\WithoutOverlapping;
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

        $this->handleOverPayment($token->user->id,"debug:TokenFound",(int)$this->journal_entry->amount,$this->journal_entry->second_party_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);

        //make sure this is a transaction
        if($this->journal_entry->ref_type !== "player_donation") return;

        $this->handleOverPayment($token->user->id,"debug:RefType",(int)$this->journal_entry->amount,$this->journal_entry->second_party_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);

        $tax_code = TaxCode::decodeTaxCode($this->journal_entry->reason);
        if(!$tax_code) return;

        $this->handleOverPayment($token->user->id,"debug:InvoiceLoad",(int)$this->journal_entry->amount,$this->journal_entry->second_party_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);

        $invoices = $tax_code->getTaxInvoices($token->user->id);
        if($invoices->isEmpty()){
            $this->handleOverPayment($token->user->id,"billing::tax.tax_no_matching_invoice",(int)$this->journal_entry->amount,$this->journal_entry->second_party_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);
            return;
        }

        $remaining = $this->coverInvoices($invoices,(int)$this->journal_entry->amount);

        $this->handleOverPayment($token->user->id,"debug:InvoicesCovered",(int)$this->journal_entry->amount,$this->journal_entry->second_party_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);

        //overpayment
        if($remaining > 0){
            $this->handleOverPayment($token->user->id,"debug:SmthRemaining",(int)$this->journal_entry->amount,$this->journal_entry->second_party_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);

            $this->handleOverPayment($token->user->id,"billing::tax.too_much_tax_paid", $remaining,$this->journal_entry->second_party_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);
        }

        $this->handleOverPayment($token->user->id,"debug:Done",(int)$this->journal_entry->amount,$this->journal_entry->second_party_id,$this->journal_entry->first_party_id,$this->journal_entry->reason);

    }
}