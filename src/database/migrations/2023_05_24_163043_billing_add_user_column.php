<?php

use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Seat\Services\Models\Schedule;

class BillingAddUserColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seat_billing_character_bill', function (Blueprint $table) {
            $table->bigInteger("user_id")->unsigned()->nullable();
            $table->index("user_id");
        });

        CharacterBill::with("character.refresh_token")->chunk(1000,function ($bills){
            foreach ($bills as $bill){
                $bill->user_id = $bill->character->refresh_token->user_id ?? null;
                $bill->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seat_billing_character_bill', function (Blueprint $table) {
            $table->dropColumn("user_id");
        });
    }
}
