<?php

namespace app\main\model;

use think\Model;


class WithdrawModel extends Model{

    protected $name = "tab_withdraw";
    protected $connection = 'db.main';
    protected $resultType = "collection";
    protected $dateFormat = false;//这个关闭自动转时间戳

    public static function self(){
        return new self();
    }
    /**
     * 获取提现列表 后台使用
     * @param array $map 筛选规则
     * @param string $order 排序规则
     * @param int $limit 分页大小
     * @param array $param 额外参数
     * @return object
     */
    public function getWithdrawList($map,$order,$limit,$param=[])
    {
        $list = $this->field('*')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$param]);

        return $list;
    }
    /**
     * 获取提现详情
     * @param array $map 筛选
     * @return array
     */
    public function getDetail($map)
    {
        return $this->where($map)->find();
    }
    /**
     * 更新提现状态
     * @param array $param
     * @return boolean
     */
    public function edit($param=[])
    {
        $fm = FinanceModel::self();
        $map['draw_no'] = $param['draw_no'];
        $withdraw = $this->where('draw_no',$map['draw_no'])->find();
        switch($param['state']) {
            case 3:
                $this->where('draw_no',$map['draw_no'])->setField('state',3);
                return true;
            break;
            case 2:
                $this->startTrans();
                $info = [
                    'time' => $withdraw['create_time'],
                    'reason' => $param['cancel'],
                    'id' => $map['draw_no']
                ];
                $finance = [
                    'cash' => $withdraw['cash'],
                    'charge' => $withdraw['service_free'],
                    'state' => 2,
                    'user_id' => $withdraw['user_id'],
                    'user_name' => $withdraw['user_name'],
                    'cap_type' => FINANCE_WITHDRAW,
                    'pay_type' => 0,
                    'bal_pay_type' => 1 ,
                    'lang' => 'finance_withdraw:fail',
                    'remark' => json_encode($info)
                ];
                try {
                    $withdraw_update = [
                        'state' => 2,
                        'cancel' => $param['cancel']
                    ];
                    $this->where($map)->update($withdraw_update);
                    $cm = CustomerModel::self();
                    if($withdraw['type'] == '0'){
                        $cm->where('id',$withdraw['user_id'])->setInc('balance',$withdraw['cash']);
                        $fm->where('id',$withdraw['finance_id'])->update(['state'=>0]);
                        $fm->addFinance($finance);
                    }
                    if($withdraw['type'] == '1'){
                        $cm->where('id',$withdraw['user_id'])->setInc('deposit',$withdraw['cash']);
                    }
                    MessageModel::self()->withdrawSend($param['draw_no'],time(),$param);
                    $this->commit();
                } catch (Exception $ex){
                    $this->rollback();
                    return false;
                }
            break;
            case 1:
                $this->startTrans();
                $map['draw_no'] = $param['draw_no'];
                $withdraw_finance = $fm->field('remark')->where('id',$withdraw['finance_id'])->find();
                $info = json_decode($withdraw_finance['remark'], true);
                $info['time'] = time();
                $update = [
                    'state' => 2,
                    'lang' => 'finance_withdraw:success',
                    'remark' => json_encode($info)
                ];
                try {
                    $withdraw_update = [
                        'state' => 1,
                        'bank_finance' => $param['bank_finance']
                    ];
                    $this->where($map)->update($withdraw_update);
                    if($withdraw['type'] == '0'){
                        $fm->where('id',$withdraw['finance_id'])->update($update);
                    }
                    MessageModel::self()->withdrawSend($param['draw_no'],time(),$param);
                    $this->commit();
                } catch (Exception $ex){
                    $this->rockback();
                    return false;
                }
            break;
            default:
                return false;
            break;
        }
        return true;
    }
}
