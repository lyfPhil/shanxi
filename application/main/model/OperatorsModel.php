<?php

namespace app\main\model;

use think\Model;


class OperatorsModel extends Model{
    //put your code here
    protected $name = 'tab_operators';
    protected $connection = 'db.main';
    protected $resultSetType = 'collection';
    
    public static function self()
    {
        return new self();
    }
    

    /**
     * 获取游戏运营商列表
     * @param array $map 筛选条件
     * @param string $order 排序条件
     * @param int $limit 分页大小
     * @param array $params 额外参数
     * @return object 
     */
    public function getOperatorsList($map,$order,$limit,$params=[])
    {
        $ret = $this->field('*')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$params]);
        return $ret;
    }
    /**
     * 批量删除数据
     * @param array $ids id数组
     * @return true/false
     */
    public function remove($ids)
    {
        if(is_array($ids)){
            $this->where('id','in',$ids);
        }else{
            $this->where('id',$ids);
        }
        return $this->delete();
    }
    /**
     * 新增数据
     * @param array $data 数组
     * @return int id数值
     */
    public function add($data)
    {
        $data['create_time'] = time();
        return $this->insertGetId($data);
    }
    
    
}
