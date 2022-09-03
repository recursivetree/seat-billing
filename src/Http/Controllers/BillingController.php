<?php

namespace Denngarr\Seat\Billing\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Seat\Web\Http\Controllers\Controller;
use Seat\Eveapi\Models\Alliances\Alliance;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Denngarr\Seat\Billing\Validation\ValidateSettings;
use Denngarr\Seat\Billing\Helpers\BillingHelper;

class BillingController extends Controller
{
    use BillingHelper;

    public function getLiveBillingView(int $alliance_id = 0)
    {
        $start_date = carbon()->startOfMonth()->toDateString();
        $end_date = carbon()->endOfMonth()->toDateString();

        //dd($start_date,$end_date);

        $mining_stats = DB::table('corporation_member_trackings')
            ->select('corporation_id')
            ->leftJoin('character_minings', 'corporation_member_trackings.character_id', '=', 'character_minings.character_id')
            ->whereBetween('date', [$start_date, $end_date])
            ->groupBy('corporation_id');

        if (setting("pricevalue", true) == "m") {
            $mining_stats = $mining_stats->selectRaw('SUM((character_minings.quantity / 100) * (invTypeMaterials.quantity * (? / 100)) * average_price) as mining', [(float) setting("refinerate", true)])
                ->leftJoin('invTypeMaterials', 'character_minings.type_id', '=', 'invTypeMaterials.typeID')
                ->leftJoin('market_prices', 'invTypeMaterials.materialTypeID', '=', 'market_prices.type_id');
        } else {
            $mining_stats = $mining_stats->selectRaw('SUM(character_minings.quantity * market_prices.average_price) as mining')
                ->leftJoin('market_prices', 'character_minings.type_id', '=', 'market_prices.type_id');
        }

        $bounty_stats = $this->getBountyTotal(strval(carbon()->year),strval(carbon()->month));

        $stats = DB::table('corporation_infos')
            ->select('corporation_infos.corporation_id', 'corporation_infos.alliance_id', 'corporation_infos.name', 'corporation_infos.tax_rate', 'mining', 'bounties')
            ->selectRaw('COUNT(corporation_member_trackings.character_id) as members')

            ->selectRaw('COUNT(refresh_tokens.character_id) as actives')
            ->join('corporation_member_trackings', 'corporation_infos.corporation_id', '=', 'corporation_member_trackings.corporation_id')
            ->leftJoin('refresh_tokens', function ($join) {
                $join->on('corporation_member_trackings.character_id', '=', 'refresh_tokens.character_id')
                    ->whereNull('deleted_at');
            })

            ->leftJoin(DB::raw('(' . $mining_stats->toSql() . ') mining_stats'), function($join) {
                $join->on('corporation_infos.corporation_id', '=', 'mining_stats.corporation_id');
            })
            ->leftJoin(DB::raw('(' . $bounty_stats->toSql() . ') bounty_stats'), function ($join) {
                $join->on('corporation_infos.corporation_id', '=', 'bounty_stats.corporation_id');
            })
            ->mergeBindings($mining_stats)
            ->mergeBindings($bounty_stats)

            ->groupBy('corporation_infos.corporation_id', 'corporation_infos.alliance_id', 'corporation_infos.name', 'corporation_infos.tax_rate', 'mining', 'bounties')
            ->orderBy('name');

        if ($alliance_id !== 0) {
            $stats->where('alliance_id', $alliance_id);
        }

        $stats = $stats->get();

        //dd($stats);

        $alliances = Alliance::whereIn('alliance_id', CorporationInfo::select('alliance_id'))->orderBy('name')->get();

        $dates = $this->getCorporationBillingMonths($stats->pluck('corporation_id')->toArray());

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
        return view('billing::settings');
    }

    public function saveBillingSettings(ValidateSettings $request)
    {
        setting(["oremodifier", intval($request->oremodifier)], true);
        setting(["oretaxrate", intval($request->oretaxrate)], true);
        setting(["refinerate", intval($request->refinerate)], true);
        setting(["bountytaxrate", intval($request->bountytaxrate)], true);
        setting(["ioremodifier", intval($request->ioremodifier)], true);
        setting(["ioretaxrate", intval($request->ioretaxrate)], true);
        setting(["ibountytaxrate", intval($request->ibountytaxrate)], true);
        setting(["irate", intval($request->irate)], true);
        setting(["pricevalue", intval($request->pricevalue)], true);

        return redirect()->back()->with('success', 'Billing Settings have successfully been updated.');
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

    public function previousBillingCycle($year, $month)
    {
        $corporations = $this->getCorporations();

        $stats = $this->getCorporationBillByMonth($year, $month)->sortBy('corporation.name');

        $dates = $this->getCorporationBillingMonths($corporations->pluck('corporation_id')->toArray());

        return view('billing::pastbill', compact('stats', 'dates', 'year', 'month'));
    }
}
