<?php

namespace app\main\model;

use think\Model;
use think\Db;
Class AddressModel extends Model{
    
    protected $name = "tab_address";
    protected $connection = 'db.main';
    public static function self(){
        return new self();
    }
    /*
     * 单个地址信息
     */
    public function getAddressByCondition($where,$field='*'){
        return $this->field($field)->where($where)->find();
    }
    
    public function getAddress($where,$page){
        $map = [
            'user_id'=>$where['user_id'],
            'status'=>1,
        ];
        if(isset($where['game_id'])&&$where['game_id']!=''){
            $map['game_id']=$where['game_id'];
        }
        $cnt = $this->where($map)->field('id')->count();
        $this->where($map)->field('id,user_id,game_id,game_name,service_id,server_name,user_name,mobile,country_code');
        $addr = $this->page($page)->order('id desc')->select();
        return [$addr,$cnt];
    }
    
    public function addAddr($data){
        $id = $this->insertGetId($data);
        return [$id, 0];
    }
    
    public function editAddr($data){
        $map = [
            'id'=>$data['id'],
            'user_id'=>$data['user_id']
        ];
        $this->where($map)->update($data);
        return [$data['id'],0];
    }
    
    public function delAddr($where){
        $map = [
            'id'=>$where['id'],
            'user_id'=>$where['user_id']
        ];
        $data['status'] = 0;
        $this->where($map)->update($data);
        return 0;
    }
    public function getAddrById($adr_id){
        return $this->field('game_name,mobile,user_name,server_name')->where($adr_id)->find();
    }
}

