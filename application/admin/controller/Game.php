<?php

namespace app\admin\controller;

use app\main\model\GameModel;
use app\main\model\GameServiceModel;
use app\admin\controller\Admin;
use app\v1\service\FormatService;
use think\Request;
use think\Db;

class Game extends Admin
{
    const CAME_TYPE =[
            '0' => 'PC Games',
            '1' => 'Mobile Games',
            '2' => 'Web Games',
            '4' => 'Game Card'
        ];
    const RE_STATUS = [
            '0' => 'Not Recommended',
            '1' => 'Recommended',
            '2' => 'Popular'
    ];
    /**
     * 游戏管理列表
     * @return
     */
    public function index()
    {
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['game_name'] = ['like', '%'.$search['keywords'].'%'];
        }
        if (isset($search['status']) && $search['status'] != '') {
            $map['status'] = $search['status'];
        }
        if (isset($search['game_type']) && $search['game_type'] != '') {
            $map['game_type'] = $search['game_type'];
        }
        if (isset($search['recommend_status']) && $search['recommend_status'] != '') {
            $map['recommend_status'] = $search['recommend_status'];
        }
        /*
         * 时间筛选
         */
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
        $map['type'] = 1;
        $data = GameModel::self()->getGamePageList($map, 'status desc,sort desc,id desc', 10, $search);
        $page = $data->render();
        $this->assign('recommend_status',  self::RE_STATUS);
        $this->assign('type',self::CAME_TYPE);
        $this->assign('list',$data);
        $this->assign('search', $search);
        $this->assign('page', $page);
        return view();
    }
    /**
     * 删除游戏
     * @return
     */
    public function remove()
    {
        $ids = Request::instance()->param('id');
        $result = GameModel::self()->remove($ids);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    /**
     * 编辑游戏
     * @return
     */
    public function edit()
    {
        $id = Request::instance()->param('id');
        $gm = GameModel::self();
        $ret = $gm->where('id',$id)->find();
        if(!$ret)
        {
            return $this->response(400);
        }
        $detail = FormatService::formatNull($ret->getData());
        $game_service = GameServiceModel::self()->field('id,service_name')->group('service_name')->order('id')->select();
        //处理封面图片路径
        if (!empty($detail['cover'])) {
            $detail['cover'] = buildImageUrl($detail['cover']);
        }
        //处理icon路径
        if (!empty($detail['icon'])){
            $detail['icon'] = buildImageUrl($detail['icon']);
        }
        $goods_type = explode(',', $detail['goods_type']);
        $this->assign('game_service',$game_service);
        $this->assign('game',$detail);
        $this->assign('type',self::CAME_TYPE);
        $this->assign('goods_type',$goods_type);

        return view();
    }
    /**
     * 更新游戏
     * @return
     */
    public function update()
    {
        $request = Request::instance();
        $data = $request->param();
        $valid = validate('game');
        if (!$valid->scene('edit')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        $data['goods_type'] = implode(',',$data['goods_type']);
        if(isset($data['cover']) && $data['cover']){
            $data['cover'] = parse_url($data['cover'], PHP_URL_PATH);
        }
        if(isset($data['icon']) && $data['icon']){
            $data['icon'] = parse_url($data['icon'], PHP_URL_PATH);
        }
        $am = GameModel::self();
        $ret = $am->edit($data);
        if (is_numeric($ret)) {
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }
    }
    /**
     * 游戏新增
     * @return
     */
    public function create()
    {
        $game_service = GameServiceModel::self()->field('id,service_name')->group('service_name')->order('id')->select();

        $this->assign('game_service',$game_service);
        $this->assign('type', self::CAME_TYPE);
        return view();
    }
    public function create_step2()
    {
        $data = Request::instance()->param();
        $this->assign('game_id',$data['game_id']);
        return view();
    }

    /**
     * 处理新增游戏的数据
     * @return
     */
    public function store()
    {
        $data = Request::instance()->param();
        //dump($data);exit();
        if(isset($data['goods_type']) && $data['goods_type'] != ''){
            $data['goods_type'] = implode(',',$data['goods_type']);
        }
        if(isset($data['cover']) && $data['cover'] != ''){
            $data['cover'] = parse_url($data['cover'], PHP_URL_PATH);
        }
        if(isset($data['icon']) && $data['icon'] != ''){
            $data['icon'] = parse_url($data['icon'], PHP_URL_PATH);
        }
        $valid = validate('game');
        if (!$valid->scene('add')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        $am = GameModel::self();
        $ret = $am->add($data);
        if (is_numeric($ret)) {
            return $this->response(200,'',$ret);
        } else {
            return $this->response(201,$ret);
        }

    }
    public function uploadPic()
    {
        $data = Request::instance()->param();
        if(isset($data['cover']) && $data['cover']){
            $data['cover'] = parse_url($data['cover'], PHP_URL_PATH);
        }
        $update = ['cover'=>$data['cover']];
        $am = GameModel::self();
        $ret = $am->where('id',$data['game_id'])->update($update);
        if (is_numeric($ret)) {
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }
    }
    /**
     * 删除游戏封面图片
     * @return
     */
    public function removeIcon()
    {
        $id = Request::instance()->param('id');
        $data=['id'=>$id, 'icon'=>NULL];
        $result = GameModel::self()->edit($data);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    public function removeCover()
    {
        $id = Request::instance()->param('id');
        $data=['id'=>$id, 'cover'=>NULL];
        $result = GameModel::self()->edit($data);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    /**
     * 批量处理
     * @return
     */
    public function handle()
    {
        $params = Request::instance()->param();
        $gm = GameModel::self();
        $ids = $params['ids'];
        if(!$ids){
            return $this->response(400);
        }
        switch ($params['type'])
        {
            case 'delete':
                $res = $gm->remove($ids);
                break;
            case 'low':
                $data['status'] = '-1';
                $res = $gm->updateArray($data, $ids);
                break;
            case 'hot':
                $data['recommend_status'] = 2;
                $res = $gm->updateArray($data, $ids);
                break;
            case 'recommend':
                $data['recommend_status'] = 1;
                $res = $gm->updateArray($data, $ids);
                break;
            case 'notrecommend':
                $data['recommend_status'] = 0;
                $res = $gm->updateArray($data, $ids);
                break;
        }
        if($res !== false){
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
    /**
     * 修改排序 sort字段 数字越大越靠前
     * @return
     */
    public function reorder()
    {
        $data = Request::instance()->param();
        $list = array_combine($data['ids'],$data['sort']);
        $gm = GameModel::self();
        $vals =[];
        foreach($list as $k=>$v){
            $vals[]=['id'=>$k, 'sort'=>$v];
        }
        $result = $gm->saveAll($vals);
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }
    public function hotGames()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['game_type']) && $search['game_type'] != ''){
            if($search['game_type'] == '4'){
                $map['type'] = 2;
            }else{
                $map['type'] = 1;
            }
            $map['game_type'] = $search['game_type'];
        }
        $list = GameModel::self()->getHotGames($map,'sort desc',10);
        $page = $list->render();
        $this->assign('search',$search);
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('type',self::CAME_TYPE);

        return $this->fetch('hotgame/hotgames');
    }
    public function createHotGames()
    {
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['game_name'] = array('like', '%'.$search['keywords'].'%');
        }
        if(isset($search['game_type']) && $search['game_type'] != ''){
            $map['game_type'] = $search['game_type'];
        }
        $game = GameModel::self()->field('id,game_name')->where($map)->order('sort')->select();
        $this->assign('type',self::CAME_TYPE);
        $this->assign('games',$game);
        return $this->fetch("hotgame/createhotgames");
    }
    public function addhotgames()
    {
        $parma = Request::instance()->param();
        $update = [
            'recommend_status'=>2,
            'sort'=>$parma['sort']
        ];
        $ret = GameModel::self()->where('id',$parma['game_id'])->update($update);
        if(is_numeric($ret)){
            return $this->response(200);
        }
        return $this->response(201);
    }
    public function removeHotGames()
    {
        $ids = Request::instance()->param('id');
        $result = GameModel::self()->where('id',$ids)->update(['recommend_status'=>0]);
        if ($result) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
}
