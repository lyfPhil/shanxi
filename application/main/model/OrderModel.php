<?php
namespace app\main\model;

use app\main\model\PayModel;
use app\main\model\MessageModel;
use app\main\model\GoodsModel;
use app\main\model\CustomerModel;
use app\main\model\FinanceModel;
use app\main\model\PointCardModel;
use app\main\model\RecordModel;
use app\main\model\ConfigModel;
use app\main\model\VipUserModel;
use think\Cache;
use think\Model;
use think\Db;
class OrderModel extends Model{
    protected $name = "tab_order";
    protected $connection = 'db.main';
    protected $dateFormat = false;//这个关闭自动转时间戳

    public static function self(){
        return new self();
    }

    public function  getOrderByCondition($where,$field='*'){
        return $this->field($field)->where($where)->order('id desc')->find();
    }

    public function getOrder($where,$page,$device_type)
    {
        if ($where['type']==0) {
            $map['sell_id'] = $where['user_id'];
            $order_status = [
                ORDER_CANCEL,ORDER_PAY,ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,
                ORDER_APPEAL,ORDER_CLOSE,ORDER_COMPLETE,ORDER_INTERVENE,ORDER_APPEAL_CLOSE
            ];
            $map['state']   = ['in',$order_status];
            $map['status']  = ['not in',[0,10]];
        } else {
            $order_status = [
                ORDER_CANCEL,ORDER_PAY,ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,
                ORDER_APPEAL,ORDER_CLOSE,ORDER_COMPLETE,ORDER_INTERVENE,ORDER_APPEAL_CLOSE
            ];
            $map['buy_id']  = $where['user_id'];
            $map['state']   = ['in',$order_status];
            $map['status']  = ['not in',[0,11]];
        }
        if (isset($where['state'])) {
            if ($device_type != 'web') {
                if (in_array($where['state'],[ORDER_CANCEL,ORDER_APPEAL,ORDER_CLOSE,ORDER_INTERVENE,ORDER_APPEAL_CLOSE])) {
                    $map['state'] = ['in',[ORDER_CANCEL,ORDER_APPEAL,ORDER_CLOSE,ORDER_INTERVENE,ORDER_APPEAL_CLOSE]];
                } else {
                   $map['state'] = $where['state'];
                }
            } else {
                $map['state'] = ['in',$where['state']];
            }
            unset($where['state']);
        }
        if (isset($where['goods_num'])) {
            $goods_model = GoodsModel::self();
            $condition['order_number'] = ['like', '%'.$where['goods_num'].'%'];
            $condition['user_id'] = $where['user_id'];
            $goods_id = $goods_model->where($condition)->column('id');
            $where['goods_id'] = ['in', $goods_id];
            unset($where['goods_num']);
        }
        unset($where['user_id']);
        unset($where['type']);
        $cnt  = $this->where($map)->where($where)->count();
        $this->field('id,order_sn,state,title,game_id,goods_id,price,goods_type,num,total,actual,sell_id,sell_name,buy_id,buy_name,server_name, create_time');
        $order  = $this->where($map)->where($where)->page($page)->order('create_time desc')->select();
        return [$order,$cnt];
    }
    /*
     * 线上支付，不走ordermove,走这个
     * @param $data=['order_id'=>  'type'=> ]需要这两个参数
     */
    public function orderPay($data){
        $order = $this->where('id',$data['order_id'])->find();
        //事务
        //$data['type'] = FormatService::formatPayTypeToNo($data['type']);处理类型
        $add_data = [
            'user_id'   =>$order['buy_id'],
            'user_name' =>$order['buy_name'],
            'cash'      =>$order['total'],
            'cap_type'  =>2,
            'pay_type'  =>3,//$data['type'],
            'bal_pay_type'=>0,
            'text'      =>'订单号：'.$order['order_sn']
        ];
        FinanceModel::self()->addFinance($data);
        OrderModel::self()-where('id',$data['order_id'])->setField(['state'=>2,'pay_type'=>$data['type']]);
        MessageModel::self()->sellMessage($order['id'],ORDER_SEND,$time);
    }
    /*
     * 余额支付
     */
    public function orderPayByBalance($order_id,$now){
        $order = $this->where('id',$order_id)->find();
        $this->startTrans();
        try{
            if ($order['state']==1) {
                $add_data = [
                    'user_id'   =>$order['buy_id'],
                    'user_name' =>$order['buy_name'],
                    'cash'      =>$order['actual'],
                    'cap_type'  =>FINANCE_PAY,
                    'pay_type'  =>0,
                    'bal_pay_type'=>0,
                    'lang' => 'finance_pay',
                    'remark' => json_encode(['pay_type' => 0, 'id' => $order['order_sn']])
                ];

                $fm = FinanceModel::self();
                $cm = CustomerModel::self();
                $pm = PayModel::self();

                $fm->addFinance($add_data);
                $cm->where('id',$order['buy_id'])->setDec('balance',$order['actual']);
                $pm->where('order_sn',$order['order_sn'].'_'.$order_id)->setField(['state'=>2]);
                $mm = MessageModel::self();
                $key= 'exist_'.$order['buy_id'].$order['goods_id'].$order['num'];
                Cache::rm($key);
                $pay_type = 0;
                if ($order['goods_type']==5) {
                    $temp = json_decode($order['ext_attr'],true);//自动发卡
                    if ($temp['send_type']==2) {
                        $point_model = PointCardModel::self();
                        $point_model->autoSendCard($order);
                        $mm->sendOrderMessage($order_id, 'autosendcard', $order);
                        $this->commit();
                        return [$pay_type,3];
                    }
                }
                $mm->sendOrderMessage($order_id,'payed',$order);
                $this->commit();
                return  [$pay_type,2];
            } else {
                MessageModel::self()->sendOrderMessage($order_id,'disagreeclose',$order);
                $this->commit();
                return [-1,2];
            }
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }

    public  function orderConfirm($order_id,$now){ //确认收货
        $this->startTrans();
        try{
            $order        = $this->where('id',$order_id)->find();
            $config_model = ConfigModel::self();
            $config       = $config_model->getValue('transaction');
            $trans        = json_decode($config,true);
            $add_income   = [
                'user_id'   =>$order['sell_id'],
                'user_name' =>$order['sell_name'],
                'cap_type'  =>FINANCE_INCOME,
                'pay_type'  =>$order['pay_type'],
                'bal_pay_type'=>1,
                'lang' => 'finance_income',
            ];
            $vuser_model = VipUserModel::self();
            $vip = $vuser_model->getOneUserVip($order['sell_id'], 1);
            if ($vip) {
                $trans['precent'] = $vip['precent'];
                $trans['base'] = $vip['base'];
            }
            $free = number_format($order['actual']*$trans['precent']+$trans['base'], 2);
            $add_income['cash']   = $order['actual']-$free;
            $add_income['charge'] = $free;
            $add_income['remark'] = json_encode(['free' => $free, 'id' => $order['order_sn']]);
            $fm = FinanceModel::self();
            $gm = GoodsModel::self();
            $cm = CustomerModel::self();
            $mm = MessageModel::self();
            $re = RecordModel::self();
            //增加流水记录
            $fm->addFinance($add_income);
            //商品销量增加
            $gm->where('id',$order['goods_id'])->setInc('paid_cnt',$order['num']);
            // 卖家余额增加 订单数量增加
            $seller = $cm->where('id',$order['sell_id'])->find();
            $up_seller['balance']   = $seller['balance'] + $add_income['cash'];
            $up_seller['order_num'] = $seller['order_num'] + 1;
            $cm->updateJyUser($order['sell_id'],$up_seller);
            // 消息通知 通知卖家买家已收货，钱到账
            $mm->sendOrderMessage($order_id,'confirm',$order);
            //记录已经卖出商品
            $pic = $gm->getOneGoodsByCondition($order['goods_id'], 'pic_url');
            $record['record'] = [
                'id'    => $order['goods_id'],
                'title' => $order['title'],
                'pic_url' => $pic,
                'goods_type'  => $order['goods_type'],
                'server_name' => $order['server_name']
            ];
            $record['user_id'] = $order['sell_id'];
            $record['type'] = 4;
            $re->addRecord($record);
            $this->commit();
            return $free;
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }

    public  function orderCancel($order_id,$now,$user_id=''){ //取消订单
        $data=$this->field(true)->where('id',$order_id)->find();
        $this->startTrans();
        try{
            $fm = FinanceModel::self();
            $gm = GoodsModel::self();
            $cm = CustomerModel::self();
            $mm = MessageModel::self();
            $gm->where('id',$data['goods_id'])->setInc('stock',$data['num']);//增加库存
            if (in_array($data['state'],[2,6,7])) {     //如果用户已经付款、退款到余额
                $add_data=[
                    'user_id'   =>$data['buy_id'],
                    'user_name' =>$data['buy_name'],
                    'cash'      =>$data['actual'],
                    'cap_type'  =>FINANCE_REFUND,
                    'pay_type'  =>$data['pay_type'],
                    'bal_pay_type'=>1,
                    'lang' => 'finance_refund',
                    'remark' => json_encode(['id' => $data['order_sn']])
                ];
                $fm->addFinance($add_data);// 增加流水记录
                $cm->where('id',$data['buy_id'])->setInc('balance',$add_data['cash']);// 买家余额增加
            }
            $mm->sendOrderMessage($order_id,'close',$data,$user_id);//订单取消
            $this->commit();
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }

    public function orderMove($order_id, $state,$user_id='')
    {
        try{
            $config_model = ConfigModel::self();
            $message      = MessageModel::self();
            $now          = time();
            $array        = ['state'=>$state];
            $config       = $config_model->getValue('order_expire_time');
            $expire       = json_decode($config,true);
            switch($state){
                case '2': //付款
                    $array['pay_time']  = $now;//已经付款时间
                    list($pay_type,$state) = $this->orderPayByBalance($order_id,$now);
                    if ($pay_type!=-1) {
                        $array['pay_type'] = $pay_type;
                    }
                    $array['state'] = $state;
                    break;
                case '3': //发货
                    $array['ship_time'] = $now;
                    $array['over_time'] = $now+$expire['confirm_expire'];//确认收货过期时间
                    $message->sendOrderMessage($order_id,'send');
                    break;
                case '4': //确认收货 更新订单状态，增加卖家余额，生成一条流水记录,买卖家信用增长
                    $array['deal_time'] = $now;
                    $array['over_time'] = $now+$expire['comment_expire'];//7天内自动好评
                    $array['charge'] = $this->orderConfirm($order_id,$now);
                    break;
                case '5':
                    $message->sendOrderMessage($order_id,'comment');
                    break;
                case '7'://交易关闭
                    $array['over_time'] = $now+$expire['cancel_expire'];//3天内卖家不处理，
                    $message->sendOrderMessage($order_id,'commitclose');
                    break;
                case '6'://申诉
                    $array['over_time'] = $now+$expire['appeal_expire'];//2天内卖家不处理同意申诉
                    $message->sendOrderMessage($order_id,'commitappeal');
                    break;
                case '10'://客服介入
                    $message->sendOrderMessage($order_id,'disagreeappeal');
                break;
                case 0: //取消订单
                case 11:
                    $this->orderCancel($order_id,$now,$user_id);
                    break;
                default:
                    break;
            }
            $this->where('id',$order_id)->setField($array);
            $this->commit();
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
        return 0;
    }

    public function orderSave($data,$flash)
    {
        $gm         = GoodsModel::self();
        $goods_id   = $data['goods_id'];
        $goods      = $gm->where('id', $goods_id)->field('stock')->find();
        if ($goods['stock'] < $data['num']) {
            return -1;
        }
        $this->startTrans();
        try{
            if ($gm->where('id', $goods_id)->setDec('stock', $data['num']))
            {
                $config_model       = ConfigModel::self();
                $config             = $config_model->getValue('order_expire_time');
                $expire             = json_decode($config,true);
                $now                = time();
                $data['order_sn']   = gen_uniqSN();
                $flash['order_sn']  = $data['order_sn'];
                $flash['create_time']= $now;
                $data['flash_id']   = Db::name('tab_goods_flash')->insertGetId($flash);
                $data['state']      = 1;
                $data['create_time']= $now;
                $data['over_time']  = $now + $expire['pay_expire'];//内网半小时1800
                $order_id = $this->insertGetId($data);
                if ($goods['stock'] - $data['num']==0) {
                    $gm->where('id',$goods_id)->setField('state',3);
                }
                MessageModel::self()->sendOrderMessage($order_id,'create');
                $this->commit();
                return [$order_id,$data['order_sn']];
            }
        } catch(Exception $e) {
            $this->rollback();
            throw Exception($e);
        }
    }
    public function getLatesDeals($where){
        $lists = $this->field('id,title,actual,buy_id,buy_name,goods_type,server_name,deal_time')
             ->where('state',4)
             ->order('deal_time desc')
             ->limit($where['size'])
             ->select();
        return $lists;
    }

    public function getLatestDealsByGameType($where){
        $goods = GoodsModel::self()->field('id')
               ->where(['status'=>1,'state'=>1])
               ->where('game_type',$where['game_type'])
               ->select();
        $goods_id = array_column($goods,'id');
        $cnt = $this->field('id,goods_id')->where('state',4)->where(['goods_id'=>['in',$goods_id]])->count();
        $lists = $this->field('id,title,actual,buy_id,buy_name,goods_type,server_name,deal_time')
                ->where('state',4)
                ->where(['goods_id'=>['in',$goods_id]])
                ->order('deal_time desc')
                ->limit($where['size'])
                ->select();
        return $lists;
    }

    public function getLatestDealsByGoodsType($where){
        $cnt = $this->field('id,goods_type')->where('state',4)->where('goods_type',$where['goods_type'])->count();
        $lists = $this->field('id,title,actual,buy_id,buy_name,goods_type,server_name,deal_time')
             ->where('state',4)
             ->where('goods_type',$where['goods_type'])
             ->order('deal_time desc')
             ->limit($where['size'])
             ->select();
        return $lists;
    }
    /**
     * 获取订单列表 后台使用
     * @param array $map 筛选条件
     * @param string $order 排序条件
     * @param int $limit 分页大小
     * @param array $params 额外参数
     * @return object
     */
    public function getOrderList($map,$order,$limit,$params=[])
    {
        $ret = $this->field('*')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$params]);
        return $ret;
    }

    public function expireOrderDeal($state){
        $order = $this->field('id,state,title,goods_id,order_sn,sell_id,buy_id,sell_name,buy_name,num,actual')
                ->where('state',$state)
                ->where(['over_time'=>['<',time()]])
                ->select();
        return $order;
    }
    //统计交易商品数,后台使用
    public function status($time)
    {
        $map['create_time'] = array('between',array($time-24*3600,$time));
        return [
            'title'=>'交易成功商品数',
            'total'=>$this->where('state','in',[ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->count(),//商品交易成功总数
            'newly'=>$this->where('create_time', '>', $time)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->count(),//今日新增交易商品总数
            'oldly'=>$this->where($map)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->count()//昨天交易商品总数
        ];
    }
    //统计商品交易金额数,后台使用
    public function orderFinanceStatus($time)
    {
        //交易成功商品总金额数
        $data = $this->field('actual')->where('state','in',[ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->select();
        $total_num = 0;
        foreach ($data as $item)
        {
            $total_num += $item['actual'];
        }
        //今日交易商品金额，已付款的
        $today = $this->field('actual')->where('create_time', '>', $time)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->select();
        $today_num = 0;
        foreach ($today as $item)
        {
            $today_num += $item['actual'];
        }
        //昨天交易商品金额
        $map['create_time'] = array('between',array($time-24*3600,$time));
        $yesterday = $this->field('actual')->where($map)->where('state','in',[ORDER_SEND,ORDER_RECIEVE,ORDER_SUCCESS,ORDER_REPLY,ORDER_COMPLETE])->select();
        $yesterday_num = 0;
        foreach ($yesterday as $item)
        {
            $yesterday_num += $item['actual'];
        }
        return [
            'title'=>'商品交易成功金额数',
            'total'=>$total_num,//商品交易成功总数
            'newly'=>$today_num,//今日新增交易商品总数
            'oldly'=>$yesterday_num,//昨天交易商品总数
        ];
    }
}