<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_issues', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('issue_id');
            $table->string('name', 255)->nullable();
            $table->string('french_name', 255)->nullable();
            $table->enum('status', ['0', '1'])->default('1')->comment("0:disable, 1:enable");
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
        Schema::dropIfExists('sub_issues');
    }
}
