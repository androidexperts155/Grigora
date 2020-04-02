7<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoreFieldsToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 255)->unique()->nullable();
            $table->string('my_referal', 255)->unique()->nullable();
            $table->string('from_referal', 255)->unique()->nullable();
            $table->string('french_name', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('id_proof', 255)->nullable();
            $table->string('franchisee_proof', 255)->nullable();
            $table->string('license_image', 255)->nullable();
            $table->string('address_proof', 255)->nullable();
            $table->string('phone', 255)->unique();
            $table->string('otp', 255)->nullable();
            $table->string('otp_expire_time')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('french_address', 255)->nullable();
            $table->decimal('latitude', 10,2)->nullable();
            $table->decimal('longitude', 11,2)->nullable();
            $table->integer('promo_id')->nullable();
            $table->integer('offer')->default('0');
            $table->decimal('wallet', 11,2)->default('0.0');
            $table->enum('role', ['1', '2', '3', '4', '5'])->comment("1=>admin, 2=>user, 3=>driver, 4=>restaurant, 5=>subadmin");
            $table->string('device_token', 255)->nullable();
            $table->enum('device_type', ['0','1'])->nullable()->comment('0=>android, 1=>ios');
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->enum('full_time', ['0', '1'])->nullable()->comment('0: not 24 hours, 1:24 hours');
            $table->string('facebook_id', 255)->nullable();
            $table->string('instagram_id', 255)->nullable();
            $table->string('twiter_id', 255)->nullable();
            $table->string('google_id', 255)->nullable();
            $table->string('receipt_id', 255)->nullable();
            $table->enum('approved', ['0','1'])->nullable()->comment('0=>unapproved, 1=>approved');
            $table->enum('language', ['1','2'])->default('1')->comment("1=>english, 2=>french");
            $table->enum('pure_veg', ['0','1'])->default('0')->comment("0=>not pure veg, 1=>pure veg");
            $table->enum('pickup', ['0','1'])->default('0')->comment("0=>pick up not available, 1=>pickup available");
            $table->integer('preparing_time')->nullable();
            $table->enum('table_booking', ['0','1'])->default('0')->comment("0=>table booking not available, 1=>table booking available");
            $table->integer('no_of_seats')->nullable()->comment("total number of seats available at restaurant");
            $table->enum('notification', ['0','1'])->default('1')->comment("0=>off, 1=>on");
            $table->enum('status', ['0','1'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
