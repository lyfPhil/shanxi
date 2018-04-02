<?php

namespace app\user\service;

use app\common\service\ToolService;
use app\user\model\UserModel;
use app\main\model\CustomerModel;
//use app\user\model\TokenModel;
use app\common\service\QueueService;
use think\Config;
use think\Env;
use think\Validate;
use think\Db;
use think\Cache;
use think\Log;

class LoginService {
    public static function accountType($username){
        if (Validate::is($username, 'email')) {
            return  'email';
        } else if (isValidPhone($username)) {
            return 'mobile';
        }
        return 'name';
    }

    public static function setToken($user, $params){
        //$tm = TokenModel::self();
        $device_type = $params;
        $dev = $device_type==='web'?'web':'mobile';
        $userId = $user['id'];
        $key = 'token_'.$userId;
        $data = Cache::get($key);
        $token = ToolService::token($user['username']);
        if ($data)
        {
            $data['token'][$dev] = $token;
        }
        else{
            $data = [
                'user_id'    =>$user['id'],
                'token'      =>[$dev=>$token],
                'username'   =>$user['username'],
                'avatar'     =>$user['avatar'],
                'mobile'     =>$user['mobile'],
                'email'      =>$user['email'],
                'nickname'   =>$user['nickname'],
                'status'     =>$user['status'],
                'ext_attr'   =>$user['ext_attr'],
            ];
        }
        self::updateToken($user['id'], $data);
        return $token;
    }

    public static function updateToken($userId, $data)
    {
        $setting_token = Config::get('setting.token');
        $expire = isset($setting_token['token_expire'])?$setting_token['token_expire']:3600;
        $key = 'token_'.$userId;
        Cache::set($key, $data,  $expire);
    }

    public static function checkToken($userId, $device_type, $token){
        $key = 'token_'.$userId;
        $dev = $device_type==='web'?'web':'mobile';
        $res = Cache::get($key);
        if (!$res)
        {
            Log::record("[checkToken] get Cache failed key=".$key, 'error'); 
            return 403100;
        }

        Log::record("[checkToken] {$key} {$res['token'][$dev]} <=> {$token}", 'info'); 
        if (isset($res['token']) && isset($res['token'][$dev]) && $res['token'][$dev] == $token)
        {
            self::updateToken($userId, $res);
            return $res;
        }
        return 403100;
    }

    public static function updateTokendata($userId, $data)
    {
        $key='token_'.$userId;
        $tokenData = Cache::get($key);
        $val=array_merge($tokenData, $data);
        self::updateToken($userId, $val);
    }

    public static function delToken($userId, $device_type){
        $key = 'token_'.$userId;
        $dev = $device_type==='web'?'web':'mobile';
        $data = Cache::get($key);
        if (!$data)
        {
            return true;
        }
        unset($data['token'][$dev]);
        if (count($data['token']) > 0)
        {
            self::updateToken($userId, $data);
        }
        else
        {
            Cache::rm($key);
        }
        return true;
    }

    public static function regIminfo($user_id, $nickname="")
    {
        $job = '\app\tasks\job\TimJob';
        $args = [
            'action'=>'REG',
            'id' => $user_id,
            'nickname'=>$nickname,
            'time'=>time(),
        ];
        $jobId = QueueService::push($job, $args);
    }
    public static function updateIminfo($user_id, $nickname="")
    {
        $job = '\app\tasks\job\TimJob';
        $args = [
            'action'=>'UPDATE',
            'id' => $user_id,
            'nickname'=>$nickname,
            'time'=>time(),
        ];
        $jobId = QueueService::push($job, $args);
    }

    public static function sendSms($args)
    {
        $job = '\app\tasks\job\SmsJob';
        $jobId = QueueService::push($job, $args);
    }

    public static function sendVerifySms($country_code, $phone, $verify_code)
    {
        $args = [
            'code'=>$country_code,
            'phone'=>$phone,
            'params'=>[strval($verify_code)],
        ];
        if ($country_code == '86')
        {
            $args['tmplId'] = 64254;
        }
        else
            $args['tmplId'] = 64246;

        self::sendSms($args);
    }

