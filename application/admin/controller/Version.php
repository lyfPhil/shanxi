<?php

namespace app\admin\controller;

use app\main\model\VersionModel;
use think\Request;

class Version extends Admin{

    public function versionList()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['keywords']) && $search['keywords'] != '')
        {
            $map['nickname'] = array('like', '%'.$search['keywords'].'%');
        }
        $list = VersionModel::self()->getVersionList($map,'id desc',10,$search);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);

        return view();
    }
    public function edit()
    {
        $id = Request::instance()->param('id');
        $data = VersionModel::self()->field('*')->where('id',$id)->find();

        $this->assign('data',$data);
        return view();
    }

    public function create()
    {
        return view();
    }
    public function store()
    {
        $request = Request::instance();
        if($request->isAjax())
        {
            $data = $request->param();
            $ret = VersionModel::self()->insertGetId($data);
            if(is_numeric($ret))
            {
                return $this->response(200);
            }else
            {
                return $this->response(201);
            }
        }
    }
    public function update()
    {
        $request = Request::instance();
        $data = $request->param();
        if($request->isAjax())
        {
            $data = $request->param();
            $ret = VersionModel::self()->update($data);
            if($ret)
            {
                return $this->response(200);
            }else
            {
                return $this->response(201);
            }
        }
    }
    public function remove()
    {
        $id = Request::instance()->param('id');
        $result = VersionModel::self()->remove($id);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
}
