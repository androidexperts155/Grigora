<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('location_type_id');
            $table->string('address',255)->nullable();
            $table->string('french_address',255)->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('complete_address')->nullable();
            $table->text('complete_french_address')->nullable();
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
        Schema::dropIfExists('users_locations');
    }
}
