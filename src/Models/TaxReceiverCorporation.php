<?php

namespace Denngarr\Seat\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Web\Models\User;

class TaxReceiverCorporation extends Model
{
    protected $table = 'seat_billing_tax_invoice_receivers';

    public $timestamps = false;

    public function receiver_corporation(){
        return $this->belongsTo(CorporationInfo::class,'receiver_corporation_id','corporation_id');
    }

    public function substitute_corporation(){
        return $this->belongsTo(CorporationInfo::class,'substitute_corporation_id','corporation_id');
    }
}
