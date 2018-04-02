<?php
namespace app\pay\service;
use think\Config;
use app\pay\extend\PayssionClient;

class PayssionService{
    protected static $_conf = null;
    public static function getHandler() 
    {
        if (!self::$_conf)
        {
            $_conf=Config::get('pay.payssion'); 
            self::$_conf = $_conf;
        }
        $apikey = self::$_conf['apikey'];
        $secretkey = self::$_conf['secretkey'];
        $online = self::$_conf['online'];
        $handler = new PayssionClient($apikey, $secretkey, $online);
        return $handler;
    }

    public static function Create($pay_data)
    {
        $handler = self::getHandler();
        $data = [
            'amount'=>$pay_data['amount'],
            'description'=> $pay_data['description'],
            'order_id'=>$pay_data['order_id'],
            'pm_id'=>$pay_data['pm_id'],
        ];
        if (isset($pay_data['return_url']))
        {
            $data['return_url']=$pay_data['return_url'];
        }

        if (isset($pay_data['currency']))
        {
            $data['currency'] = $pay_data['currency'];
        }
        else
        {
            $data['currency'] = self::$_conf['currency'];
        }
        $response = $handler->create($data);
        if ($handler->isSuccess())
        {
            $todo = $response['todo'];
            if ($todo) {
                $todo_list = explode('|', $todo);
                if (in_array("redirect", $todo_list)) {
                    $paylink = $response['redirect_url'];
                    $trans = $response['transaction'];
                    $trans_id = $trans['transaction_id'];
                    return ['pay_url'=>$paylink, 'transid'=>$trans_id];
                }
            } 
        }
        return false;
    }
    public static function getDetail($order_id, $trans_id)
    {
        $handler = self::getHandler();
        $response = $handler->getDetails(['order_id'=> $order_id, 'transaction_id'=> $trans_id]);
        if (!$handler->isSuccess())
        {
            return false;
        }
        return $response['transaction'];
    }

    public static function notifyCheck($params)
    {
        if (!self::$_conf)
        {
            $_conf=Config::get('pay.payssion'); 
            self::$_conf = $_conf;
        }
        $apikey = self::$_conf['apikey'];
        $secretkey = self::$_conf['secretkey'];
        $pm_id = $params['pm_id'];
        $amount = $params['amount'];
        $currency = $params['currency'];
        $order_id = $params['order_id'];
        $state = $params['state'];
        $check_array = array(
            $apikey,
            $pm_id,
            $amount,
            $currency,
            $order_id,
            $state,
            $secretkey
        );
        $check_msg = implode('|', $check_array);
        $check_sig = md5($check_msg);
        $notify_sig = $params['notify_sig'];
        return $notify_sig === $check_sig;
    }
}
