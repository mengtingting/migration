<?php
/**
 * Created by PhpStorm.
 * User: mtt17
 * Date: 2018/7/4
 * Time: 10:27
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Model
{
    use SoftDeletes;    //使用软删除
    protected $table = 'users';
    public $timestamps = true;
    protected $dates = ['deleted_at'];  //软删除
}