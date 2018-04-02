<?php

namespace app\admin\controller;

use  think\Request;
use app\main\model\GoodsTypeModel;

class Goodstype extends Admin{

    public function index()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['keywords']) && $search['keywords'] != '')
        {
            $map['goods_type'] = $search['keywords'];
        }
        $list = GoodsTypeModel::self()->getList($map,'id',10,$search);
        $page = $list->render();
        
        $this->assign('list',$list);
        $this->assign('page',$page);
        
        return view(); 
    }
}
