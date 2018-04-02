<?php

namespace app\admin\controller;

use think\Request;
use app\main\model\FinanceModel;
use app\v1\service\FormatService;

class Finance extends Admin{
    const PAY_TYPE = [
        '0'=>'账户余额',
        '1'=>'支付宝',
        '2'=>'微信',
        '3'=>'其他'
    ];

    /**
     * 资金流水列表
     */
    public function index()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['order_sn']) && $search['order_sn'] != ''){
            $map['order_sn'] = array('like','%'.trim($search['order_sn']).'%');
        }
        if(isset($search['user_name']) && $search['user_name'] != ''){
            $map['user_name'] = array('like','%'.trim($search['user_name']).'%');
        }
        if(isset($search['cap_type']) && $search['cap_type'] != '')
        {
            $map['cap_type'] = $search['cap_type'];
        }
        if(isset($search['pay_type']) && $search['pay_type'] != '')
        {
            $map['pay_type'] = $search['pay_type'];
        }
        //创建时间
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
        if(isset($search['state']) && $search['state'] != '')
        {
            $map['state'] = $search['state'];
        }
        if(isset($search['keywords']) && $search['keywords'] != '')
        {
            $map['order_sn'] = array('like','%'.$search['keywords'].'%');
        }
        $fm = FinanceModel::self();
        $lists = $fm->getFinanceList($map,'id desc',10,$search);
        $page = $lists->render();
        $data = [];
        foreach($lists as $item)
        {
            $remark = json_decode($item['remark'],true);
            if (isset($remark['recharge_type'])) {
                $remark['recharge_type'] = FormatService::formatRechargeType($remark['recharge_type']);
            }
            if (isset($remark['pay_type'])) {
                $remark['pay_type'] = FormatService::formatPayType($remark['pay_type']);
            }

            $temp = lang($item['lang'], $remark);
            $array = explode('/',$temp);
            if (count($array) == 1) {
                $item['title'] = $array[0];
                $item['number'] = '';
                $item['remark'] = '';
            } elseif(count($array) == 2) {
                $item['title'] = $array[0];
                $item['number'] = $array[1];
                $item['remark'] = '';
            } else {
                $item['title'] = $array[0];
                $item['number'] = $array[1];
                $item['remark'] = $array[2];
            }
            $data[] = FormatService::formatNull($item->getData());
        }
        $this->assign('pay_type',  self::PAY_TYPE);
        $this->assign('search',$search);
        $this->assign('list',$data);
        $this->assign('page',$page);
        
        return view();
    }
}