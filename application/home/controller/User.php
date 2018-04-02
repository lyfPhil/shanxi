<?php
namespace app\home\controller;

use app\common\controller\Common;
use think\Controller;
use think\Session;
use think\Request;
use think\Url;
use app\user\model\UserModel;
use app\main\model\CustomerModel;
use app\user\service\LoginService;
use think\Cache;
use think\Config;
use think\Cookie;
use think\Env;
class User extends Common
{
	/**
     * 登录验证
     */
    public function login() {
        // if (!Request::instance()->isAjax()) {
        //     return $this->error('请求错误');
        // }
        // $postData = input('post.');
        // $user = [
        //     'username' => $postData['username'],
        //     'password' => strtoupper(sha1($postData['password']))
        // ];
        // $ret = UserModel::self()->doMobile($user);

        return $this->response(200,'登录成功');
    }

    /**
     * 退出登录
     */
    public function out() {
        Session::clear('admin');
        return $this->success('注销成功，已退出登录', url('admin/login/index'));
    }

    public function register(){
        if(!$this->request->isPost()) {
            return $this->error(400);
        }
        $data = $this->param;
        
        $register = new UserModel();
        $user['password'] = $data['password'];
        $user['username'] = $data['username'];
        $user['country_code'] = 86;
        $user['mobile'] = $data['mobile'];
        $user['email'] = $data['email'];
        $ret = $register->registerMobile($user);
        if (is_numeric($ret)) {
            return $this->response(200,'注册成功');
        }    
        return $this->error($ret);
    }

}

