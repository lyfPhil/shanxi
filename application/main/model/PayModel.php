<?php
namespace app\main\model;

use think\Model;

class PayModel extends Model{
    protected $name = "tab_payment";
    protected $connection = 'db.main';

    public static function self(){
        return new self();
    }

    public function add($data){
        $data['create_time'] = time();
        $data['state'] = 1;
        return $this->save($data);
    }

    public function getByTransId($transId)
    {
        return $this->where('transid', $transId)->find();
    }

    public function getByOrderSN($orderSN)
    {
        return $this->where('order_sn', $orderSN)->find();
    }

    public function updateData($where, $data)
    {
        $data['update_time'] = time();
        return $this->where($where)->update($data);
    }

    public function updateState($payinfo, $state)
    {
        $order_sn = $payinfo['order_sn'];
        $transid = $payinfo['transid'];
        $where = ['order_sn'=>$order_sn,
            'transid'=>$transid];
        $this->updateData($where, ['state'=>$state]);
        $it =explode('_', $order_sn);
        if (count($it)==2 && $state == 2)
        {
            $order_id = $it[1];
            OrderModel::self()->orderMove($order_id, $state);
        }
    }    
}
