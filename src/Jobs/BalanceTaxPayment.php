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
use Seat\Web\Models\User;

class BalanceTaxPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, TaxProcessingHelper;

    private $user_id;
    private $corporation_id;

    public function __construct($user_id, $corporation_id)
    {
        $this->user_id = $user_id;
        $this->corporation_id = $corporation_id;
    }

    public function tags()
    {
        return ["seat-billing", "tax",];
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->user_id ?? rand()))->releaseAfter(10)];
    }

    public function handle(){
        $overtaxed_invoices = TaxInvoice::where("user_id", $this->user_id)
            ->where("receiver_corporation_id", $this->corporation_id)
            ->where("state","overtaxed")
            ->get();

        $payable_invoices = TaxInvoice::where("user_id", $this->user_id)
            ->where("receiver_corporation_id", $this->corporation_id)
            ->whereIn("state",["open","pending"])
            ->get();

        $total = 0;
        foreach ($overtaxed_invoices as $invoice){
            $total +=  $invoice->paid - $invoice->amount;

            $invoice->paid = $invoice->amount;
            $invoice->save();
        }

        $remaining_overpayment = $this->coverInvoices($payable_invoices, $total);

        if($remaining_overpayment > 0) {
            $this->handleOverPayment(
                $this->user_id,
                "billing::tax.overpayment_balancing_remainder",
                $remaining_overpayment,
                $this->corporation_id,
                User::find($this->user_id)->main_character_id ?? 3000001,
                TaxCode::generateCorporationCode($this->corporation_id)
            );
        }
    }
}