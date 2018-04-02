<?php
namespace app\main\model;

use think\Model;

class HelpCategoryModel extends Model
{
    const SPACE = "\xe3\x80\x80\xe3\x80\x80";
    protected $name = 'sx_help_category';
    protected $connection = 'db.cms';
    protected $resultSetType = 'collection';

    public static function self(){
        return new self();
    } 

    public function getList($map = array(), $order='', $category_id="")
    {
        $list = $this->where($map)
            ->order('reorder desc,id desc')
            ->select();
        return unlimitedForLevel($list->toArray(), $html = self::SPACE, $root = 0, $levle = 0);
    }

    public function checkAlias($alias)
    {
        return $this->where('alias',$alias)->find(); 
    }

    public function add($data)
    {
       return $this->insertGetId($data); 
    }
    public function edit($data)
    {
       return $this->where('id', $data['id'])->update($data); 
    }

    public function remove($id)
    {
       return $this->where('id', $id)->delete();
    }
}
