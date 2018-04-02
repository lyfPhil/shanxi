<?php
namespace app\tasks\job;

use app\user\model\VerifyModel;
use think\View;
use think\Config;

class MailJob
{
    private $view = null;
    public function setUp()
	{
            $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));
            $this->vm = VerifyModel::self();
	}

	public function perform()
	{
            $mail = new \PHPMailer();
            $mail->isSMTP();
            $mail->CharSet = "utf8";
            $mail->Host = "hwsmtp.exmail.qq.com";
            $mail->SMTPAuth = true;
            $mail->Username = "service@tpgame.co.th";
            $mail->Password = "Pr24V7GxYuhv9iQu";
            $mail->SMTPSecure = "ssl";
            $mail->Port = 465;
            $mail->setFrom("service@tpgame.co.th","TPGame");
            $address = $this->args['address'];
            $strs="";
            if (is_array($address))
            {
                foreach ($address as $v)
                {
                    $mail->AddAddress($v);
                    $strs=$v.";";
                }
            }
            else
            {
                $mail->AddAddress($address);
                $strs = $address;
            }
            $mail->Subject = $this->args['subject'];
            $template = $this->args['template'];
            $params = $this->args['params'];
            $body = $this->view->fetch($template, $params);
            $mail->Body = $body;
            $mail->IsHTML(true);
            if(!$mail->send())
            {
                echo "Send to [$strs] Error: ".$mail->ErrorInfo. "\n";
            }
            else
            {
                if ($this->args['type'] == 0) {
                    $info = $this->vm->getByaccount($address);
                    $now = time();
                    $data = [
                        'id'         =>$info['id'],
                        'count'      =>$info['count'] +1,
                        'send_time'  =>$now,
                        'expire_time'=>$now + 120    //2分钟内有效
                    ];
                    $this->vm->updateVerifycode($data);
                }
                echo "Send to [$strs] Success \n";
            }
	}

	public function tearDown()
	{

	}
}
