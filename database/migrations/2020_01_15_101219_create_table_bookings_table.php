<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('restaurant_id');
            $table->integer('no_of_seats');
            $table->date('date');
            $table->time('start_time_from');
            $table->time('start_time_to');
            $table->enum('booking_status', ['1', '2', '3'])->comment("1: placed, 2:accepted, 3:rejected by restaurant, 4:time over and custmer didn't come");
            $table->string('cancel_reason', 255)->nullable();
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
        Schema::dropIfExists('table_bookings');
    }
}
