<?php
namespace app\admin\controller;

use think\Request;
use app\main\model\HelpArticleModel;
use app\main\model\HelpCategoryModel;


class Helpcategory extends Admin
{
    public function index()
    {
        $data = HelpCategoryModel::self()->getList();
        $this->assign('data', $data);
        return view();
    }

    public function create()
    {
        $data['category'] = HelpCategoryModel::self()->getList();
        $this->assign('data', $data);
        return view();
    }

    public function store()
    {
        $data = Request::instance()->param();
        if (!$data['title'] || !$data['alias']) {
            return $this->response(400);
        }

        $cm = HelpCategoryModel::self();
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
        $cm = HelpCategoryModel::self();
        $data = $cm->get(['id'=>$id]);
        $map['id'] = array('<>', $id);
        $data['category'] = $cm->getList($map);
        $this->assign('data', $data);
        return view();
    }

    public function update()
    {
        $data = Request::instance()->param();
        $id = $data['id'];
        $cm = HelpCategoryModel::self();
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
        $cm = HelpCategoryModel::self();

        $checkSon = $cm->where('parent_id', $id)->find();
        if ($checkSon) {
            return $this->response(201);
        }
        // 检查栏目下是否有内容
        $checkContent = HelpArticleModel::self()
            ->where('category_id', $id)
            ->find();
        if ($checkContent) {
            return $this->error("该栏目下内容不为空");
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
        $cm = HelpCategoryModel::self();

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

