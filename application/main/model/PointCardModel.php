<?php
namespace app\main\model;

use think\Model;
use app\main\model\OrderModel;
use app\main\model\GoodsModel;
use app\main\model\GoodsPicModel;
use app\main\service\CommService;
use app\main\model\MessageModel;
use app\common\service\CryptService;
use app\v1\service\FormatService;
use app\user\service\LoginService;
use think\Db;
class PointCardModel extends Model{
    protected $name = 'tab_point_card';
    protected $connection = 'db.main';
    protected $dateFormat = false;

    public static function self(){
        return new self();
    }

    public function extractPass($where, $page, $user_id = 0){
        $map =[
            'buy_id|sell_id' => $user_id,
            'order_sn' => $where['order_sn'],
            'status'   => 1
        ];
        $card_list = [];
        $list = $this->field('card_num,buy_id,password,extract')->where($map)->page($page)->select();
        if(!empty($list)){
            if($list[0]['extract'] == 0 && $list[0]['buy_id'] != 0 && $list[0]['buy_id'] == $user_id ){//未提取且为买家查看时
                $ret = LoginService::checkJyPass($where['password'], $user_id);
                if ($ret != 0) {
                    return $ret;
                } else {
                    $this->updateExtractStatus($where['order_sn']);
                }
            } else if ($list[0]['extract'] == 0 && $list[0]['buy_id'] != $user_id) {//未提取且为卖家查看时
                foreach($list as $val){
                    $card_num = CryptService::decrypt($val['card_num']);
                    $val['card_num'] = FormatService::ShieldInfo($card_num,'card_num');
                    $password = CryptService::decrypt($val['password']);
                    $val['password'] = FormatService::ShieldInfo($password,'card_password');
                    unset($val['extract']);
                    $card_list[] = $val;
                }
                return $card_list;
            }
        }
        foreach($list as $val){
            $val['card_num'] = CryptService::decrypt($val['card_num']);
            $val['password'] = CryptService::decrypt($val['password']);
            unset($val['extract']);
            $card_list[] = $val;
        }
        return $card_list;
    }

    /*
     * 独立出来,用于密码加密
     */
    public function formatJsonToArray($json){
        $temp = json_decode($json,true);
        if(is_null($temp)){
            return 400;
        }
        $card_list = [];
        foreach($temp as $val){
            if(!isset($val['card_num'])||!isset($val['password'])){
                return 400;
            }
            if($val['password']==''){
                return 901300;
            }
            $val['card_num'] = CryptService::encrypt($val['card_num']);
            $val['password'] = CryptService::encrypt($val['password']);
            $card_list[]= $val;
        }
        return $card_list;
    }

    protected function updateExtractStatus($order_sn){
        $this->startTrans();
        try {
            $this->where(['order_sn'=>$order_sn])->setField('extract',1);
            $msg_model = MessageModel::self();
            $order_model = OrderModel::self();
            $where['order_sn'] = $order_sn;
            $order = $order_model->getOrderByCondition($where);
            $msg_model->sendOrderMessage($order['id'], 'extractpass',$order);
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            throw Exception($e);
        }
    }
    /*
     * 点卡json数据转为数组要独立出来,方便给加密
     */
    public function addPointCardGoods($data,$pic,$ext){
        $now = time();
        $data['create_time'] = $now;
        $data['state']       = 1;  //审核 0:未审核 1:在售中 2:审核失败
        $data['order_number']= 'GD_'.gen_uniqSN();
        $goodsmodel = GoodsModel::self();
        $pic_model = GoodsPicModel::self();
        $this->startTrans();
        try{
            if(isset($ext['send_type'])&&isset($ext['json'])){
                $list = $this->formatJsonToArray($ext['json']);
                if(is_numeric($list)){
                    return [$list,''];
                }
                $data['stock'] = count($list);
                $goods_id = $goodsmodel->strict(false)->insertGetId($data);
                $card_list = [];
                $status = 0;
                foreach($list as $val){
                    $val['goods_id'] = $goods_id;
                    $val['sell_id'] = $data['user_id'];
                    $val['status'] = $status;
                    $card_list[]=$val;
                }
                $this->insertAll($card_list);
            }else{
                $goods_id = $goodsmodel->strict(false)->insertGetId($data);
            }
            $pic_model->addGoodsPicByGoodsId($goods_id, $pic);
            $this->commit();
            /*CommService::setGoodsSearch('add',$goods_id);*/
        } catch (Exception $e) {
            $this->rollback();
            throw Exception($e);
        }
        return [0,$goods_id];
    }

