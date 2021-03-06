<?php

namespace Denngarr\Seat\Billing\Helpers;

use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Illuminate\Support\Facades\DB;
use Seat\Services\Models\UserSetting;
use Seat\Web\Models\User;
use Illuminate\Support\Collection;

use Illuminate\Database\Eloquent\Builder;
use Seat\Eveapi\Models\Corporation\CorporationMemberTracking;

trait BillingHelper
{

    public function getCorporationMemberTracking(int $corporation_id): Builder
    {

        return CorporationMemberTracking::where('corporation_id', $corporation_id);

    }

    public function getCharacterBilling($corporation_id, $year, $month)
    {
        if (setting("pricevalue", true) == "m") {
            $ledger = DB::table('character_minings')
                ->select('users.main_character_id','character_infos.name')
                ->selectRaw('SUM((character_minings.quantity / 100) * (invTypeMaterials.quantity * ' . (setting("refinerate", true) / 100) . ') * market_prices.average_price) as amounts')
                ->join('invTypeMaterials', 'character_minings.type_id', 'invTypeMaterials.typeID')
                ->join('market_prices', 'invTypeMaterials.materialTypeID', 'market_prices.type_id')
                ->join('character_affiliations', 'character_minings.character_id', 'character_affiliations.character_id')
                ->join('refresh_tokens','refresh_tokens.character_id','character_minings.character_id')
                ->join('users', 'users.id', 'refresh_tokens.user_id')
                ->join('character_infos','users.main_character_id','character_infos.character_id')
                ->where('year', $year)
                ->where('month', $month)
                ->where('character_affiliations.corporation_id', $corporation_id)
                ->groupby('users.main_character_id','character_infos.name')
                ->get();
        } else {
            $ledger = DB::table('character_minings')
                ->select('users.main_character_id','character_infos.name')
                ->selectRaw('SUM(character_minings.quantity * market_prices.average_price) as amounts')
                ->join('market_prices', 'character_minings.type_id', 'market_prices.type_id')
                ->join('character_affiliations', 'character_minings.character_id', 'character_affiliations.character_id')
                ->join('refresh_tokens','refresh_tokens.character_id','character_minings.character_id')
                ->join('users', 'users.id', 'refresh_tokens.user_id')
                ->join('character_infos','users.main_character_id','character_infos.character_id')
                ->where('year', $year)
                ->where('month', $month)
                ->where('character_affiliations.corporation_id', $corporation_id)
                ->groupby('users.main_character_id','character_infos.name')
                ->get();
        }

        return $ledger;
    }

    private function getTrackingMembers($corporation_id)
    {
        return $this->getCorporationMemberTracking($corporation_id);
    }

    public function getMainsBilling($corporation_id, $year = null, $month = null)
    {
        if (is_null($year)) {
            $year = date('Y');
        }
        if (is_null($month)) {
            $month = date('n');
        }

        $summary = [];
        $taxrates = $this->getCorporateTaxRate($corporation_id);

        $ledger = $this->getCharacterBilling($corporation_id, $year, $month);

        foreach ($ledger as $entry) {


            if (!isset($summary[$entry->main_character_id])) {
                $summary[$entry->main_character_id]['amount'] = 0;
            }

            $summary[$entry->main_character_id]['amount'] += $entry->amounts;
            $summary[$entry->main_character_id]['id'] = $entry->main_character_id;
            $summary[$entry->main_character_id]['taxrate'] = $taxrates['taxrate'] / 100;
            $summary[$entry->main_character_id]['modifier'] = $taxrates['modifier'] / 100;
            $summary[$entry->main_character_id]['name'] = $entry->name;
        }
        return $summary;
    }

    public function getCorporateTaxRate($corporation_id)
    {
        $tracking = $this->getCorporationMemberTracking($corporation_id);
        $total_chars = $tracking->count();
        if ($total_chars == 0) {
            $total_chars = 1;
        }

        $reg_chars = DB::table("corporation_member_trackings")
            ->where("corporation_id",$corporation_id)
            ->join('refresh_tokens', function ($join) {
                $join->on('corporation_member_trackings.character_id', '=', 'refresh_tokens.character_id')
                    ->whereNull('deleted_at');
            })
            ->count();

        $mining_taxrate = setting('ioretaxrate', true);
        $mining_modifier = setting('ioremodifier', true);
        $pve_taxrate = setting('ibountytaxrate', true);

        if (($reg_chars / $total_chars) < (setting('irate', true) / 100)) {
            $mining_taxrate = setting('oretaxrate', true);
            $mining_modifier = setting('oremodifier', true);
            $pve_taxrate = setting('bountytaxrate', true);
        }

        return ['taxrate' => $mining_taxrate, 'modifier' => $mining_modifier, 'pve' => $pve_taxrate];
    }

    private function getMiningTotal($corporation_id, $year, $month)
    {
        $ledgers = $this->getCharacterBilling($corporation_id, $year, $month);

        return $ledgers->sum('amounts');
    }

    private function getCorporationBillingMonths($corporation_id)
    {
        if (!is_array($corporation_id)) {
            $corporation_ids = [$corporation_id];
        } else {
            $corporation_ids = $corporation_id;
        }

        return CorporationBill::select(DB::raw('DISTINCT month, year'))
            ->whereIn('corporation_id', $corporation_ids)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    private function getCorporationBillByMonth($year, $month)
    {
        return CorporationBill::with('corporation')
            ->where("month", $month)
            ->where("year", $year)
            ->get();
    }

    private function getPastMainsBillingByMonth($corporation_id, $year, $month)
    {
        return CharacterBill::with('character:character_id,name')
            ->where("corporation_id", $corporation_id)
            ->where("month", $month)
            ->where("year", $year)
            ->get();
    }

    public function getCorporationLedgerBountyPrizeByMonth(int $corporation_id, int $year = null, int $month = null): Collection
    {
        $query = DB::table('corporation_wallet_journals')
            ->select("second_party_id")
            ->selectRaw('MONTH(date) as month')
            ->selectRaw('YEAR(date) as year')
            ->selectRaw('ROUND(SUM(amount)) as total')
            ->where('corporation_id', $corporation_id)
            ->whereIn('ref_type', ['bounty_prizes', 'bounty_prize', 'ess_escrow_transfer'])
            ->where(DB::raw('YEAR(date)'), !is_null($year) ? $year : date('Y'))
            ->where(DB::raw('MONTH(date)'), !is_null($month) ? $month : date('m'))
            ->groupBy('second_party_id','date')
            ->orderBy(DB::raw('SUM(amount)'), 'desc');

        return $query->get();
    }

    private function getBountyTotal($year, $month)
    {
        $bounty_stats = DB::table('corporation_wallet_journals')
            ->select("corporation_wallet_journals.corporation_id")
            ->selectRaw('SUM(amount) / corporation_infos.tax_rate as bounties')
            ->join('corporation_infos', 'corporation_infos.corporation_id', '=', 'corporation_wallet_journals.corporation_id')
            ->whereIn('corporation_wallet_journals.ref_type', ['bounty_prizes', 'bounty_prize', 'ess_escrow_transfer'])
            ->where(DB::raw('YEAR(corporation_wallet_journals.date)'), !is_null($year) ? $year : date('Y'))
            ->where(DB::raw('MONTH(corporation_wallet_journals.date)'), !is_null($month) ? $month : date('m'))
            ->groupBy('corporation_wallet_journals.corporation_id','corporation_infos.tax_rate');

        return $bounty_stats;
    }

}
