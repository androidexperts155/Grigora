<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cart_id');
            $table->integer('user_id')->comment("it can be user_id or device_id based on login_type");
            $table->enum('login_type', ['1', '2'])->default('2')->comment("1: with login, 2: without login");
            $table->integer('restaurant_id');
            $table->integer('driver_id')->nullable();
            $table->timestamp('request_time')->nullable();
            $table->integer('quantity');
            $table->enum('payment_method', ['1', '2', '3'])->comment("1=>cash, 2=>card, 3=>grigora wallet");
            $table->string('promocode')->nullable();
            $table->integer('app_fee')->nullable();
            $table->decimal('driver_fee', 8,2)->nullable();
            $table->decimal('delivery_fee', 8,2);
            $table->string('start_address', 255)->nullable();
            $table->decimal('start_lat', 10,2)->nullable();
            $table->decimal('start_long', 11,2)->nullable();
            $table->decimal('end_lat', 10,2)->nullable();
            $table->string('delivery_address', 255)->nullable();
            $table->string('preparing_time', 255)->nullable();
            $table->enum('update_preparing_time', ['0','1'])->default('0')->comment("0:restaurant not update preparing time, 1: restaurant updated the preparing time");
            $table->timestamp('preparing_end_time')->nullable();
            $table->timestamp('order_accepted_time')->nullable();
            $table->string('time_remaining', 255)->nullable();
            $table->timestamp('schedule_time')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->decimal('end_long', 11,2)->nullable();
            $table->string('reference', 255)->nullable();
            $table->decimal('price_before_promo', 8,2);
            $table->decimal('price_after_promo', 8,2);
            $table->decimal('final_price', 8,2);
            $table->enum('order_type', ['1', '2'])->comment('1:delivery, 2:pickup')
            $table->enum('order_status', ['0', '1', '2', '3', '4','5','6', '7', '8', '9'])->comment("0=>place order, 1=> schedule order, 2=>preparing order, 3=>driver assigned, 4=>out of delivery, 5=> deliverd, 6=>rejected by restaurant, 7=> order is almost ready, 8=>order cancelled by customer, 9=>restaurant start preparing");
            $table->enum('cancel_accepted', ['0', '1'])->default('0')->comment('0:not did any action, 1:cancel pickup order request');
            $table->integer('cancel_type')->nullable();
            $table->enum('dispatch', ['0','1'])->default('0')->comment("0=>not dispatched,1=>dispatched");
            $table->enum('is_schedule', ['0', '1'])->default('0')->comment("0=>not schedule,1:scheduled");
            $table->text('payment_data')->nullable();
            $table->enum('group_order', ['0', '1'])->default('0')->comment('0:normal order, 1:group order');
            $table->enum('notification_sent', ['0', '1'])->default('0')->comment('0:notification not sent, 1:notification sent');
            $table->integer('max_per_person')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
