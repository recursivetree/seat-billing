<?php

namespace Denngarr\Seat\Billing\Jobs;

use Denngarr\Seat\Billing\BillingSettings;
use Denngarr\Seat\Billing\Helpers\BillingHelper;
use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Denngarr\Seat\Billing\Models\TaxInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Seat\Eveapi\Models\Corporation\CorporationInfo;

class GenerateInvoices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, BillingHelper;

    private $year;
    private $month;

    /**
     * @param $year
     * @param $month
     */
    public function __construct($year, $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function tags()
    {
        return ["seat-billing", "bill","tax"];
    }


    public function handle(){
        $update_bills = BillingSettings::$GENERATE_TAX_INVOICES->get(false);
        if(!$update_bills) return;

        $query = CharacterBill::where("year", $this->year)
            ->where("month", $this->month);
        $invoice_whitelist = BillingSettings::$TAX_INVOICE_WHITELIST->get([]);
        if(count($invoice_whitelist) > 0){
            $query->whereIn('corporation_id',$invoice_whitelist);
        }

        $now = now();
        $current_year = $now->year;
        $current_month = $now->month;
        $is_prediction = 100*$this->year + $this->month >= 100*$current_year + $current_month;

        $bills = $query->get();
        foreach ($bills as $bill){
            $tax_invoice = $bill->tax_invoice;

            if($tax_invoice && $bill->mining_tax < BillingSettings::$INVOICE_THRESHOLD->get(0)){
                $tax_invoice->delete();
                continue;
            }

            //dd($bill->tax_invoice_id, $tax_invoice);

            if ($tax_invoice === null) {
                $tax_invoice = new TaxInvoice();
                $tax_invoice->user_id = $bill->user_id;
                $tax_invoice->character_id = $bill->character_id;
                $tax_invoice->receiver_corporation_id = $bill->corporation_id;
                $tax_invoice->paid = 0;
                $tax_invoice->reason_translation_key = "billing::billing.tax_invoice_message";
                $tax_invoice->reason_translation_data = ["month" => $this->month, "year" => $this->year];
                $tax_invoice->due_until = \Carbon\Carbon::create($this->year, $this->month, 1, 1, 1, 1, "Europe/London")->endOfMonth()->addDays(30);
            }
            if ($is_prediction) {
                $tax_invoice->state = "prediction";
            } else {
                $tax_invoice->state = "open";
            }
            $tax_invoice->amount = $bill->mining_tax;
            $tax_invoice->save();

            $bill->tax_invoice_id = $tax_invoice->id;
            $bill->save();
        }
    }
}