<?php

namespace Denngarr\Seat\Billing\Helpers;

use Denngarr\Seat\Billing\Models\TaxInvoice;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

trait TaxProcessingHelper
{
    private function handleOverPayment($user, $reason, $amount, $corporation_id, $character_id, $code){
        // this means someone has paid, but there is no open invoice
        // add an overpaid tax entry
        $invoice = new TaxInvoice();
        $invoice->user_id = $user;
        $invoice->character_id = $character_id;
        $invoice->receiver_corporation_id = $corporation_id;
        $invoice->amount = 0;
        $invoice->paid = $amount;
        $invoice->state = "overtaxed";
        $invoice->reason_translation_key = $reason;
        $invoice->reason_translation_data = [
            "tax"=> number($amount, 0),
            "corp"=>CorporationInfo::find($corporation_id)->name ?? "Unknown Corporation",
            "code"=>$code,
        ];
        $invoice->due_until = now();
        $invoice->save();
    }

    private function coverInvoices($invoices, $amount){
        $remaining = $amount;
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
        return $remaining;
    }
}