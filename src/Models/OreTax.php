<?php

namespace Denngarr\Seat\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Eveapi\Models\Wallet\CorporationWalletJournal;

class OreTax extends Model
{
    protected $fillable = ["group_id","tax_rate"];

    public $timestamps = false;

    protected $primaryKey = 'tax_rate';


    protected $table = 'seat_billing_ore_tax';
}
