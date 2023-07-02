<?php

namespace Denngarr\Seat\Billing\Http\Controllers;

use Denngarr\Seat\Billing\Helpers\TaxCode;
use Denngarr\Seat\Billing\Jobs\BalanceTaxPayment;
use Denngarr\Seat\Billing\Models\TaxInvoice;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Seat\Web\Models\User;

class TaxInvoiceController extends Controller
{
    public function getUserTaxInvoices(Request $request){

        $invoices = TaxInvoice::with("character","receiver_corporation")
            ->where("user_id",auth()->user()->id)
            ->get()
            ->groupBy("receiver_corporation_id");

        $now = now();

        return view("billing::tax.userTaxInvoices", compact("invoices", "now"));
    }

    public function getForeignUserTaxInvoices($user_id){
        $invoices = TaxInvoice::with("character","receiver_corporation")
            ->where("user_id",$user_id)
            ->get()
            ->groupBy("receiver_corporation_id");

        $now = now();

        return view("billing::tax.userTaxInvoices", compact("invoices", "now"));
    }

    public function balanceUserOverpayment(Request $request){
        $request->validate([
            "corporation_id"=>"required|integer"
        ]);

        BalanceTaxPayment::dispatch(auth()->user()->id, (int)$request->corporation_id);

        return redirect()->back()->with("success",trans("billing::tax.overpayment_balancing_scheduled"));
    }

    public function corporationSelectionPage(){
        $corporation_ids = TaxInvoice::select("receiver_corporation_id")->distinct()->pluck("receiver_corporation_id");
        $corporations = CorporationInfo::whereIn("corporation_id", $corporation_ids)->get();

        return view("billing::tax.corporationList", compact("corporations"));
    }

    public function corporationOverviewPage($corporation_id){
        $corporation = CorporationInfo::find($corporation_id);
        if(!$corporation) {
            abort(404);
        }

        $total_invoices_count = TaxInvoice::where("receiver_corporation_id", $corporation_id)->count();
        $open_invoices_count = TaxInvoice::where("receiver_corporation_id", $corporation_id)->whereIn("state",["open","pending"])->count();
        $completed_invoices_count = TaxInvoice::where("receiver_corporation_id", $corporation_id)->where("state","completed")->count();
        $open_isk = TaxInvoice::where("receiver_corporation_id", $corporation_id)->whereIn("state",["open","pending"])->sum("amount");
        $overdue_isk = TaxInvoice::where("receiver_corporation_id", $corporation_id)->whereDate("due_until","<",now())->sum("amount") - TaxInvoice::where("receiver_corporation_id", $corporation_id)->whereDate("due_until","<",now())->sum("paid");

        $user_totals = TaxInvoice::with("user.main_character")
            ->select("user_id")
            ->selectRaw("SUM(amount) as total")
            ->selectRaw("SUM(paid) as paid")
            ->selectRaw("(select SUM(CAST(amount AS SIGNED) - CAST(paid AS SIGNED)) from seat_billing_tax_invoices as inside where inside.user_id = seat_billing_tax_invoices.user_id and inside.receiver_corporation_id=receiver_corporation_id and inside.due_until<CURRENT_DATE() and inside.state in (\"open\",\"pending\") ) as overdue")
            ->where("receiver_corporation_id", $corporation_id)
            ->groupBy("user_id")
            ->get();


        return view("billing::tax.corporationOverviewPage", compact("corporation","total_invoices_count", "open_invoices_count","completed_invoices_count","open_isk", "user_totals", "overdue_isk"));
    }
}
