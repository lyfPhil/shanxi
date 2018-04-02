<?php
namespace app\main\model;

use app\main\model\CustomerModel;
use app\main\model\WithdrawModel;
use app\main\model\VipUserModel;
use think\Model;
use think\Cache;
use think\Config;
class FinanceModel extends Model{
    protected $name = "tab_finance";
    protected $dateFormat = false;
    protected $connection = 'db.main';

    public static function self(){
        return new self();
    }

    public function getLists($where,$page){
        if(isset($where['type'])){
            if ($where['type'] == FINANCE_PAY_DEPOSIT) {
                $map['cap_type'] = ['in',[FINANCE_PAY_DEPOSIT,FINANCE_REFUND_DEPOSTI]];
            } else {
                $map['cap_type'] = $where['type'];
            }
        }
        $map['user_id'] = $where['user_id'];
        $cnt   = $this->where($map)->count();
        $field = 'id,order_sn,cash,charge,state,cap_type,pay_type,bal_pay_type,create_time,lang,remark';
        $lists = $this->where($map)->field($field)
                    ->order('id desc')->page($page)
                    ->select();
        return [$cnt,$lists];
    }

    public function addWithDrawal($data,$user,$bank){
        $now          = time();
        $config_model = ConfigModel::self();
        $temp         = $config_model->getValue('withdrawal');
        $withdrawal   = json_decode($temp,true);
        $vuser_model = VipUserModel::self();
        $vip = $vuser_model->getOneUserVip($user['user_id'], 2);
        if ($vip) {
            $withdrawal['precent'] = $vip['precent'];
        }
        $service_free = round($data['cash']*$withdrawal['precent'],2);
        $add_drawal = [
            'draw_no'       => gen_uniqSN(),
            'user_name'     => $user['username'],
            'user_id'       => $user['user_id'],
            'cash'          => $data['cash'],
            'type'          => 0,
            'service_free'  => $service_free,
            'actual_cash'   => $data['cash'] - $service_free,
            'open_name'     => $bank['open_name'],
            'bank_name'     => $bank['bank_name'],
            'bank_card'     => $bank['bank_card'],
            'create_time'   => $now,
            'over_time'     => $now+ $withdrawal['deadline']
        ];
        $this->startTrans();
        try{
            $finance_id = $this->addWithDrawalFinance($add_drawal);
            $add_drawal['finance_id'] = $finance_id;
            $wm = WithdrawModel::self();
            $cm = CustomerModel::self();
            $wm -> insert($add_drawal);
            $cm -> where('id',$user['user_id'])->setDec('balance',$data['cash']);
            $this -> commit();
            $key = 'drawal_'.$user['username'].$user['user_id'];
            $next_time = strtotime(date('Y-m-d',$now+$withdrawal['day']));
            $count = Cache::get($key);
           if ($count) {
                if ($count>=$withdrawal['count']) {
                    return 400;
                }
                $count += 1;
                Cache::set($key,$count,$next_time-$now);
            }  else {
                Cache::set($key,1,$next_time-$now);
            }
            return 0;
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }
    protected function addWithDrawalFinance($add_drawal){
        $info = [
            'id' => $add_drawal['draw_no'],
            'free' => number_format($add_drawal['service_free'],2),
            'cash' => number_format($add_drawal['actual_cash'],2)
        ];
        $add_finance = [
            'create_time' => time(),
            'order_sn'  => 'JY_'.gen_uniqSN(),
            'status'    => 1,
            'cash'      => $add_drawal['cash'],
            'charge'    => $add_drawal['service_free'],
            'state'     => 1,
            'user_id'   => $add_drawal['user_id'],
            'user_name' => $add_drawal['user_name'],
            'cap_type'  => FINANCE_WITHDRAW,
            'pay_type'  => 0,
            'bal_pay_type' => 0,
            'lang' => 'finance_withdraw:wait',
            'remark' => json_encode($info),
        ];
        return $this->insertGetId($add_finance);
    }
    public function getOneDepositRefund($user_id,$field='*',$expire=true){
        $map = [
            'user_id'=>$user_id,
            'cap_type'=>6,
        ];
        $deposit = $this->where($map)->order('id desc')->field($field)->find();
        if(!$deposit){
            return 400;
        }
        if($expire == true){
            $config_model = ConfigModel::self();
            $data       = $config_model->getValue('deposit');
            $config     = json_decode($data,true);
            $time       = strtotime(date('Y-m-d',$deposit['create_time']+$config['refund']));
            if (time() < $time) {
                return 400;
            }
        }
        return $deposit;
    }

    public function payDeposit($data){
        $this->startTrans();
        try{
            $cm = CustomerModel::self();
            if($cm->where('id',$data['user_id'])->setInc('deposit',$data['deposit'])&&
                $cm->where('id',$data['user_id'])->setDec('balance',$data['deposit']))
            {
                $add_data = [
                    'cash'      => $data['deposit'],
                    'charge'    => 0.00,
                    'state'     => 2,
                    'user_id'   => $data['user_id'],
                    'user_name' => $data['username'],
                    'cap_type'  => FINANCE_PAY_DEPOSIT,
                    'pay_type'  => 0,
                    'bal_pay_type'=> 0,
                    'lang' => 'finance_pay_deposit'
                ];
                $this->addFinance($add_data);
                MessageModel::self()->depositMessage($data['user_id']);
                $this->commit();
                return 0;
            }
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }
    /**
     * [depositRefund 退押金]
     * @param  array $user [用户信息]
     * @return int       [0:成功]
     */
    public function depositRefund($user){
        $now        = time();
        $finance_data = [
            'create_time'   =>  $now,
            'order_sn'      =>  'JY_'.gen_uniqSN(),
            'status'        =>  1,
            'cash'          =>  $user['deposit'],
            'charge'        =>  0.00,
            'state'         =>  2,
            'user_id'       =>  $user['user_id'],
            'user_name'     =>  $user['user_name'],
            'cap_type'      =>  FINANCE_REFUND_DEPOSTI,
            'pay_type'      =>  0,
            'bal_pay_type'  =>  1,
            'lang' => 'finance_refund_deposit',
        ];
        $this->startTrans();
        try{
            $this->insert($finance_data);
            $update['deposit'] = 0;
            $update['balance'] = $user['balance'] + $user['deposit'];
            CustomerModel::self()->where('id',$user['user_id'])->update($update);
            MessageModel::self()->refundDepositMessage($user['user_id']);
            $this->commit();
            return 0;
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }


    public function addFinance($data){
        $data['create_time'] = time();
        $data['order_sn']    = 'JY_'.gen_uniqSN();
        $data['status']      = 1;
        return $this->insert($data);
    }

    /**
     * 获得流水资金列表
     * @param array $map 筛选条件
     * @param string $order 排序条件
     * @param int $limit 分页大小
     * @param array $param 而外参数
     * @return object
     */
    public function getFinanceList($map,$order,$limit,$param=[])
    {
        $res = $this->field('id,order_sn,user_name,cash,charge,state,cap_type,status,create_time,remark,lang,pay_type')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$param]);
        return $res;
    }
    //总资金流水
    public function financeStatus()
    {
        $recharge = $this->field('cash')->where(['state'=>2,'cap_type'=>1])->select();
        $recharge_cash = 0;
        foreach ($recharge as $item)
        {
            $recharge_cash += $item['cash'];
        }
        $withdraw = $this->field('cash,charge')->where(['state'=>2,'cap_type'=>4])->select();
        $withdraw_cash = 0;
        foreach($withdraw as $item)
        {
            $withdraw_cash += $item['cash'];
        }
        $deposit = $this->field('cash')->where(['state'=>2,'cap_type'=>7])->select();
        $deposit_cash = 0;
        foreach($deposit as $item)
        {
            $deposit_cash += $item['cash'];
        }
        $charge = $this->field('charge')->where(['state'=>2,'cap_type'=>4])->select();
        $charge_cash = 0;
        foreach($charge as $item)
        {
            $charge_cash += $item['charge'];
        }
        return  [
            'title' => 'Total Amount of Funds Flow',
            'recharge' => $recharge_cash,//充值收入
            'withdraw' => $withdraw_cash,//提现支出
            'deposit'  => $deposit_cash,//退押金支出
            'charge'   => $charge_cash, //提现服务费收入
        ];
    }
    //今日资金流水
    public function todayFinanceStatus($time)
    {
        $where['create_time'] = array('>',$time);
        $recharge = $this->field('cash')->where(['state'=>2,'cap_type'=>1])->where($where)->select();
        $recharge_cash = 0;
        foreach ($recharge as $item)
        {
            $recharge_cash += $item['cash'];
        }
        $withdraw = $this->field('cash,charge')->where(['state'=>2,'cap_type'=>4])->where($where)->select();
        $withdraw_cash = 0;
        foreach($withdraw as $item)
        {
            $withdraw_cash += $item['cash'];
        }
        $deposit = $this->field('cash')->where(['state'=>2,'cap_type'=>7])->where($where)->select();
        $deposit_cash = 0;
        foreach($deposit as $item)
        {
            $deposit_cash += $item['cash'];
        }
        $charge = $this->field('charge')->where(['state'=>2,'cap_type'=>4])->where($where)->select();
        $charge_cash = 0;
        foreach($charge as $item)
        {
            $charge_cash += $item['charge'];
        }
        return  [
            'title' => 'Today Funds Flow',
            'recharge' => $recharge_cash,//今日充值收入
            'withdraw' => $withdraw_cash,//今日提现支出
            'deposit'  => $deposit_cash,//今日退押金支出
            'charge'   => $charge_cash,//今日提现服务费收入
        ];
    }
      //统计订单交易手续费
    public function orderCharge($time)
    {
        //今日手续费
        $where['create_time'] = array('>',$time);
        $charge = $this->field('charge')->where(['state'=>2,'cap_type'=>3])->where($where)->select();
        $today_charge = 0;
        foreach($charge as $item)
        {
            $today_charge += $item['charge'];
        }
        $map['create_time'] = array('between',array($time-24*3600,$time));
        //昨日手续费
        $ycharge = $this->field('charge')->where(['state'=>2,'cap_type'=>3])->where($map)->select();
        $yesterday_charge = 0;
        foreach($ycharge as $item)
        {
            $yesterday_charge += $item['charge'];
        }
        //总订单手续费
        $acharge = $this->field('charge')->where(['state'=>2,'cap_type'=>3])->select();
        $all_charge = 0;
        foreach($acharge as $item)
        {
            $all_charge += $item['charge'];
        }
        return  [
            'title' => 'Order Fees',
            'today' => $today_charge,//今日订单手续费
            'yesterday' => $yesterday_charge,//昨天订单手续费
            'all'  => $all_charge,//总订单手续费
        ];
    }

}
