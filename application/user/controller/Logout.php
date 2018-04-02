<?php
namespace app\user\controller;

use app\common\controller\ApiBase;
use app\user\model\UserModel;
use app\user\service\LoginService;
use think\Cookie;
/**
 * Class Login
 */
class Logout extends ApiBase {
    /**
     * 登出接口
     * @desc 验证用户名密码
     * @method POST
     * @parameter string user_id 用户ID
     * @response string token 令牌
     */
    public function index(){
        if (!$this->is_login()) {
            return $this->wrong(400000);
        }
        if(!checkParam($this->param,['user_id','device_type'])){
            return $this->wrong(400);
        }
        $user_id = $this->param['user_id'];
        $device_type = $this->param['device_type'];
        $res = LoginService::delToken($user_id, $device_type);
        $data=['ret'=>0];
        Cookie::clear('token');
        Cookie::clear('user_id');
        return $this->response($data);
    }
}
