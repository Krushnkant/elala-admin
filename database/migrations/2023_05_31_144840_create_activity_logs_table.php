<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->text('old_data')->nullable();
            $table->text('new_data')->nullable();
            $table->integer('type')->nullable()->comment('1 = Proflie, 2 = Experience,3=order');
            $table->string('action')->nullable()->comment('1=insert,2=update,3=delete');
            $table->integer('item_id')->nullable();
            $table->integer('user_id')->nullable();
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
        Schema::dropIfExists('activity_logs');
    }
}
