<?php

namespace Denngarr\Seat\Billing\Commands;

use Denngarr\Seat\Billing\Jobs\GenerateInvoices;
use Denngarr\Seat\Billing\Jobs\UpdateBills;
use Illuminate\Console\Command;


class BillingUpdate extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:update {--F|force} {--N|now}  {year?} {month?}';

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
        //ensure we update the last month
        $year = date('Y', strtotime('-1 month'));
        $month = date('n', strtotime('-1 month'));
        $historical = false;

        if (($this->argument('month')) && ($this->argument('year'))) {
            $year = $this->argument('year');
            $month = $this->argument('month');
            $historical = true;
        }

        $year = intval($year);
        $month = intval($month);
        $force = $this->option('force') || !$historical;

        if($month<1 || $month>12) {
            $this->error("Month is out of range");
        }
        if($year < 2015) {
            $this->error("You are trying to generate a tax report for a year before seat was even created! Did you accidentally specify e.g. 24 instead of 2024?");
            return;
        }

        $this->info(sprintf("updating month=%d year=%d force=%b", $month, $year, $force));

        if($this->option('now')){
            UpdateBills::dispatchSync($force, $year, $month);
            GenerateInvoices::dispatchSync($year, $month);
        } else {
            UpdateBills::withChain([
                new GenerateInvoices($year, $month)
            ])->dispatch($force, $year, $month);
        }
    }


}
