<?php
namespace app\admin\model;

use think\Config;
use think\Model;
use think\Session;
use traits\model\SoftDelete;
use app\user\service\LoginService;
use app\main\model\CustomerModel;
use think\Env;
use think\Loader;

class UserModel extends Model
{
    use SoftDelete;
    protected $name = 'sx_admin_user';
    protected $connection = 'db.cms';
    protected $deleteTime = 'delete_time';
    protected $um = null;

    public static function self(){
        return new self();
    } 

    public function initialize(){
        parent::initialize();
        $this->um = \app\user\model\UserModel::self();
    }

    public function getById($userId)
    {
        $data=$this->get(['id'=>$userId]);
        if ($data)
        {
            return $data;
        }
        return false;
    }
    public function getUserNamesByIds($uids){
        $list = $this->field('id, username')->where('id', 'in', $uids)
            ->where('status','1')
            ->select();
        $ret = [];
        foreach ($list as $item) {
            $data  = $item->getData();
            $ret[$item['id']] = $data['username'];
        }
        return $ret;
    }

    public function getUserInfo()
    {
        $userInfo = Session::get('userinfo', 'admin');
        if (substr($userInfo['avatar'], 0, 4) != 'http')
        {
            $userInfo['avatar'] = buildImageUrl($userInfo['avatar']);
        }
        return $userInfo;
    }

    public function login($user)
    {
        switch(LoginService::accountType($user['username']))
        {
            case 'email':
                $ret = $this->doEmail($user);
                break;
            default:
                $ret = $this->doName($user);
                break;
        }
        if ('900301' == $ret)
        {
            return info('用户名或密码错误，请重新输入');
        }
        $userinfo = $ret;
        $groupinfo = GroupModel::self()->get(['id'=>$userinfo['group_id']]);
        if (!$groupinfo)
        {
            return info('权限不予许', 403);
        }
        $userinfo['group_id'] = $userinfo['group_id'];
        $userinfo['group_name'] = $groupinfo['name'];
        $userinfo['administrator'] = $userinfo['administrator'];
        return info('登录成功', 1, '', $userinfo);
    }

    public function add(array $data = [])
    {
        if($data['confirm_password'] != $data['password']) {
            return info(lang('The password is not the same twice'), 0);
        }
        $where = "username = '{$data['username']}'";
        if ($data['mobile']) {
            $where .= " OR mobile = '{$data['mobile']}'";
        }   
        if ($data['email']) {
            $where .= " OR email = '{$data['email']}'";
        }
        $user = $this->where($where)->find();
        if (!empty($user)) {
            return info(lang('Account already exists'), 0);
        }
        unset($data['confirm_password']);
        $uid=$this->regAdmin($data);
        $data['id'] = $uid;
        if ($uid > 0)
        {
            if($data['reg_im'] == '1'){
                LoginService::regIminfo($data['id'], $data['username']);
            }
            $log = ['username'=>$data['username'],'operation_time'=>date('Y-m-d H:i:s',time())];
            Loader::model('LogRecord')->record( lang('Add admin user',$log) );
            return info(lang('Add succeed'), 200);
        }else{
            return info(lang('Add failed') ,400);
        }
    }

    public function edit(array $data = [])
    {
        $data['update_time'] = time();
        $uid=$this->regAdmin($data);
        if(is_numeric($uid)){
            return info(lang('Edit succeed'), 200);
        }else{
            return info(lang('Edit failed'), 400);
        }
    }

    public function deleteById($id)
    {
        if ($this->um->updateUser(['status'=>0], $id)!= '0')
        {
            return info(lang('Delete failed'), 0);
        }
        $result = $this->where('id',$id)->update(['status'=>0]);
        if ($result > 0) {
            return info(lang('Delete succeed'), 200);
        }
    }

    public function getList($map = [], $order = ['id'=>'desc'], $page_size = 2, $params = [])
    {
        $it = $this->alias('u')
            ->field('u.*, g.name as group_name')
            ->join('sx_auth_group g', 'u.group_id = g.id', 'LEFT')
            ->whereOr($map)    
            ->order($order)
            ->paginate($page_size,false,[
                'type'     => 'bootstrap',
                'var_page' => 'page',
                'query'    =>$params,
            ]);
        return $it; 
    }

