<?php

namespace Denngarr\Seat\Billing\Http\Controllers;

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

    public function getLiveBillingView(int $alliance_id = 0)
    {
        $start_date = carbon()->startOfMonth()->toDateString();
        $end_date = carbon()->endOfMonth()->toDateString();

        $stats = collect();

        $alliances = Alliance::whereIn('alliance_id', CorporationInfo::select('alliance_id'))->orderBy('name')->get();

        $dates = $this->getCorporationBillingMonths();

        return view('billing::summary', compact('alliances', 'stats', 'dates'));
    }

    private function getCorporations()
    {
        if (auth()->user()->admin) {
            $corporations = CorporationInfo::orderBy('name')->get();
        } else {
            $corpids = CharacterInfo::whereIn('character_id', auth()->user()->associatedCharacterIds())
                ->select('corporation_id')
                ->get()
                ->toArray();

            $corporations = CorporationInfo::whereIn('corporation_id', $corpids)->orderBy('name')->get();
        }

        return $corporations;
    }

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
            'ioretaxmodifier'       => 'required|integer|min:0|max:200',
            'ibountytaxmodifier'    => 'required|integer|min:0|max:200',
            'irate'             => 'required|integer|min:0|max:200',
            'r64taxmodifier'    => 'required|integer|min:0',
            'r16taxmodifier'    => 'required|integer|min:0',
            'r32taxmodifier'    => 'required|integer|min:0',
            'r8taxmodifier'     => 'required|integer|min:0',
            'r4taxmodifier'     => 'required|integer|min:0',
            'gastax'     => 'required|integer|min:0',
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

        OreTax::updateOrCreate(["group_id"=>1923],["tax_rate"=>intval($request->r64taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>1922],["tax_rate"=>intval($request->r32taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>1921],["tax_rate"=>intval($request->r16taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>1920],["tax_rate"=>intval($request->r8taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>1884],["tax_rate"=>intval($request->r4taxmodifier)]);
        OreTax::updateOrCreate(["group_id"=>711],["tax_rate"=>intval($request->gastax)]);

        return redirect()->route("billing.settings")->with('success', 'Billing Settings have successfully been updated.');
    }

    public function getUserBilling($corporation_id)
    {

        $summary = $this->getMainsBilling($corporation_id);

        //dd($summary);

        return $summary;
    }

    public function getPastUserBilling($corporation_id, $year, $month)
    {
        $summary = $this->getPastMainsBillingByMonth($corporation_id, $year, $month);

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

        $stats = $this->getCorporationBillByMonth($year, $month)->sortBy('corporation.name');
        $dates = $this->getCorporationBillingMonths();

        return view('billing::pastbill', compact('stats', 'dates', 'year', 'month'));
    }
}
