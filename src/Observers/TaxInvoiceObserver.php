<?php

namespace Denngarr\Seat\Billing\Observers;

class TaxInvoiceObserver
{
    public static function saving($tax_invoice){
        $remaining = $tax_invoice->amount - $tax_invoice->paid;
        if($remaining < 0){
            $tax_invoice->state = "overtaxed";
        } else if ($remaining === 0) {
            $tax_invoice->state = "completed";
        } else if ($tax_invoice->paid > 0 && $tax_invoice->state !== "prediction") {
            $tax_invoice->state = "pending";
        }

        return true;
    }
}