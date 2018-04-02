<?php
namespace app\common\service;
use think\Config;

class SignService {
    public static function checkSign($param, $path){
        $post_sign = strtolower($param['sign']);
        $timestamp = intval($param['t'])/1000;
        $sign_config = Config::get('setting.sign');
        $expire = 300;
        if (isset($sign_config['sign_expire']))
        {
            $expire = $sign_config['sign_expire'];
        }

        if (!isset($sign_config['api_key']))
        {
            return false;
        }
        $api_keys = $sign_config['api_key'];

        $now = time();
        if ($now - $timestamp > $expire)
        {
            return false;
        }
        $dev_type = $param['device_type'];
        $key = $api_keys[$dev_type];
        $s = '/'.$path.$dev_type.$key.$param['t'];
        $sign = strtolower(md5($s));
        $result = $post_sign!=$sign?false:true;
        return $result;
    }
}
