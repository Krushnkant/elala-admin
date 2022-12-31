<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('receiver_id')->nullable();
            $table->longText('message_text')->nullable();
            $table->enum('type', ['text', 'image', 'video', 'content', 'audio', 'document', 'contact', 'location', 'lbl'])->nullable();
            $table->enum('is_deleted', [0, 1])->default(0);
            $table->integer('deleted_by')->nullable();
            $table->enum('tick', [0, 1, 2])->default(0);
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
        Schema::dropIfExists('chat');
    }
}
