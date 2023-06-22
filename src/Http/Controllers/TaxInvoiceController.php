<?php

namespace Denngarr\Seat\Billing\Http\Controllers;

use Denngarr\Seat\Billing\Helpers\TaxCode;
use Denngarr\Seat\Billing\Jobs\BalanceTaxPayment;
use Denngarr\Seat\Billing\Models\TaxInvoice;
use Illuminate\Support\Facades\DB;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Seat\Web\Models\User;

class TaxInvoiceController extends Controller
{
    public function getUserTaxInvoices(){
        $invoices = TaxInvoice::with("character","receiver_corporation")
            ->where("user_id",auth()->user()->id)
            ->get()
            ->groupBy("receiver_corporation_id");

        return view("billing::tax.userTaxInvoices", compact("invoices"));
    }

    public function balanceUserOverpayment(Request $request){
        $request->validate([
            "corporation_id"=>"required|integer"
        ]);

        BalanceTaxPayment::dispatch(auth()->user()->id, (int)$request->corporation_id);

        return redirect()->back()->with("success",trans("billing::tax.overpayment_balancing_scheduled"));
    }
}
