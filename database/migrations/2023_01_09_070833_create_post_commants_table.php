<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostCommantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_commants', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id');
            $table->integer('user_id');
            $table->integer('post_id');
            $table->text('commant')->collation('utf8_general_ci')->nullable();
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
        Schema::dropIfExists('post_commants');
    }
}
