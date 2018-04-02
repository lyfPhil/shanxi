<?php
namespace app\admin\model;

use think\Config;
use think\Model;
use think\Session;

class AuthModel extends Model
{
    protected $name = 'sx_auth_rule';
    protected $resultSetType = 'collection';
    protected $connection = 'db.cms';

    public static function self(){
        return new self();
    } 

    public function getInfo($map)
    {
        return $this->where($map)->find();
    }

    /**
     * 获取权限规则列表

     * @params $map array 查询条件
     * @params $order array|string 排序规则
     * @return array
     */
    public function getList($map = [], $order = 'reorder asc')
    {
        return $this->where($map)->order($order)->select()->toArray();
    }

    /**
     * 获取权限规则列表(Tree)

     * @params $map array 查询条件
     * @params $order array|string 排序规则
     * @return array
     */
    public  function getListTree($map = [], $order = 'reorder asc')
    {
        $list = $this->where($map)->order($order)->select();
        return list_to_tree($list->toArray(), $pk='id', $parent_id = 'parent_id', $child = 'child', $root = 0);
    }

    /**
     * 删除
     *
     * @params $id int 分类ID
     * @return int
     */
    public  function remove($id)
    {
        return $this->where('id', $id)->delete();
    }

    /**
     * 排序
     *
     * @params $id int 分类ID
     * @params $order int 排序数值
     * @return int
     */
    public  function reorder($id, $order)
    {
        return $this->where('id', $id)->setField('reorder', $order);
    }

    /**
     * 查询子规则
     *
     * @params $parent_id int 分类ID
     * @return array
     */
    public  function getChild($parent_id)
    {
        return $this->where('parent_id', $parent_id)->select();
    }

    /**
     * 创建
     *
     * @params $data array 创建数据
     * @return mix
     */
    public  function add($data)
    {
        return $this->isUpdate(false)->save($data);
    }

    /**
     * 更新
     *
     * @params $data array 需要更新的数据
     * @return mix
    */
    public  function edit($data)
    {
        return $this->where('id', $data['id'])->update($data);
    }

}
