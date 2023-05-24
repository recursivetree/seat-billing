<?php

namespace Denngarr\Seat\Billing\Http\Controllers;

use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Denngarr\Seat\Billing\Models\OreTax;
use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\RefreshToken;
use Seat\Web\Http\Controllers\Controller;
use Denngarr\Seat\Billing\Helpers\BillingHelper;
use Illuminate\Http\Request;
use Seat\Web\Models\User;

class BillingController extends Controller
{
    use BillingHelper;

    public function getBillingSettings()
    {
        $ore_tax = OreTax::all();

        return view('billing::settings', compact("ore_tax"));
    }

    const ALLOWED_PRICE_SOURCES = ["sell_price","buy_price","adjusted_price","average_price"];

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
            'pricesource'       => 'required|string|in:'. implode(",",self::ALLOWED_PRICE_SOURCES),
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
        setting(["price_source", $request->pricesource], true);

        OreTax::updateOrCreate(["group_id"=>1923],["tax_rate"=>intval($request->r64taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>1922],["tax_rate"=>intval($request->r32taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>1921],["tax_rate"=>intval($request->r16taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>1920],["tax_rate"=>intval($request->r8taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>1884],["tax_rate"=>intval($request->r4taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>711],["tax_rate"=>intval($request->gastax)]);
        OreTax::updateOrCreate(["group_id"=>465],["tax_rate"=>intval($request->icetax)]);

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

    public function getUserBill(){
        return $this->getUserBillByUserId(auth()->user());
    }

    public function getUserBillByCharacter($character_id){
        return $this->getUserBillByUserId(RefreshToken::find($character_id)->user);
    }

    private function getUserBillByUserId($user){
        $months = CharacterBill::where("user_id",$user->id)
            ->orderBy("character_id","ASC")
            ->get()
            ->groupBy(function ($bill){
                return $bill->year * 12 + $bill->month;
            })
            ->sortKeysDesc();

        return view("billing::userBill",compact("months"));
    }
}
