<?php
namespace app\user\controller;

use think\Validate;

use app\common\controller\ApiBase;
use app\user\model\VerifyModel;
use app\user\model\UserModel;
use app\user\service\LoginService;
use app\common\service\CryptService;
use think\Db;
use think\Cache;
class Verifycode extends ApiBase
{
    public function code()
    {
        if (!checkParam($this->param,['country_code','username'])) {
            return $this->wrong(400);
        }
        $account['country_code'] = $this->param['country_code'];
        $account['username'] = $this->param['username'];
        $mobile = $this->param['country_code'].$this->param['username'];
        if(LoginService::accountType($mobile) !== 'mobile')
        {
             return $this->wrong(900401);
        }
        $user = UserModel::self();
        $result = $user->mobielIsUnique($account);
        if( $result !== true ){
            return $this->wrong($result);
        } else {
            $vm = VerifyModel::self();
            $code = $vm -> getVerifycode($this->param['country_code'].ltrim($this->param['username'],0));
            if($code){
                $key = uniqid($account['username']);
                Cache::set($account['username'],$key,300);
                LoginService::sendVerifySms($account['country_code'],ltrim($account['username'],'0'),$code);
            }elseif($code===0){
                return $this->wrong(900605);
            }else{
                return $this->wrong(900609);
            }
            return $this -> response(['key'=>$key]);
        } 
    }
    /*
     * 找回密码的验证码
     */
    public function codeFindBack(){
        if(LoginService::needCaptcha()){
            $rules['captcha'] = 'require';
            $message['captcha.require'] = 900700;
            $check_captcha = new Validate($rules,$message);
            if(!$check_captcha->check($this->param)){
                return $this->wrong($check_captcha->getError());
            }
            if(!captcha_check($this->param['captcha'])){
                return $this->wrong(900701);
            }
        }
        if (!checkParam($this->param,['username'])) {
            return $this->wrong(400);
        }
        $username = $this->param['username'];
        $exist    = UserModel::self()->field('country_code,email,mobile')->where(['email|mobile'=>$username])->find();
        if (!$exist) {
            LoginService::addCounts();
            return $this->wrong(404010);
        }
        if($username == $exist['mobile']){
            $username = $exist['country_code'].ltrim($exist['mobile']);
        }
        $v      = VerifyModel::self();
        $code   = $v -> getVerifycode($username);
        if($code===false){
            return $this->wrong(900609);
        }elseif($code===0){
            return $this->wrong(900605);
        }
        $account = LoginService::accountType($username);
        switch($account){
            case 'email':
                LoginService::sendMailCode($code,$username);
            break;
            case 'mobile' :
                LoginService::sendVerifySms($exist['country_code'],ltrim($exist['mobile'],'0'),$code);
            break;
            default:
                return $this->wrong(900101);
        }
        $res = ['ret'=>0];
        $ip  = get_client_ip(1, true);
        $key = 'NEEDCAPTCHA_'.$ip;
        Cache::rm($key);
        return $this->response($res);
    }
    /*
     * 找回密码 验证码验证
     */
    public function checkCode(){
        if (!checkParam($this->param,['username','code'])) {
            return $this->wrong(400);
        }
        $username = $this->param['username'];
        $code     = $this->param['code'];
        $user     = UserModel::self()->field('country_code,email,mobile')->where(['email|mobile'=>$username])->find();
        if($username == $user['mobile']){
            $username = $user['country_code'].ltrim($user['mobile'],'0');
        }
        $v      = VerifyModel::self();
        $result = $v->checkCode($username, $code);
        if ($result!==200) {
            return $this->wrong($result);
        }
        $key = 'findbackpasstep1_'.$username;
        $key = CryptService::encrypt($key);
        $res = ['key'=>$key];
        return $this->response($res);
    }
    
    public function checkMail()
    {  
        $v = $this->param['v'];
        $res = LoginService::checkActiveMail($v);
        if (is_numeric($res)) {
            return $this->wrong($res);
        }
        if (is_array($res)) {
            $user_id = $res['user_id'];
            $um      = UserModel::self();
            $user    = $um->field('mobile')->where('id',$user_id)->find();
            if ($user['mobile']!='') {
                $update['status'] = 11;
            } else {
                $update['status'] = 21;
            }
            $ret = $um->updateUser($update, $user_id);
            if ($ret===false) {
                return $this->wrong(904501);
            } else {
                LoginService::updateTokendata($user_id, $update);
                $ret = ['msg'=>"Active success"];
                return $this->response($ret);
            }
        }
        
    }
}
