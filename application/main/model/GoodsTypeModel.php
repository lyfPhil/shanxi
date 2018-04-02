<?php

namespace app\main\model;

use think\Model;

class GoodsTypeModel extends Model{
    protected $name = "tab_goods_type";
    protected $connection = 'db.main';
    protected $resultType = "collection";
    
    public static function self()
    {
        return new self();
    }
    /*
     * 根据游戏id返回游戏有什么商品类型
     */
    public function getGoodsTypeByGameId(){
        
    }
    /**
     *  获取已启用所有商品类型 
     *  后台使用
     * @return array 二维数组
     */
    public function getAllGoodsTypeName()
    {
        $res = $this->field('id,type_name')
                ->where('status',1)
                ->order('id')
                ->select();
        return $res;
    }
    //获取列表
    public function getList($map,$order,$limits,$params)
    {
        $res = $this->field('*')
                ->where($map)
                ->order($order)
                ->paginate($limits,false,['query'=>$params]);
        return $res;
    }
    
    
}
