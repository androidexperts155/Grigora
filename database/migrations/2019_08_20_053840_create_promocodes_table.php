<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promocodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('french_name')->nullable();
            $table->text('description')->nullable();
            $table->text('french_description')->nullable();
            $table->string('code')->nullable();
            $table->float('percentage', 8, 2)->nullable();
            $table->integer('no_of_attempts')->nullable();
            $table->integer('min_order_value')->nullable();
            $table->enum('status', ['0', '1'])->default('1');
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
        Schema::dropIfExists('promocodes');
    }
}
