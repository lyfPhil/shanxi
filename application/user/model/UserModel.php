<?php

namespace app\user\model;

use think\Db;
use think\Model;
use think\Config;
use app\main\model\CustomerModel;
use app\user\model\ThirdUserModel;
use app\user\service\LoginService;
use app\main\service\UploadService;
use app\v1\service\FormatService;
use app\main\model\PrevidModel;
class UserModel extends Model
{
    protected $name = "sx_user";
    protected $connection = 'db.user';
    protected $dateFormat = false;

    public static function self(){
        return new self();
    }

    private function setSession($data)
    {
        if (isset($data['ext_attr']) && !empty($data['ext_attr']))
        {
            if (is_string($data['ext_attr']))
            {
                $data['ext_attr'] = json_decode($data['ext_attr'], true);
            }
        }
        unset($data['password']);
        session('user', $data);
    }
    /*
     * 通过手机号码登录
     */
    public function doMobile($user)
    {
        $result = $this->where('mobile',$user['username'])->find();
        if (!empty($result)) {
            if($result['status']==0){
                return 900309;
            }
            $pass = make_password($user['password'], $result['uniq_id']);
            if ($pass === $result['password']) {
                $customer_model = CustomerModel::self();
                $is_seller = $customer_model->getOneUserInfo($result['id'], 'seller_status,vid');
                $result['vid'] = $is_seller['vid'];
                $result['is_seller'] = $is_seller['seller_status'];
                $this->setSession($result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => intval(get_client_ip(1, true)),
                ];
                $this->where('id', $result["id"])->update($data);
                return 0;
            }
            return 900301;
        }
        return 900301;
    }
    /*
     * 通过邮箱登录
     */
    public function doEmail($user)
    {
        $result = $this->where('email', $user['username'])->find();

        if (!empty($result)) {
            if($result['status']==0){
                return 900309;
            }
            $pass = make_password($user['password'], $result['uniq_id']);
            if ($pass === $result['password']) {
                $customer_model = CustomerModel::self();
                $is_seller = $customer_model->getOneUserInfo($result['id'], 'seller_status,vid');
                $result['is_seller'] = $is_seller['seller_status'];
                $result['vid'] = $is_seller['vid'];
                $this->setSession($result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => intval(get_client_ip(1, true)),
                ];
                $this->where('id', $result["id"])->update($data);
                return 0;
            }
            return 900301;
        }
        return 900301;
    }
    /*
     * 通过邮箱注册
     */
    public function registerEmail($user)
    {
        $result    = $this->where('email', $user['email'])->find();
        if (empty($result)) {
            $user_uniq = uniqid($user['email']);
            $ip=intval(get_client_ip(1, true));
            $now = time();
            $config_avatar = Config::get('icon.avatar');
            $avatar = $config_avatar[rand(0,count($config_avatar) - 1)];
            $data   = [
                'username'      => $user['email'],
                'email'         => $user['email'],
                'mobile'        => '',
                'nickname'      => $user['email'],
                'avatar'        => $avatar,
                'uniq_id'       => $user_uniq,
                'password'      => make_password($user['password'], $user_uniq),
                'reg_ip'        => $ip,
                'reg_time'      => $now,
                'last_login_ip' => $ip,
                'last_login_time'=> $now,
                'status'        => 20,//邮箱未激活状态
                "type"          => 2,
            ];
            $userId = $this->insertGetId($data);
            $data   = $this->where('id', $userId)->find();
            $this->setSession($data);
            return 0;
        }
        return 900503;
    }
    /*
     * 通过手机号码注册
     */
    public function registerMobile($user)
    {
        $resultm = $this->where('mobile', $user['mobile'])->find();
        $resulte = $this->where('email', $user['email'])->find();
        if (empty($resultm) && empty($resulte)) {
            $user_uniq = uniqid($user['mobile']);
            $ip=intval(get_client_ip(1, true));
            $config_avatar = Config::get('icon.avatar');
            $avatar = $config_avatar[rand(0,count($config_avatar) - 1)];
            $now = time();
            $data   = [
                'username'      => $user['username'],
                'email'         => '',
                'mobile'        => $user['mobile'],
                'country_code'  => $user['country_code'],
                'nickname'      => $user['username'],
                'avatar'       => $avatar,
                'uniq_id'       => $user_uniq,
                'password'      => make_password($user['password'], $user_uniq),
                'reg_ip'        => $ip,
                'reg_time'      => $now,
                'last_login_ip' => $ip,
                'last_login_time'=> $now,
                'status'        => 1,//手机注册
                "type"          => 2,
            ];
            $userId = $this->insertGetId($data);
            // $data   = $this->where('id', $userId)->find();
            // $this->setSession($data);
            return $userId;
        }
        return '注册失败,手机号或邮箱已存在';
    }

