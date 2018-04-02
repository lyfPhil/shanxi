<?php
namespace app\home\controller;
use app\home\model\Article;
class Artlist extends Common
{
    public function index()
    {
        $article=new Article();
        $cateid=input('cateid');
        $artRes=$article->getAllArticles($cateid);
        $hotRes=$article->getHotRes($cateid,2);
        $cate=new \app\home\model\Cate();
        $cateInfo=$cate->getCateInfo($cateid);
        $this->assign(array(
            'artRes'=>$artRes,
            'hotRes'=>$hotRes,
            'cateInfo'=>$cateInfo,
            ));
        return view('artlist');
    }
    //文章图片列表
    public function imglist()
    {
        $article=new Article();
        $artRes=$article->getAllArticle(input('cateid'));
        $cate=new \app\home\model\Cate();
        $cateInfo=$cate->getCateInfo(input('cateid'));
        $this->assign(array(
            'artRes'=>$artRes,
            'cateInfo'=>$cateInfo,
            ));
        return $this->fetch();
    }
}
