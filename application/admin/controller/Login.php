<?php

namespace app\admin\controller;

use app\common\controller\Common;
use think\Controller;
use think\Loader;
use think\Request;
use think\Url;
use think\Session;
use think\Config;
use app\admin\model\UserModel;

class Login extends Common {

    /**
     * 后台登录首页
     */
    public function index() {
        if (Session::has('userinfo', 'admin')) {
            $this->redirect(url('admin/index/index'));
        }
        return view();
    }

    /**
     * 登录验证
     */
    public function doLogin() {
        if (!Request::instance()->isAjax()) {
            return $this->success('请求错误');
        }
        $postData = input('post.');
        $captcha = $postData['verify_code'];
        if (!captcha_check($captcha)) {
            return $this->error('验证码错误');
        };
        $user = [
            'username' => $postData['username'],
            'password' => strtoupper(sha1($postData['password']))
        ];
        $ret = UserModel::self()->login($user);
        if ($ret['code'] !== 1) {
            return $this->error($ret['msg']);
        }
        $data = $ret['data'];
        $userinfo = [
            'id' => $data['id'],
            'group_id' => $data['group_id'],
            'group_name' => $data['group_name'],
            'avatar' => $data['avatar'],
            'username' => $data['username'],
            'nickname' => $data['nickname'],
            'status' => $data['status'],
            'administrator' => $data['administrator'],
            'login_time' => time(),
        ];
        Session::set('userinfo', $userinfo, 'admin');
        set_sso($data['id']);
        Loader::model('LogRecord')->record('登录成功');
        return $this->success('登录成功', url('admin/index/index'));
    }

    /**
     * 退出登录
     */
    public function out() {
        Session::clear('admin');
        return $this->success('注销成功，已退出登录', url('admin/login/index'));
    }

}
