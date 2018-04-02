<?php

namespace app\main\model;

use think\Model;

class BankInfoModel extends \think\Model{
    protected $name = 'tab_bank_info';
    protected $connection = 'db.main';

    public static function self()
    {
        return new self();
    }
    /*
     * 获取单个银行卡信息
     */
    public function getOneBankInfo($map,$field='*'){
        $map['status'] = 1;
        $bank = $this->where($map)->field($field)->find();
        if ($bank) {
           return $bank;
        } else {
            return 400;
        }
    }
    /*
     * 获取多个银行卡
     */
    public function getBankIconBatch($bankIds, $device_type){
        $field = 'id,bank_icon';
        $bank = $this->field($field)->where('id','in',$bankIds)->select();
        $bank_list = [];
        foreach($bank as $val){
            $bank_list[$val['id']] = $val['bank_icon'];
        }
        return $bank_list;
    }

    public function getBankInfoList($map,$order,$limit,$param=[])
    {
        $res = $this->field('*')
                ->where($map)
                ->order('id asc')
                ->paginate($limit,false,['query'=>$param]);
        return $res;
    }

    public function getDetails($id)
    {
        return $this->get($id);
    }

    public function edit($data)
    {
        return $this->where('id',$data['id'])->update($data);
    }

    public function remove($ids)
    {
        if(is_array($ids))
        {
            $this->where('id','in',$dis);
        }else{
            $this->where('id',$ids);
        }
        return $this->update(['status'=>0]);
    }

    public function add($data)
    {
        return $this->insertGetId($data);
    }
}
