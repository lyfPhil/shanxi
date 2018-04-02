<?php

namespace app\main\model;

use think\Model;
use think\Db; 

class GameServiceModel extends Model
{
    protected $name = "tab_game_service";
    protected $connection = 'db.main';
    protected $resultSetType = 'collection';
    
    public static function self()
    {
        return new self();
    }
    //获取游戏区服务器列表
    public function gameServiceList($map,$order,$limit,$params=[])
    {
        $ret = $this->alias('a')
                ->field('a.*,b.game_name,c.operators_name')
                ->join('tab_game b','a.game_id=b.id','LEFT')
                ->join('tab_operators c','a.operators_id=c.id','LEFT')
                ->where($map)
                ->order($order)
                ->paginate($limit, false, ['query'=>$params]);
        return $ret;
    }
    public function remove($ids)
    {
        if(is_array($ids))
        {
            $this->where('id','in',$ids);
        }
        else
        {
            $this->where('id',$ids);
        }
        return $this->delete();
    }
    public function edit($data)
    {
        return $this->where('id',$data['id'])->update($data);
    }
    public function add($data)
    {
        $data['create_time'] = time();
        return $this->insertGetId($data);
    }
}
