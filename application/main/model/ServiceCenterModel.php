<?php

namespace app\main\model;

use think\Model;

class ServiceCenterModel extends Model{

    protected $name = "tab_service_center";
    protected $connection = 'db.main';

    public static function self()
    {
        return new self();
    }

    //获取用户咨询列表
    public function getConsult($map,$order,$limit,$params=[])
    {
        $map['a.ptype'] = 2;
        $res = $this->alias('a')
                ->field('a.*,b.nickname')
                ->join('tab_user b','a.user_id = b.id','LEFT')
                ->order($order)
                ->paginate($limit,FALSE,['query'=>$params]);

        return $res;
    }
    //获取用户投诉列表
    public function getComplaint($map,$order,$limit,$params=[])
    {
        $map['a.ctype'] = 1;
        $res = $this->alias('a')
                ->field('a.*,b.nickname')
                ->join('tab_user b','a.user_id = b.id','LEFT')
                ->where($map)
                ->order($order)
                ->paginate($limit,FALSE,['query'=>$params]);
        return $res;
    }
    //获取用户建议列表
    public function getSuggest($map,$order,$limit,$params=[])
    {
        $res = $this->where($map)->order($order)->paginate($limit,FALSE,['query'=>$params]);
        return $res;
    }
    //回复更新
    public function edit($data)
    {
        $data['recovery_time'] = time();
        $ret = $this->where('id',$data['id'])->update($data);
        return $ret;
    }


}
