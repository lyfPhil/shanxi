<?php
namespace app\home\controller;
use think\Controller;
use app\home\model\Conf;
use app\home\model\Cate;
class Common extends Controller{
    public function _initialize(){
        $this->getConf();
        $this->getTitle();
        $this->getNavCates();
        if (input('cateid')) {
            $this->getPos(input('cateid'));
        }elseif(input('artid')){
            $artRes=db('article')->field('cateid')->find(input('artid'));
            $cateid=$artRes['cateid'];
            $this->getPos($cateid);
        }
        $cate=new Cate();
        $RecBottom=$cate->getRecBottom();
        $cateInfo=$cate->getCateInfo(input('cateid'));
        $linkres=db('link')->order('sort desc')->select();
        // dump($cateInfo);die;
        $this->assign(array(
            'RecBottom'=>$RecBottom,
            'cateInfo'=>$cateInfo,
            'linkres'=>$linkres,
            ));
    }
    public function getConf(){
        $conf=new Conf();
        $confdata=$conf->getAllconf();
        $confres=array();
        foreach ($confdata as $k => $v) {
            $confres[$v['enname']]=$v['cnname'];
        }
        $this->assign('confres',$confres);
        // dump($confres);die;
    }
    public function getNavCates(){
        $cateres=db('cate')->where(array('pid'=>0))->select();
        foreach ($cateres as $k => $v) {
            $children=db('cate')->where(array('pid'=>$v['id']))->select();
            if ($children) {
                $cateres[$k]['children']=$children;
            }else{
                $cateres[$k]['children']=0;
            }
        }
        $this->assign('cateres',$cateres);
        // dump($cateres);die;
    }
    public function getTitle(){
        $title=db('cate')->where('id',input('cateid'))->value('catename');
        if (input()) {
            # code...
        }
        if (!$title) {
            $title='首页';
        }
        // dump($title);die;
        $this->assign('title',$title);
    }

    public function getPos($cateid){
        $cate=new Cate();
        $arr=$cate->getparents($cateid);
        $this->assign('arr',$arr);
    }

}