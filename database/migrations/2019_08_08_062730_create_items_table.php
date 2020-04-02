<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('restaurant_id');
            $table->integer('parent_cuisine_id')->nullable();
            $table->integer('cuisine_id');
            $table->string('name', 255);
            $table->string('french_name', 255);
            $table->text('description')->nullable();
            $table->text('french_description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('offer_price', 8, 2);
            $table->string('approx_prep_time', 255)->nullable();
            $table->enum('in_offer', ['0','1'])->comment('0=> not in offer, 1=> in offer')->default('0');
            $table->enum('pure_veg', ['0','1', '2'])->default('0')->comment("0=>not pure veg, 1=>pure veg, 2=>contains egg");
            $table->enum('approved', ['0','1'])->default('0')->comment("0:not approved 1: approved by admin");
            $table->enum('featured', ['0','1'])->default('0')->comment("0:not marked 1: restaurant marked item as featured.");
            $table->enum('status', ['0','1']);
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
        Schema::dropIfExists('items');
    }
}
