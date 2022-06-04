<?php

namespace Denngarr\Seat\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Character\CharacterInfo;

class CharacterBill extends Model
{
    public $timestamps = true;

    protected $table = 'seat_billing_character_bill';

    protected $fillable = ['id', 'character_id', 'month', 'year', 'mining_bill', 'mining_taxrate'];

    public function character(){
        return $this->belongsTo(CharacterInfo::class,'character_id','character_id');
    }
}
