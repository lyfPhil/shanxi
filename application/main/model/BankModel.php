<?php
namespace app\main\model;
 
use think\Model;

class BankModel extends Model{
    protected $name="tab_bank";
    protected $dateFormat = false;
    protected $connection = 'db.main';

    public static function self(){
        return new self();
    }
    
    public function getOneUserBankInfo($where,$field='*'){
        $bank = $this->where($where)->field($field)->find();
        if(!$bank){
            return 404500;
        }else{
            return $bank;
        }
    }
    
    public function isExi($user_id,$bank_card)
    {
        $map = [
            'user_id'   =>$user_id,
            'bank_card' =>$bank_card,
        ];
        return $this->where($map)->field('id')->find();
    }
    
    public function addBank($data)
    {
        $data['create_time']=time();
        return $this->insertGetId($data);
    }
    
    public function delBank($user_id,$bank_id)
    {
        $map = [
            'user_id'=>$user_id,
            'id'=>$bank_id
        ];
        $ret = $this->where($map)->setField('status',0);
        if($ret==1){
            return 0;
        }else{
            return 400;
        }
        
    }
    /*
     * 获取用户银行卡信息
     */
    public function getUserBankInfo($where)
    {
        $lists=$this->field('id,open_name,bank_name,bank_card,bank_id')
                ->where('status',1)
                ->where($where)
                ->order('id desc')
                ->select();
        return $lists;
    }
}
