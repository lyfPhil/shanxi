<?php

namespace app\user\model;

use think\Db;
use think\Model;
use think\Config;

class TokenModel extends Model
{
    protected $name = "qly_user_token";
    protected $connection = 'db.user';

    public static function self(){
        return new self();
    } 

    public function setToken($user, $data)
    {
        $map = ['user_id'=>$user['id'], 'device_type'=>$data['device_type']];
        if($this->where($map)->find())
            return $ret=$this->where($map)->update($data);
        else
            return $this->insert($data);
    }

    public function delToken($userId, $device_type)
    {
        $map = ['user_id'=>$userId, 'device_type'=>$device_type];
        $res = $this->where($map)->delete();
        return 0;
    }
}
