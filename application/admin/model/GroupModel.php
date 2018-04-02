<?php
namespace app\admin\model;

use think\Config;
use think\Model;
use think\Session;

/**
 * 操作日志记录
 */
class GroupModel extends Model
{
    protected $name = 'sx_auth_group';
    protected $resultSetType = 'collection';
    protected $connection = 'db.cms';
    public static function self(){
        return new self();
    } 

    public function getList($ignore_admin=true)
    {
        if (!$ignore_admin) //是否忽略超级管理员
            $this->where('id', '>', 1);
        return $this->select()->toArray();
    }

    public function edit( $data )
    {
        $group =[
            'name' => $data['name'],
            'remark'=> $data['remark'],
        ];
        return  $this->where(['id'=>$data['id']])->update( $group );
    }

    public function store( $data )
    {
        $group =[
            'name' => $data['name'],
            'remark'=> $data['remark'],
        ];
        return $this->insertGetId( $group );
    }

    public function remove($id)
    {
        return $this->where('id', $id)->delete();
    }
}
