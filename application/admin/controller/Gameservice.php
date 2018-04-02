<?php

namespace app\admin\controller;

use app\admin\controller\Admin;
use app\main\model\GameServiceModel;
use app\main\model\GameModel;
use app\main\model\OperatorsModel;
use think\Request;

class gameService extends Admin{
    //put your code here
    public function index()
    {
        $search = Request::instance()->param();
        $map = array();
        //搜索条件
        if (isset($search['game_id']) && $search['game_id'] != '') {
            $map['a.game_id'] = $search['game_id'];
        }
        if (isset($search['service_name']) && $search['service_name'] != '') {
            $map['a.service_name'] = array(array('like', '%'.trim($search['service_name']).'%'),'or');
        }
        if(isset($search['keywords']) && $search['keywords'] != ''){
            $map['b.game_name'] = array(array('like','%'.trim($search['keywords']).'%'),'or'); 
        }
        /*
         * 时间筛选
         */
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
        $list= GameServiceModel::self()->gameServiceList($map,'id desc',10,$search);
        $page = $list->render();
        $game_cate = GameModel::self()->getType(['type'=>1]);
        $this->assign('search',$search);
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('game_cate',$game_cate);
        return view();
    }
    public function pointcard()
    {
        $search = Request::instance()->param();
        $map = array();
        //搜索条件
        if (isset($search['card_id']) && $search['card_id'] != '') {
            $map['a.game_id'] = $search['card_id'];
        }
        if (isset($search['card_face']) && $search['card_face'] != '') {
            $map['a.service_name'] = array('like', '%'.$search['card_face'].'%');
        }
        if(isset($search['keywords']) && $search['keywords'] != ''){
            $map['b.game_name'] = array('like','%'.$search['keywords'].'%'); 
        }
        /*
         * 时间筛选
         */
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
        $map['a.type'] = 2;
        $list= GameServiceModel::self()->gameServiceList($map,'id desc',10,$search);
        $page = $list->render();
        $card_cate = GameModel::self()->getType(['type'=>2]);
        $this->assign('search',$search);
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('card_cate',$card_cate);
        return view();
    }
    //remove 
    public function remove()
    {
        $ids = Request::instance()->param('id');
        $result = GameServiceModel::self()->remove($ids);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        } 
    }
    public function edit()
    {
        $id = Request::instance()->param('id');
        $detail = GameServiceModel::self()->where('id',$id)->find();
        if(!$detail){
            return $this->response(400);
        }
        if($detail['type'] == 2)
        {
            $game_name = GameModel::self()->field('id,sort,game_name')->where('game_type',4)->order('sort desc')->select();
            $this->assign('game',$game_name);
            $this->assign('detail',$detail);
            return $this->fetch('edit_card');
        }
        $game_name = GameModel::self()->field('id,sort,game_name')->order('sort desc')->select();
        $operators = OperatorsModel::self()->field('id,operators_name')->where('status',1)->select();
        $parent_serviece = GameServiceModel::self()->field('parent_id as id,service_name')->group('service_name')->order('id')->select();
        $this->assign('parent_service',$parent_serviece);
        $this->assign('operators',$operators);
        $this->assign('detail',$detail);
        $this->assign('game',$game_name);
        return view();
    }
    public function update()
    {
        $request = Request::instance();
        $data = $request->param();      
        $ret = GameServiceModel::self()->edit($data);
        if($ret){
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
    public function create()
    {
        $param = Request::instance()->param();
        $map = array();
        if(isset($param['keywords']) && $param['keywords'] != ''){
            $map['game_name'] = array('like','%'.trim($param['keywords']).'%');
        }
        $game_name = GameModel::self()->field('id,sort,game_name')->where($map)->order('sort desc')->select();
        $operators = OperatorsModel::self()->field('id,operators_name')->where('status',1)->select();

        $this->assign('operators',$operators);
        $this->assign('game',$game_name);
        return view();
    }
    public function store()
    {
        $data = Request::instance()->param();
        $ret = GameServiceModel::self()->add($data);
        if (is_numeric($ret)) {
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }
    }
    /**
     * 新增点卡面额
     * @return
     */
    public function create_pointcard_cost()
    {
        $param = Request::instance()->param();
        $map = array();
        if(isset($param['keywords']) && $param['keywords'] != ''){
            $map['game_name'] = array('like','%'.trim($param['keywords']).'%');
        }
        $game = GameModel::self()->field('id,sort,game_name')->where($map)->where('type',2)->order('sort desc')->select();
        $this->assign('game',$game);
        return view();
    }
}
