<?php
namespace app\home\controller;
use app\home\model\Article;
class Search extends Common
{
    public function index()
    {
        $article=new Article();
        $hotRes=$article->getSerHot();
        $keywords=input('keywords');
        $artRes=db('article')->where('title','like','%'.$keywords.'%')->order('id desc')->paginate(2,false,$config=['query'=>array('keywords'=>$keywords)]);
        // dump($artRes);
        $this->assign(array(
            'artRes'=>$artRes,
            'keywords'=>$keywords,
            'hotRes'=>$hotRes,
            ));
        return view('search');
    }
}
