<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('order_id')->nullable();
            $table->string('transaction_data')->nullable();
            $table->string('reference')->nullable();
            $table->string('amount');
            $table->string('reason')->nullable();
            $table->enum('type', ['1', '2', '3', '4','5','6'])->comment("1=>payout, 2=>transaction, 3=>add in grigora wallet,4=>deduct from grigora wallet,5:send money,6:receive money");
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
        Schema::dropIfExists('transactions');
    }
}
