<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExperiencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('type')->default(0)->comment('1->offline,2->online');
            $table->string('location',255)->nullable();
            $table->string('latitude',255)->nullable();
            $table->string('longitude',255)->nullable();
            $table->integer('category_id');
            $table->string('title',255)->nullable();
            $table->text('description')->nullable();
            $table->integer('duration');
            $table->string('age_limit',255)->nullable();
            $table->integer('is_bring_item')->default(1)->comment('0->not available,1->available');
            $table->integer('is_meet_address')->default(1)->comment('1->Use Current Location,2->Add Manually');
            $table->text('meet_address')->nullable();
            $table->string('meet_address_flat_no',255)->nullable();
            $table->string('meet_city',255)->nullable();
            $table->string('meet_state',255)->nullable();
            $table->string('meet_country',255)->nullable();
            $table->string('pine_code',255)->nullable();
            $table->string('meet_latitude',255)->nullable();
            $table->string('meet_longitude',255)->nullable();
            $table->integer('max_member_public_group_size');
            $table->integer('max_member_private_group_size');
            $table->integer('individual_rate');
            $table->integer('min_private_group_rate');
            $table->integer('cancellation_policy_id');
            $table->string('proccess_page',255)->nullable();
            $table->integer('estatus')->default(1)->comment('1->Active,2->Deactive,3->Deleted,4->Pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('experiences');
    }
}
