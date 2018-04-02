<?php

namespace app\admin\controller;

use app\main\model\RechargeModel;
use think\Request;
use app\v1\service\FormatService;
use think\Session;
use app\main\model\CustomerModel;
use think\Loader;
use app\common\service\CryptService;

class Recharge extends Admin{

    const PAY_TYPE =[
        '0'=> 'Bank (under line)',
        '1'=> 'Bank (cash)',
        '2'=> '7—11',
        '3'=> 'Octopus',
    ];
    const DELAY_REASON =[
        '1'=> "Can't receive the top-up payment after 30 days",
        '2'=> "Can't contact with you",
        '3'=> 'The top-up amount you submitted is not the same with what your transfer into',
        '4'=> 'Other reasons'
    ];
    /**
     * 充值列表
     * @return
     */
    public function index()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['charge_no']) && $search['charge_no'] != ''){
            $map['charge_no'] = array('like','%'.$search['charge_no'].'%');
        }
        if(isset($search['user_name']) && $search['user_name'] != ''){
            $map['user_name'] = array('like','%'.$search['user_name'].'%');
        }
        if(isset($search['user_name']) && $search['user_name'] != '')
        {
            $map['user_name'] = array('like','%'.$search['user_name'].'%');
        }
         if(isset($search['pay_type']) && $search['pay_type'] != '')
        {
            $map['pay_type'] = $search['pay_type'];
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
            $map['create_time'] = array('<=',strtotime($search['end_time']));
        }
        if(isset($search['keywords']) && $search['keywords'] != '')
        {
            $map['charge_no'] = array('like','%'.$search['keywords'].'%');
        }
        $rm = RechargeModel::self();
        $userRow = Session::get('userinfo', 'admin');
        $data = [];
        switch ($userRow['group_id'])
        {
            case 1:
                $lists = $rm->getRechargeList($map,'id desc',10,$search);
                foreach($lists as $item)
                {
                    $temp = json_decode($item['transfer_info'], true);
                    $item['bank_name'] = $temp['bank_name'];
                    $item['pay_type'] = FormatService::formatRechargeType($item['pay_type']);
                    $item['bank_card'] = CryptService::decrypt($temp['bank_card']);
                    $data[] = FormatService::formatNull($item->getData());
                }
                break;
            case 3://财务
                $map['state'] = array('in',[0,1,3]);
                $lists = $rm->getRechargeList($map,'state,id desc',10,$search);
                foreach($lists as $item)
                {
                    $temp = json_decode($item['transfer_info'], true);
                    $item['bank_name'] = $temp['bank_name'];
                    $item['pay_type'] = FormatService::formatRechargeType($item['pay_type']);
                    $item['bank_card'] = CryptService::decrypt($temp['bank_card']);
                    $data[] = FormatService::formatNull($item->getData());
                }
                break;
            case 4://客服 不需要看到银行信息
                $map['state']  = array('in',[1,3]);
                $lists = $rm->getRechargeList($map,'state desc,id desc',10,$search);
                foreach($lists as $item)
                {
                    $temp = json_decode($item['transfer_info'], true);
                    $item['bank_name'] = $temp['bank_name'];
                    $item['pay_type'] = FormatService::formatRechargeType($item['pay_type']);
                    $item['bank_card'] = FormatService::ShieldInfo(CryptService::decrypt($temp['bank_card']), 'bank');
                    $data[] = FormatService::formatNull($item->getData());
                }
                break;
            default :
                $map['state']  = array('in',[1,2]);
                $lists = $rm->getRechargeList($map,'id desc',10,$search);
                break;
        }
        $page = $lists->render();
        
        $this->assign('group_id', $userRow['group_id']);
        $this->assign('pay_type', self::PAY_TYPE);
        $this->assign('search', $search);
        $this->assign('list', $data);
        $this->assign('page', $page);
        return view();
    }

    public function edit()
    {
        $params = Request::instance()->param();
        $map['id'] = $params['id'];
        $res = RechargeModel::self()->getDetail($map);
         if(!$res)
        {
            return $this->response(400);
        }
        $detail = FormatService::formatNull($res->getData());
        if(!empty($detail['transfer_info']))
        {   //0:银行(线下) 1:7-11 2:八达通
            switch ($detail['pay_type'])
            {
                case 0:
                case 1:
                    $temp = json_decode($detail['transfer_info'], true);
                    $detail['real_name'] = $temp['real_name'];
                    $detail['bank_name'] = $temp['bank_name'];
                    $detail['bank_card'] = CryptService::decrypt($temp['bank_card']);
                    $receive_info = json_decode($detail['receive_info'], true);
                    $detail['receive'] = $receive_info;
                break;
            }
        }
        $detail['pay_type_no'] = $detail['pay_type'];
        $detail['pay_type'] = FormatService::formatRechargeType($detail['pay_type']);
        if(!empty($detail['invoice'])){
            $detail['invoice'] = buildImageUrl($detail['invoice']);
        }
        $detail['create_time'] = date("Y-m-d H:i:s",$detail['create_time']);
        $detail['over_time'] = date("Y-m-d H:i:s",$detail['over_time']);
        $this->assign('access',$this->group_id);
        $this->assign('detail',$detail);
        if($this->group_id == 3){
            return $this->fetch("finance_edit");
        }
        if($this->isadmin && $detail['state'] != 3){
            return $this->fetch("finance_edit");
        }
        return view();
    }
    /**
     * 更新充值状态
     * @return
     */
    public function update()
    {
        if(request()->isAjax())
        {
            $param = Request::instance()->param();
            if(isset($param['delay_reason']) && $param['delay_reason'] != ''){
                $param['cancel'] = self::DELAY_REASON[$param['delay_reason']];
            }
            $wm = RechargeModel::self();
            $ret = $wm->updateInfo($param);
            $log = [
                'operation_time'=> date("Y-m-d H:i:s", time()),
                'charge_no'=>$param['charge_no']
            ];
            if($param['state'] == '1')$log['status'] = lang('pass');
            if($param['state'] == '2')$log['status'] = lang('no pass');
            if($param['state'] == '3')$log['status'] = lang('finance pass');
            Loader::model('LogRecord')->record( lang('Operation Recharge',$log) );
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
