<?php

namespace Denngarr\Seat\Billing\Http\Controllers;

use Denngarr\Seat\Billing\Models\CorporationBill;
use Denngarr\Seat\Billing\Models\OreTax;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Sde\InvGroup;
use Seat\Web\Http\Controllers\Controller;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Denngarr\Seat\Billing\Validation\ValidateSettings;
use Denngarr\Seat\Billing\Helpers\BillingHelper;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    use BillingHelper;

    public function getBillingSettings()
    {
        $ore_tax = OreTax::all();

        return view('billing::settings', compact("ore_tax"));
    }

    public function saveBillingSettings(Request $request)
    {
        $request->validate([
            'oremodifier'       => 'required|integer|min:0|max:200',
            'oretaxrate'        => 'required|integer|min:0|max:200',
            'bountytaxrate'     => 'required|integer|min:0|max:200',
            'ioremodifier'      => 'required|integer|min:0|max:200',
            'ioretaxmodifier'   => 'required|integer|min:0|max:200',
            'ibountytaxmodifier'=> 'required|integer|min:0|max:200',
            'irate'             => 'required|integer|min:0|max:200',
            'r64taxmodifier'    => 'required|integer|min:0',
            'r16taxmodifier'    => 'required|integer|min:0',
            'r32taxmodifier'    => 'required|integer|min:0',
            'r8taxmodifier'     => 'required|integer|min:0',
            'r4taxmodifier'     => 'required|integer|min:0',
            'gastax'            => 'required|integer|min:0',
            'icetax'            => 'required|integer|min:0',
        ]);

        setting(["oremodifier", intval($request->oremodifier)], true);
        setting(["oretaxrate", intval($request->oretaxrate)], true);
        setting(["refinerate", intval($request->refinerate)], true);
        setting(["bountytaxrate", intval($request->bountytaxrate)], true);
        setting(["ioremodifier", intval($request->ioremodifier)], true);
        setting(["ioretaxmodifier", intval($request->ioretaxmodifier)], true);
        setting(["ibountytaxmodifier", intval($request->ibountytaxmodifier)], true);
        setting(["irate", intval($request->irate)], true);
        setting(["pricevalue", $request->pricevalue], true);

        OreTax::whereIn("group_id",[1923,1922,1921,1920,1884,711, 465])->delete();

        $ore = new OreTax;
        $ore->group_id = 1923;
        $ore->tax_rate = intval($request->r64taxmodifier);
        $ore->save();

        $ore = new OreTax;
        $ore->group_id = 1922;
        $ore->tax_rate = intval($request->r32taxmodifier);
        $ore->save();

        $ore = new OreTax;
        $ore->group_id = 1921;
        $ore->tax_rate = intval($request->r16taxmodifier);
        $ore->save();

        $ore = new OreTax;
        $ore->group_id = 1920;
        $ore->tax_rate = intval($request->r8taxmodifier);
        $ore->save();

        $ore = new OreTax;
        $ore->group_id = 1884;
        $ore->tax_rate = intval($request->r4taxmodifier);
        $ore->save();

        $ore = new OreTax;
        $ore->group_id = 711;
        $ore->tax_rate = intval($request->gastax);
        $ore->save();

        $ore = new OreTax;
        $ore->group_id = 465;
        $ore->tax_rate = intval($request->icetax);
        $ore->save();

//        OreTax::updateOrCreate(["group_id"=>1923],["tax_rate"=>intval($request->r64taxmodifier)]);
//        OreTax::updateOrCreate(["group_id"=>1922],["tax_rate"=>intval($request->r32taxmodifier)]);
//        OreTax::updateOrCreate(["group_id"=>1921],["tax_rate"=>intval($request->r16taxmodifier)]);
//        OreTax::updateOrCreate(["group_id"=>1920],["tax_rate"=>intval($request->r8taxmodifier)]);
//        OreTax::updateOrCreate(["group_id"=>1884],["tax_rate"=>intval($request->r4taxmodifier)]);
//        OreTax::updateOrCreate(["group_id"=>711],["tax_rate"=>intval($request->gastax)]);

        return redirect()->route("billing.settings")->with('success', 'Billing Settings have successfully been updated.');
    }

    public function getCharacterBill($corporation_id, $year, $month)
    {
        $summary = $this->getCharacterBillByMonth($corporation_id, $year, $month);

        return $summary;
    }

    public function showCurrentBill()
    {
        $year = date('Y');
        $month = date('n');

        return $this->showBill($year, $month);
    }

    public function showBill($year, $month)
    {

        $stats =  CorporationBill::with('corporation.alliance')
            ->where("month", $month)
            ->where("year", $year)
            ->get();
        $dates = $this->getCorporationBillingMonths();

        return view('billing::bill', compact('stats', 'dates', 'year', 'month'));
    }
}
