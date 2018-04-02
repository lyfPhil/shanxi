<?php
namespace app\home\model;
use think\Model;
use app\home\model\Cate;
class Article  extends Model
{
    protected $name = 'sx_article';
    protected $connection = 'db.cms'; 
   // 1:文章列表栏目 2:单页栏目
    public function getAllArticles($cateid){
        $cate=new Cate();
        $allCateID=$cate->getchilrenid($cateid);
        $artRes=db('article')->where("cateid IN($allCateID)")->order('id desc')->paginate(2);
        return $artRes;
    }
    // 3:图片列表
    public function getAllArticle($cateid){
        $cate=new Cate();
        $allCateID=$cate->getchilrenid($cateid);
        $artRes=db('article')->alias('a')->field('a.*,b.name')
            ->join('sx_city b','a.city_id=b.id','LEFT')
            ->where("cateid IN($allCateID)")->order('id desc')->paginate(20);
        return $artRes;
    }

    public function getHotRes($cateid,$num){
        $cate=new Cate();
        $allCateID=$cate->getchilrenid($cateid);
        $artRes=db('article')->where("cateid IN($allCateID)")->order('click desc')->limit($num)->select();
        return $artRes;
    }

    public function getSerHot(){
       $artRes=db('article')->order('click desc')->limit(5)->select();
        return $artRes;
    }

    public function getSiteHot(){
        $siteHotArt=$this->field('id,title,thumb,desc,author,item')->order('click desc')->limit(6)->select();
        return $siteHotArt;
    }

    public function getNewArticle(){
        $newArtiecleRes=db('article')->field('a.*,c.catename')
            ->alias('a')
            ->join('sx_cate c','a.cateid=c.id')
            ->order('a.id desc')
            ->limit(10)
            ->select();
        return $newArtiecleRes;
    }

    public function getRecArt(){
        $recArt=$this->where('rec','=',1)->field('id,title,desc,thumb')->order('id desc')->limit(4)->select();
        return $recArt;
    }
}
