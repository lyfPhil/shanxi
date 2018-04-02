<?php

namespace app\admin\controller;

use app\admin\controller\Admin;
use app\main\model\HelpArticleModel;
use think\Request;
use app\main\model\HelpCategoryModel;
use app\admin\model\UserModel;
use think\Session;
use think\Env;

class Helpcenter extends Admin{

    public function index()
    {
        $search = Request::instance()->param();
        $map = array();
        //搜索条件
        if (isset($search['title']) && $search['title'] != '') {
            $map['a.title'] = array('like', '%'.$search['title'].'%');
        }
        if (isset($search['category_id']) && $search['category_id'] != '') {
            $map['a.category_id'] = $search['category_id'];
        }
        if(isset($search['first_time']) && $search['first_time'] != '')
        {
            if(isset($search['end_time']) && $search['end_time'] != ''){
                $map['a.create_time'] = array('between',[strtotime($search['first_time']),  strtotime($search['end_time'])]);
            }  else {
                $map['a.create_time'] = array('>=',  strtotime($search['first_time']));
            }
        }else if(isset ($search['end_time']) && $search['end_time'] != '')
        {
            $map['a.create_time'] = array('<',strtotime($search['end_time']));
        }
        $map['a.type'] = 1;
        $hm = HelpArticleModel::self();
        $list = $hm->getHelpArticleList($map,'id desc',10,$search);
        $uids = [];
        foreach($list as $item)
        {
            if (!in_array($item['uid'], $uids))
            {
                $uids[] = $item['uid'];
            }
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
        }
        $page = $list->render();
        $users = UserModel::self()->getUserNamesByIds($uids);
        $cate =HelpCategoryModel::self()->getList();

        $this->assign('search',$search);
        $this->assign('users',$users);
        $this->assign('cate',$cate);
        $this->assign('list',$list);
        $this->assign('page',$page);
        return view();
    }
    public function create()
    {
        $data['category'] = HelpCategoryModel::self()->getList();
        $this->assign('category',$data['category']);
        $this->assign('data', $data);
        return view();
    }
    public function store()
    {
        $request = Request::instance();
        if($request->isAjax()){
            $data = $request->param();
            if(isset($data['image']) && $data['image'] != ''){
                $data['image'] = parse_url($data['image'], PHP_URL_PATH);
            }
            $admin_info = Session::get('userinfo','admin');
            $data['uid'] = $this->uid;
            $data['username'] = $admin_info['username'];
            $data['type'] = 1;
            $ret = HelpArticleModel::self()->add($data);
        }
        if(is_numeric($ret)){
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
    public function edit()
    {
        $params = Request::instance()->param();
        $detail = HelpArticleModel::self()->where('id',$params['id'])->find();
        if(!empty($detail['image'])){
            $detail['image'] = buildImageUrl($detail['image']);
        }
        $cm = HelpCategoryModel::self();
        $category = $cm->getList();
        $new_category = [];
        if (!empty($category)) {
            foreach ($category as $v) {
                // 选中
                if ($detail['category_id'] == $v['id']) {
                    $v['selected'] = 'selected';
                } else {
                    $v['selected'] = '';
                }
                array_push($new_category, $v);
            }
        }
        $this->assign('category',$new_category);
        $this->assign('data',$detail);

        return view();
    }
    public function remove()
    {
        $id = Request::instance()->param('id');
        $result = HelpArticleModel::self()->remove($id);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function removeimage()
    {
        $id = Request::instance()->param('id');
        $data=['id'=>$id, 'image'=>''];
        $result = HelpArticleModel::self()->edit($data);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }

    }
    public function update()
    {
        $data = Request::instance()->param();
        if(isset($data['image']) && $data['image'] != ''){
            $data['image'] = parse_url($data['image'], PHP_URL_PATH);
        }
        $data['uid'] = $this->uid;
        $ret = HelpArticleModel::self()->edit($data);
        if(is_numeric($ret)){
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
    public function handle()
    {
        $param = Request::instance()->param();
        $hc = HelpArticleModel::self();
        $ids = $param['ids'];
        if(!$ids){
            return $this->response(400);
        }
        switch ($param['type'])
        {
            case 'change':
                $data = ['status'=>1];
                $ret = $hc->updateArray($data, $ids);
                break;
            case 'delete':
                $ret = $hc->remove($ids);
                break;
        }
        if($ret != false ){
            return $this->response(200);
        }else{
            return $this->response(201);
        }


    }
    /**
     * 公告
     */
    public function announce_list(){
        $search = Request::instance()->param();
        $map = array();
        //搜索条件
        
        if (isset($search['status']) && $search['status'] != '') {
            $map['status'] = $search['status'];
        }
        if (isset($search['title']) && $search['title'] != '') {
            $map['title'] = $search['title'];
        }
        if(isset($search['first_time']) && $search['first_time'] != '')
        {
            if(isset($search['end_time']) && $search['end_time'] != ''){
                $map['create_time'] = array('between',[strtotime($search['first_time']),  strtotime($search['end_time'])]);
            }  else {
                $map['create_time'] = array('>=',  strtotime($search['first_time']));
            }
        }else if(isset ($search['end_time']) && $search['end_time'] != '')
        {
            $map['create_time'] = array('<',strtotime($search['end_time']));
        }
        $ance_model = HelpArticleModel::self();
        $map['type'] = 2;
        $list = $ance_model->getAnnouncePaginateList($map);
        foreach($list as &$item) {
            $item['detail_url'] = Env::get('web.main').'/notice/detail/'.$item['id'];
        }
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('search', $search);
        return view();
    }

    public function announce_detail(){
        $id = $this->param['id'];
        $where['id'] = $id;
        $ance_model = HelpArticleModel::self();
        $detail = $ance_model->getOneAnnounceDetail($where);
        $this->assign('detail', $detail);
        return view();
    }

    public function create_announce(){
        return view();
    }

    public function save_announce(){
        if (!$this->request->isAjax()) {
            return $this->response(400);
        }
        $param = $this->param;
        $admin_user = Session::get('userinfo','admin');
        $ance_model = HelpArticleModel::self();
        if (isset($param['id'])) {
            $data = [
                'uid' => $admin_user['id'],
                'username' => $admin_user['username'],
                'title' => $param['title'],
                'content' => $param['content'],
                'description' => $param['description'],
                'status' => $param['status']
            ];
            $ret = $ance_model->where('id', $param['id'])->update($data);
        } else {
            $data = [
                'uid' => $admin_user['id'],
                'username' => $admin_user['username'],
                'title' => $param['title'],
                'content' => $param['content'],
                'create_time' => time(),
                'description' => $param['description'],
                'status' => $param['status'],
                'type' => 2//公告
            ];
            $ret = $ance_model->insert($data);
        }
        if ($ret == 1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function edit_announce(){
        $where['id'] = $this->param['id'];
        $ance_model = HelpArticleModel::self();
        $detail = $ance_model->getOneAnnounceDetail($where);
        $this->assign('detail', $detail);
        return view();
    }

    public function remove_announce(){
        if (!$this->request->isAjax()) {
            return $this->response(400);
        }
        $id = $this->param['id'];
        $ance_model = HelpArticleModel::self();
        $ret = $ance_model->where('id', $id)->setField('status', 0);
        if ($ret == 1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
}
