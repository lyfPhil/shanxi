<?php

namespace app\admin\controller;

use think\Request;
use app\main\model\OperatorsModel;

class Operators extends Admin{
    /**
     * 运营商列表
     * @return
     */
    public function index()
    {
        $search = Request::instance()->param();
        $map = array();
        //搜索条件
        if (isset($search['operators_name']) && $search['operators_name'] != '') {
            $map['operators_name'] = array('like', '%'.$search['operators_name'].'%');
        }
        if (isset($search['first_time']) && $search['first_time'] != '') {
            $map['creat_time'] = ['>',$search['first_time']];
        }
        if(isset($search['end_time']) && $search['end_time'] != '')
        {
            $map['creat_time'] = ['<',$search['end_time']];
        }
        $lists = OperatorsModel::self()->getOperatorsList($map,'id desc',5,$search);
        $page = $lists->render();
        $this->assign('page',$page);
        $this->assign('list',$lists);
        
        return view();
    }
    /**
     * 编辑更新
     * @return
     */
    public function edit()
    {
        $id = Request::instance()->param('id');
        $detail = OperatorsModel::self()->get($id);
        
        $this->assign('detail',$detail);
        return view();
    }
    /**
     * 更新操作
     * @return
     */
    public function update()
    {
        if(request()->isAjax())
        {
            $param = Request::instance()->param();
            $ret = OperatorsModel::self()->update($param);
            if ($ret != false) {
                return $this->response(200);
            } else 
            {
                return $this->response(201);
            }
        }
    }
    /**
     * 删除
     * @return
     */
    public function remove()
    {
        $ids = Request::instance()->param('id');
        $res = OperatorsModel::self()->remove($ids);
        if($res != false){
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
    public function create()
    {
        return view();
    }
    /**
     * 新增
     * @return
     */
    public function store()
    {
        if(request()->isAjax())
        {
            $data = Request::instance()->param();
            $ret = OperatorsModel::self()->add($data);
            if(is_numeric($ret)){
                return $this->response(200);
            }else{
                return $this->response(201);
            }
        }
    }
}
