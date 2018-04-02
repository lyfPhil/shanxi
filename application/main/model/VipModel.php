<?php
namespace app\main\model;

use think\Model;

class VipModel extends Model{

    protected $name = "tab_vip";
    protected $connection = 'db.main';

    public static function self(){
    	return new self();
    }
    /**
     * getVipList 获取vip列表
     * @return [type] [description]
     */
    public function getVipList($map, $order, $limit, $params = []){
        $list = $this->paginate($limit, false, ['query' => $params]);
        return $list;
    }
}