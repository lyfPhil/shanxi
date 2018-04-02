<?php
//环信
namespace app\main\extra;
use think\Cache;
use think\Config;

class EasemobService{
    private $url = '';
	private $header=array();
    private $token_key='easemob_token';
    private $token='';

    public function __construct(){
        $config = Config::get('setting.easemob');
        $this->url = $config['url'];
        $this->token = Cache::get($this->token_key);
        if (!$this->token)
        {
            $data = [
                'grant_type'    => 'client_credentials',
                'client_id'     => $config['client_id'],
                'client_secret' => $config['client_secret'],
            ];
            $url = $this->url."/token";
            $rs=$this->curl($url, $data);
            $this->token = $rs['access_token'];
            Cache::set($this->token_key, $this->token, $rs['expires_in']);
        }

        $this->header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        );
    }

    public function register($username, $password, $nickname){
        $url = $this->url . "/users";
        $data = array(
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname
        );

        return $this->curl($url, $data, array($this->header[0]), "POST");
    }

    public function auth_register($username, $password, $nickname){
        $url = $this->url . "/users";
        $data = array(
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname
        );
        return $this->curl($url, $data, $this->header, "POST");
    }
    /*
     * 注册IM用户[批量]批量注册的用户数量不要过多, 建议在20-60之间
     *
     *@param  $data   二维数组
     */
    public function more_register($data){
        $url = $this->url . "/users";
        return $this->curl($url, $data, $this->header, "POST");
    }
    /*
     * 获取IM用户[单个]
     */
    public function get_single_user($username){
        $url = $this->url."/users/${username}";
        return $this->curl($url, "", array($this->header[1]), "GET");
    }
    /*
     * 获取IM用户[批量]可分页
     */
    public function get_more_user($limit,$page=''){
        $url = $this->url . "/users?limit=".$limit;
        if($page!=''){
            $url.='&cursor='.$page;
        }
        return $this->curl($url, "", array($this->header[1]), "GET");
    }
    /*
     * 删除IM用户[单个]
     */
    public function delete_single_user($username){
        $url = $this->url . "/users/${username}";
        return $this->curl($url, "", array($this->header[1]), "DELETE");
    }
    /*
     * 删除IM用户[批量]  删除最开始的n条
     */
    public function delete_more_user($limit,$page=''){
        $url = $this->url . "/users/?limit=".$limit;
        if($page !=''){
            $url.='&cursor='.$page;
        }
        return $this->curl($url, "", array($this->header[1]), "DELETE");
    }

    /*
     * 重置IM用户密码
     */
    public function update_user_password($username, $newpassword){
        $url = $this->url . "/users/${username}/password";
        $data['newpassword'] = $newpassword;
        return $this->curl($url, $data, array($this->header[1]), "PUT");
    }
    /*
     * 修改用户昵称
     */
    public function update_user_nickname($username, $nickname){
        $url = $this->url . "/users/${username}";
        $data['nickname'] = $nickname;
        return $this->curl($url, $data, array($this->header[1]), "PUT");
    }
    /*
     * 给IM用户的添加好友
     * 给owner_username用户添加friend_username用户
     */
    public function add_friend_contacts($owner_username, $friend_username){
        $url = $this->url . "/users/${owner_username}/contacts/users/${friend_username}";
        return $this->curl($url, "", array($this->header[1]), "POST");
    }
    /*
     * 解除IM用户的好友关系
     * 给owner_username解除friend_username关系
     */
    public function delete_user_contacts($owner_username, $friend_username){
        $url = $this->url . "/users/${owner_username}/contacts/users/${friend_username}";
        return $this->curl($url, "",  array($this->header[1]), "DELETE");
    }
    /*
     *
     *查看好友  查看某个IM用户的好友信息
     */
    public function get_contacts_user($owner_username){
        $url = $this->url . "/users/${owner_username}/contacts/users";
        return $this->curl($url, "",  array($this->header[1]), "GET");
    }
    /*
     *
     *获取用户黑名单
     */
    public function get_user_blockslist($owner_username,$data){
        $url = $this->url . "/users/${owner_username}/blocks/users";
        return $this->curl($url, $data,  array($this->header[1]), "GET");
    }
    /*
     *
     *给某个用户添加黑名单
     */
    public function add_user_blockslist($owner_username,$data){
        $url = $this->url . "/users/${owner_username}/blocks/users";
        return $this->curl($url, $data,  array($this->header[1]), "POST");
    }

    /*
     *
     *从IM用户的黑名单中减人
     */
    public function delete_user_blockslist($owner_username,$blocked_username){
        $url = $this->url . "/users/${owner_username}/blocks/users/${blocked_username}";
        return $this->curl($url, '', array($this->header[1]), "DELETE");
    }
    /*
     *
     *查看用户在线状态
     */
    public function is_line($username){
        $url = $this->url . "/users/${username}/status";
        $header = array(
            'Content-Type:application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, '', $header, "GET");
    }
    /*
     *
     *查看用户查询离线消息数 获取一个IM用户的离线消息数
     */
    public function offline_msg_count($owner_username){
        $url = $this->url . "/users/${owner_username}/offline_msg_count";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, '', $header, "GET");
    }
    /*
     *
     *通过离线消息的id查看用户的该条离线消息状态
     */
    public function offline_msg_status($username,$msg_id){
        $url = $this->url . "/users/${username}/offline_msg_status/${msg_id}";
        $header = array(
            'Content-Type : application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, '', $header, "GET");
    }
    /*
     *
     *禁用某个IM用户的账号，禁用后该用户不可登录，下次解禁后该账户恢复正常使用。
     */
    public function forbidden_user($username){
        $url = $this->url . "/users/${username}/deactivate";
        $header = array(
            'Content-Type : application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, '', $header, "POST");
    }
    /*
     *
     *解除对某个IM用户账号的禁用，解禁后用户恢复正常使用。
     */
    public function unforbidden_user($username){
        $url = $this->url . "/users/${username}/activate";
        $header = array(
            'Content-Type : application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, '', $header, "POST");
    }
    /*
     *
     *强制用户下线如果某个IM用户已经登录环信服务器，强制其退出登录
     */
    public function user_disconnect($username){
        $url = $this->url . "/users/${username}/disconnect";
        $header = array(
            'Content-Type : application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, '', $header, "GET");
    }
    /*
     *
     *导出聊天记录
     */
    public function download_chatmessages($hour){
        $url = $this->url . "/chatmessages/".$hour;
        $header = array(
            'Content-Type : application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, '', $header, "GET");
    }
    /*
    发送文本消息
     */
    public function send_text_message($sender, $receiver, $msg){
        $url = $this->url . "/messages";

        $header = array(
            'Content-Type : application/json',
            'Authorization: Bearer ' . $this->token
        );
        $data = array(
            'target_type' => 'users',
            'target' => array(
                '0' => $receiver
            ),
            'msg' => array(
                'type' => "txt",
                'msg' => $msg
            ),
            'from' => $sender,
            'ext' => array(
                'attr1' => 'v1',
                'attr2' => "v2"
            )
        );
        return $this->curl($url, $data, $header, "POST");
    }

    /*
    获取app中所有的群组
     */
    public function get_all_chatgroups(){
        $url = $this->url . "/chatgroups";

        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, '', $header, "GET");
    }

    /*
     * curl请求
     */
    private function curl($url, $data, $header = false, $method = "POST"){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $ret = curl_exec($ch);
        return json_decode($ret,true);
    }
}
