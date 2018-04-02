<?php
namespace app\tasks\job;
use app\main\extra\EasemobService;
use app\user\model\UserModel;

class EasemobJob
{
    private $Em=null;
    private $Um=null;

    public function setUp()
	{
        $this->Em = new EasemobService();
        $this->Um = UserModel::self();
	}

	public function perform()
	{
        $action = $this->args['action'];
        switch($action)
        {
        case "REG":
            $this->register($this->args);
            break;
        case "UPDATE":
            $this->update_nickname($this->args);
            break;
        case "SEND":
            $this->send_message($this->args);
            break;
        default:
            break;
        }
	}
	
	public function tearDown()
	{
		// ... Remove environment for this job
	}    

    private function register($args)
    {
        $user_id = $args['id'];
        $nickname = $args['nickname'];
        $username = get_im_id($user_id);
        $pass = get_im_pass($user_id);
        $res=$this->Em->auth_register($username, $pass, $nickname);
        $this->Um->addAttr(['easemob'=>1], $user_id, true);
    }

    private function update_nickname($args)
    {
        $user_id = $args['id'];
        $nickname = $args['nickname'];
        $username = get_im_id($user_id);
        $this->Em->update_user_nickname($username, $nickname);
    }

    private function send_message($args)
    {
        $from_id = $args['from_id'];
        $to_id = $args['to_id'];
        $from_user = get_im_id($from_id);
        $to_user = get_im_id($to_id);
        $msg = $args['messages'];
        $this->Em->send_text_message($from_user, $to_user, $msg);
    }
}
