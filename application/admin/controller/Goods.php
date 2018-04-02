<?php


namespace app\admin\controller;

use think\Request;
use app\main\model\GoodsModel;
use app\main\model\GoodsTypeModel;
use app\main\service\FormatService;
use think\Env;
class Goods extends Admin{
    const  GOODS_TYPE=[
        //账号类型
        '1'     =>'机票',
        '2'     =>'酒店',
        '3'     =>'假期游',
        '4'     =>'巴士票',
        '5'     =>'火车票',
        '6'     =>'周末游',
        '7'     =>'景点门票',
        '8'     =>'美食特产',
    ];
    const DELIVER_TYPE=[
        '0'=>'Consignment',
        '1'=>'Guarantee'
    ];

    /**
     * 商品列表
     * @return
     */
    public function index()
    {
        $search = Request::instance()->param();
        //搜索条件
        $map = array();
        if(isset($search['title']) && $search['title'] != ''){
            $map['title'] = array(array('like','%'.trim($search['title']).'%'),'or');
        }
        if(isset($search['game_name']) && $search['game_name'] != '')
        {
            $map['game_name'] = array(array('like','%'.trim($search['game_name']).'%'),'or');
        }
        if(isset($search['deliver_id']) && $search['deliver_id'] != ''){
            $map['deliver_id'] = $search['deliver_id'];
        }
        if(isset($search['user_name']) && $search['user_name'] != ''){
            $map['user_name'] = $search['user_name'];
        }
        if(isset($search['goods_type']) && $search['goods_type'] != '')
        {
            $map['goods_type'] = $search['goods_type'];
        }
        if(isset($search['state']) && $search['state'] != '')
        {
            $map['state'] = $search['state'];
        }
        //发布时间
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
        $gm = GoodsModel::self();
        $list = $gm->getGoodsPageList($map,'id desc',10,$search);
        foreach($list as $item)
        {
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            $item['goods_type_name'] = FormatService::formatGoodsTypesName($item['goods_type']);
            $item['detail_url'] = Env::get('web.main').'/goods/detail/'.$item['id'];
        }
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('search',$search);
        $this->assign('type',  self::GOODS_TYPE);
        $this->assign('deliver_type',  self::DELIVER_TYPE);

        return view();
    }
    /**
     * 查看商品详情
     * @return
     */
    public function detail()
    {
        $map['id'] = Request::instance()->param('id');
        $data = GoodsModel::self()->where($map)->find();
        $data['goods_type_name'] = FormatService::formatGoodsTypesName($data['goods_type']);
        $data['pic_url'] = buildImageUrl($data['pic_url']);
        $data['create_time'] = date("Y-m-d H:i:s",$data['create_time']);
        $data['over_time'] = !empty($data['over_time'])?date("Y-m-d H:i:s",$data['over_time']):"";
        if(!$data){
            return $this->wrong(404600);
        }
        $this->assign('data',$data);
        return view();
    }
    /**
     * 广告获取商品详情
     */
    public function cover_goods_detail(){
        $where['id'] = $this->param['goods_id'];
        $goods_model = GoodsModel::self();
        $field = 'price,user_name';
        $goods = $goods_model->getOneGoodsByCondition($where, $field);
        return $this->response(200,'',$goods);
    }
    /**
     * 获取账号密码,商品详情页使用
     * @return
     */
    public function getPassword()
    {
        $param = Request::instance()->param();
        $field = $param['pw_type'];
        $data = GoodsModel::self()->field($field)->where('id',$param['id'])->find();
        if($data)
            return $this->response(200,"",['pwd'=>$data[$field]]);
        else
            return $this->response(201,$msg="获取密码失败！");
    }
    public function handle()
    {
        $param = Request::instance()->param();
        $gm = GoodsModel::self();
        $ids = $param['ids'];
        switch ($param['type'])
        {
            case 'check':
                $data['state'] = 0;
                $ret = $gm->updateArray($data,$ids);
                break;
            case 'on_offer':
                $data['state'] = 1;
                $ret = $gm->updateArray($data,$ids);
                break;
            case 'check_false':
                $data['state'] = 2;
                $ret = $gm->updateArray($data,$ids);
                break;
            case 'sold_out':
                $data['state'] = 3;
                $ret = $gm->updateArray($data,$ids);
                break;
        }
        if($ret !== false){
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
}
