<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('host_id');
            $table->float('total_amt');
            $table->date('payment_date');
            $table->datetime('release_date');
            $table->boolean('payment_status')->default(0)->comment('0->Pending,1->Completed');
            $table->boolean('estatus')->default(1)->comment('0->Deactive,1->Active,2->Delete');
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
        Schema::dropIfExists('supplier_payments');
    }
}
