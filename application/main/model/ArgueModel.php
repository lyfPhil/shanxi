<?php

namespace app\main\model;

use think\Model;
use app\main\model\FinanceModel;
use app\main\model\OrderModel;
use app\main\model\CustomerModel;
use app\main\model\ConfigModel;
use app\main\model\MessageModel;

class ArgueModel extends Model{

    protected $name = 'tab_argue';
    protected $connection = 'db.main';
    protected $dateFormat = false;
    public static function self()
    {
        return new self();
    }

    public function getOrderArgueList($where,$field='*'){
        return $this->where($where)->field($field)->order('id desc')->select();
    }

    /**
     * checkLastArgue 检验最后一次争论是否到达10天
     * @param  int $user_id  用户id
     * @return int
     */
    public function checkLastArgue($user_id)
    {
        $where['user_id|to_user_id'] = $user_id;
        $where['order_state'] = ['in',[0,11]];
        $lastargue = $this->where($where)->order('create_time desc')->field('create_time')->find();
        if ($lastargue) {
            $check_time = $lastargue['create_time'] + 24*3600*10;
            if (time() < $check_time) {
                return 400700;
            }
        }
        return 0;
    }
    /**
     * 获取订单申诉列表
     * @param array $map 筛选条件
     * @param string $order 排序条件
     * @param int $limit 分页大小
     * @param array $param 额外参数
     * @return object
     */
    public function getArgueLists($map,$order,$limit,$param=[])
    {
        $order_model = OrderModel::self();
        $ret = $this->alias('a')
               ->field('b.*, a.*')
               ->join('tab_order b', 'a.order_sn = b.order_sn')
               ->where($map)
               ->group('b.order_sn')
               ->order('a.create_time desc')
               ->paginate($limit, false, ['query'=>$param]);
        return $ret;
    }
    public function handleArgue($data,$adminUser,$order)
    {
        //新增申诉状态 2把对应订单的订单金额退还给买卖双方 3添加退款流水
        $fm = FinanceModel::self();
        $cm = CustomerModel::self();
        $this->startTrans();
        //添加新的一条申述
        $result = [
            'order_sn' => $data['order_sn'],
            'user_type' => '0',
            'order_state' => '11',
            'user_id' => $adminUser['id'] ,
            'user_name' => $adminUser['username'],
            'to_user_id' => '',
            'create_time' => time(),
            'result' => $data['result'],
            'reason' => $data['reason']
        ];
        //退回卖家金额流水
        $data['seller_cash'] = $order['actual'] - $data['buyer_cash'];
        $data['buyer_cash'] = floatval($data['buyer_cash']);
        $free = 0;
        try {
            //加入申诉结果
            $this->insertGetId($result);
            //改变订单状态
            if (floatval($data['seller_cash']) > 0) {
                $config_model = ConfigModel::self();
                $trans_config = $config_model->getValue('transaction');
                $trans = json_decode($trans_config, true);
                $free = number_format($data['seller_cash']*$trans['precent']+$trans['base'], 2);
                $actual = $data['seller_cash'] - $free;
                if ($actual <= 0) {
                    return -1;
                }
                $seller_finance = [
                    'cash' => $actual,
                    'charge' => $free,
                    'state' => 2,
                    'user_id' => $order['sell_id'],
                    'user_name' => $order['sell_name'],
                    'cap_type' => 3,//售得
                    'pay_type' => 0,
                    'bal_pay_type' => 1 ,//收入
                    'lang' => 'finance_income',
                    'remark' => json_encode(["free"=>$free,"id"=>$data['order_sn']])
                ];
                $fm->addFinance($seller_finance);
                $cm->where('id',$order['sell_id'])->setInc('balance',$actual);
            }
            if ($data['buyer_cash'] != 0) {
                $buyer_finance = [
                    'cash' => $data['buyer_cash'],
                    'charge' => 0.00,
                    'state' => 2,
                    'user_id' => $order['buy_id'],
                    'user_name' => $order['buy_name'],
                    'cap_type' => 5,//退款
                    'pay_type' => 0,
                    'bal_pay_type' => 1 ,//收入
                    'lang' => 'finance_refund',
                    'remark' => json_encode(["pay_type"=>0,"id"=>$data['order_sn']])
                ];
                $fm->addFinance($buyer_finance);
                $cm->where('id',$order['buy_id'])->setInc('balance',$data['buyer_cash']);
            }
            OrderModel::self()->where('order_sn',$data['order_sn'])->update(['state' => '11','charge' => $free]);
            $message_model = MessageModel::self();
            $extract['result'] = $data['result'];
            $extract['reason'] = $data['reason'];
            $message_model->sendOrderMessage($order['id'], 'customerdealappeal', $order, '', $extract);
            $this->commit();
        } catch (Exception $ex) {
            $this->rollback();
            return false;
        }
        return true;
    }

}
