<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('app_fee')->default('10');
            $table->decimal('delivery_fee', 11,2)->nullable()->comment("per mile");
            $table->integer('min_order')->nullable()->comment('minimum order for delivery for free delivery');
            $table->ingeger('min_km')->nullable()->comment('minimun kilo meter for free delivery');
            $table->integer('distance')->nullable();
            $table->decimal('min_wallet', 11,2)->default('0.00');
            $table->decimal('max_wallet', 11,2)->default('0.00');
            $table->decimal('sender_refer_earn', 11,2)->default('0.00');
            $table->decimal('receiver_refer_earn', 11,2)->default('0.00');
            $table->decimal('loyality', 11,2)->default("0.00");
            $table->integer('naira_to_points')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
