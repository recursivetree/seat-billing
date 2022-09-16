<?php

use Denngarr\Seat\Billing\Models\CharacterBill;
use Denngarr\Seat\Billing\Models\CorporationBill;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Seat\Services\Models\Schedule;

class ChangeBillFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('seat_billing_corp_bill', function (Blueprint $table) {
            //rename+change pve tables
            $table->renameColumn("pve_bill","pve_total");
            $table->bigInteger("pve_tax")->default(0);

            //rename+change mining tables
            $table->renameColumn("mining_bill","mining_total");
            $table->bigInteger("mining_tax")->default(0);
        });

        //migrate bill data
        $bills = CorporationBill::all();
        foreach ($bills as $bill){
            $bill->pve_tax = $bill->pve_total * ($bill->pve_taxrate/100.0);
            $bill->mining_tax = $bill->mining_total * ($bill->mining_taxrate/100.0) * ($bill->mining_modifier/100.0);
            $bill->save();
        }

        Schema::table('seat_billing_character_bill', function (Blueprint $table) {
            //rename+change mining tables
            $table->renameColumn("mining_bill","mining_total");
            $table->bigInteger("mining_tax")->default(0);
        });

        //migrate bill data
        $bills = CharacterBill::all();
        foreach ($bills as $bill){
            $bill->mining_tax = $bill->mining_total * ($bill->mining_taxrate/100.0) * ($bill->mining_modifier/100.0);
            $bill->save();
        }


        Schema::create("seat_billing_ore_tax", function (Blueprint $table){
           $table->bigIncrements("id");
           $table->bigInteger("group_id")->index();
           $table->smallInteger("tax_rate");
        });

        $tax_rate = (setting("oretaxrate",true) ?? 0) / 100;
        $old_incentivised_tax = (setting("ioretaxrate",true) ?? 0) / 100;
        $incentive_modifier = floor((($tax_rate===0)? 1 : $old_incentivised_tax / $tax_rate) * 100);
        setting(["ioretaxmodifier",$incentive_modifier],true);

        $tax_rate = (setting("bountytaxrate",true) ?? 0) / 100;
        $old_incentivised_tax = (setting("ibountytaxrate",true) ?? 0) / 100;
        $incentive_modifier = floor((($tax_rate===0)? 1 : $old_incentivised_tax / $tax_rate) * 100);
        setting(["ioretaxmodifier",$incentive_modifier],true);

        $schedule = new Schedule();
        $schedule->command = "billing:update:live";
        $schedule->expression = "0 0 * * *";
        $schedule->allow_overlap = false;
        $schedule->allow_maintenance = false;
        $schedule->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("seat_billing_ore_tax");

        Schema::table('seat_billing_corp_bill', function (Blueprint $table) {
            //rename+change pve tables
            $table->renameColumn("pve_total","pve_bill");
            $table->dropColumn("pve_tax");

            //rename+change mining tables
            $table->renameColumn("mining_total", "mining_bill");
            $table->dropColumn("mining_tax");
        });

        Schema::table('seat_billing_character_bill', function (Blueprint $table) {
            //rename+change mining tables
            $table->renameColumn("mining_total", "mining_bill");
            $table->dropColumn("mining_tax");
        });
    }
}
