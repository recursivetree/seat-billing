<?php

namespace Denngarr\Seat\Billing\Jobs;

use Denngarr\Seat\Billing\Helpers\TaxCode;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        //make sure this is a transaction
        if($this->journal_entry->ref_type !== "player_donation") return;

        $token = RefreshToken::find($this->journal_entry->first_party_id);
        if($token === null) return;

        $tax_code = TaxCode::decodeTaxCode($this->journal_entry->reason);
        if(!$tax_code) return;

        $invoices = $tax_code->getTaxInvoices($token->user->id);
        if($invoices->isEmpty()){
            $this->handleOverPayment($token->user->id,"billing::tax.tax_no_matching_invoice",(int)$this->journal_entry->amount);
            return;
        }

        $remaining = (int)$this->journal_entry->amount;
        foreach ($invoices as $invoice){
            if($invoice->state == "open" || $invoice->state == "pending"){
                $missing = $invoice->amount - $invoice->paid;
                if($remaining >= $missing){
                    $payment = $missing;
                } else {
                    $payment = $remaining;
                }
                $invoice->paid += $payment;
                $remaining -= $payment;

                $invoice->save();
            }
        }

        //overpayment
        if($remaining > 0){
            $this->handleOverPayment($token->user->id,"billing::tax.too_much_tax_paid", $remaining);
        }
    }

    private function handleOverPayment($user, $reason, $amount){
        // this means someone has paid, but there is no open invoice
        // add an overpaid tax entry
        $invoice = new TaxInvoice();
        $invoice->user_id = $user;
        $invoice->character_id = $this->journal_entry->first_party_id;
        $invoice->receiver_corporation_id = $this->journal_entry->second_party_id;
        $invoice->amount = 0;
        $invoice->paid = $amount;
        $invoice->state = "overtaxed";
        $invoice->reason_translation_key = $reason;
        $invoice->reason_translation_data = [
            "tax"=> number($amount, 0),
            "corp"=>$this->journal_entry->second_party->name,
            "code"=>$this->journal_entry->reason,
        ];
        $invoice->save();
    }
}