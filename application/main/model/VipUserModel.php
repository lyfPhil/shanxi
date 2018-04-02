<?php
namespace app\main\model;

use app\main\model\VipModel;
use app\main\model\ConfigModel;
use think\Model;

class VipUserModel extends Model{

    protected $name = "tab_vip_user";
    protected $connection = 'db.main';

    public static function self(){
    	return new self();
    }
    /**
     * getVipList 获取vip列表
     * @return [type] [description]
     */
    public function getUserVipList($map, $order, $limit, $params = []){
        $list = $this->where($map)
                     ->order($order)
                     ->paginate($limit, false, ['query' => $params]);
        return $list;
    }
    /**
     * getOneUserVip 获取用户一个特权
     * @param  int    $user_id 用户id
     * @param  int    $type    特权类型
     * @return array|false     特权享有的值|没有特权
     */
    public function getOneUserVip($user_id, $type){
        $now_time = time();
        $map['user_id'] = $user_id;
        $map['type'] = $type;
        $map['status'] = 1;
        $map['start_time'] = ['<' ,$now_time];
        $map['end_time'] = ['>', $now_time];
        $user = $this->field('vip_id, type')->where($map)->order('id desc')->find();
        if(!$user) {
            return false;
        }
        $vip_model = VipModel::self();
        $vip = $vip_model->field('id, value')->where(['id' => $user['vip_id'],'status' => 1])->find();
        if ($vip) {
            $precent = json_decode($vip['value'], true);
            return $precent;
        } else {
            return false;
        }
    }
    /**
     * TransFree 返回交易手续费(有会员的用会员，没有用普通配置)
     * @param  [type] $user_id [description]
     * @param  [type] $price   [description]
     * @return int    $free
     */
    public function TransFree($user_id, $price){
        $now_time = time();
        $map['user_id'] = $user_id;
        $map['type'] = 1;
        $map['status'] = 1;
        $map['start_time'] = ['<' ,$now_time];
        $map['end_time'] = ['>', $now_time];
        $order = 'id desc';//sort desc  相同类型的根据优先级取
        $user = $this->field('vip_id, type')->where($map)->order($order)->find();
        if ($user) {
            //先判断该用户是否有该类型的优惠
            $vip_model = VipModel::self();
            $vip = $vip_model->field('id, value')->where(['id' => $user['vip_id'],'status' => 1])->find();
            if ($vip) {
                //再判断该优惠是否禁用
                $precent = json_decode($vip['value'], true);
                $free = round($price*$precent['precent'] + $precent['base'],2);
                return $free;
            } else {
                $config_model = ConfigModel::self();
                $trans_config = $config_model->getValue('transaction');
                $trans = json_decode($trans_config, true);
                $free = round($price*$trans['precent'] + $trans['base'], 2);
                return $free;
            }
        }
        //用户没有优惠或者优惠禁用，找配置
        $config_model = ConfigModel::self();
        $trans_config = $config_model->getValue('transaction');
        $trans = json_decode($trans_config, true);
        $free = round($price*$trans['precent'] + $trans['base'], 2);
        return $free;
    }
}