    public static function sendMailCode($code,$mail){
        $args = [
            'subject' => lang('email findback password'),
            'address' => $mail,
            'template'=> 'mail/captcha',
            'params' => ['code'=>$code],
            'type' => 0,//邮箱验证码
        ];
        self::sendMail($args);
    }

    public static function sendActiveMail($user_id, $mail)
    {
        $prefix = 'pbfu823sdafg;ji12j4389ii';
        $t= time();
        $sig = md5($prefix.$t.$user_id);
        $v=base64_encode("$user_id|$t|$sig");
        $host = Env::get('web.host_m');
        $url = $host."/account/activation?v=".urlencode($v);
        $args = [
            'subject' => lang('email active'),
            'address' => $mail,
            'template' => 'mail/verify',
            'params' =>[ 'url'=>$url],
            'type' => 1,//邮箱激活
        ];
        self::sendMail($args);
    }
    public static function checkActiveMail($v)
    {
        $prefix = 'pbfu823sdafg;ji12j4389ii';
        $d = base64_decode($v);
        $tmp = explode('|', $d);
        if (count($tmp) != 3)
        {
            return 900509;
        }
        $user_id = $tmp[0];
        $t = $tmp[1];
        $sig = $tmp[2];
        $check_sig = md5($prefix.$t.$user_id);
        if ($sig != $check_sig)
        {
            return 900509;
        }
        $now = time();
        if ($now - $t > 2*3600)
        {
            return 900502;
        }
        return ['checked' => true, 'user_id'=>$user_id];
    }

    public static function sendMail($args)
    {
        $job = '\app\tasks\job\MailJob';
        $jobId = QueueService::push($job, $args);
    }
    public static function checkLoginPass($pass,$user_id){
        $user = UserModel::self()->field('password,uniq_id')->where('id',$user_id)->find();
        $password = make_password($pass,$user['uniq_id']);
        if($password!=$user['password'])
        {
            return 900201;
        }
        else
        {
            return 0;
        }
    }
    /*
     * 检验交易密码
     */
    public  static function checkJyPass($pass,$user_id){
        $map = ['id'=>$user_id];
        $key = 'wrongpass_'.$user_id;
        $max_wrong = 3;
        $wrong = Cache::get($key);
        if ($wrong >= $max_wrong) {
            return ['code' => 900809, 'message' =>'' ];
        }
        $uniq = UserModel::self()->field('uniq_id')->where($map)->find();
        $user = CustomerModel::self()->field('deal_password')->where($map)->find();
        $password = make_password($pass,$uniq['uniq_id']);
        if($user['deal_password']==''){
            return ['code' => 400, 'message' => ''];
        }
        if($user['deal_password']!=$password)
        {
            if (!$wrong) {
                $wrong = 1;
            } else {
                $wrong += 1;
            }
            Cache::set($key, $wrong, 60*60*3);
            if ($wrong >= $max_wrong) {
                return ['code' => 900809, 'message' =>'' ];
            } else {
                return ['code' => 900801, 'message' => lang('jy_pass wrong count', ['count' => $max_wrong - $wrong]) ];
            }
        }else{
            Cache::set($key,0);
            return 0;
        }
    }

    public static function needCaptcha()
    {
        $ip = get_client_ip(1, true);
        $key = 'NEEDCAPTCHA_'.$ip;
        $conf = Config::get('setting.login_captcha');
        $c =Cache::get($key,0);
        if ($c >= $conf['counts']) {
            return true;
        }
        return false;
    }

    public static function addCounts(){
        $ip = get_client_ip(1, true);
        $key = 'NEEDCAPTCHA_'.$ip;
        $conf = Config::get('setting.login_captcha');
        $c = Cache::get($key,0);
        if ($c > 0) {
            $exp = $conf['expire_time'];
            $c +=1;
            Cache::set($key,$c,$exp);//使用自增::inc的话会将有效期失效
        } else {
            Cache::set($key,1);//如果在这里设置过期时间，也会失效
        }
    }
}
