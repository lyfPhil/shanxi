<?php

namespace app\admin\controller;

use think\Request;
use app\main\model\ServiceCenterModel;
use app\v1\service\FormatService;
use think\Env;
use think\Session;

//服务中心
class Servicecenter extends Admin{

    //用户咨询
    public function consult()
    {
        $search = Request::instance()->param();
        $map = [];
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['nickname'] = array('like', '%'.$search['keywords'].'%');
        }
        $lists = ServiceCenterModel::self()->getConsult($map,'id desc',5,$search);
        foreach ($lists as $item) {
            $lists[] = FormatService::formatNull($item->getData());
        }
        $page = $lists->render();
        $this->assign('list',$lists);
        $this->assign('page',$page);
        return view();
    }
    //用户建议
    public function suggest()
    {
        $search = Request::instance()->param();
        $map = [];
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['username'] = array('like', '%'.$search['keywords'].'%');
        }
        $map['ftype'] = 1;
        $lists = ServiceCenterModel::self()->getSuggest($map,'id desc',10,$search);
        $page = $lists->render();
        $this->assign('list',$lists);
        $this->assign('page',$page);
        return view();
    }
    //回复
    public function edit()
    {
        $map['id'] = Request::instance()->param('id');
        $userInfo = Session::get('userinfo', 'admin');
        $data = ServiceCenterModel::self()->where($map)->find();
        $data = FormatService::formatNull($data->getData());
        if (!empty($data['picture'])) {
            $data['picture'] = json_decode($data['picture'], true);
        }
        $data['recovery_name'] = $userInfo['username'];
        $this->assign('data',$data);
        return view();
    }
    //提交保存
    public function update()
    {
        $data = Request::instance()->param();
        $userInfo = Session::get('userinfo', 'admin');
        $data['recovery_id'] = $userInfo['id'];
        $data['recovery_name'] = $userInfo['username'];
        //update
        $service_model = ServiceCenterModel::self();
        $ret = $service_model->edit($data);
        if ($ret != false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
}
