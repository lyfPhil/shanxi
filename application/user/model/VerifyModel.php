<?php

namespace app\user\model;

use think\Db;
use think\Model;

class VerifyModel extends Model
{
    protected $name = "qly_verification_code";
    protected $connection = 'db.user';

    public static function self(){
        return new self();
    }

    public function checkCode($account, $code)
    {
        if (empty($account) || empty($code)) return false;
        $now   = time();
        $item  = $this->where('account', $account)->find();
        if (empty($item)) {
            return 900601;
        }
        if ($code === $item['code']) {
            if ($now > $item['expire_time']) {
                return 900601;
            } else {
                return 200;
            }
        } else {
            return 900601;
        }
    }

    public function getVerifycode($account)
    {
        if (empty($account)) return false;
        $now   = time();
        $maxCount   = 5;
        $item  = $this->where('account', $account)->find();
        $result                = false;
        $find = false;
        if (empty($item)) {
            $result = true;
        } else {
            $item = $item->getData();
            $find = true;
            $sendTime       = $item['send_time'];
            $todayStartTime = strtotime(date('Y-m-d', $now));
            if ($sendTime < $todayStartTime){
                $item['count'] = 0;
                $result = true;
            }elseif(($item['count'] < $maxCount)&&(($now - $sendTime)>60)) {
                $result = true;
            }elseif($item['count'] > $maxCount){
                return false;
            }elseif(($now - $sendTime)<60){
                return 0;
            }
        }

        if ($result)
        {
            if (!$find)
            {
                $result = get_verification_code();
                $data=[
                    'count' => 0,
                    'send_time'=>$now,
                    'expire_time'=>$now +300,
                    'code'=>$result,
                    'account'=>$account
                    ];
                $this->updateVerifycode($data);
            }
            elseif($item['expire_time'] < $now) //过期重新获取验证码
            {
                $result = get_verification_code();
                $item['code'] = $result;
                $this->updateVerifycode($item);
            }
            else
            {                                   //60秒过后获取新的验证码
                $result = get_verification_code();
                $item['code'] = $result;
                $this->updateVerifycode($item);
            }
        }
        return $result;
    }

    public function updateVerifycode($data)
    {
        if (isset($data['id']) && $data['id'] > 0)
        {
            return  $this->where('id', $data['id'])->update($data);
        }
        else
        {
            return  $this->insert($data);
        }
    }

    public function getByaccount($account)
    {
        return $this->where('account', $account)->find();
    }
}
