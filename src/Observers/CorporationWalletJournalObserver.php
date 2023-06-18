<?php

namespace Denngarr\Seat\Billing\Observers;

use Denngarr\Seat\Billing\Jobs\ProcessTaxPayment;

class CorporationWalletJournalObserver
{
    public static function created($journal_entry){
        if($journal_entry->ref_type === "player_donation" && substr($journal_entry->reason,0,3) === "tax"){
            ProcessTaxPayment::dispatch($journal_entry);
        }
    }
}