<?php
namespace app\common\service;

class CryptService {
    private static $key='*U()*@#IDKLJKLGD';
    private static $iv='*(!@IJDEF&*)!@%_';
    private static $mode = 'aes-128-cbc';
    public static  function getKey()
    {
        return [md5(self::$key, true), md5(self::$iv, true)];
    }
    public static function encrypt($orig_data){
        list($key, $iv) = self::getKey();
        $ciphertext= openssl_encrypt($orig_data, self::$mode, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($ciphertext);
    }
    public static function decrypt($ciphertext){
        list($key, $iv) = self::getKey();
        $ciphertext = base64_decode($ciphertext);
        $orig_data = openssl_decrypt($ciphertext, self::$mode, $key, OPENSSL_RAW_DATA, $iv);
        return $orig_data;
    }
}
