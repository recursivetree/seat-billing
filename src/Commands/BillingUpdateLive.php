<?php

namespace Denngarr\Seat\Billing\Commands;

use Denngarr\Seat\Billing\Jobs\UpdateBills;
use Illuminate\Console\Command;


class BillingUpdateLive extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:update:live {--N|now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This updates the bill of the current month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //ensure we update the last month
        $year = date('Y');
        $month = date('n');

        if($this->option('now')){
            UpdateBills::dispatchSync(true, $year, $month);
        } else {
            UpdateBills::dispatch(true, $year, $month);
        }
    }


}
