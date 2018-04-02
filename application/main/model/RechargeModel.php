<?php

namespace app\main\model;

use think\Model;
use think\Db;
use app\main\model\FinanceModel;
use app\main\model\MessageModel;
use app\v1\service\FormatService;
use app\main\model\ConfigModel;
class RechargeModel extends Model
{
    protected $name = "tab_recharge";
    protected $connection = 'db.main';
    protected $dateFormat = false;

    public static function self(){
        return new self();
    }

    public function getLists($where,$page){
        if(isset($where['type'])){
            if ($where['type'] == 0) {
                $map['state'] = ['in',[0,3]];
            } else {
                $map['state'] = $where['type'];
            }
        }
        $map['user_id'] = $where['user_id'];
        $cnt = $this->field('id')->where($map)->count();
        $lists =$this->field('id, charge_no,user_name,cash,create_time,state,pay_type,cancel,transfer_info, receive_info, invoice')
                ->where($map)
                ->order('id desc')
                ->page($page)
                ->select();
        return [$cnt,$lists];
    }
    /*
     * 获取单个充值号信息
     */
    public function getOneRechargeInfo($where,$field='*'){
        $recharge = $this->field($field)->where($where)->find();
        if(!$recharge){
            return 400;
        }
        return $recharge;
    }

    public function addRecharge($data,$info,$config){
        $now = time();
        $qly_model = Db::name('tab_qly_bank');
        $qly_bank = $qly_model->field('receiver,bank_name,bank_card')->where('id',$data['receiver_id'])->find();
        if(empty($qly_bank)) {
            return 400;
        }
        $receiver = json_encode($qly_bank);
        $add = [
            'charge_no'  =>charge_no(),
            'user_id'    =>$data['user_id'],
            'user_name'  =>$data['username'],
            'pay_type'   =>$data['type'],
            'now_balance'=>$data['balance'],
            'cash'       =>$data['cash'],
            'create_time'=>$now,
            'over_time'  =>$now+$config['expire'],//30天的处理时间
            'receive_info'=>$receiver,
            'transfer_info'=>json_encode($info)
        ];
        $this->insert($add);
        return $add['charge_no'];
    }
    /**
     * 获取充值列表
     * @param array $map 筛选条件
     * @param string $order 排序条件
     * @param int $limit 分页大小
     * @param array $param 额外参数
     * @return object
     */
    public function getRechargeList($map,$order,$limit,$param)
    {
        $res = $this->field('id,charge_no,user_name,pay_type,cash,receive_info,transfer_info,state,status,create_time')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$param]);
        return $res;
    }
    /**
     * 获取充值详情
     * @param array $map 筛选
     * @return array/false
     */
    public function getDetail($map)
    {
        return $this->where($map)->find();
    }
    /**
     * 充值审核，更新状态 后台使用
     * @param array $params 参数
     * @return boolean
     */
    public function updateInfo($params)
    {
        $charge_no = $params['charge_no'];
        $fm = FinanceModel::self();
        $cm = CustomerModel::self();
        $this->startTrans();
        switch($params['state'])
        {
            case 1:
                $map['charge_no'] = $charge_no;
                $charge = $this->getDetail($map);
                $userinfo = $cm->getOneUserInfo($charge['user_id']);
                $info = [
                    'recharge_no'=> $charge_no,
                    'recharge_type' => $charge['pay_type']
                ];
                $finance = [
                    'cash'      => $charge['cash'],
                    'charge'    => 0.00,
                    'state'     => 2,
                    'user_id'   => $userinfo['id'],
                    'user_name' => $charge['user_name'],
                    'cap_type'  => 1,
                    'pay_type'  => $charge['pay_type'],
                    'bal_pay_type' => FINANCE_RECHARGE ,
                    'lang' => 'finance_recharge',
                    'remark' => json_encode(['recharge_type' => $charge['pay_type'],'id' => $charge_no])
                ];
                $update = [
                    'cumulative' => $userinfo['cumulative']+$charge['cash'],
                    'balance'    => $userinfo['balance']+$charge['cash']
                ];
                $data = [
                    'state'     =>'1',
                ];
                try {
                    $cm->where('id',$charge['user_id'])->update($update);
                    $this->where('charge_no',$charge_no)->update($data);
                    MessageModel::self()->rechargeSend($charge_no,time(),$params);
                    //添加流水
                    $fm->addFinance($finance);
                    $this->commit();
                } catch (Exception $ex) {
                    $this->rollback();
                    return false;
                }
                break;
            case 2:
                $data = [
                    'state' => '2',
                    'cancel' => $params['cancel']
                ];
                $this->where('charge_no',$charge_no)->update($data);
                MessageModel::self()->rechargeSend($charge_no,time(),$params);
                $this->commit();
                break;
            case 3:
                 $data = [
                    'state'=>'3',
                    'pay_info'=>$params['pay_info'],
                    'pay_time'=>$params['pay_time']
                ];
                $this->where('charge_no',$charge_no)->update($data);
                $this->commit();
                break;
        }
        return true;
    }
}

