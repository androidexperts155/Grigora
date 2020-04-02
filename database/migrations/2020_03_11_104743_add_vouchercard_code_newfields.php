<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVouchercardCodeNewfields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('voucher_card_codes', function (Blueprint $table) {           
            $table->enum('valid', ['0', '1'])->default('1')->comment("0: Not valid, 1: Valid");     
            $table->integer('amount');              
   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('voucher_card_codes', function (Blueprint $table) {
                       
                      
   
        });
    }
}