    public function checkUser($map)
    {
        if (!$this->where($map)->find())
        {
            return false;
        }
        return true;
    }
    public function checkRule($user)
    {
        $userInfo = Session::get('userinfo', 'admin');
        if(($user['id'] == $userInfo['id']) && ($user['username'] == $userInfo['username'])){
            return false;
        }
        return true;
    }

    public function status($time)
    {
        return [
            'title'=>'用户数',
            'total'=>$this->um->count(),
            'newly'=>$this->um->where('reg_time', '>', $time)->count(),
            'pending'=>$this->um->where('status', 2)->count(),
        ];
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
    public function doName($user)
    {
        $result = $this->where('username', $user['username'])->find();
        if (!empty($result)) {
            if($result['status']==0){
                return 900309;
            }
            $pass = make_password($user['password'], $result['uniq_id']);
            if ($pass === $result['password']) {
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => intval(get_client_ip(1, true)),
                ];
                $this->where('id', $result["id"])->update($data);
                return $result;
            }
            return 900301;
        }
        return 900301;
    }

    public function doEmail($user)
    {
        $result = $this->where('email', $user['username'])->find();
        
        if (!empty($result)) {
            if($result['status']==0){
                return 900309;
            }
            $pass = make_password($user['password'], $result['uniq_id']);
            if ($pass === $result['password']) {
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => intval(get_client_ip(1, true)),
                ];
                $this->where('id', $result["id"])->update($data);
                return $result;
            }
            return 900301;
        }
        return 900301;
    }
    public function regAdmin($user)
    {
        $result = $this->where('username', $user['username'])->find();
        $ip=intval(get_client_ip(1, true));
        $now = time();
        if (empty($result)) {
            $user_uniq = uniqid($user['mobile']);
            $data   = [
                'username'      => $user['username'],
                'nickname'      => $user['username'],
                'avatar'       => '/user_avatar_'.rand(0,5).'.jpg',
                'group_id'     => $user['group_id'],
                'password'      => make_password(strtoupper(sha1($user['password'])), $user_uniq),
                'email'         => $user['email'],
                'mobile'        => $user['mobile'],
                'country_code'  => $user['country_code'],
                'administrator' => 0,
                'create_time'   => $now,
                'last_login_ip' => $ip,
                'last_login_time'=> $now,
                'uniq_id'       => $user_uniq,
                'status'        => 1,
            ];
            $userId = $this->insertGetId($data);
            return $userId;
        }
        else{
            $map = [
                'update_time'=>$now,
            ];
            if (!empty($user['password']))$map['password'] = make_password(strtoupper(sha1($user['password'])), $result['uniq_id']);
            if (isset($user['mobile'])) $map['mobile'] = $user['mobile'];
            if (isset($user['email'])) $map['email'] = $user['email'];
            if (isset($user['group_id'])) $map['group_id'] = $user['group_id'];
            if (isset($user['status'])) $map['status'] = $user['status'];
            if (isset($user['country_code'])) $map['country_code'] = $user['country_code'];
            $this->updateUser($map, $result['id']);
            return $result['id'];
        }
    }
    /*
     * 获取系统用户的头像和昵称 key 系统id value 系统基本信息
     */
    public function getSystemInfoKeyValue($system_id,$field='*'){
        $system_user = $this->field($field)->where(['id'=>['in',$system_id]])->select();
        $system_user_list = [];
        foreach ($system_user as $val) {
            $system_user_list[$val['id']] = $val;
        }
        return $system_user_list;
    }
    /*
     * 添加特殊属性
    */
    public function addAttr($attr,$user_id){
        $userInfo = $this->where('id',$user_id)->find();
        $ext_attr = json_decode($userInfo['ext_attr'],true);
        foreach ($attr as $key => $val) {
            $ext_attr[$key] = $val;
        }
        $data['ext_attr'] = json_encode($ext_attr);
        $this->where('id',$user_id)->update($data);
    }
    /**
     * 统计平台用户总资金
     */
    public function allUserFinance()
    {
        $cm = CustomerModel::self();
        $user_finance = $cm->field('balance,deposit')->select();
        $all_balance = 0;
        $all_deposit = 0;
        foreach($user_finance as $item){
            $all_balance += $item['balance'];
            $all_deposit += $item['deposit'];
        }
        $data = [
            'title'       => "用户资金总额",
            'total_finance'=> $all_balance+$all_deposit,
            'all_balance' => $all_balance,
            'all_deposit' => $all_deposit
        ];
        return $data;
    }
}
