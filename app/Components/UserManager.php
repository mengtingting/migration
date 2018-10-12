<?php
/**
 * Created by PhpStorm.
 * User: mtt17
 * Date: 2018/7/4
 * Time: 10:24
 */

namespace App\Components;


use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserManager
{
    /*
     * 根据id获取用户信息，带token
     *
     * By mtt
     *
     * 2018-07-04
     */
    public static function getByIdWithToken($id)
    {
//        dd($id);
        $user = User::where('id', '=', $id)->first();
//        dd($user);
        return $user;
    }

    /*
     * 根据id获取用户信息，不带敏感信息
     *
     * By Amy
     *
     * 2018-07-10
     */
    public static function getByIdWithOutToken($id)
    {
        $user = User::find($id);
        if ($user) {
            $user=self::RemoveSensitiveInformation($user);
        }
        return $user;
    }

    /*
     * 根据vip_eth_address获取用户信息，不带敏感信息
     *
     * By Amy
     *
     * 2018-07-30
     */
    public static function getByVipEthAddressWithOutToken($vip_eth_address)
    {
        $user = User::where('vip_eth_address', $vip_eth_address)->first();
        if ($user) {
            $user=self::RemoveSensitiveInformation($user);
        }
        return $user;
    }

    /*
     * 根据recharge_eth_address获取用户信息，不带敏感信息
     *
     * By Amy
     *
     * 2018-07-30
     */
    public static function getByRechargeEthAddressWithOutToken($recharge_eth_address)
    {
        $user = User::where('recharge_eth_address', $recharge_eth_address)->first();
        if ($user) {
            $user=self::RemoveSensitiveInformation($user);
        }
        return $user;
    }

    /*
     * 根据address获取用户信息，不带敏感信息，并返回类型
     *
     * by Amy
     *
     * 2018-10-08
     */
    public static function getByEthAddressWithOutToken($eth_address){
        $user = User::where('vip_eth_address', $eth_address)->first();
        if($user){
            $user['recharge_method']=1;
        }
        else{
            $user = User::where('recharge_eth_address', $eth_address)->first();
            if($user){
                $user['recharge_method']=2;
            }
        }
        if($user){
            $user=self::RemoveSensitiveInformation($user);
        }
        return $user;
    }

    /*
    * 根据id获取用户信息
    *
    * By mtt
    *
    * 2018-07-06
    */
    public static function getById($id)
    {
        $user = self::getByIdWithToken($id);
        if ($user) {
            $user=self::RemoveSensitiveInformation($user);
        }
        return $user;
    }

    //根据条件检索数据
    /*
      * By mtt
      *
      * 2018-07-04
     */
    public static function getListByCon($con_arr, $is_paginate)
    {
        $users = new User();
        if (array_key_exists('type', $con_arr) && !Utils::isObjNull($con_arr['type'])) {
            $users = $users->where('type', '=', $con_arr['type']);
        }
        if (array_key_exists('status', $con_arr) && is_numeric($con_arr['status'])) {
            $users = $users->where('status', '=', $con_arr['status']);
        }
        if (array_key_exists('search_word', $con_arr) && !Utils::isObjNull($con_arr['search_word'])) {
            $users = $users->where('nick_name', 'like', '%' . $con_arr['search_word'] . '%')
                ->orwhere('real_name', 'like', '%' . $con_arr['search_word'] . '%')
                ->orwhere('phonenum', 'like', '%' . $con_arr['search_word'] . '%');
        }
        if (array_key_exists('invite_code', $con_arr) && !Utils::isObjNull($con_arr['invite_code'])) {
            $users = $users->where('invite_code', '=', $con_arr['invite_code']);
        }
        if (array_key_exists('level_invite_code', $con_arr) && !Utils::isObjNull($con_arr['level_invite_code'])) {
            $users = $users->where('level_invite_code', '=', $con_arr['level_invite_code']);
        }
        if (array_key_exists('nick_name', $con_arr) && !Utils::isObjNull($con_arr['nick_name'])) {
            $users = $users->where('nick_name', '=', $con_arr['nick_name']);
        }
        if (array_key_exists('phonenum', $con_arr) && !Utils::isObjNull($con_arr['phonenum'])) {
            $users = $users->where('phonenum', '=', $con_arr['phonenum']);
        }
        if (array_key_exists('phonenum_prefix', $con_arr) && !Utils::isObjNull($con_arr['phonenum_prefix'])) {
            $users = $users->where('phonenum_prefix', '=', $con_arr['phonenum_prefix']);
        }
        if (array_key_exists('is_big', $con_arr) && is_numeric($con_arr['is_big'])) {
            $users = $users->where('is_big', '=', $con_arr['is_big']);
        }
        if (array_key_exists('is_recommend', $con_arr) && is_numeric($con_arr['is_recommend'])) {
            if($con_arr['is_recommend']>0){
                $users = $users->where('recommend', '>',0);
            }
            else{
                $users = $users->where('recommend', 0);
            }
        }
        $users = $users->orderby('id', 'desc');
        if ($is_paginate) {
            $users = $users->paginate(Utils::PAGE_SIZE);
        } else {
            $users = $users->get();
        }
        $users=self::RemoveSensitiveInformation($users);
        return $users;
    }

    public static function getInfoByLevel($info, $level)
    {
        if (strpos($level, '0') !== false) {
            $con_arr = array(
                'user_id' => $info->id,
                'created_at' => DateTool::dateAdd('D', -1, DateTool::getToday(), 'Y-m-d'),
            );
            $info->yesterday_honor = TodayHonorManager::getListByCon($con_arr, true)->first();
        }
        return $info;
    }

    /**
     * 根据用户级别查询
     *
     * By Amy
     *
     * @param  $level 用户等级范围（false：全部输出/非false 为数组，等级范围）
     * @param  $is_rand  是否随机获取（false/true）
     * @param  $take  输出条数（false：全部输出/非false 为数字，输出几条）
     * @param  $not_id  排除此id（false：无排除对象/非false 为数组，排除的id）
     * @param  $language  语言（空：无语言限制/非空 语言）
     *
     * 2018-07-07
     *
     */
    public static function getListsByLevel($level = false, $is_rand = false, $take = false, $not_id = false, $language = '')
    {
        $lists = User::where('status', 'like', '%%');
        if ($level != false) {
            $lists = $lists->whereBetween('level', $level);
        }
        if ($not_id) {
            $lists = $lists->whereNotIn('id', $not_id);
        }
        if ($language) {
            $lists = $lists->orderBy(DB::raw("if(language='" . $language . "' ,1,0)"), 'desc');
        }
        if ($is_rand != false) {
            $lists = $lists->orderBy(DB::raw('RAND()'));
        }
        if ($take != false) {
            if ($take == 1) {
                $lists = $lists->first();
            } else {
                $lists = $lists->take($take);
                $lists = $lists->get();
            }
        } else {
            $lists = $lists->get();
        }
        $lists=self::RemoveSensitiveInformation($lists);
        return $lists;
    }

    /**
     * 获取推荐用户
     *
     * By Amy
     *
     * @param  $recommend 是否是推荐（false：否/true 是）
     * @param  $take  输出条数（false：全部输出/非false 为数字，输出几条）
     * @param  $not_id  排除此id（false：无排除对象/非false 为数组，排除的id）
     * @param  $language  语言（空：无语言限制/非空 语言）
     * @param  $seq  排序（1：按id倒序排序；2：按id顺序排序；3：按recommend倒序排序；其他：随机）
     *
     * 2018-07-07
     *
     */
    public static function getListsByRecommend($recommend = false, $take = false, $not_id = false, $language = '',$seq='')
    {
        $lists = User::where('status', 'like', '%%');
        if ($not_id) {
            $lists = $lists->whereNotIn('id', $not_id);
        }
        if ($recommend == false) {
            $lists = $lists->where('recommend', 0);
        } else {
            $lists = $lists->where('recommend', '<>', 0);
        }
        if ($language) {
            $lists = $lists->orderBy(DB::raw("if(language='" . $language . "' ,1,0)"), 'desc');
        }
        //排序
        if($seq==1){
            $lists = $lists->orderby('id', 'desc');
        }
        else if($seq==2){
            $lists = $lists->orderby('id', 'asc');
        }
        else if($seq==3){
            $lists = $lists->orderby('recommend', 'desc');
        }
        else{
            $lists=$lists->orderBy(DB::raw('RAND()'));
        }
        if ($take != false) {
            if ($take == 1) {
                $lists = $lists->first();
            } else {
                $lists = $lists->take($take);
                $lists = $lists->get();
            }
        } else {
            $lists = $lists->get();
        }
        $lists=self::RemoveSensitiveInformation($lists);
        return $lists;
    }


    /**
     * 根据用户获取推荐用户
     *
     * By Amy
     *
     * @param  $user_id 用户id
     * @param  $is_rand  是否随机获取（false/true）
     * @param  $take  输出条数（false：全部输出/非false 为数字，输出几条）
     * @param  $language  语言（空：无语言限制/非空 语言）
     *
     * 2018-07-09
     *
     */
    public static function getListsByUserForRecommends($user_id, $is_rand = false, $take = false, $language = '')
    {
        //获取该用户已关注的项目
        $follow_project_con_arr = array(
            'user_id' => $user_id,
            'status' => 1,
            'f_table' => Utils::PROJECT_TABLE
        );
        $follow_project_lists = FollowManager::getListByCon($follow_project_con_arr, false);
        //整合出已关注项目的集合
        $follow_project_ids = array();
        foreach ($follow_project_lists as $follow_project_list) {
            array_push($follow_project_ids, $follow_project_list['f_id']);
        }
        //获取该用户已关注的用户
        $follow_user_con_arr = array(
            'user_id' => $user_id,
            'status' => 1,
            'f_table' => Utils::USER_TABLE
        );
        $follow_user_lists = FollowManager::getListByCon($follow_user_con_arr, false);
        //排除已关注的用户和自己
        $not_user_ids = array();
        array_push($not_user_ids, (int)$user_id);
        foreach ($follow_user_lists as $follow_user_list) {
            array_push($not_user_ids, $follow_user_list['f_id']);
        }
        $lists = User::leftJoin('t_follow_info', 't_follow_info.user_id', '=', 't_user_info.id')
            ->select(DB::raw('t_user_info.* ,count(*) as common_follow_count'))
            ->whereNotIn('t_user_info.id', $not_user_ids)
            ->whereIn('t_follow_info.f_id', $follow_project_ids)
            ->groupBy('t_follow_info.user_id')
            ->having('common_follow_count', '>', '3');
        if ($language) {
            $lists = $lists->orderBy(DB::raw("if(language='" . $language . "' ,1,0)"), 'desc');
        }
        if ($is_rand != false) {
            $lists = $lists->orderBy(DB::raw('RAND()'));
        }
        if ($take != false) {
            if ($take == 1) {
                $lists = $lists->first();
            } else {
                $lists = $lists->take($take);
                $lists = $lists->get();
            }
        } else {
            $lists = $lists->get();
        }
        $lists=self::RemoveSensitiveInformation($lists);
        return $lists;
    }

    /*
     * 配置用户信息，用于更新用户信息和新建用户信息
     *
     * By mtt
    *
    * 2018-07-06
     *
     */
    public static function setInfo($info, $data)
    {
        if (array_key_exists('real_name', $data)) {
            $info->real_name = array_get($data, 'real_name');
        }
        if (array_key_exists('nick_name', $data)) {
            $info->nick_name = array_get($data, 'nick_name');
        }
        if (array_key_exists('password', $data)) {
            $info->password = array_get($data, 'password');
        }
        if (array_key_exists('avatar', $data)) {
            $info->avatar = array_get($data, 'avatar');
        }
        if (array_key_exists('phonenum_prefix', $data)) {
            $info->phonenum_prefix = array_get($data, 'phonenum_prefix');
        }
        if (array_key_exists('phonenum', $data)) {
            $info->phonenum = array_get($data, 'phonenum');
        }
        if (array_key_exists('xcx_openid', $data)) {
            $info->xcx_openid = array_get($data, 'xcx_openid');
        }
        if (array_key_exists('fwh_openid', $data)) {
            $info->fwh_openid = array_get($data, 'fwh_openid');
        }
        if (array_key_exists('app_openid', $data)) {
            $info->app_openid = array_get($data, 'app_openid');
        }
        if (array_key_exists('unionid', $data)) {
            $info->unionid = array_get($data, 'unionid');
        }
        if (array_key_exists('gender', $data)) {
            $info->gender = array_get($data, 'gender');
        }
        if (array_key_exists('status', $data)) {
            $info->status = array_get($data, 'status');
        }
        if (array_key_exists('token', $data)) {
            $info->token = array_get($data, 'token');
        }
        if (array_key_exists('country', $data)) {
            $info->country = array_get($data, 'country');
        }
        if (array_key_exists('province', $data)) {
            $info->province = array_get($data, 'province');
        }
        if (array_key_exists('city', $data)) {
            $info->city = array_get($data, 'city');
        }
        if (array_key_exists('honor_value', $data)) {
            $info->honor_value = array_get($data, 'honor_value');
        }
        if (array_key_exists('is_big', $data)) {
            $info->is_big = array_get($data, 'is_big');
        }
        if (array_key_exists('kntt_value', $data)) {
            $info->kntt_value = array_get($data, 'kntt_value');
        }
        if (array_key_exists('level', $data)) {
            $info->level = array_get($data, 'level');
        }
        if (array_key_exists('abstract', $data)) {
            $info->abstract = array_get($data, 'abstract');
        }
        if (array_key_exists('recommend', $data)) {
            $info->recommend = array_get($data, 'recommend');
        }
        if (array_key_exists('follow_num', $data)) {
            $info->follow_num = array_get($data, 'follow_num');
        }
        if (array_key_exists('comment_num', $data)) {
            $info->comment_num = array_get($data, 'comment_num');
        }
        if (array_key_exists('language', $data)) {
            $info->language = array_get($data, 'language');
        }
        if (array_key_exists('is_vip', $data)) {
            $info->is_vip = array_get($data, 'is_vip');
        }
        if (array_key_exists('invite_code', $data)) {
            $info->invite_code = array_get($data, 'invite_code');
        }
        if (array_key_exists('level_invite_code', $data)) {
            $info->level_invite_code = array_get($data, 'level_invite_code');
        }
        if (array_key_exists('vip_eth_address', $data)) {
            $info->vip_eth_address = array_get($data, 'vip_eth_address');
        }
        if (array_key_exists('recharge_eth_address', $data)) {
            $info->recharge_eth_address = array_get($data, 'recharge_eth_address');
        }
        if (array_key_exists('seq', $data)) {
            $info->seq = array_get($data, 'seq');
        }
        return $info;
    }

    /*
     * 注册用户
     *
    * By mtt
    *
    * 2018-07-06
     *
     */
    public static function register($data)
    {
        //根据区号查询国家信息
        $con_arr = array(
            'phonenum_prefix' => $data['phonenum_prefix'],//手机区号
        );
        $mobile_prefix = MobilePrefixManager::getListByCon($con_arr, false)->first();
        //创建用户信息
        $user = new User();
        $user = self::setInfo($user, $data);
        $user->token = self::getGUID();
        $user->country = $mobile_prefix['country'];
        $user->nick_name = 'KNT'.Utils::getRandomString(12);
        $user->save();
        $user = self::getByIdWithToken($user->id);
        return $user;
    }


    /*
     * 根据user_id和token校验合法性，全部插入、更新、删除类操作需要使用中间件
     *
     * By Amy
     *
     * 2018-07-09
     *
     * 返回值
     *
     */
    public static function ckeckToken($id, $token)
    {
        //根据id、token获取用户信息
        $count = User::where('id', $id)->where('token', $token)->count();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * 根据用户openid获取用户信息
     *
     * By mtt
    *
    * 2018-07-06
     */
    public static function getByXCXOpenId($openid)
    {
        $user = User::where('xcx_openid', '=', $openid)->first();
        return $user;
    }


    // 生成guid
    /*
     * 生成uuid全部用户相同，uuid即为token
     *
     */
    public static function getGUID()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));

            $uuid = substr($charid, 0, 8)
                . substr($charid, 8, 4)
                . substr($charid, 12, 4)
                . substr($charid, 16, 4)
                . substr($charid, 20, 12);
            return $uuid;
        }
    }


    /*
    * 根据phonenum获取用户信息
    *
    * By mtt
    *
    * 2018-07-06
    */
    public static function getByPhonenum($phonenum, $phonenum_prefix)
    {
        $user = User::where('phonenum', '=', $phonenum)->where('phonenum_prefix', '=', $phonenum_prefix)->first();
        return $user;
    }

    /*
    * 根据手机号码+密码进行登录
    *
    * By mtt
    *
    * 2018-07-06
    */
    public static function getByPhonenumAndPassword($phonenum, $password, $phonenum_prefix)
    {
        $user = User::where('phonenum', $phonenum)->where('password', $password)->where('phonenum_prefix', $phonenum_prefix)->first();
        return $user;
    }

    /*
    * 根据手机号和动态密码登录
    *
    * By mtt
    *
    * 2018-07-06
    */
    public static function getByPhonenumAndVertifyCode($phonenum, $vertify_code, $phonenum_prefix)
    {
        $result = SMSManager::judgeVertifyCode($phonenum, $vertify_code);
        $user = null;
        if ($result) {
            $user = self::getByPhonenum($phonenum, $phonenum_prefix);
            //如果获取到用户，则返回
            if (!$user) {
                $user = self::getByIdWithToken($user->id);
            }
        }
        return $user;
    }

    /*
     * 如果用户没有邀请码，则生成邀请码的逻辑
     *
     * By mtt
     *
     * 2018-07-17
     *
     */
    public static function generateYQCode($user_id)
    {
        $user = self::getByIdWithToken($user_id);       //需要注意带token
        //yq_code为空，则需要生成yq_code
        if (Utils::isObjNull($user->invite_code)) {
            $invite_code = null;
            for ($i = 0; $i < 1; $i) {
                $invite_code = Utils::create_invite_code(6);
                $con_arr = array(
                    'invite_code' => $invite_code
                );
                //如果该邀请码没有被使用
                if (self::getListByCon($con_arr, false)->count() == 0) {
                    break;
                }
            }
            $user->invite_code = $invite_code;
            $user->save();
        }
        return $user;
    }

    /*
     * 获取平台用户所有的代币等价于KNTT值的总和
     *
     * by Amy
     *
     * 2018-08-29
     */
    public static function sumAllKNTT()
    {
        $users = User::where('kntt_value', '>', 0)->get();
        $sum_kntt = 0;
        foreach ($users as $user) {
            $sum_kntt = bcadd($sum_kntt, $user['kntt_value'], 8);
        }
        return $sum_kntt;
    }
    /*
     * 获取平台用户kntt_value大于0的用户
     *
     * by Amy
     *
     * 2018-08-29
     */
    public static function getUserHaveKNTT()
    {
        $users = User::where('kntt_value', '>', 0)->get();
        return $users;
    }

    /*
     * 通过邀请码获取用户
     *
     * by Amy
     *
     * 2018-10-08
     *
     */
    public static function getUserByInviteCode($invite_code){
        $user=User::where('invite_code',$invite_code)->first();
        if($user){
            $user=self::RemoveSensitiveInformation($user);
        }
        return $user;
    }

    /*
     * 去掉关键敏感信息
     *
     * by Amy
     *
     * 2018-09-03
     */
    public static function RemoveSensitiveInformation($user){
        unset($user['token']);
        unset($user['password']);
        unset($user['xcx_openid']);
        unset($user['fwh_openid']);
        unset($user['app_openid']);
        unset($user['unionid']);
        unset($user['vip_eth_address']);
        unset($user['recharge_eth_address']);
        return $user;
    }
}