<?php

namespace app\admin\controller;

use app\main\model\WithdrawModel;
use think\Request;
use app\v1\service\FormatService;
use think\Loader;
use app\common\service\CryptService;
use app\main\model\FinanceModel;
use app\main\model\CustomerModel;
use think\Session;
class Withdraw extends Admin{
    /**
     * 提现列表
     * @return
     */
    public function index()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['draw_no']) && $search['draw_no'] != ''){
            $map['draw_no'] = array('like','%'.$search['draw_no'].'%');
        }
        if(isset($search['user_name']) && $search['user_name'] != ''){
            $map['user_name'] = array('like','%'.$search['user_name'].'%');
        }
        if(isset($search['keywords']) && $search['keywords'] != '')
        {
            $map['order_sn'] = array('like','%'.$search['keywords'].'%');
        }
        if(isset($search['state']) && $search['state'] != '')
        {
            $map['state'] = $search['state'];
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
        $fm = WithdrawModel::self();
        //获取提现列表
        $map['type'] = 0;
        $admin_info = Session::get('userinfo','admin');
        $order = 'state,id desc';
        if ($admin_info['group_id'] == 3) {
            $map['state'] = ['in',[1,3]];
            $order = 'state desc,id desc';
        }
        $lists = $fm->getWithdrawList($map, $order,10,$search);
        $page = $lists->render();
        $data = [];
        foreach($lists as $item)
        {
            $data[] = FormatService::formatNull($item->getData());
        }
        $this->assign('search',$search);
        $this->assign('list',$data);
        $this->assign('page',$page);
        return view();
    }
    /**
     * 缴纳保证金列表
     * @return
     */
    public function depositList()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['draw_no']) && $search['draw_no'] != ''){
            $map['order_sn'] = array('like','%'.$search['draw_no'].'%');
        }
        if(isset($search['user_name']) && $search['user_name'] != ''){
            $map['user_name'] = array('like','%'.$search['user_name'].'%');
        }
        if(isset($search['keywords']) && $search['keywords'] != '')
        {
            $map['order_sn'] = array('like','%'.$search['keywords'].'%');
        }
        if(isset($search['state']) && $search['state'] != '')
        {
            $map['state'] = $search['state'];
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
        $fm = FinanceModel::self();
        //获取退还押金列表
        $map['cap_type'] = 7;
        $lists = $fm->getFinanceList($map,'state,id desc',10,$search);
        $page = $lists->render();
        $data = [];
        foreach($lists as $item)
        {
            $data[] = FormatService::formatNull($item->getData());
        }
        $this->assign('search',$search);
        $this->assign('list',$data);
        $this->assign('page',$page);
        return view();
    }
   public function edit()
    {
        $id = Request::instance()->param('id');
        $map['id'] = $id;
        $res = WithdrawModel::self()->getDetail($map);
        $res['bank_card'] = CryptService::decrypt($res['bank_card']);
        $user_model = CustomerModel::self();
        $user = $user_model->getOneUserInfo($res['user_id'], 'real_name, idcard_pic, idcard');
        if(!$res) {
            return $this->response(400);
        }
        $detail = FormatService::formatNull($res->getData());
        $detail['idcard'] = $user['idcard'] ?  CryptService::decrypt($user['idcard']) : '';
        $detail['real_name'] = $user['real_name'];
        $detail['idcard_pic'] = $user['idcard_pic'] ? buildImageUrl($user['idcard_pic']) : '';
        $this->assign('detail',$detail);
        $admin = Session::get('userinfo', 'admin');
        $this->assign('admin_info',$admin);
        return view();
    }
    public function editDeposit()
    {
        $id = Request::instance()->param('id');
        $map['id'] = $id; 
        $res = WithdrawModel::self()->getDetail($map);
        $res['bank_card'] = CryptService::decrypt($res['bank_card']);
        if(!$res)
        {
            return $this->response(400);
        }
        $detail = FormatService::formatNull($res->getData());
        $this->assign('detail',$detail);
        return view();
    }
    /**
     * 更新提现状态
     * @return 
     */
    public function update()
    {
        if(request()->isAjax())
        {
            $param = Request::instance()->param();
            if($param['state'] != 2)
            {
                $param['cancel'] = '';
            }
            $wm = WithdrawModel::self();
            $ret = $wm->edit($param);
            $log = [
                'operation_time'=>  date("Y-m-d H:i:s", time()),
                'draw_no'=> $param['draw_no']
            ];
            if($param['state'] == '1')$log['status'] = lang('pass');
            if($param['state'] == '2')$log['status'] = lang('no pass');
            if($param['state'] == 3) $log['status'] = lang('customer pass');
            Loader::model('LogRecord')->record(lang('Operation Withdraw',$log));
            if($ret != false)
            {
                return $this->response(200);
            }else
            {
                return $this->response(201);
            }
        }
    }
    public function updateDeposit()
    {
        if(request()->isAjax()){
            $param = Request::instance()->param();
            if($param['state'] != 2){   
                $param['cancel'] = '';
            }
            $wm = WithdrawModel::self();
            $ret = $wm->edit($param);
            $log = [
                'operation_time'=>  date("Y-m-d H:i:s", time()),
                'draw_no'=> $param['draw_no']
            ];
            if($param['state'] == '1')$log['status'] = lang('pass');
            if($param['state'] == '2')$log['status'] = lang('no pass');
            Loader::model('LogRecord')->record(lang('Operation Withdraw',$log));
            if($ret != false)
            {
                return $this->response(200);
            }else
            {
                return $this->response(201);
            }
        }
    }
}
