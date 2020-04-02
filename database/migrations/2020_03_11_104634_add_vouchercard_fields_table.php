<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVouchercardFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('voucher_card', function (Blueprint $table) {               
           $table->enum('status', ['0', '1'])->default('1')->comment("0: Disable, 1: Enable");
           $table->integer('amount')->nullable(); 

   
        });
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