    /*
     * 第三方登录
     */
    public function thirdPartyUserLogin($third_user){
        $result = $this->where('id',$third_user['user_id'])->find();
        $customer_model = CustomerModel::self();
        $is_seller = $customer_model->getOneUserInfo($third_user['user_id'], 'seller_status,vid');
        $result['is_seller'] = $is_seller['seller_status'];
        $result['vid'] = $is_seller['vid'];
        $this->setSession($result);
        $time = time();
        $last_login_time = intval(get_client_ip(1, true));
        $update_user = [
            'last_login_time' => $time,
            'last_login_ip'   => $last_login_time,
        ];
        $update_third = [
            'last_login_time' => $time,
            'last_login_ip'   => $last_login_time,
            'login_times'     => $third_user['login_times'] + 1,
        ];
        try{
            $this->where('id',$third_user['user_id'])->update($update_user);
            $third_user_model = ThirdUserModel::self();
            $third_user_model->where(['id' => $third_user['id']])->update($update_third);
            $this->commit();
            return 0;
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }

    /*
     * 第三方有邮箱的注册
     */
    public function thirdPartyUserEmailReg($third_user){
        $ip = intval(get_client_ip(1, true));
        $time = time();
        $config_avatar = Config::get('icon.avatar');
        $avatar = $config_avatar[rand(0,count($config_avatar) - 1)];
        $user = [
            'username' => $third_user['email'],
            'nickname' => $third_user['email'],
            'email'    => $third_user['email'],
            'avatar'   => $avatar,
            'mobile'   => '',
            'uniq_id'  => uniqid($third_user['email']),
            'password' => '',
            'reg_ip'   => $ip,
            'reg_time' => $time,
            'last_login_ip' => $ip,
            'last_login_time' => $time,
            'status'   => 21,
            'type'     => 2,
        ];
        $customer_model = CustomerModel::self();
        $customer_model->startTrans();
        $this->startTrans();
        try{
            $user_id = $this->insertGetId($user);
            $third_user_model = ThirdUserModel::self();
            $user['third_id'] = $third_user['third_id'];//用户第三方唯一标示码
            $user['id'] = $user_id;
            $user['third_type'] = $third_user['third_type'];
            $third_id = $third_user_model->thirdPartyUserReg($user_id, $user);
            $vid = $customer_model->initUser($user_id, $user);
            $this->commit();
            $customer_model->commit();
            //注册im要放到commit后面
            LoginService::regIminfo($user_id, $user['username']);
            //如果要ext_attr字段的话，要重新查表
            $user = $this->where('id', $user_id)->find();
            $user['is_seller'] = 0;
            $user['vid'] = $vid;
            $this->setSession($user);
            return 0;
        } catch (Exception $ex) {
            $this->rollback();
            $customer_model->rollback();
            throw Exception($ex);
        }
    }
    /*
     *第三方获取不到邮箱的注册
     */
    public function thirdNoEmailReg($third_user){
        $count = $this->where('email',$third_user['email'])->count();
        if ($count >= 1) {
            return 900503;
        }
        $ip = intval(get_client_ip(1, true));
        $time = time();
        $config_avatar = Config::get('icon.avatar');
        $avatar = $config_avatar[rand(0,count($config_avatar) - 1)];
        $user = [
            'username' => $third_user['email'],
            'nickname' => $third_user['email'],
            'email'    => $third_user['email'],
            'avatar'   => $avatar,
            'mobile'   => '',
            'uniq_id'  => uniqid($third_user['email']),
            'password' => '',
            'reg_ip'   => $ip,
            'reg_time' => $time,
            'last_login_ip' => $ip,
            'last_login_time' => $time,
            'status'   => 20,
            'type'     => 2,
        ];
        $customer_model = CustomerModel::self();
        $customer_model->startTrans();
        $this->startTrans();
        try{
            $user_id = $this->insertGetId($user);
            $third_user_model = ThirdUserModel::self();
            $user['third_id'] = $third_user['third_id'];
            $user['third_type'] = $third_user['third_type'];
            $third_user_model->thirdPartyUserReg($user_id, $user);
            $vid = $customer_model->initUser($user_id,$user);
            $this->commit();
            $customer_model->commit();
            //注册im要放到commit后面
            LoginService::regIminfo($user_id, $user['username']);
            //如果要ext_attr字段的话，要重新查表
            $user = $this->where('id', $user_id)->find();
            $user['is_seller'] = 0;
            $user['vid'] = $vid;
            $this->setSession($user);
            return 0;
        } catch (Exception $ex) {
            $this->rollback();
            $customer_model->rollback();
            throw Exception($ex);
        }

    }
    /**
     * 本身第三方邮箱已经注册了账号
     */
    public function emailIsExsit($third_user){
        $count = $this->where('email', $third_user['email'])->count();
        if ($count >= 1) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * 通过邮箱重置密码
     * @param $email
     * @param $password
     * @return int
     */
    public function emailPasswordReset($email, $password)
    {
        $result = $this->where('email', $email)->find();
        if (!empty($result)) {
            $user_uniq = uniqid($email);
            $data = [
                'uniq_id'   => $user_uniq,
                'password'       => make_password($password, $user_uniq),
            ];
            $this->where('email', $email)->update($data);
            return 0;
        }
        return 1;
    }

    /**
     * 通过手机重置密码
     * @param $mobile
     * @param $password
     * @return int
     */
    public function mobilePasswordReset($mobile, $password)
    {
        $result    = $this->where('mobile', $mobile)->find();
        if (!empty($result)) {
            $user_uniq = uniqid($mobile);
            $data = [
                'uniq_id'   => $user_uniq,
                'password'       => make_password($password, $user_uniq),
            ];
            $this->where('mobile', $mobile)->update($data);
            return 0;
        }
        return 1;
    }

    /**
     * 用户密码修改
     * @param $user
     * @return int
     */
    public function editPassword($password, $userId)
    {
        $user = $this->where('id', $userId)->find();
        if ($password['password']!=$password['repassword']) {
            return 400;
        }

        $data['password'] = make_password($password['password'], $user['uniq_id']);
        $ret   = $this->where('id', $userId)->setField('password',$data['password']);
        if($ret==0){
            return 900209;
        } else {
            return 0;
        }
    }

    public function updateUser($data,$user_id=0){
        if($user_id){
            if (isset($data['ext_attr']) && is_array($data['ext_attr']))
            {
                $data['ext_attr'] = json_encode($data['ext_attr']);
            }
            $ret = $this->where('id',$user_id)->update($data);
            if ($ret==1) {
                return 0;
            } else {
                return false;
            }
        }
        return false;
    }

    public function addAttr($attr, $user_id, $nosession=false){
        if ($nosession) {
            $insession = false;
            $userInfo = $this->where('id', $user_id)->find();
        } else {
            $userInfo = user_info();
        }

        $ext_attr = json_decode($userInfo['ext_attr'], true);
        if (is_null($ext_attr)) {
            $userInfo['ext_attr'] = [];
        }
        foreach ($attr as $k => $v) {
            $ext_attr[$k] = $v;
        }
        $data['ext_attr'] = $ext_attr;
        if (!$nosession) {
            $this->setSession($userInfo);
        }
        $this->updateUser($data, $user_id);
    }
    /*
     * 判断手机是否唯一
     */
    public function mobielIsUnique($account)
    {
        $or = '0'.ltrim($account['username'],'0');
        $result = $this->where("(mobile=:mobile or mobile=:or) and country_code=:country_code ")
                ->bind(['mobile'=>$account['username'],'or'=>$or,'country_code'=>$account['country_code']])
                ->count();
        if ($result==0) {
            return true;
        }
        return 900403;
    }

    public function regAdmin($user)
    {
        $result = $this->where('username', $user['username'])->find();
        $ip=intval(get_client_ip(1, true));
        $now = time();
        if (empty($result)) {
            $user_uniq = uniqid($user['mobile']);
            $config_avatar = Config::get('icon.avatar');
            $avatar = $config_avatar[rand(0,count($config_avatar) - 1)];
            $data   = [
                'id'            => $user['id'],
                'username'      => $user['username'],
                'email'         => $user['email'],
                'mobile'        => $user['mobile'],
                'country_code'  => $user['country_code'],
                'nickname'      => $user['username'],
                'avatar'       => $avatar,
                'uniq_id'       => $user_uniq,
                'password'      => make_password(strtoupper(sha1($user['password'])), $user_uniq),
                'reg_ip'        => $ip,
                'reg_time'      => $now,
                'last_login_ip' => $ip,
                'last_login_time'=> $now,
                'status'        => 1,
                "type"          => 1,
            ];
            $userId = $this->insertGetId($data);
            return $userId;
        }
        else{
            $map = [
                'type'=>1,
                'status'=>1,
                'update_time'=>$now,
            ];
            if ($user['password'])$map['password'] = make_password(strtoupper(sha1($user['password'])), $result['uniq_id']);
//            if ($user['mobile']) $map['mobile'] = $user['mobile'];
//            if ($user['email']) $map['email'] = $user['email'];
//            if ($user['country_code']) $map['country_code'] = $user['country_code'];
            $this->updateUser($map, $result['id']);
            $result['type'] = 1;
            return $result['id'];
        }
    }

    public function getUserByIds($uids){
        $it = $this->field('id, username, nickname, mobile, country_code, email, avatar, ext_attr')
            ->where('id', 'in', $uids)
            ->where('status','<>',-1)
            ->select();
        $ret = [];
        foreach ($it as $item) {
            $data  = $item->getData();
            if (is_string($data['ext_attr']))
            {
                $data['ext_attr'] = json_decode($data['ext_attr'], true);
            }
            $ret[$item['id']] = $data;
        }
        return $ret;
    }
    /*
     * 获取用户或系统头像和昵称
     */
    public function getImAvatar($userIds){
        $system_id = [];
        $user_id   = [];
        $mix_id    = [];
        $temp = explode(',',$userIds);
        foreach ($temp as $val) {
            if ($val != '' && is_numeric($val) ) {
                if ($val<100000) {
                    $system_id[] = $val;
                } else {
                    $user_id[] = $val;
                }
                $mix_id[] = $val;
            }
        }
        $mix_id = array_unique($mix_id);
        $system_user = [];
        $users = [];
        if (count($system_id) > 0) {
            $system_user_model = \app\admin\model\UserModel::self();
            $field = 'id,avatar,username as nickname';
            $system_user = $system_user_model->getSystemInfoKeyValue($system_id, $field);
        }
        if (count($user_id) > 0) {
            $field = 'id,avatar,nickname';
            $users = $this->getUserInfoKeyValue($user_id, $field);
        }
        $mix_temp = $users + $system_user;//
        $mix_info = [];
        $is_exist = array_column($mix_temp, 'id');
        foreach ($mix_id as $val) {
            if (in_array($val, $is_exist)) {
                $mix_info[] = $mix_temp[$val];
            }
        }
        return $mix_info;
    }
    /*
     * 获取用户信息 以用户id为键 用户信息为值
    */
    private function getUserInfoKeyValue($user_id,$field='*'){
        $user = $this->where(['id'=>['in',$user_id]])->field($field)->select();
        $user_list = [];
        foreach ($user as $val) {
            $user_list[$val['id']] = $val;
        }
        return $user_list;
    }

    public function getUserInfo($userId){
        return $this->where('id', $userId)->cache(600)->find()->getData();
    }
    /**
     * 获取用户列表,除了管理员外 后台使用
     * @param array $map 筛选条件
     * @param string $order 排序条件
     * @param int $page_size 分页大小
     * @param array $params 额外参数
     * @return object
     */
    public function getUserList($map, $order = ['id'=>'desc'], $page_size, $params = [])
    {
        $it = $this->alias('a')
            ->field('a.*,b.vid,b.balance,b.lock_balance,b.idcard,b.buyer_reputation,b.seller_reputation,b.deposit')
            ->join('pp_jy.tab_user b','a.id=b.id','LEFT')
            ->where($map)
            ->order($order)
            ->paginate($page_size,false,[
                'type'     => 'bootstrap',
                'var_page' => 'page',
                'query'    =>$params,
            ]);
        return $it;
    }
    /**
     * 根据用户id获取用户信息
     * @param int $uid 用户id号
     * @return array 资源数组
     */
    public function getUserDetailsById($uid)
    {
        $map = ['id'=>$uid];
        return $this->where($map)->find();
    }
     public function remove($id)
    {
        $cm = CustomerModel::self();
        $cm->startTrans();
        $this->startTrans();
        if (is_array($id)) {
            $map['id'] = array('in',$ids);
        } else {
            $map['id'] = $id;
        }
        try {
            $this->where($map)->update(['status'=>0]);
            $cm->where($map)->update(['lock_status'=>0]);
            $this->commit();
            $cm->commit();
        } catch (Exception $ex) {
            $this->rollback();
            $cm->rollback();
            return false;
        }
        return true;
    }
    /**
     * userIsExsit 判断用户是否存在
     * @param  type $username 用户
     */
    public function userIsExist($username,$where = []){
        $user = $this->where("username=:username or mobile=:mobile or email=:email" )
                     ->where($where)
                     ->bind(['username'=>$username,'mobile'=>$username,'email'=>$username])
                     ->find();
        return $user;
    }
}
