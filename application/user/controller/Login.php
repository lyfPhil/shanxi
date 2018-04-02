<?php
namespace app\user\controller;

use app\common\controller\ApiBase;
use app\user\model\UserModel;
use app\user\model\CustomerModel;
use app\user\model\ThirdUserModel;
use app\user\service\LoginService;
use app\common\service\CryptService;
use app\v1\service\FormatService;
use think\Cache;
use think\Env;
use think\Validate;
use think\Config;
use think\Cookie;
/**
 * Class Login
 */
class Login extends ApiBase {
    /**
     * 登陆接口
     * @desc 验证用户名密码
     * @method POST
     * @parameter string username 用户名
     * @parameter string password 密码
     * @response string token 令牌
     */
    public function index(){
        return true;
        // if (!$this->request->isPost()) {
        //     return $this->wrong(400);
        // }
        // $rules = [
        //     'username'  =>  'require',
        //     'password'  =>  'require',
        // ];
        // $message = [
        //     'username.require'  =>  400,
        //     'password.require'  =>  400,
        // ];
        // $data = $this->param;
        // $validate = new Validate($rules,$message);
        // if (!$validate->check($data)) {
        //   return $this->wrong($validate->getError());
        // }
        // if (LoginService::needCaptcha()) {
        //     $rules['captcha']           = 'require';
        //     $message['captcha.require'] = 900700;
        //     $check_captcha = new Validate($rules,$message);
        //     if (!$check_captcha->check($data)) {
        //         return $this->wrong($check_captcha->getError());
        //     }
        //     if (!captcha_check($this->param['captcha'])) {
        //         return $this->wrong(900701);
        //     }
        // }
        // $account = $data['username'];
        // switch (LoginService::accountType($account)) {
        //     case 'email':
        //         $ret = UserModel::self()->doEmail($data);
        //     break;
        //     default://因为注册时电话号码和区号时分开的，故可以直接用mobile登录
        //         $ret = UserModel::self()->doMobile($data);
        //     break;
        // }
        // if ($ret != 0) {
        //     LoginService::addCounts();
        //     return $this->wrong($ret);
        // }
        // $user = user_info();
        // $token = LoginService::setToken($user, $data);

        // if (is_int($token)) {
        //     return $this->wrong(400);
        // }
        // $res = [
        //     'user_id'   => $user['id'],
        //     'token'     => $token,
        //     'username'  => $user['username'],
        //     'nickname'  => $user['nickname'],
        //     'email'     => $user['email'],
        //     'country_code'=> $user['country_code']?strval($user['country_code']):"",
        //     'mobile'    => $user['mobile'],
        //     'status'    => $user['status'],
        //     'is_seller' => $user['is_seller'],
        //     'avatar'    => Env::get('web.imgprefix').'/'.$user['avatar'],
        //     'last_login_time' => $user['last_login_time'],
        //     'im_user'   =>'',
        //     'im_pass'   =>'',
        //     'vid' => $user['vid']
        // ];
        // $config = Config::get('token');
        // $expire = $config['token_expire'];
        // Cookie::set('token',$res['token'],$expire);
        // Cookie::set('user_id',$res['user_id'],$expire);
        // return $this->response($res);
    }

    public function get_login_info(){
        if (!$this->is_login()) {
            return $this->wrong(400100);
        }
        $um = UserModel::self();
        $user = $um->where('id',$this->userInfo['user_id'])->find();
        $res = [
            'user_id'   =>$user['id'],
            'username'  =>$user['username'],
            'nickname'  =>$user['nickname'],
            'email'     =>$user['email'],
            'mobile'    =>$user['mobile'],
            'status'    =>$user['status'],
            'avatar'    =>buildImageUrl($user['avatar']),
        ];
        return $this->response($res);
    }

    /*
     * 重置密码
     */
    public function resetPass(){
        $username = $this->param['username'];
        $key = CryptService::decrypt($this->param['key']);
        $check = explode('_',$key);
        if ($check[0] != 'findbackpasstep1') {
            return $this->wrong(400);
        }
        $pass = $this->param['password'];
        $repass = $this->param['repassword'];
        if ($pass != $repass) {
            return $this->wrong(400);
        }
        switch (LoginService::accountType($username)) {
            case 'email':
                $ret = UserModel::self()->emailPasswordReset($username,$pass);
            break;
            default :
                $ret = UserModel::self()->mobilePasswordReset($username,$pass);
            break;
        }
        $res = ['ret'=>$ret];
        return $this->response($res);
    }

    public function get_iminfo()
    {
        if(!$this->is_login()){
            return $this->wrong(400100);
        }
        $userid = $this->userInfo['user_id'];
        $im_user = get_im_id($userid);
        $config = Config::get('setting.opentim');
        if ($config['type'] == 0)
        {
            $im_pass = get_tim_sig($userid);
        }
        else
        {
            $im_pass = get_im_pass($userid);
        }
        $ret = [
            'im_user'=>$im_user,
            'im_pass'=>$im_pass,
        ];
        return $this->response($ret);
    }

}