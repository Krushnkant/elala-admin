<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('experience_id')->nullable();
            $table->dateTime('booking_date')->nullable();
            $table->integer('schedule_time_id')->nullable();
            $table->integer('total_member')->nullable();
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
        Schema::dropIfExists('order_slots');
    }
}
