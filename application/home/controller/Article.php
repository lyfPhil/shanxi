<?php
namespace app\home\controller;
use think\Request;
use app\home\model\Article as ArticleModel;
class Article extends Common
{
    public function index()
    {
        $artid=input('artid');
        db('article')->where(array('id'=>$artid))->setInc('click');
        $articles=db('article')->find($artid);
        $article= new ArticleModel();
        $hotRes=$article->getHotRes($articles['cateid'],2);
        $comment=db('tourists')->where(['artid'=>$artid])->select();
        $count=db('tourists')->where(['artid'=>$artid,'pid'=>0])->count();
        $this->assign(array(
            'articles'=>$articles,
            'hotRes'=>$hotRes,
            'artid'=>$artid,
            'comment'=>$comment,
            'count'=>$count,
            ));
        // dump($comment);die();
        return view('article');
    }

}