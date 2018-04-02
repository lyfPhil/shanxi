<?php
namespace app\home\model;
use think\Model;
class Cate  extends Model
{
    protected $name = 'sx_cate';
    protected $connection = 'db.cms';
    public function getchilrenid($cateid){
        $cateres=$this->select();
        $arr=$this->_getchilrenid($cateres,$cateid);
        $arr[]=$cateid;
        $strId=implode(',', $arr);
        return $strId;
    }

    public function _getchilrenid($cateres,$cateid){
        static $arr=array();
        foreach ($cateres as $k => $v) {
            if($v['pid'] == $cateid){
                $arr[]=$v['id'];
                $this->_getchilrenid($cateres,$v['id']);
            }
        }

        return $arr;
    }


    public function getparents($cateid){

        $cateres=db('cate')->field('id,pid,type,catename')->select();
        $cates=db('cate')->field('id,pid,type,catename')->find($cateid);
        $pid=$cates['pid'];
        if($pid){
            $arr=$this->_getparentsid($cateres,$pid);
        }
        // foreach ($cateres as $k => $v) {
        //     if ($v['type']==1) {
        //         $cates['type']='artlist';
        //     }elseif ($v['type']==2) {
        //         $cates['type']='page';
        //     }elseif ($v['type']==3) {
        //         $cates['type']='imglist';
        //     }
        // }
        $arr[]=$cates;
        // dump($arr);die;
        return $arr;
    }

    public function _getparentsid($cateres,$pid){
        static $arr=array();
        foreach ($cateres as $k => $v) {
            if($v['id'] == $pid){
                // if ($v['type']==1) {
                //     $v['type']='artlist';
                // }elseif ($v['type']==2) {
                //     $v['type']='page';
                // }elseif ($v['type']==3) {
                //     $v['type']='imglist';
                // }
                $arr[]=$v;
                $this->_getparentsid($cateres,$v['pid']);
            }

        }

        return $arr;
    }

    public function getRecIndex(){
        $recIndex=$this->alias('a')
            ->field('b.*')
            ->join('sx_article b','a.id=b.cateid','LEFT')
            ->order('id desc')
            ->where('rec_index','=',1)
            ->select();
        return $recIndex;
    }

    public function getRecBottom(){
        $RecBottom=$this->order('id desc')->where('rec_bottom','=',1)->select();
        return $RecBottom;
    }

    public function getCateInfo($cateid){
        $cateInfo=$this->field('catename,keywords,desc')->find($cateid);
        return $cateInfo;
    }


}
