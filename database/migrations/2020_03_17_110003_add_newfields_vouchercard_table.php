<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewfieldsVouchercardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('voucher_card', function (Blueprint $table) {                
            $table->string('amount_french_name');     
            $table->string('amount_english_name');    

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('voucher_card', function (Blueprint $table) {                
            
        });
    }
}
