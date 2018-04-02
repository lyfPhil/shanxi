<?php

namespace app\admin\controller;

use think\Request;
use app\main\model\OrderModel;
use app\main\model\ArgueModel;
use app\main\model\GoodsTypeModel;
use app\main\model\AddressModel;
use app\v1\service\FormatService;
use think\Session;
use think\Loader;
use think\Env;
class Order extends Admin{
     /**
      * 订单列表
      * @return
      */
    public function index()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['order_sn']) && $search['order_sn'] != '') {
            $map['order_sn'] = array('like','%'.$search['order_sn'].'%');
        }
        if(isset($search['title']) && $search['title'] != '') {
            $map['title'] = array('like','%'.$search['title'].'%');
        }
        if(isset($search['sell_name']) && $search['sell_name'] != '') {
            $map['sell_name'] = array('like','%'.$search['sell_name'].'%');
        }
        if(isset($search['buy_name']) && $search['buy_name'] != '') {
            $map['buy_name'] = array('like','%'.$search['buy_name'].'%');
        }
        if(isset($search['keywords']) && $search['keywords'] != '') {
            $map['title|server_name'] = array('like','%'.$search['keywords'].'%');
        }
        if(isset($search['goods_type']) && $search['goods_type'] != '') {
            $map['goods_type'] = $search['goods_type'];
        }
        if(isset($search['state']) && $search['state'] != '') {
            $map['state'] = $search['state'];
        }
        $list = OrderModel::self()->getOrderList($map,'id desc',10,$search);
        foreach ($list as $item) {
            $item['goods_type_name'] = FormatService::formatGoodsTypesName($item['goods_type']);
            $item['detail_url'] = Env::get('web.main').'/commodity/detail/'.$item['goods_id'];
        }
        $goods_type = GoodsTypeModel::self()->getAllGoodsTypeName();
        // var_dump($goods_type);die();
        $page = $list->render();
        $this->assign('search',$search);
        $this->assign('type',$goods_type);
        $this->assign('page',$page);
        $this->assign('list',$list);
        return view();
    }
    /**
     * 订单申诉列表
     * @return
     */
    public function argue()
    {
        $search = Request::instance()->param();
        $map = [];
        // $map['b.state'] = 10;
        if(isset($search['keywords']) && $search['keywords'] != '') {
            $map['b.order_sn'] = $search['keywords'];
        }
        if(isset($search['status']) && $search['status'] != '') {
            $map['b.state'] = $search['status'];
        }
        if(isset($search['buy_name']) && $search['buy_name'] != '') {
            $map['b.buy_name'] = array('like','%'.$search['buy_name'].'%');
        }
        $lists = ArgueModel::self()->getArgueLists($map,'',10,$search);
        // foreach ($lists as $item) {
        //     $item['order_state'] = $this->getArgueStatus($item['order_sn']);
            // var_dump($item['order_state']);die();
        // }
        $page = $lists->render();
        $this->assign('search',$search);
        $this->assign('list',$lists);
        $this->assign('page',$page);
        return view();
    }
    /**
     * 订单详情
     * @return
     */
    public function detail()
    {
        $map['order_sn'] = Request::instance()->param('order_sn');
        $order = OrderModel::self()->where($map)->find();
        if(!$order){
            return $this->wrong(404600);
        }
        $order['goods_type_name'] = FormatService::formatGoodsTypesName($order['goods_type']);
        $info = AddressModel::self()->where('user_id',$order['buy_id'])->find();
        if(!empty($order['ext_attr'])){
            $order['ext_attr'] = FormatService::formatExtAttr($order['ext_attr']);
        }
        $address = json_decode($order['address_info'],TRUE);
        $this->assign('address',$address);
        $this->assign('info',$info);
        $this->assign('data',$order);
        return view();
    }
    /**
     * 申诉详情
     * @return
     */
    public function argueDetails()
    {
        $map['order_sn'] = Request::instance()->param('order_sn');
        $order = OrderModel::self()->field('order_sn,price,total,actual,title,num')->where($map)->find();
        $argue = ArgueModel::self()->where($map)->select();
        $argue_list = [];
        foreach($argue as $item)
        {
            $detail = json_decode($item['detail'],true);
            $pic = [];
            $item['text'] = "";
            if($detail['pic']!='')
            {
                foreach($detail['pic'] as $val){
                    $pic[]= buildImageUrl($val);
                }
            }
            if($detail['text']!='')
            {
                $item['text'] = $detail['text'];
            }
            $item['pic'] = $pic;
            $argue_list[]=$item;
        }
        $count = count($argue_list);
        $state = $argue_list[$count-1]['order_state'];
        $this->assign('order',$order);
        $this->assign('state',$state);
        $this->assign('data',$argue_list);
        // var_dump($argue_list);die();
        return view();
    }
    public function handle()
    {
        $param = Request::instance()->param();
        if(!isset($param['order_sn'])){
            return $this->wrong(400);
        }
        $order = OrderModel::self()->field('*')->where('order_sn',$param['order_sn'])->find();
        if ($order['state'] != 10) {
            return $this->response(400,'Order has been processed');
        }
        if(isset($param['buyer_cash']) && $param['buyer_cash'] != ''){
            if($param['buyer_cash'] > $order['actual']){
                return $this->response(400,"The refund of the amount of the buyer should not be greater than the actual payment of the order");
            }
        }
        if(isset($param['seller_cash']) && $param['seller_cash'] != ''){
            if($param['seller_cash'] > $order['actual']){
                return $this->response(400,"The refund of the amount of the seller should not be greater than the actual payment of the order");
            }
            $param['buyer_cash'] = $order['actual']-$param['seller_cash'];
        }
        $param['reason'] = $param['result'];
        $param['result'] = lang($param['wname']);
        $AdminUser = Session::get('userinfo','admin');
        $am = ArgueModel::self();
        $ret = $am->handleArgue($param,$AdminUser,$order);
        if ($ret === true) {
            $log = [
                'operation_time' => date('Y-m-d H:i:s',time()),
                'order_sn' => $param['order_sn']
            ];
            Loader::model('LogRecord')->record(lang('Handle complaint',$log));
            return $this->response(200,"Deal with success");
        } elseif($ret === false) {
            return $this->response(201);
        } elseif($ret === -1) {
            return $this->response(201,'The cost of deducting the amount of the seller is less than zero');
        }
    }
    //获取申诉的最新状态
    public function getArgueStatus($order_sn)
    {
        $map['order_sn'] = $order_sn;
        $data = ArgueModel::self()->field('*')->where($map)->order('id desc')->limit(1)->select();
        foreach ($data as $key=>$value){
            $data['order_state'] = $value['order_state'];
        }
        return $data['order_state'];
    }
}
