<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilterListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filter_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->string('french_title', 255);
            $table->enum('ui_type', ['1', '2', '3', '4'])->comment('1:large, 2:small, 3:round, 4:list');
            $table->enum('show_all', ['0', '1'])->comment('0: not all, 1: show all');
            $table->enum('status', ['0', '1'])->default('1')->comment('0:disable, 1:enable');
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
        Schema::dropIfExists('filter_lists');
    }
}
