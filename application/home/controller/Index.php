<?php
namespace app\home\controller;
use app\home\model\Article;
use app\main\model\ArticleModel;
use app\home\model\Cate;
class Index extends Common
{
    public function index()
    {
        $article=new Article();
        $siteHotArt=$article->getSiteHot();
        $recArt=$article->getRecArt();
        $linkres=db('link')->order('sort desc')->select();
        $this->assign(array(
            'siteHotArt'=>$siteHotArt,
            'linkres'=>$linkres,
            'recArt'=>$recArt,
        ));

        return view();
    }
    public function all(){
        $article=new ArticleModel();
        $allarticle=$article->getList($map['status']=1, 'click desc',20);
        $cate=new Cate();
        $recIndex=$cate->getRecIndex();
        $this->assign(array(
            'allarticle'=>$allarticle,
            'recIndex'=>$recIndex,
        ));
        return view();
    }

}
