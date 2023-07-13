<?php

use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Seat\Services\Models\Schedule;

class BillingTaxInvoicesTimes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seat_billing_tax_invoices', function (Blueprint $table) {
            $table->timestamps();
            // migration fails if not nullable
            $table->dateTime("due_until",0)->nullable();
        });

        DB::table("seat_billing_tax_invoices")
            ->update([
                "updated_at" => now(),
                "created_at"=>now(),
                "due_until"=>now()->addDays(30)
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('seat_billing_tax_invoices', function (Blueprint $table) {
            $table->dropColumn("created_at");
            $table->dropColumn("updated_at");
            $table->dropColumn("due_until");
        });
    }
}
