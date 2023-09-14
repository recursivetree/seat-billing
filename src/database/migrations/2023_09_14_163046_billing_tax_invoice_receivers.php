<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;


class BillingTaxInvoiceReceivers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seat_billing_tax_invoice_receivers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('receiver_corporation_id')->unsigned();
            $table->bigInteger('substitute_corporation_id')->unsigned();
            $table->index('receiver_corporation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('seat_billing_tax_invoice_receivers');
    }
}
