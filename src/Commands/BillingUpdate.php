<?php

namespace Denngarr\Seat\Billing\Commands;

use Illuminate\Console\Command;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Denngarr\Seat\Billing\Helpers\BillingHelper;

class BillingUpdate extends Command
{
    use BillingHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:update {--F|force} {year?} {month?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This records the bills for the previous month if one doesn\'t exist.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lastmonth = date('Y-n', strtotime('-1 month'));
        list($year, $month) = preg_split("/-/", $lastmonth, 2);

        if (($this->argument('month')) && ($this->argument('year'))) {
            $year = $this->argument('year');
            $month = $this->argument('month');
        }

        // force update when no month or year is specified or when the force option is given
        $force = $this->option('force') || ($this->argument("year")===null && $this->argument("month")===null);

        if ($force) {
            CorporationBill::where('month', $month)
                ->where('year', $year)
                ->delete();
            CharacterBill::where('month', $month)
                ->where('year', $year)
                ->delete();
        }
        // See if corps already have a bill.  If not, generate one.

        $corps = CorporationInfo::all();
        foreach ($corps as $corp) {
            $bill = CorporationBill::where('corporation_id', $corp->corporation_id)
                ->where('year', $year)
                ->where('month', $month)
                ->get();


            if ($bill===null || $force) {
                $isIncentivisedRatesEligible = $this->isEligibleForIncentivesRates($corp->corporation_id);

                $bill = new CorporationBill();
                $bill->corporation_id = $corp->corporation_id;
                $bill->year = $year;
                $bill->month = $month;

                //mining data
                $mining_data = $this->getCorporationMiningTotal($corp->corporation_id, $year, $month);
                $bill->mining_total = $mining_data["mining_total"];
                $bill->mining_tax = $mining_data["mining_tax"];

                //bounty data
                $bounties = 0;
                $pve_data = $this->getBountyTotal($year, $month)->where('corporation_wallet_journals.corporation_id',$corp->corporation_id)->first();
                if ($pve_data){
                    $bounties = $pve_data->bounties;
                }
                $bill->pve_total = $bounties;
                $pve_tax = (setting('bountytaxrate', true) ?? 0) / 100;
                if($isIncentivisedRatesEligible){
                    $pve_tax = $pve_tax * (setting("ibountytaxmodifier",true) ?? 100) / 100;
                }
                $bill->pve_tax = $bounties * $pve_tax;

                //TODO remove when removing it from the db
                $bill->mining_taxrate = 0; // legacy fields
                $bill->mining_modifier = 0; // legacy fields
                $bill->pve_taxrate = $pve_tax * 10;

                //save corporation bill
                $bill->save();

                //character bill
                $summary = $this->getMainsBilling($corp->corporation_id, $year, $month);
                foreach ($summary as $character) {
                    $bill = CharacterBill::where('character_id', $character['id'])
                        ->where('year', $year)
                        ->where('month', $month)
                        ->get();

                    if ($bill===null || $force) {
                        $bill = new CharacterBill();
                        $bill->character_id = $character['id'];
                        $bill->corporation_id = $corp->corporation_id;
                        $bill->year = $year;
                        $bill->month = $month;
                        $bill->mining_total = $character['mining_total'];
                        $bill->mining_tax = $character['mining_tax'];
                        $bill->mining_modifier = 0;//legacy
                        $bill->mining_taxrate = 0;//legacy
                        $bill->save();
                    }
                }
            }
        }
    }
}
