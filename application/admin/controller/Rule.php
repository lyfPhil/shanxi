<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller\Admin;
use app\admin\model\AuthModel;

class Rule extends Admin
{
    const SPACE = "\xe3\x80\x80\xe3\x80\x80";

    private $data;

    public function _initialize()
    {
        parent::_initialize();
        $this->data = list_for_level(AuthModel::self()->getList(), self::SPACE);
    }

    public function index()
    {
        $this->assign('data', $this->data);
        return $this->fetch('rule/index');
    }

    public function create()
    {
        $this->assign('data', $this->data);
        return $this->fetch('rule/create');
    }

    public function store()
    {
        $data = input('post.');
        $result = AuthModel::self()->add($data);

        if (is_numeric($result)) {
            return $this->response(200);
        } else {
            return $this->response(201, $result);
        }
    }

    public function edit()
    {
        $id = Request::instance()->param('id');
        $info = AuthModel::self()->get($id);
        
        $this->assign('info', $info);
        $this->assign('list', $this->data);
        // dump($info['is_menu']);die();
        return $this->fetch('rule/edit');
    }

    public function update()
    {
        $data = input('post.');
        $result = AuthModel::self()->edit($data);

        if (is_numeric($result)) {
            return $this->response(200);
        } else {
            return $this->response(201, $result);
        }
    }

    public function remove()
    {
        $id = Request::instance()->param('id');

        // 检查该栏目是否拥有子栏目
        $checkSon = AuthModel::self()->getChild($id);
        if ($checkSon->count()) {
            return $this->response(201);
        }

        // 删除栏目
        $result = AuthModel::self()->remove($id);
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }

    public function reorder()
    {
        $data = Request::instance()->param();
        $list = array_combine($data['ids'],$data['reorder']);
        foreach($list as $k=>$v){
            $result = AuthModel::self()->reorder($k, $v);
        }

        if ($result !== false) {
            return $this->response(200);
        }

        return $this->response(201);
    }
}
