<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller\Admin;
use app\main\model\ConfigModel;

class Config extends Admin
{
    public function index()
    {
        $data = ConfigModel::self()->getList();
        $this->assign('data', $data);
        return view();
    }

    public function create()
    {
        return view();
    }

    public function store()
    {
        $data = Request::instance()->param();
        $valid = validate('config');
        if (!$valid->scene('add')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        $ret = ConfigModel::self()->add($data);
        if (is_numeric($ret)) {
            return $this->response(200);
        } else {
            return $this->response(201, $ret);
        }
    }

    public function edit()
    {
        $id = Request::instance()->param('id');
        $info = ConfigModel::self()->get($id);
        $this->assign('data', $info);
        return view();
    }

    public function update()
    {
        $data = Request::instance()->param();
        $valid = validate('config');
        if (!$valid->scene('edit')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        $cm = ConfigModel::self();
        $ret = $cm->edit($data);
        if (is_numeric($ret)) {
            return $this->response(200);
        } else {
            return $this->response(201, $ret);
        }
    }

    public function remove()
    {
        if (Request::instance()->isAjax()) {
            $id = Request::instance()->param('id');
            $cm = ConfigModel::self();
            $ret = $cm->where('id', $id)->delete();
            if (is_numeric($ret)) {
                return $this->response(200);
            } else {
                return $this->response(201, $ret);
            }
        }
    }


}

