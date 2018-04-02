<?php
namespace app\admin\model;
use think\Model;
use traits\model\SoftDelete;

class CateModel extends Model
{
    protected $name = 'sx_cate';
    protected $connection = 'db.cms';
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    public static function self(){
        return new self();
    }
    
    public function catetree(){
      $catedata = $this->alias('a')
        ->field('a.*,b.type_name')
        ->join('sx_cate_type b','a.type=b.type_id','LEFT')
        ->order('sort desc')
        ->select();
      return $this->sort($catedata);
    }
    public function sort($data,$pid=0,$level=0){

      static $arr=array();
      foreach ($data as $k => $v) {
        if ($v['pid']==$pid) {
          $v['level']=$level;
          $arr[]=$v;
          $this->sort($data,$v['id'],$level+1);
        }
      }
      return $arr;
    }

    public function add($data){
        if (empty($data) || !is_array($data)) {
            return false;
        }
        return $res=$this->insertGetId($data);
    }

    public function edit($data){

        return $this->where('id', $data['id'])->update($data);
    }

    public function remove($ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
        return $this->delete();
    }

    public function getchilid($cateid){
      $catedata = $this->select();
      return $this->_getchilid($catedata,$cateid);
    }

    public function _getchilid($catedata,$cateid){
      static $arr=array();
      foreach ($catedata as $k => $v) {
        if ($v['pid']==$cateid) {
          $arr[]=$v['id'];
          $this->_getchilid($catedata,$v['id']);
        }
      }
      return $arr;
    }
}
