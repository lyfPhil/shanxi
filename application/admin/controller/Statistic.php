<?php

namespace app\admin\controller;

use app\admin\controller\Admin;
use app\main\model\OrderModel;
use app\main\model\FinanceModel;
use think\Request;
use app\main\model\WithdrawModel;
use app\main\model\RechargeModel;
use app\main\model\GoodsModel;

class Statistic extends Admin{
    const  GOODS_TYPE=[
        //账号类型
        '1'     =>'帳號',
        '2'     =>'代練',
        '3'     =>'道具',
        '4'     =>'遊戲幣',
        '5'     =>'點數卡',
        '6'     =>'代儲',
        '7'     =>'產包',
        '8'     =>'其他',
    ];
    //put your code here
    public function order()
    {
        $param = Request::instance()->param();
        $datetime = 6;
        if(isset($param['date']) && $param['date'] != ''){
            $datetime = $param['date'];
        }
        $where = [];
        if(isset($param['goods_type']) && $param['goods_type'] != ''){
            $where['goods_type'] = $param['goods_type'];
        }
        $now = time();
        $time = $now-($now % 86400);
        $om = OrderModel::self();
        //今天数据
        $today_order_num = $om->where($where)->where('create_time','>',$time)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->count();
        $today_order_finance = $om->field('charge,actual')->where($where)->where('create_time','>',$time)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->select();
        $order_finance_data = array();
        $order_data = array();
        //计算往前六天的数据
        for($i=$datetime;$i>0;$i--)
        {
            $date = date("m-d",$time-$i*3600*24);
            $map['create_time'] = array('between',array($time-$i*24*3600,$time-($i-1)*24*3600));
            $order_num = $om->where($where)->where($map)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->count();//统计当天的订单数量
            $order_finance = $om->field('charge,actual')->where($where)->where($map)->where('state','in',[ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->select();
            $order_cash = 0;
            $order_charge = 0;
            foreach ($order_finance as $item){
                $order_cash += $item['actual'];
                $order_charge += $item['charge'];
            }
            $order_finance_data[] = array('date'=>$date,'order_cash'=>$order_cash,'order_charge'=>$order_charge);
            $order_data[] = array("date"=>$date,"order_num"=>$order_num);
        }
        $today_order_cash = 0;
        $today_order_charge = 0;
        foreach ($today_order_finance as $item){
            $today_order_cash += $item['actual'];
            $today_order_charge += $item['charge'];
        }
        $order_finance_data[] = array('date'=>date("m-d",$now),'order_cash'=>$today_order_cash,'order_charge'=>$today_order_charge);
        $order_data[] = ["date"=>date("m-d",$now),"order_num"=>$today_order_num];
        $this->assign('type',  self::GOODS_TYPE);
        $this->assign('order_finance', json_encode($order_finance_data));
        $this->assign('search',$param);
        $this->assign('order_data',json_encode($order_data));
        return view();
    }
    public function orderFinance()
    {
        $param = Request::instance()->param();
        $datetime = 6;
        if(isset($param['date']) && $param['date'] != ''){
            $datetime = $param['date'];
        }
        $where = [];
        if(isset($param['goods_type']) && $param['goods_type'] != ''){
            $where['goods_type'] = $param['goods_type'];
        }
        $now = time();
        $time = $now-($now % 86400);
        $om = OrderModel::self();
        //今天数据
        $today_order_finance = $om->field('charge,actual')->where($where)->where('create_time','>',$time)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->select();
        $order_finance_data = array();
        //计算往前六天的数据
        for($i=$datetime;$i>0;$i--)
        {
            $date = date("m-d",$time-$i*3600*24);
            $map['create_time'] = array('between',array($time-$i*24*3600,$time-($i-1)*24*3600));
            $order_finance = $om->field('charge,actual')->where($where)->where($map)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->select();
            $order_cash = 0;
            $order_charge = 0;
            foreach ($order_finance as $item){
                $order_cash += $item['actual'];
                $order_charge += $item['charge'];
            }
            $order_finance_data[] = array('date'=>$date,'order_cash'=>$order_cash,'order_charge'=>$order_charge);
        }
            $today_order_cash = 0;
            $today_order_charge = 0;
            foreach ($today_order_finance as $item){
                $today_order_cash += $item['actual'];
                $today_order_charge += $item['charge'];
            }
        $order_finance_data[] = array('date'=>date("m-d",$now),'order_cash'=>$today_order_cash,'order_charge'=>$today_order_charge);
        $this->assign('order_finance', json_encode($order_finance_data));
        $this->assign('search',$param);
        $this->assign('type',  self::GOODS_TYPE);
        return view();
    }
    public function withdraw(){
        $param = Request::instance()->param();
        $datetime = 6;
        if(isset($param['date']) && $param['date'] != ''){
            $datetime = $param['date'];
        }
        $now = time();
        $time = $now-($now % 86400);
        $withdraw_model = WithdrawModel::self();
        //今天数据
        $today_withdraw = $withdraw_model->field('cash,service_free')->where('create_time','>',$time)->where(['state'=>1,'type'=>0])->select();
        $today_withdraw_num = count($today_withdraw);
        $withdraw_data_num = array();
        $withdraw_data = array();
        //计算往前六天的数据
        for($i=$datetime;$i>0;$i--)
        {
            $date = date("m-d",$time-$i*3600*24);
            $map['create_time'] = array('between',array($time-$i*24*3600,$time-($i-1)*24*3600));
            $withdraw = $withdraw_model->field('cash,service_free')->where($map)->where(['state'=>1,'type'=>0])->select();
            $withdraw_num = count($withdraw);
            $withdraw_cash = 0;
            $withdraw_free = 0;
            foreach($withdraw as $item)
            {
                $withdraw_cash += $item['cash'];
                $withdraw_free += $item['service_free'];
            }
            $withdraw_data[] = array("date"=>$date,"withdraw"=>$withdraw_cash,"withdraw_free"=>$withdraw_free);
            $withdraw_data_num[] = array("date"=>$date,"withdraw_num"=>$withdraw_num);
        }
            $today_withdraw_cash = 0;
            $today_withdraw_free = 0;
            foreach($today_withdraw as $item)
            {
                $today_withdraw_cash += $item['cash'];
                $today_withdraw_free += $item['service_free'];
            }
        $withdraw_data[] = array("date"=>date("m-d",$now),"withdraw"=>$today_withdraw_cash,"withdraw_free"=>$today_withdraw_free);
        $withdraw_data_num[] = array("date"=>date("m-d",$now),"withdraw_num"=>$today_withdraw_num);
        $this->assign('withdraw_data',  json_encode($withdraw_data));
        $this->assign('withdraw_data_num',  json_encode($withdraw_data_num));
        $this->assign("search",$param);
        return view();
    }
    public function recharge(){
        $param = Request::instance()->param();
        $datetime = 6;
        if(isset($param['date']) && $param['date'] != ''){
            $datetime = $param['date'];
        }
        $now = time();
        $time = $now-($now % 86400);
        $recharge_model = RechargeModel::self();
        //今天数据
        $today_recharge = $recharge_model->field('cash')->where('create_time','>',$time)->where(['state'=>1])->select();
        $today_recharge_num = count($today_recharge);
        $recharge_data = array();
        $recharge_data_num = array();
        //计算往前六天的数据
        for($i=$datetime;$i>0;$i--)
        {
            $date = date("m-d",$time-$i*3600*24);
            $map['create_time'] = array('between',array($time-$i*24*3600,$time-($i-1)*24*3600));
            $recharge = $recharge_model->field('cash')->where($map)->where(['state'=>1])->select();
            $recharge_num = count($recharge);
            $recharge_cash = 0;
            foreach ($recharge as $item){
                $recharge_cash += $item['cash'];
            }
            $recharge_data[] = array("date"=>$date,"recharge_cash"=>$recharge_cash);
            $recharge_data_num[] = array("date"=>$date,"recharge_num"=>$recharge_num);
        }
        $today_recharge_cash = 0;
        foreach ($today_recharge as $item){
            $today_recharge_cash += $item['cash'];
        }
        $recharge_data[] = array("date"=>date("m-d",$now),"recharge_cash"=>$today_recharge_cash);
        $recharge_data_num[] = ["date"=>date("m-d",$now),"recharge_num"=>$today_recharge_num];
        $this->assign('recharge_data',  json_encode($recharge_data));
        $this->assign('recharge_data_num',json_encode($recharge_data_num));
        $this->assign('search',$param);
        return view();
    }
    public function Goods()
    {
        $gm = GoodsModel::self();
        $account = $gm->where('state',1)->where('goods_type',GOODS_TYPE_ACCOUNT)->count();
        $dailian = $gm->where('state',1)->where('goods_type',GOODS_TYPE_PL)->count();
        $prop = $gm->where('state',1)->where('goods_type',GOODS_TYPE_PROP)->count();
        $coin = $gm->where('state',1)->where('goods_type',GOODS_TYPE_COIN)->count();
        $pointcard = $gm->where('state',1)->where('goods_type',GOODS_TYPE_PCARD)->count();
        $rechange = $gm->where('state',1)->where('goods_type',GOODS_TYPE_RECHANGE)->count();
        $giftpack = $gm->where('state',1)->where('goods_type',GOODS_TYPE_PACKS)->count();
        $other = $gm->where('state',1)->where('goods_type',GOODS_TYPE_OTHER)->count();
        $data = [
            'account' => $account,
            'dailian' => $dailian,
            'prop'    => $prop,
            'coin'    => $coin,
            'pointcard'=> $pointcard,
            'rechange' => $rechange,
            'giftpack' => $giftpack,
            'other'    => $other
        ];
        $this->assign('goods',$data);
        return view();
    }
}