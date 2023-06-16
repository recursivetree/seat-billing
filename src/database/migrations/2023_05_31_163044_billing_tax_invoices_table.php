<?php

use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Seat\Services\Models\Schedule;

class BillingTaxInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_billing_tax_invoices', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("user_id")->unsigned();
            $table->index("user_id");
            $table->bigInteger("character_id")->unsigned();
            $table->index("character_id");
            $table->bigInteger("receiver_corporation_id")->unsigned();
            $table->bigInteger("amount")->unsigned();
            $table->bigInteger("remaining")->unsigned();
            $table->enum("state",["open","pending","completed"]);
            $table->string("reason_translation_key");
            $table->json("reason_translation_data");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('seat_billing_tax_invoices');
    }
}
