<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class BillingCharacterBillInvoiceUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE seat_billing_character_bill set tax_invoice_id=null");

        Schema::table('seat_billing_character_bill', function (Blueprint $table) {
            $table->unique("tax_invoice_id");
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
            $table->dropUnique("seat_billing_character_bill_tax_invoice_id_unique");
        });
    }
}
