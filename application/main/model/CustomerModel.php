<?php

namespace app\main\model;

use app\user\model\UserModel;
use app\main\model\CertificationModel;
use app\user\service\LoginService;
use app\main\model\PrevidModel;
use think\Model;

class CustomerModel extends Model{
    protected $name = "tab_user";
    protected $connection = 'db.main';
    public static function self(){
        return new self();
    }
    /**
     * 获取一位用户信息
     * @param string $user_id 用户ID
     * @param string $field 所需字段
     * @return array/false
     */
    public function getOneUserInfo($user_id,$field = '*'){
        return $this->field($field)->where('id',$user_id)->find();
    }
    /*
     * 批量获取卖家消息
     */
    public function getSellerBatch($seller_id){
        $seller = $this->field('id,seller_reputation,order_num,avatar,nickname,deposit,start_rating,flag,vid')
                  ->where('id','in',$seller_id)->select();
        $seller_list = [];
        foreach($seller as $val){
            $seller_list[$val['id']] = $val;
        }
        return $seller_list;
    }
    //用户中心注册成功后这里需要初始化交易相关用户信息
    public function initUser($user_id, $data){
        $this->startTrans();
        try{
            $previd_model = PrevidModel::self();
            $vid = $previd_model->getVid();
            $item=[
                'id'  => $user_id,
                'vid' => $vid,
                'avatar'   => $data['avatar'],
                'nickname' => $vid,
                'lock_status' => 1,
            ];
            $this->insert($item);
            $previd_model->lockVid($vid,$user_id);
            $this->commit();
            return $vid;
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }
    /*
     * 只更新交易表
     */
    public function updateJyUser($user_id,$data){
        $this->where('id',$user_id)->update($data);
        return 0;
    }
    /*
     * 更新交易表和用户中心
     */
    public function updateUser($user_id,$data){
        $um = UserModel::self();
        $re = RecordModel::self();
        if (isset($data['nickname'])) {
            $count = $this->where('nickname',$data['nickname'])->count();
            if ($count > 0) {
                return 901503;
            }
        }
        $um->startTrans();
        $this->startTrans();
        try{
            $this->where('id',$user_id)->update($data);
            if(isset($data['nickname']) || isset($data['avatar']))
            {
                $um->updateUser($data, $user_id);
                LoginService::updateTokendata($user_id, $data);
                if(isset($data['avatar'])) {
                    $record['type'] = 6;
                    $record['user_id'] = $user_id;
                    $record['record'] = [
                        'avatar' => $data['avatar']
                    ];
                    $re->addRecord($record);
                }
                $um->commit();
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            $um->rollback();
            throw Exception($e);
        }
        return 0;
    }
    //添加身份认证
    public function addIdCard($user_id,$data)
    {
        $cnt = $this->where('idcard',$data['idcard'])->count();
        if ($cnt>0) {
            return 900903;
        }
        $map = ['id'=>$user_id];
        $this->where($map)->update($data);
        return 0;
    }

    public function getCertInfoById($user_id)
    {
        $map = ['a.id'=>$user_id];
        $ret = $this->alias('a')
                ->field('a.idcard,a.real_name,b.status,b.id')
                ->join('tab_certification b','a.id=b.user_id','LEFT')
                ->where($map)->order('b.id desc')->find();
        return $ret;
    }
    /*
     * 设置交易密码
     */
    public function setJyPass($pass,$user){
        if($pass['deal_password']!=$pass['repassword']) {
            return 900204;
        }
        $uniq = UserModel::self()->field('uniq_id,mobile')->where('id',$user['id']) ->find();
        if ($user['idcard']==''||$uniq['mobile']=='') {
            return 400;
        }
        $data['deal_password'] = make_password($pass['deal_password'],$uniq['uniq_id']);
        $ret = $this->where('id',$user['id'])->update($data);
        if ($ret==1) {
            return 0;
        } else {
            return 900209;
        }
    }

    /**
     * 获取已认证卖家列表 后台使用
     * @param string $field 字段名
     * @param array $map 筛选条件
     * @return array
     */
    public function getSellerList($field='*',$map)
    {
        $list = $this->field($field)
                ->where('seller_status',1)
                ->where($map)
                ->order('seller_reputation')
                ->select();
        return $list;
    }
    public function getImAvatar($userIds){
        $list = [];
        if ($userIds != '') {
            $sql = $this->field('id,avatar,nickname')->where('id','in',$userIds)->select(false);
            $sql.= " order by field(id,{$userIds})";
            $data = $this->query($sql);
            $list = [];
            foreach($data as $val){
                $val['avatar'] = imgurl_add_sign($val['avatar']);
                $list[]=$val;
            }
        }
        return $list;
    }
    /*
     * 批量获取用户信息头像
     */
    public function getUserInfoByIds($userIds){
        $data = $this->field('id,avatar,nickname')->where('id','in',$userIds)->select();
        $info = [];
        foreach($data as $val){
            $info[$val['id']]=$val;
        }
        return $info;
    }
    /**
     * 提交更新用户信息
     * @param array $data
     * @return int
     * @throws type
     */
    public function updateInfo($data)
    {
        //事务
        $um = UserModel::self();
        $um->startTrans();
        $this->startTrans();
        try {
            if(isset($data['password']) && $data['password'] != ''){
                $result =$um->field('id,uniq_id')->where('id',$data['id'])->find();
                $user_uniq = $result['uniq_id'];
                $update = [
                    'password'=>make_password(strtoupper(sha1($data['password'])), $user_uniq),
                    'update_time'=>time()
                ];
                $um->where('id',$data['id'])->update($update);
            }
            if(isset($data['lock_status']) && $data['lock_status']!=''){
                $this->where('id',$data['id'])->update(['lock_status' => $data['lock_status']]);
                $um->where('id',$data['id'])->update(['status'=>$data['lock_status']]);
            }
            if(isset($data['deal_password']) && $data['deal_password'] != ''){
                $uniq = UserModel::self()->field('uniq_id')->where('id',$data['id'])->find();
                $update = [
                    'deal_password' => make_password(strtoupper(sha1($data['deal_password'])), $uniq['uniq_id'])
                ];
                $this->where('id',$data['id'])->update($update);
            }
            $this->commit();
            $um->commit();
        } catch (Exception $ex) {
            $this->rollback();
            $um->roolback();
            throw Exception($ex);
        }
        return 0;
    }
}
