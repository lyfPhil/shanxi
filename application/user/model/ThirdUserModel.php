<?php
namespace app\user\model;

use think\Model;

class ThirdUserModel extends Model{

	protected $name = "qly_third_party_user";
    protected $connection = 'db.user';

    public static function self(){
    	return new self();
    }

    public function thirdPartyUserIsExsit($third_user_exsit){
    	$map = [
            'third_id' => $third_user_exsit['third_id'],
            'third_type' => $third_user_exsit['third_type'],
            'status' => 1
    	];
    	return $this->field('id, user_id, login_times')->where($map)->find();
    }

    public function thirdPartyUserReg($user_id, $user_info){
    	$third_user = [
			'user_id'         => $user_id,
			'third_id'        => $user_info['third_id'],
			'third_type'      => $user_info['third_type'],
			'nickname'        => $user_info['username'],
			'union_id'        => '',
			'access_token'    => '',
			'create_time'     => $user_info['reg_time'],
			'login_times'     => 1,
			'last_login_ip'   => $user_info['last_login_ip'],
			'last_login_time' => $user_info['last_login_time'],
			'status'          => 1
    	];
    	return $this->insertGetId($third_user);
    }
    /**
     * thirdPartyUserBind 账号绑定第三方
     * @return int
     */
    public function thirdPartyUserBind($user_id, $third_user) {
        $where = [
            'user_id' => $user_id,
            'third_id' => $third_user['third_id'],
            'third_type' => $third_user['third_type'],
            'status'  => 0
        ];
        //用户昵称，有邮箱用邮箱，没有用昵称
        $nickname = (isset($third_user['email']) && $third_user['email'] != '') ? $third_user['email'] : $third_user['nickname'];
        $exsit = $this->field('id')->where($where)->order('id desc')->find();
        if ($exsit) {
            $where['id'] = $exsit['id'];
            $ret = $this->where($where)->setField(['status' => 1, 'nickname' => $nickname]);
            if ($ret > 0) {
                $res['id'] = intval($exsit['id']);
            }
        } else {
            $time = time();
            $ip = intval(get_client_ip(1, true));
            $third_user = [
                'user_id'    => $user_id,
                'third_id'   => $third_user['third_id'],
                'third_type' => $third_user['third_type'],
                'nickname'   => $nickname,
                'union_id'   => '',
                'access_token' => '',
                'create_time' => $time,
                'login_times' => 1,
                'last_login_ip' => $ip,
                'last_login_time' => $time,
                'status' => 1
            ];
            $id = $this->insertGetId($third_user);
            $res['id'] = $id;
        }
        $res['nickname'] = $nickname;
        $res['third_type'] = $third_user['third_type'];
        return $res;
    }
}