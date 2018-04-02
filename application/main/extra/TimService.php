<?php
namespace app\main\extra;

use think\Cache;
use think\Config;

class TimService{
    public static  $restApi = null;
    public static  $sigApi = null;

    public static function getSigApi()
    {
        if (self::$sigApi)
            return self::$sigApi;
        $config = Config::get('setting.opentim');
        $sigApi = new opentim\TLSSigApi();
        $sigApi->SetAppid($config['appid']);
        $sigApi->SetPrivateKey($config['private_key']);
        $sigApi->SetPublicKey($config['public_key']);
        self::$sigApi = $sigApi;
        return $sigApi;
    }

    public static function getRestApi()
    {
        if (self::$restApi)
            return self::$restApi;
        $config = Config::get('setting.opentim');
        $restApi = new opentim\TimRestApi;
        $admin_id = $config['admin_id'];
        $restApi->init($config['appid'], $config['admin_id']);
        if ($config['type'] == 0)
            $sig = self::getUserSig($config['admin_id'], true);
        else
            $sig = $config['admin_sig'];
        $restApi->set_user_sig($sig);
        self::$restApi = $restApi;
        return $restApi;
    }

    public static function getUserSig($user, $isadmin=false)
    {
        $key = 'OPEN_TIM_SIG_'.$user;
        $sig = Cache::get($key);
        if ($sig)
        {
            return $sig;
        }
        $sigApi = self::getSigApi();
        $config = Config::get('setting.opentim');
        $expire = $config['expire'];
        if ($isadmin)$expire = $expire * 30;
        $sig = $sigApi->genSig($user, $expire);
        if ($sig)
            Cache::set($key, $sig, $expire/2);
        return $sig;
    }
}
