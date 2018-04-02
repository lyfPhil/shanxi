<?php
namespace app\tasks\job;
use app\main\extra\TimService;
use app\user\model\UserModel;

use think\Config;

class TimJob
{
    private $restApi=null;
    private $Um=null;
    private $config = null;
    private $SystemModel = null;

    public function setUp()
    {
        $this->restApi = TimService::getRestApi();
        $this->Um = \app\user\model\UserModel::self();
        $this->SystemModel = \app\admin\model\UserModel::self();
        $this->config = Config::get('setting.opentim');
    }

    public function perform() {
        $action = $this->args['action'];
        switch ($action) {
            case "REG":
                $this->register($this->args);
                break;
            case "UPDATE":
                $this->update_nickname($this->args);
                break;
            case "SEND":
                $this->send_message($this->args);
                break;
            case "SENDCUSTOM":
                $this->send_custom_msg($this->args);
                break;
            default:
                break;
        }
    }
	
    public function tearDown(){
        // ... Remove environment for this job
    }    

    private function register($args)
    {
        $user_id = $args['id'];
        $nickname = $args['nickname'];
        $username = get_im_id($user_id);
        if ($this->config['type'] == 0) {
            $res = $this->restApi->account_import($username, $nickname, "");
        } else {
            $pass = get_im_pass($user_id);
            $res = $this->restApi->register_account($username, 3, $pass);
        }
        if ($res && $res['ErrorCode'] == 0) {
            if($args['id']>=100000) {
                $this->Um->addAttr(['tim'=>1], $user_id, true);
            } else {
                $this->SystemModel->addAttr(['tim'=>1],$user_id);
            }
            
            if ($this->config['type'] != 0 && $nickname) {
                $this->restApi->profile_portrait_set($username, $nickname);
            } 
        } else {
            echo "register for [$user_id] failed  errmsg:".json_encode($res)."\n";
        }
    }

    private function update_nickname($args)
    {
        $user_id = $args['id'];
        $nickname = $args['nickname'];
        $username = get_im_id($user_id);
        $this->restApi->profile_portrait_set($username, $nickname);
    }

    private function send_message($args)
    {
        $from_id = $args['from_id'];
        $to_id = $args['to_id'];
        $from_user = get_im_id($from_id);
        $to_user = get_im_id($to_id);
        $msg = $args['messages'];
        $this->restApi->openim_send_msg($from_user, $to_user, $msg);
    }
    
    private function send_custom_msg($args)
    {
        $from_id = $args['from_id'];
        $to_id = $args['to_id'];
        $from_user = get_im_id($from_id);
        $to_user = get_im_id($to_id);
        $desc = $args['desc'];
        $text_content = $args['data'];
        $url  = $args['url'];
        $this->restApi->openim_send_custom_msg($from_user, $to_user, $text_content, $desc, $url);
    }
}
