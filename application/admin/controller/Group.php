<?php
namespace app\admin\controller;

use app\admin\controller\Admin;
use think\Request;
use think\Db;
use app\admin\model\GroupModel;
use app\admin\model\AuthModel;
use app\admin\model\AccessModel;

class Group extends Admin
{

    public function index()
    {
        $data = GroupModel::self()->getList();
        $this->assign('data',$data);
        return view();
    }

    public function create()
    {
        $data['ruleList'] = AuthModel::self()->getListTree();
        $this->assign('data',$data);
        return view();
    }

    public function store()
    {
        $data = Request::instance()->param();
        $valid = validate('group');
        if (!$valid->scene('add')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        $group_id = GroupModel::self()->store($data);
        empty($data['rules']) ? $data['rules'] = array() : $data['rules'];
        if ($group_id) {
            $access = AccessModel::self()->store($group_id, $data['rules']);
        }
        if ($group_id !== false && $access !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function edit()
    {
        $gid = Request::instance()->param('id');
        $data = GroupModel::self()->get(['id'=>$gid]);
        $data['ruleList'] = AuthModel::self()->getListTree();
        $data['access'] =  AccessModel::self()->getAccess($gid);
        $this->assign('data',$data);
        return view();
    }

    public function update()
    {
        $data = Request::instance()->param();
        $valid = validate('group');
        if (!$valid->scene('add')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        empty($data['rules']) ? $data['rules'] = array() : $data['rules'];
        $group = GroupModel::self()->edit($data);
        $access = AccessModel::self()->store($data['id'], $data['rules']);
        if ($group !== false && $access !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function remove()
    {
        $id = Request::instance()->param('id');
        if(empty($id)){
            return $this->response(400, lang('Data ID exception'));
        }
        Db::startTrans();
        $group = GroupModel::self()->remove($id);
        $access = AccessModel::self()->remove($id);
        if ($group !== false && $access !== false) {
            Db::commit();
            return $this->response(200);
        } else {
            Db::rollback();
            return $this->response(201);
        }
    }
}
