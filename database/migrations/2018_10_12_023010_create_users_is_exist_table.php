<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersIsExistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //判断表是否存在
        if (Schema::hasTable('users')) {
            //
            Schema::table('users', function (Blueprint $table) {
                //判断列是否存在
                if (Schema::hasColumn('users', 'email')) {
                    //
                }else{
                    $table->string('email');
                }
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     *
     * 回滚执行-此写法回滚这个字段
     */
    public function down()
    {
        Schema::table('users', function (Blueprint  $table) {
            $table->dropColumn('email');
        });
    }
}
