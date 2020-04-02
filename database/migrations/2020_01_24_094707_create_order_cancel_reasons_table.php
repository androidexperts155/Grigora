<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderCancelReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_cancel_reasons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message', 255)->nullable();
            $table->string('french_message', 255)->nullable();
            $table->integer('type')->nullable();
            $table->enum('order_type', ['1', '2'])->comment("1: delivery, 2:pickup");
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
        Schema::dropIfExists('order_cancel_reasons');
    }
}
