<?php

namespace app\main\service;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use think\Config;
use Qiniu\Cdn\CdnManager;;

class QiniuService {
    protected static $handler = null;
    protected static $_auth = null;
    protected static $_bucket = null;
    protected static function getHander()
    {
        if (self::$handler)
            return self::$handler;
        $qiniu_conf=Config::get('setting.qiniu'); 
        $accessKey = $qiniu_conf['acckey'];
        $secretKey = $qiniu_conf['seckey'];
        $bucket = $qiniu_conf['bucket'];
        $auth = new Auth($accessKey, $secretKey);
        self::$_auth= $auth;
        self::$_bucket = $bucket;
        $token = $auth->uploadToken($bucket);
        $uploadMgr=new UploadManager();
        self::$handler = [$uploadMgr, $token];
        return self::$handler;
    }
 
    public static function upload($key, $file_path)
    {
        list($uploadMgr, $token) = self::getHander(); 
        list($ret, $err)=$uploadMgr->putFile($token, $key, $file_path);
        if ($err !== null)
            return $err;
        return 0; 
    }

    public static function remove($key)
    {
        if (self::$_auth == null)
            self::getHander();
        $bucketManager = new BucketManager(self::$_auth);
        $err = $bucketManager->delete(self::$_bucket, $key);
        if ($err !== null)
            return $err;
        return 0; 
    }

    public static function addSign($path)
    {
        $qiniu_conf=Config::get('setting.qiniu'); 
        $key = $qiniu_conf['key1'];
        $exp = $qiniu_conf['expire'];
        $t = time()+$exp;
        $T = sprintf("%08x", time());
        $sign = strtolower(md5($key.$path.$T));
        return $path."?sign={$sign}&t={$T}";
    }
    public static function refresh($urls)
    {
        $cdnManager = new CdnManager(self::$_auth);
        list($refreshResult, $refreshErr) = $cdnManager->refreshUrlsAndDirs($urls,$dirs=[]);
        if ($refreshErr != null) {
            return $refreshErr;
        } else {
            return $refreshResult;
        }
    }
}