    public function updatePointCard($goods,$param){
        $this->startTrans();
        try{
            $data = [
                'old_price' =>  $param['price'],
                'price'     =>  $param['price'],
                'title'     =>  $param['title'],
                'text'      =>  $param['text'],
                'ext_attr'  =>  json_encode(['send_type'=>$param['send_type']]),
                'pic_url'   =>  $param['pic'][0],
                'stock'     =>  $param['stock'],
                'state'     =>  1
            ];
            $temp = json_decode($goods['ext_attr'],true);
            $o_send_type = $temp['send_type'];
            $n_send_type = $param['send_type'];
            if( $o_send_type==1 && $n_send_type==2 ){//如果是手动发卡转为自动发卡,必须上传卡密和卡号

                if( !isset($param['list']) || $param['list']=='')
                {
                    return 901300;
                }
                $card_list = $this->cardJsonToAutoCardList($goods['id'], $goods['user_id'],$param['list']);
                if( is_numeric($card_list) )
                {
                    return $card_list;
                }
                $data['stock'] = count($card_list);//库存由这边控制
                $this->insertAll($card_list);
            } elseif( $o_send_type==2 && $n_send_type==2 ){//如果是自动发卡,要添加库存,在原有的库存上加

                if( isset($param['list']) && $param['list']!='' ){
                    $card_list = $this->cardJsonToAutoCardList($goods['id'], $goods['user_id'], $param['list']);
                    if( is_numeric($card_list) ){
                        return $card_list;
                    }
                    $data['stock'] = count($card_list) + $goods['stock'];//非空的情况下叠加
                    if ($data['stock'] > 100) {
                        return 907001;
                    }
                    $this->insertAll($card_list);
                }else{
                    $data['stock'] = $goods['stock'];//空的话不让修改库存
                }
            } elseif( $o_send_type==2 && $n_send_type==1 ){//如果是自动改为手动
                $this->deleteAutoPointCard($goods['id'], $goods['user_id'], 0);
            }
            $gm = GoodsModel::self();
            $pic_model = GoodsPicModel::self();
            $gm->where('id',$goods['id'])->update($data);
            $pic_model->updateGoodsPicByGoodsId($goods['id'],$param['pic']);
            $this->commit();
            return 0;
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }

    public function deleteAutoPointCard($goods_id,$sell_id,$status=0){
        $this->where(['goods_id'=>$goods_id,'sell_id'=>$sell_id,'status'=>$status])->delete();
    }
    /*
     * 手动发卡
     */
    public function manualSendCard($order,$json){
        $send_type = json_decode($order['ext_attr'],true);
        if($send_type['send_type']!=1){
            return 400;
        }
        $list = $this->formatJsonToArray($json);
        if(is_numeric($list)){
            return $list;
        }
        if(count($list) < $order['num']){
            return 901306;
        }
        $status = 1;
        foreach($list as $val){
            $val['goods_id'] = $order['goods_id'];
            $val['order_sn'] = $order['order_sn'];
            $val['buy_id'] = $order['buy_id'];
            $val['sell_id'] = $order['sell_id'];
            $val['status'] = $status;
            $add[] = $val;
        }
        $this->startTrans();
        try{
            $this->insertAll($add);
            $om = OrderModel::self();
            $om->orderMove($order['id'],ORDER_RECIEVE);
            $this->commit();
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
        return 0;
    }

    /*
     * 自动发卡 卡号卡密json转为数组
     */
    protected function cardJsonToAutoCardList($goods_id,$sell_id,$json){
        $list = $this->formatJsonToArray($json);
        if(is_numeric($list)){
            return $list;
        }
        $status = 0;
        foreach($list as $val){
            $val['sell_id'] = $sell_id;
            $val['goods_id'] = $goods_id;
            $val['status'] = $status;
            $card_list[]=$val;
        }
        return $card_list;
    }

    /*
     * 自动发卡
     */
    public function autoSendCard($order){
        $status = 1;
        $send = [
            'order_sn'=> $order['order_sn'],
            'buy_id'  => $order['buy_id'],
            'status'  => $status,
        ];
        $this->where(['goods_id'=>$order['goods_id'],'sell_id'=>$order['sell_id'],'status'=>0])
             ->limit($order['num'])
             ->order('id asc')
             ->update($send);
    }
}