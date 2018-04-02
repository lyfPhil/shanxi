<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use think\Validate;
use app\user\model\UserModel;
use app\user\model\VerifyModel;
use app\common\controller\ApiBase;
use app\user\service\LoginService;
use app\main\model\CustomerModel;
use think\Cache;
use think\Config;
use think\Cookie;
use think\Env;
class Register extends ApiBase
{
    public function phone()
    {
        if(!$this->request->isPost()) {
            return $this->wrong(400);
        }
        // $validate = new Validate([
        //     'code'     => 'require',
        //     'password' => 'require'
        // ]);
        // $validate->message([
        //     'code.require'     => 400,
        //     'password.require' => 400,
        // ]);
        $data = $this->param;
        // if (!$validate->check($data)) {
        //     return $this->wrong($validate->getError());
        // }

        // $vmobile = $data['country_code'].ltrim($data['username'],'0');
        // $result = VerifyModel::self()->checkCode($vmobile, $data['code']);

        // if ( $result !== 200 )  {
        //     return $this->wrong($result);
        // }
        // $mobile = $data['country_code'].$data['username'];
        // if (isValidPhone($mobile)) {
            $register          = new UserModel();
            $user['password'] = $data['password'];
            $user['username'] = $data['username'];
            $user['country_code'] = 86;
            $user['mobile'] = $data['mobile'];
            $user['email'] = $data['email'];
            $ret            = $register->registerMobile($user);
        // } else {
        //     return $this->wrong(900401);
        // }
        if ($ret == 0)
        {
            $user = user_info();
            $vid = CustomerModel::self()->initUser($user['id'],$user);
            LoginService::regIminfo($user['id'], $user['username']);
            $token = LoginService::setToken($user, $this->param);
            $res=[
                'ret'=>$ret,
                'user_id'=>user_current_id(),
                'token'=>$token,
                'username'=>$user['username'],
                'country_code'=> 86,
                'email'=>$user['email'],
                'mobile'=>$user['mobile'],
                'status'=>$user['status'],
                'avatar'=>buildImageUrl($user['avatar']),
                'im_user'=>'',
                'im_pass'=>'',
                'is_seller' => 0,
                'vid' => $vid,
            ];
            $config = Config::get('token');
            $expire = $config['token_expire'];
            Cookie::set('token',$res['token'],$expire);
            Cookie::set('user_id',$res['user_id'],$expire);
            return $this->response($res);
        }
        return $this->wrong($ret);
    }

    public function email()
    {
        if(!$this->request->isPost())
        {
            return $this->wrong(400);
        }
        $rules =[
            'username'=>'require|email',
            'password'=>'require'
        ];
        $message = [
            'username.require'  => 400,
            'username.email'    => 400,
            'password.require'  => 400,
        ];

        $validate = new Validate($rules,$message);
        $data = $this->param;
        if (!$validate->check($data)){
            return $this->wrong($validate->getError());
        }
        //做图像验证码
        if(strtolower($this->param['device_type'])=='web'||LoginService::needCaptcha()){
            $rules['captcha']           = 'require';
            $message['captcha.require'] = 900700;
            $check_captcha = new Validate($rules,$message);
            if(!$check_captcha->check($data)){
                return $this->wrong($check_captcha->getError());
            }
            if(!captcha_check($this->param['captcha'])){
                return $this->wrong(900701);
            }
        }
        $register = UserModel::self();
        $user['email'] = $data['username'];
        $user['password'] = $data['password'];
        $ret              = $register->registerEmail($user);

        if($ret == 0)
        {
            $user = user_info();
            $vid = CustomerModel::self()->initUser($user['id'],$user);
            LoginService::regIminfo($user['id'], $user['username']);
            $token = LoginService::setToken($user, $this->param);
            $res=[
                'ret'=>$ret,
                'user_id'=>user_current_id(),
                'token'=>$token,
                'username'=>$user['username'],
                'country_code'=> $user['country_code']?strval($user['country_code']):"",
                'email'=>$user['email'],
                'mobile'=>$user['mobile'],
                'status'=>$user['status'],
                'avatar'=>buildImageUrl($user['avatar']),
                'im_user'=>'',
                'im_pass'=>'',
                'is_seller'=> 0,
                'vid' => $vid
            ];
            $config = Config::get('token');
            $expire = $config['token_expire'];
            Cookie::set('token',$res['token'],$expire);
            Cookie::set('user_id',$res['user_id'],$expire);
            LoginService::sendActiveMail($res['user_id'],$res['email']);
            return $this->response($res);
        }else{
            LoginService::addCounts();
            return $this->wrong($ret);
        }
    }
}
