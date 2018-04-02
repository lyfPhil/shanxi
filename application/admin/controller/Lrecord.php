<?php

namespace app\admin\controller;

use app\admin\model\LogRecord;
use app\admin\model\UserModel;
use think\Request;


class Lrecord extends Admin{
    
    public function index()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['username']) && $search['username'] != ''){
            $map['b.username'] = $search['username'];
        }
        if(isset($search['ip']) && $search['ip'] != ''){
            $map['a.ip'] = $search['ip'];
        }
        //时间筛选
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
        $lm = LogRecord::self();
        $list = $lm->getList($map,'id desc',15,$param=[]);
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('search',$search);
        return view();
    }
    public function remove()
    {
        $ids = Request::instance()->param('id');
        $ret = logRecord::self()->remove($ids);
        if($ret != false){
            return $this->response(200);
        }
        return $this->response(201);
    }
    public function edit()
    {
        $id = Request::instance()->param('id');
        $detail = LogRecord::self()->alias('a')
                ->field('a.*,b.username')
                ->join('sx_admin_user b','a.user_id = b.id','LEFT')
                ->where('a.id',$id)
                ->find();
        $this->assign('detail',$detail);
 
        return view();
    }
}
