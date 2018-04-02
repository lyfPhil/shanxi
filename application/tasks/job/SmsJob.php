<?php
namespace app\tasks\job;

use think\Config;
use app\user\model\VerifyModel;
use app\main\extra\SmsSingleSender;

class SmsJob
{
    private $sender = null;
    private $vm = null; 

    public function setUp()
	{
            $sms_conf = Config::get('setting.sms');
            $appid = $sms_conf['appid'];
            $appkey = $sms_conf['appkey'];
            $this->sender = new SmsSingleSender($appid, $appkey);
            $this->vm = VerifyModel::self();
	}

	public function perform()
	{
            $phone = $this->args['phone'];
            $country_code = strval($this->args['code']);
            $mobile = $country_code.$phone;
            $info = $this->vm->getByaccount($mobile);
        if (!$info)
        {
            echo "Get from db failed\n";
            return ;
        }
        if (isset($this->args['tmplId']))
        {
            $tmplId = $this->args['tmplId'];
            $params = $this->args['params'];
            $result = $this->sender->sendWithParam($country_code, $phone, $tmplId, $params, "", "", "");
        }
        else
        {
            $msg = $this->args['msg'];
            $result = $this->sender->send(0, $country_code, $phone, $msg, '', ''); 
        }
        $rsp = json_decode($result, true);
        if ($rsp)
        {
            if($rsp['result'] == 0)
            {
                $now = time();
                $data = [
                    'id'=>$info['id'],
                    'send_time'=>$now,
                    'expire_time'=>$now +300,
                    'count'=>$info['count'] + 1,
                ];
                $this->vm->updateVerifycode($data);
            }
            else
            {
                echo "Send to [$phone] faile Error: ".$rsp['errmsg']."\n";
            }
        }
        else
            echo "Send to [$phone] return null \n";
	}
	
	public function tearDown()
	{

	}    
}
