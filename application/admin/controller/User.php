<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\UserModel;
use app\admin\model\GroupModel;
use think\Session;
use think\Loader;

class User extends Admin
{

    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 列表
     */
    public function index()
    {
        $search = Request::instance()->param();
        $map = $params = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $params = ['keywords' => $search['keywords']];
            $map['username'] = $map['email'] = $map['phone'] = array('like', '%'.$search['keywords'].'%');
        }
        $data = UserModel::self()->getList($map, 'id', 15, $params);
        $user = Session::get('userinfo', 'admin');
        $this->assign('user',$user);
        $this->assign('data',$data);
        $this->assign('search', $search);
        return view();
    }

    public function create()
    {
        $data['groupList'] = GroupModel::self()->getList(False);
        $user = Session::get('userinfo', 'admin');
        $this->assign('user',$user);
        $this->assign('data', $data);
        return view();
    }

    public function store()
    {
        $data = Request::instance()->param();
        $userValidate = validate('User');
	if(!$userValidate->scene('add')->check($data)) {
            return $this->response(400, $userValidate->getError());
	}
        $mobile = $data['country_code'].$data['mobile'];
        if (!isValidPhone($mobile))
        {
            return $this->response(400, lang('Mobile Error'));
        }
        $result = UserModel::self()->where('mobile', $data['mobile'])->find();
        if(!empty($result)){
            return $this->response(400,  lang("Mobile already exists"));
        }
        $res = UserModel::self()->where('email', $data['email'])->find();
        if(!empty($res)){
            return $this->response(400,  lang("Email already exists"));
        }
//        if(isset($data['id']) && $data['id'] != '')
//        {
//            $ret = UserModel::self()->where('id',$data['id'])->find();
//            if(!empty($ret)){
//                return $this->response(400,  lang('ID already exists')); 
//            }
//        }
        return UserModel::self()->add($data);
    }

    public function edit()
    {
        $uid = Request::instance()->param('id');
        if(intval($uid) < 0){
            return $this->response(400, lang('Data ID exception'));
        }
        if (intval($uid == 1) && $this->uid != 1) {
           return $this->response(403, lang('Edit without authorization'));
        }
        $data['groupList'] = GroupModel::self()->getList(False);
        $data['userInfo'] = UserModel::self()->getById($uid);
        $this->assign('data',$data);
        return view();
    }
    
    public function update()
    {
        $data = Request::instance()->param();
        //检测用户
        if($data['password'] && $data['confirm_password'] != $data['password']){
            return $this->response(400, lang('The two passwords No match!'));
        }
        $um = UserModel::self();
        $userInfo =$um->get(['id'=>$data['id']]);
        //检测邮箱
        if ($userInfo['email'] != $data['email']) {
            $checkEmail = $um->checkUser(['email' => $data['email']]);
            if ($checkEmail) {
                return $this->response(900503);
            }
        }
        //检测电话
        if ($userInfo['mobile'] != $data['mobile']) {
            $checkPhone = $um->checkUser(['mobile' => $data['mobile']]);
            if ($checkPhone) {
                return $this->response(900403);
            }
        }
        //禁止修改自己的权限
        if(isset($data['group_id']) && $data['group_id'] != ''){
            if($userInfo['group_id'] != $data['group_id'])
            {
                $checkRule = $um->checkRule($userInfo);
                if(!$checkRule){
                    return $this->response(909001);
                }
            }
        
        } 
        $ret = $um->edit($data);
        if($ret['code'] == 200){
            $user = $um->field('username')->where('id',$data['id'])->find();
            $log = [
                'operation_time'=> date('Y-m-d H:i:s',time()),
                'admin_user'=> $user['username']
            ];
            Loader::model('LogRecord')->record(lang('Update admin user',$log));
        }
        return $ret;
    }

    public function remove()
    {
        $id = Request::instance()->param('id');
        if(empty($id)){
            return $this->response(400, lang('Data ID exception'));
        }
        if (intval($id == 1 || in_array(1, explode(',', $id)))) {
            return $this->response(403, lang('Delete without authorization'));
        }
        $user = UserModel::self()->field('username')->where('id',$id)->find();
        $result =  UserModel::self()->deleteById($id);
        if($result>0){
            $log = [
                'operation_time' => date('Y-m-d H:i:s',time()),
                'admin_user' => $user['username']
            ];
            Loader::model('LogRecord')->record(lang('Deleted admin user',$log));
        }
        return $result;

    }
}
