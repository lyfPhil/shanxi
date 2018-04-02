<?php
/**
 * @Author: Marte
 * @Date:   2018-03-11 10:16:44
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-03-11 21:19:32
 */
namespace app\admin\model;
use think\Model;
class CateTypeModel extends Model
{
    protected $name = 'sx_cate_type';
    protected $connection = 'db.cms';
    public static function self(){
        return new self();
    }

    public function getCateType(){
        $cateTypeRes=$this->select();
        return $cateTypeRes;
    }
    public function getType($id){
        $cateTypeRes=$this->find($id);
        return $cateTypeRes;
    }

    public function delCateType($id){
        $res=$this->destroy($id);
        return $res;
    }

    public function updateType($data){
        $res=$this->update($data);
        return $res;
    }


}