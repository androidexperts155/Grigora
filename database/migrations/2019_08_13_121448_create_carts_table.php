<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('restaurant_id');
            $table->integer('quantity');
            $table->decimal('total_price', 8,2);
            $table->enum('group_order', ['0', '1'])->default('0')->comment('0:normal cart, 1:group cart');
            $table->enum('cart_type', ['1', '2'])->comment('1:delivery cart, 2:pickup');
            $table->string('share_link', 255)->nullable();
            $table->integer('max_per_person')->nullable();
            $table->enum('status', ['0', '1'])->default('1')->comment("0:disable, 1:not disable");
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
        Schema::dropIfExists('carts');
    }
}
