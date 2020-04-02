<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatHeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_heads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ticket_id');
            $table->integer('issue_id');
            $table->integer('subissue_id');
            $table->integer('sender_id');
            $table->integer('reciever_id');
            $table->text('last_message');
            $table->enum('message_type', ['1','2',])->comment("1:chat,2:image");    
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
        Schema::dropIfExists('chat_heads');
    }
}
