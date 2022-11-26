<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('custom_orderid',150)->nullable()->comment('YYMMDD0001');
            $table->integer('user_id')->nullable();
            $table->integer('experience_id')->nullable();
            $table->dateTime('booking_date')->nullable();
            $table->integer('schedule_time_id')->nullable();
            $table->integer('adults')->nullable();
            $table->integer('children')->nullable();
            $table->integer('infants')->nullable();
            $table->integer('total_member')->nullable();
            $table->float('adults_amount')->nullable();
            $table->float('children_amount')->nullable();
            $table->float('infants_amount')->nullable();
            $table->float('total_amount')->nullable();
            $table->integer('payment_type')->nullable()->comment('1->Prepaid, 2->COD');
            $table->longText('payment_transaction_id')->nullable();
            $table->longText('payment_currency')->nullable();
            $table->longText('gateway_name')->nullable();
            $table->longText('payment_mode')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->integer('payment_status')->nullable()->comment('1->Pending, 2->Success, 3->Cancelled, 4->Failed');
            $table->integer('is_show')->default(0)->comment('0->Not Show Recode,2->Show Recode');
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
        Schema::dropIfExists('orders');
    }
}
