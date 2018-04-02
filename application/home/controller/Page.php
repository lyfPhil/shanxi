<?php
namespace app\home\controller;

class Page extends Common
{
    public function index()
    {
        $cates=db('cate')->find(input('cateid'));
        $cate=new \app\home\model\Cate();
        $cateInfo=$cate->getCateInfo(input('cateid'));
        $article=new \app\home\model\Article();
        $hotRes=$article->getHotRes(input('cateid'),1);
        $this->assign(array(
            'cates'=>$cates,
            'cateInfo'=>$cateInfo,
            'hotRes'=>$hotRes,
            ));
        return view('page');
    }
}
