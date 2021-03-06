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

        if ($this->option('force') == true) {
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


            if ((count($bill) == 0) || ($this->option('force') == true)) {
                $rates = $this->getCorporateTaxRate($corp->corporation_id);

                $bill = new CorporationBill();
                $bill->corporation_id = $corp->corporation_id;
                $bill->year = $year;
                $bill->month = $month;
                $bill->mining_bill = $this->getMiningTotal($corp->corporation_id, $year, $month);

                $bounties = 0;
                $pve_data = $this->getBountyTotal($year, $month)->where('corporation_wallet_journals.corporation_id',$corp->corporation_id)->first();
                if ($pve_data){
                    $bounties = $pve_data->bounties;
                }
                $bill->pve_bill = $bounties;

                $bill->mining_taxrate = $rates['taxrate'];
                $bill->mining_modifier = $rates['modifier'];
                $bill->pve_taxrate = $rates['pve'];
                $bill->save();
            
                $summary = $this->getMainsBilling($corp->corporation_id, $year, $month);

                foreach ($summary as $character) {
                    $bill = CharacterBill::where('character_id', $character['id'])
                        ->where('year', $year)
                        ->where('month', $month)
                        ->get();
                    if ((count($bill) == 0) || ($this->option('force') == true)) {
                        $bill = new CharacterBill();
                        $bill->character_id = $character['id'];
                        $bill->corporation_id = $corp->corporation_id;
                        $bill->year = $year;
                        $bill->month = $month;
                        $bill->mining_bill = $character['amount'];
                        $bill->mining_taxrate = ($character['taxrate'] * 100);
                        $bill->mining_modifier = $rates['modifier'];
                        $bill->save();
                    }
                }
            }
        }
    }
}
