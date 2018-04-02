<?php
namespace app\admin\controller;

use think\Request;
use app\main\model\ArticleModel;
use app\main\model\CategoryModel;

class Category extends Admin
{
    public function index()
    {
        $data = CategoryModel::self()->getList();
        $this->assign('data', $data);
        return view();
    }

    public function create()
    {
        $data['category'] = CategoryModel::self()->getList();
        $this->assign('data', $data);
        return view();
    }

    public function store()
    {
        $data = Request::instance()->param();

        if (!$data['title'] || !$data['alias']) {
            return $this->response(400);
        }

        $cm = CategoryModel::self();
        $check = $cm->checkAlias($data['alias']);
        if ($check) {
            return $this->response(400, lang('Alias is exists'));
        }

        $result = $cm->add($data);
        if (is_numeric($result)) {
            return $this->response(200);
        } else {
            return $this->response(500);
        }
    }

    public function edit()
    {
        $id = Request::instance()->param('id');
        $cm = CategoryModel::self();
        $data = $cm->get(['id'=>$id]);
        $map['id'] = array('<>', $id);
        $data['category'] = $cm->getList($map);
        $this->assign('data', $data);
        return view();
    }

    public function update()
    {
        $data = input('post.');
        $id = $data['id'];
        $cm = CategoryModel::self();
        $info = $cm->get(['id'=>$id]);
        if ($data['alias'] != $info['alias']) {
            $checkEnName = $cm->checkAlias($data['alias']);
            if ($checkEnName) {
                return $this->response(400, lang('Alias is exists'));
            }
        }

        $result = $cm->edit($data);

        if (is_numeric($result)) {
            return $this->response(200);
        } else {
            return $this->response(500);
        }
    }

    public function remove()
    {
        $id = Request::instance()->param('id');
        $cm = CategoryModel::self();

        $checkSon = $cm->where('parent_id', $id)->find();
        if ($checkSon) {
            return $this->response(201);
        }
        // 检查栏目下是否有内容
        $checkContent = ArticleModel::self()
            ->where('category_id', $id)
            ->find();
        if ($checkContent) {
            return $this->response(201);
        }

        // 删除栏目
        $result = $cm->remove($id);
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }
    public function reorder()
    {
        $data = Request::instance()->param();
        $list = array_combine($data['ids'],$data['reorder']);
        $cm = CategoryModel::self();

        $vals =[];
        foreach($list as $k=>$v){
            $vals[]=['id'=>$k, 'reorder'=>$v];
        }
        $result = $cm->saveAll($vals);
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }


}

