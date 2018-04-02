<?php
namespace app\pay\service;

use think\Config;
use app\main\model\CustomerModel;
use app\main\model\GoodsModel;
use app\main\model\OrderModel;
use app\main\model\PayModel;

class PaymentService{
    protected static $_conf = null;
    protected static $_key  = null;
    protected static function init_conf()
    {
        if (self::$_conf)
            return ;
        $_conf=Config::get('pay.common'); 
        self::$_conf = $_conf;
        self::$_key = $_conf['key'];
        return self::$_conf;
    }
    
    public static function makePay($user_id, $order_id, $pay_type, $order_sn) 
    {
        self::init_conf();
        $om = OrderModel::self();
        $orderInfo = $om->get(['id'=>$order_id]);
        if (!$orderInfo)
        {
            return false;
        }
        if ($orderInfo['buy_id'] != $user_id ||
            $orderInfo['order_sn'] != $order_sn)
        {
            return false;
        }

        $pay_data = [
            'amount'=> 0.01,//$orderInfo['total'], //0.01 for test
            'description'=> $orderInfo['title'],
            'order_id'=>$order_sn .'_'.$order_id,
            'currency' =>'USD', // self::$_conf['currency'],
        ]; 
        $pm = PayModel::self();
        $payinfo = $pm->getByOrderSN($pay_data['order_id']);
        //todo expired check
        if ($payinfo && $payinfo['pay_type'] == $pay_type)
        {
            return $payinfo->getData();
        }
        $res = false;
        switch($pay_type)
        {
        case 'payssion_alipay':
            $pay_data['pm_id'] = 'alipay_cn';
            $res = PayssionService::create($pay_data);
            break;
        case 'payssion_tenpay':
            $pay_data['pm_id'] = 'tenpay_cn';
            $res = PayssionService::create($pay_data);
            break;
        default:
            return false;
            break;
        }

        if (!$res || !is_array($res))
        {
            return false;
        }
        $res['amount'] = $pay_data['amount'];
        $res['order_sn'] = $pay_data['order_id'];
        $res['currency'] = $pay_data['currency'];
        $res['order_type'] = 0;
        $res['pay_type'] = $pay_type;
        $pm->add($res);
        return $res;       
    }
    
    public static function makeDefaultPay($user_id,$order_id, $pay_type, $order_sn){
        $o = OrderModel::self();
        $order = $o->field('id,order_sn,goods_id,buy_id,total,actual,num,title,price,state')
                ->where(['order_sn'=>$order_sn,'id'=>$order_id])->find();
        if(!$order){
            return 404600;
        }
        if($order['state']!=1){
            return 400;
        }
        $user = CustomerModel::self()->getOneUserInfo($user_id,'deal_password,balance');
        if($user['deal_password']==''){
            return 400200;
        }
        if($user['balance']<$order['actual']){
            return 400500;
        }
        $goods = GoodsModel::self()->field('pic_url')->where('id',$order['goods_id'])->find();
        if(!$goods){
            return 901009;
        }
        $pay['order_id'] = $order['order_sn'].'_'.$order['id'];
        $pm = PayModel::self();
        $pay_data = $pm->getByOrderSN($pay['order_id']);
        //过期时间
        if($pay_data && $pay_data['pay_type']==$pay_type)
        {
            if($pay_data['amount']!=$order['actual']){//卖家修改价格后
                $res['amount'] = $order['actual'];
                $res['update_time'] = time();
                $pm->where('order_sn',$pay['order_id'])->update($res);
            }else{
                $res['amount'] = $pay_data['amount']; 
            }
            $res['transid'] = $pay_data['transid'];           
            $res['order_sn'] = $pay_data['order_sn'];
            $res['currency'] = $pay_data['currency'];
            $res['order_type'] = 0;
            $res['pay_type'] = $pay_type;
        }else{
            $res['create_time'] = time();
            $res['transid'] = $order['order_sn'];
            $res['amount'] = $order['actual'];
            $res['order_sn'] = $pay['order_id'];
            $res['currency'] = 'THB';//配置的货币
            $res['order_type'] = 0;
            $res['pay_type'] = $pay_type;
            $pm->insert($res);
        }
        $res['goods_price'] = $order['price'];
        $res['pic_url'] = $goods['pic_url'];
        $res['description'] = $order['title'];
        return $res;
    }
}
