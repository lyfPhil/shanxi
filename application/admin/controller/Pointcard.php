<?php
namespace app\admin\controller;

use think\Request;
use app\main\model\GameModel;
use app\v1\service\FormatService;

class Pointcard extends Admin{

    const RE_STATUS = [
            '0' => 'Not Recommended',
            '1' => 'Recommended',
            '2' => 'Hot Recommended'
    ];

    public function pointCard()
    {
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['game_name'] = array('like', '%'.$search['keywords'].'%');
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
        $map['type'] = 2;
        $data = GameModel::self()->getGamePageList($map, 'status desc,sort desc,id desc', 10, $search);
        $page = $data->render();
        $this->assign('recommend_status',  self::RE_STATUS);
        $this->assign('list',$data);
        $this->assign('search', $search);
        $this->assign('page', $page);
        return view();
    }
     /**
     * 编辑点卡
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
        //处理封面图片路径
        if (!empty($detail['cover'])) {
            $detail['cover'] = buildImageUrl($detail['cover']);
        }
        //处理icon路径
        $detail['icon'] = buildImageUrl($detail['icon']);
        $goods_type = explode(',', $detail['goods_type']);
        $this->assign('detail',$detail);

        return view();
    }
    /**
     * 点卡新增
     * @return
     */
    public function create_pointcard()
    {
        return view();
    }
    /**
     * 处理新增的数据
     * @return
     */
    public function store()
    {
        $data = Request::instance()->param();
        $gm = GameModel::self();
        if(isset($data['icon']) && $data['icon']){
            $data['icon'] = parse_url($data['icon'], PHP_URL_PATH);
        }
        $data['game_type'] = 4;
        $data['goods_type'] = 5;
        $ret = $gm->add($data);
        if (is_numeric($ret)){
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }
    }
    /**
     * 编辑更新
     * @return
     */
    public function update()
    {
        $data = Request::instance()->param();
        $gm = GameModel::self();
        if(isset($data['icon']) && $data['icon']){
            $data['icon'] = parse_url($data['icon'], PHP_URL_PATH);
        }
        $ret = $gm->edit($data);
        if (is_numeric($ret)){
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }
    }
}
