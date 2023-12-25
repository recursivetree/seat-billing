<?php

namespace Denngarr\Seat\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Eveapi\Models\Corporation\CorporationInfo;
use Seat\Web\Models\User;

class TaxInvoice extends Model
{
    protected $fillable = ["group_id","tax_rate"];

    protected $table = 'seat_billing_tax_invoices';

    protected $casts = [
        'reason_translation_data' => 'array',
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function character(){
        return $this->belongsTo(CharacterInfo::class,'character_id','character_id');
    }

    public function receiver_corporation(){
        return $this->belongsTo(CorporationInfo::class,'receiver_corporation_id','corporation_id');
    }

    public function isValidReceiverCorporation($corporation_id){
        $special_rules = TaxReceiverCorporation::where("substitute_corporation_id",$this->receiver_corporation_id)->exists();

        if(!$special_rules) {
            return $this->receiver_corporation_id === $corporation_id;
        }

        return TaxReceiverCorporation::where("substitute_corporation_id",$this->receiver_corporation_id)
            ->where("receiver_corporation_id",$corporation_id)
            ->exists();
    }
}
