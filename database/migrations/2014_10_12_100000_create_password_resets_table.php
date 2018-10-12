<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();
            $table->string('token');
            $table->string('nick_name');
            $table->string('a');
            $table->timestamp('created_at')->nullable();
        });

//        Schema::table('users', function (Blueprint $table) {
//            $table->string('nick_name');
//        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     *
     * 回滚整个表
     */
    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
}
