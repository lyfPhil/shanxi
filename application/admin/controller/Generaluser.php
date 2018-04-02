<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use think\Request;
use app\user\model\UserModel;
use app\main\model\CertificationModel;
use app\main\model\CustomerModel;
use app\main\model\PrevidModel;
use app\v1\service\FormatService;
use think\Env;
use think\Loader;
use app\common\service\CryptService;

class GeneralUser extends Admin{

    const REG_STATUS=[
        '0'=>'禁用',
        '1'=>'手机号注册',
        '10'=>'手机号注册，邮箱未激活',
        '11'=>'手机号注册，邮箱已激活',
        '20'=>'邮箱注册，邮箱未激活',
        '21'=>'邮箱注册，邮箱已激活'
    ];
    /**
     * 用户列表
     * @return
     */
    public function  index()
    {
        $search = Request::instance()->param();
        $map = array();
        $map['type'] = 2;
        if(isset($search['vid']) && $search['vid'] !=''){
            $map['vid'] = array(array('like','%'.trim($search['vid']).'%'),'or');
        }
        if(isset($search['username']) && $search['username'] !=''){
            $map['username'] = array(array('like','%'.trim($search['username']).'%'),'or');
        }
        if(isset($search['email']) && $search['email'] !=''){
            $map['email'] = array('like','%'.$search['email'].'%');
        }
        if(isset($search['mobile']) && $search['mobile'] !=''){
            $map['mobile'] = array('like','%'.$search['mobile'].'%');
        }
        if(isset($search['deposit']) && $search['deposit'] != ''){
            if($search['deposit'] == '0'){
                $map['deposit'] = array('eq','0.00');
            }
            if($search['deposit'] == '1'){
                $map['deposit'] = array('>','0.00');
            }
        }
        $um = UserModel::self();
        $data = $um->getUserList($map,'reg_time desc', 15, $search);
        $this->assign('status',  self::REG_STATUS);
        $this->assign('data',$data);
        $this->assign('search', $search);
        return view();
    }
    /**
     * 编辑用户信息
     * @return
     */
    public function edit()
    {
       $uid = Request::instance()->param('id');
       if(intval($uid) < 0)
       {
           return $this->response(400, lang('Data ID exception'));
       }
       if (intval($uid == 1) && $this->uid != 1) {
           return $this->response(403, lang('Edit without authorization'));
        }
        $data = UserModel::self()->getUserDetailsById($uid);
        $detail = CustomerModel::self()->getOneUserInfo($uid);
        $detail['idcard'] = CryptService::decrypt($detail['idcard']);
        $detail['v_icon'] = is_vicon($detail['flag']);
        // $detail = FormatService::formatNull($detail->getData());
        $this->assign('detail',$detail);
        $this->assign('data',$data);
        return view();
    }
    /**
     *
     * @return
     */
    public function update()
    {
        $data = request()->param();
        $cm = CustomerModel::self();
        $valid = validate('Generaluser');
        if (!$valid->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        if(!empty($data['password']) && $data['confirm_password'] != $data['password']){
            return $this->response(400, lang('The two passwords No match!'));
        }
        $ret = $cm->updateInfo($data);
        if($ret == 0)
        {
            $user = UserModel::self()->field('username')->where('id',$data['id'])->find();
            $log = [
                'operation_time'=>  date("Y-m-d H:i:s", time()),
                'user_name'=> $user['username']
            ];
            Loader::model('LogRecord')->record(lang('Update User Infomation',$log));
            return $this->response(200);
        }else
        {
            return $this->response(201);
        }
    }
    /**
     * 删除用户信息
     * @return
     */
    public function remove()
    {
       $ids = Request::instance()->param('id');
       $userInfo = UserModel::self()->field('username')->where('id',$ids)->find();
       $ret = UserModel::self()->remove($ids);
       if($ret){
           $log = [
                'operation_time'=>  date("Y-m-d H:i:s", time()),
                'user_name'=> $userInfo['username'],

            ];
           Loader::model('LogRecord')->record(lang('Deleted User',$log));
           return $this->response(200);
       }else{
           return $this->response(201);
       }

    }
    //卖家认证
    public function certification()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['vid']) && $search['vid'] != '')
        {
           $map['b.vid'] = array('like','%'.$search['vid'].'%');
        }
        if(isset($search['keywords']) && $search['keywords'] != '')
        {
           $map['keywords'] = array('like','%'.$search['keywords'].'%');
        }
        if(isset($search['account']) && $search['account'] != '')
        {
           $map['account'] = array('like','%'.$search['account'].'%');
        }
        if(isset($search['real_name']) && $search['real_name'] != '')
        {
           $map['real_name'] = array('like','%'.$search['real_name'].'%');
        }
        if(isset($search['idcard']) && $search['idcard'] != '')
        {
           $map['idcard'] = array('like','%'.$search['idcard'].'%');
        }
        if(isset($search['status']) && $search['status'] != ''){
            $map['status'] = $search['status'];
        }
        if(isset($search['first_time']) && $search['first_time'] != '')
        {
            if(isset($search['end_time']) && $search['end_time'] != ''){
                $map['create_time'] = array('between',[strtotime($search['first_time']),  strtotime($search['end_time'])]);
            }  else {
                $map['create_time'] = array('>=',  strtotime($search['first_time']));
            }
        }else if(isset ($search['end_time']) && $search['end_time'] != '')
        {
            $map['create_time'] = array('<',strtotime($search['end_time']));
        }
        $lists = CertificationModel::self()->getCertList($map,'status,create_time desc',10,$search);
        foreach($lists as $item)
        {
            $item['idcard'] = CryptService::decrypt($item['idcard']);
            $item['bank_num'] = CryptService::decrypt($item['bank_num']);
        }
        $page = $lists->render();
        $this->assign('search',$search);
        $this->assign('list',$lists);
        $this->assign('page',$page);
        return view();

    }
    //卖家认证详情
    public function checkCertification()
    {
        $id = Request::instance()->param('id');
        $data = CertificationModel::self()->find($id);
        $user = db('pp_jy.tab_user')->field('vid')->where('id',$data['user_id'])->find();
        $data['vid'] = $user['vid'];
        $data['idcard'] = CryptService::decrypt($data['idcard']);
        $data['bank_num'] = CryptService::decrypt($data['bank_num']);
        if (!empty($data['id_pic'])) {
            $data['id_pic'] = buildImageUrl($data['id_pic']);
        }
        if (!empty($data['bank_pic'])) {
            $data['bank_pic'] = buildImageUrl($data['bank_pic']);
        }
        $this->assign('data',$data);
        return view();
    }
    //更新保存
    public function saveCertification()
    {
        $data = Request::instance()->param();
        $id = $data['id'];
        $seller_name = CertificationModel::self()->field('account')->where('id',$id)->find();
        $result = CertificationModel::self()->edit($id,$data);
        if ($result !== false) {
            $log = [
                'operation_time'=>  date("Y-m-d H:i:s", time()),
                'seller_name'=> $seller_name['account']
            ];
            if($data['status'] == '1')$log['status'] = lang('pass');
            if($data['status'] == '2')$log['status'] = lang('no pass');
            Loader::model('LogRecord')->record(lang('Seller Certification',$log));
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function unbindvid(){
        $where['user_id'] = $this->param['user_id'];
        $where['vid'] = $this->param['vid'];
        $previd_model = PrevidModel::self();
        $ret = $previd_model->unbindVid($where);
        if ($ret == 0) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
}
