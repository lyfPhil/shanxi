<?php
namespace app\home\controller;
use app\home\model\Article;
use app\home\model\City;
use app\home\model\Cate;
class Imglist extends Common
{
    
    public function index()
    {
        $article=new Article();
        $artRes=$article->getAllArticle(input('cateid'));
        $cate=new \app\home\model\Cate();
        $cateInfo=$cate->getCateInfo(input('cateid'));
        $city=new City();
        $cityinfo=$city->getcity();
        $this->assign(array(
            'artRes'=>$artRes,
            'cateInfo'=>$cateInfo,
            'cityinfo'=>$cityinfo,
            ));
        return view('imglist');
    }
}
