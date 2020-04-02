<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemSubCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_sub_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('item_cat_id');
            $table->string('name', 255);
            $table->string('french_name', 255);
            $table->decimal('add_on_price', 10,2);
            $table->enum('status', ['0','1'])->default('1')->comment("0=>disable,1=>enable");
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
        Schema::dropIfExists('item_sub_categories');
    }
}
