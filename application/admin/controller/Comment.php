<?php
namespace app\admin\controller;

use think\Request;
use think\Db;
use app\main\model\ArtcommentModel;
use app\main\model\CommentModel;
use app\user\model\UserModel;

class Comment extends Admin
{
    // 景点文章评论
    public function articleComment()
    {
        
        $search = Request::instance()->param();
        $map = array();
        $map['type']=0;
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['a.title'] = array('like', '%'.trim($search['keywords']).'%');
        }
        if (isset($search['status']) && $search['status'] != '') {
            $map['c.status'] = $search['status'];
        }
        /*
         * 时间筛选
         */
        if(isset($search['first_time']) && $search['first_time'] != '')
        {
            if(isset($search['end_time']) && $search['end_time'] != ''){
                $map['c.create_at'] = array('between',[strtotime($search['first_time']),  strtotime($search['end_time'])]);
            }  else {
                $map['c.create_at'] = array('>=',  strtotime($search['first_time']));
            }
        }else if(isset ($search['end_time']) && $search['end_time'] != '')
        {
            $map['c.create_at'] = array('<',strtotime($search['end_time']));
        }
        $data = ArtcommentModel::self()->getList($map, 'id desc', 10, $search);
        foreach($data as $item)
        {
            if(!empty($item['create_at']))
            {
               $item['create_at'] = date("Y-m-d H:i:s",$item['create_at']);
            }
        }
        $uids = [];
        foreach($data as $item)
        {
            if (!in_array($item['uid'], $uids))
            {
                $uids[] = $item['uid'];
            }
            if (!in_array($item['to_uid'], $uids))
            {
                $uids[] = $item['to_uid'];
            }
            if (!in_array($item['from_uid'], $uids))
            {
                $uids[] = $item['from_uid'];
            }
        }
        $users = UserModel::self()->getUserByIds($uids);
        $page = $data->render();
        $this->assign('data', $data);
        $this->assign('users', $users);
        $this->assign('search', $search);
        $this->assign('page', $page);
        // print_r($data);die();
        return view();
    }
    // 博客文章评论
    public function blogComment()
    {
        
        $search = Request::instance()->param();
        $map = array();
        $map['type']=1;
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['a.title'] = array('like', '%'.trim($search['keywords']).'%');
        }
        if (isset($search['status']) && $search['status'] != '') {
            $map['c.status'] = $search['status'];
        }
        /*
         * 时间筛选
         */
        if(isset($search['first_time']) && $search['first_time'] != '')
        {
            if(isset($search['end_time']) && $search['end_time'] != ''){
                $map['c.create_at'] = array('between',[strtotime($search['first_time']),  strtotime($search['end_time'])]);
            }  else {
                $map['c.create_at'] = array('>=',  strtotime($search['first_time']));
            }
        }else if(isset ($search['end_time']) && $search['end_time'] != '')
        {
            $map['c.create_at'] = array('<',strtotime($search['end_time']));
        }
        $data = ArtcommentModel::self()->getList($map, 'id desc', 10, $search);
        foreach($data as $item)
        {
            if(!empty($item['create_at']))
            {
               $item['create_at'] = date("Y-m-d H:i:s",$item['create_at']);
            }
        }
        $uids = [];
        foreach($data as $item)
        {
            if (!in_array($item['uid'], $uids))
            {
                $uids[] = $item['uid'];
            }
            if (!in_array($item['to_uid'], $uids))
            {
                $uids[] = $item['to_uid'];
            }
            if (!in_array($item['from_uid'], $uids))
            {
                $uids[] = $item['from_uid'];
            }
        }
        $users = UserModel::self()->getUserByIds($uids);
        $page = $data->render();
        $this->assign('data', $data);
        $this->assign('users', $users);
        $this->assign('search', $search);
        $this->assign('page', $page);
        // print_r($data);die();
        return view();
    }
    /**
     * 商品评论
     * @return
     */
    public function goodscomment()
    {
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['a.title'] = array('like', '%'.$search['keywords'].'%');
        }
        if (isset($search['status']) && $search['status'] != '') {
            $map['c.status'] = $search['status'];
        }
        /*
         * 时间筛选
         */
        if(isset($search['first_time']) && $search['first_time'] != '')
        {
            if(isset($search['end_time']) && $search['end_time'] != ''){
                $map['c.create_time'] = array('between',[strtotime($search['first_time']),  strtotime($search['end_time'])]);
            }  else {
                $map['c.create_time'] = array('>=',  strtotime($search['first_time']));
            }
        }else if(isset ($search['end_time']) && $search['end_time'] != '')
        {
            $map['c.create_time'] = array('<',strtotime($search['end_time']));
        }
        $data = CommentModel::self()->getCommentPageList($map, 'id desc', 10, $search);
        foreach($data as $item)
        {
            if(!empty($item['create_time']))
            {
               $item['create_time'] = date("Y-m-d H:i:s",$item['create_time']);
            }
        }
        $uids = [];
        foreach($data as $item)
        {
            if (!in_array($item['to_user_id'], $uids))
            {
                $uids[] = $item['to_user_id'];
            }
            if (!in_array($item['user_id'], $uids))
            {
                $uids[] = $item['user_id'];
            }
        }
        $users = UserModel::self()->getUserByIds($uids);
        $page = $data->render();
        $this->assign('data', $data);
        $this->assign('users', $users);
        $this->assign('search', $search);
        $this->assign('page', $page);
        return view();
    }
    public function handle()
    {
        $data = Request::instance()->param();
        $acm = ArtcommentModel::self();
        $ids = $data['ids'];
        if (!$ids)
        {
            return $this->response(400);
        }
        switch ($data['type']) {
        case 'delete':
            $result = $acm->remove($ids);
            break;
        case 'change':
            $map=['status'=>1];
            $result = $acm->updateArray($map, $ids);
            break;
        case 'cancel':
            $map=['status'=>0];
            $result = $acm->updateArray($map, $ids);
            break;
        }
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }
    public function handleGoodsComment()
    {
        $data = Request::instance()->param();
        $cm = CommentModel::self();
        $ids = $data['ids'];
        if (!$ids)
        {
            return $this->response(400);
        }
        switch ($data['type']) {
        case 'delete':
            $result = $cm->remove($ids);
            break;
        case 'change':
            $map=['status'=>1];
            $result = $cm->updateArray($map, $ids);
            break;
        case 'cancel':
            $map=['status'=>0];
            $result = $cm->updateArray($map, $ids);
            break;
        }
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }


    public function edit()
    {
        $id = Request::instance()->param('id');
        $acm = ArtcommentModel::self();
        $data = $acm->getInfo($id)->toArray();
        $uids =[$data['to_uid'], $data['from_uid']];
        $users = UserModel::self()->getUserByIds($uids);
        $data['from_username'] = isset($users[$data['from_uid']])?$users[$data['from_uid']]['username']:'';
        $data['to_username'] = isset($users[$data['to_uid']])?$users[$data['to_uid']]['username']:'';
        $this->assign('data', $data);
        return view();
    }

    public function blogedit()
    {
        $id = Request::instance()->param('id');
        $acm = ArtcommentModel::self();
        $data = $acm->getInfo($id)->toArray();
        $uids =[$data['to_uid'], $data['from_uid']];
        $users = UserModel::self()->getUserByIds($uids);
        $data['from_username'] = isset($users[$data['from_uid']])?$users[$data['from_uid']]['username']:'';
        $data['to_username'] = isset($users[$data['to_uid']])?$users[$data['to_uid']]['username']:'';
        $this->assign('data', $data);
        return view();
    }

    public function goodsEdit()
    {
        $id = Request::instance()->param('id');
        $cm = CommentModel::self();
        $data = $cm->getGoodsCommentInfo($id)->toArray();
        $uids =[$data['to_user_id'], $data['user_id']];
        $users = UserModel::self()->getUserByIds($uids);
        $data['from_username'] = isset($users[$data['user_id']])?$users[$data['user_id']]['username']:'';
        $data['to_username'] = isset($users[$data['to_user_id']])?$users[$data['to_user_id']]['username']:'';
        $this->assign('data', $data);
        return view();
    }
    public function updateGoodsStatus()
    {
        $data = input('post.');
        $cm = CommentModel::self();
        $result = $cm->where('id',$data['id'])->update($data);
        if (is_numeric($result)) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    public function update()
    {
        $data = input('post.');
        $acm = ArtcommentModel::self();
        $result = $acm->edit($data);
        if (is_numeric($result)) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    public function removeGoodsComment()
    {
        if (Request::instance()->isAjax()) {
            $id = Request::instance()->param('id');
            $result = CommentModel::self()->remove($id);
            if (is_numeric($result)) {
                return $this->response(200);
            } else {
                return $this->response(201);
            }
        }
    }
    public function remove()
    {
        if (Request::instance()->isAjax()) {
            $id = Request::instance()->param('id');
            $result = ArtcommentModel::self()->remove($id);
            if (is_numeric($result)) {
                return $this->response(200);
            } else {
                return $this->response(201);
            }
        }
    }

}
