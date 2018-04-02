<?php
namespace app\tasks\controller;

use app\common\controller\CliBase;
use app\main\model\OrderModel;
use app\main\model\MessageModel;
use app\main\model\ArgueModel;
use app\main\model\GoodsModel;
use app\main\model\FinanceModel;
use app\main\model\CustomerModel;
use app\main\model\CommentModel;
use app\main\model\RechargeModel;
use app\main\model\ConfigModel;
use app\main\model\VipUserModel;
use app\v1\service\FormatService;
use think\Env;
use think\Db;
class Autodeal extends CliBase
{
    /*
     * 到期未支付订单
     * 更改订单状态，发送站内消息,订单处理结果,增加商品库存
    */
    public function NotPayOrder()
    {
        $om = OrderModel::self();
        $data = $om->expireOrderDeal(ORDER_PAY);
        if(count($data) > 0){
            $buy        = [];
            $sell       = [];
            $message    = [];
            $argue      = [];
            $argue_list = [];
            $now        = time();
            $goods_id = array_column($data, 'goods_id');
            $goods_model = GoodsModel::self();
            $goods = $goods_model->where(['id' => ['in',$goods_id]])->column('pic_url', 'id');
            foreach($data as $val){
                $more = [
                    'goods_title' => $goods[$val['goods_id']],
                    'pic' => $goods[$val['goods_id']]
                ];
                //卖家一方收到的消息
                $sell['title']      = 'auto deal not pay seller title';
                $sell['create_time']= $now;
                $sell['user_id']       = $val['sell_id'];
                $sell['to_user_id'] = $val['buy_id'];
                $sell['user_type'] = 0;
                //买家一方收到的消息
                $buy['title']       = 'auto deal not pay buyer title';
                $buy['create_time'] = $now;
                $buy['user_id']        = $val['buy_id'];
                $buy['to_user_id'] = $val['sell_id'];
                $buy['user_type'] = 1;
                $sell['more'] = $buy['more'] = json_encode($more);
                //取消交易处理的结果
                $argue['order_sn']      = $val['order_sn'];
                $argue['order_state']   = ORDER_CANCEL;
                $argue['user_type']     = 0;
                $argue['create_time']   = $now;
                $argue['reason']        = 'auto deal not pay reason';//"30分钟内未付款";
                $argue['result']        = 'auto deal not pay result';//"买家{$val['buy_name']}30分钟内未付款，系统默认取消订单";
                $message[]    = $buy;
                $message[]    = $sell;
                $argue_list[] =$argue;
            }
            $om->startTrans();
            try{
                $ret = $om->field('id,state,order_sn,over_time')
                          ->where('state',ORDER_PAY)
                          ->where(['over_time'=>['<',time()]])
                          ->setField('state',ORDER_CANCEL);
                $goods = GoodsModel::self();
                foreach($data as $val){//为了防止发生错误，故一个一个加
                    $goods->field('id,stock')->where('id',$val['goods_id'])->setInc('stock',$val['num']);
                }
                MessageModel::self()->send_message($message);
                ArgueModel::self()->insertAll($argue_list);
                $om->commit();
            } catch (Exception $ex) {
                $om->rollback();
                throw Exception($ex);
            }
        }
    }
    /*
     * 到期卖家未处理取消交易,系统默认卖家同意
     * 更改订单状态，发送站内消息，订单处理结果,退款到买家余额,记流水帐，商品库存+
     */
    public function NotCancelOrder()
    {
        $om = OrderModel::self();
        $data = $om->expireOrderDeal(ORDER_CLOSE);
        if(count($data)>0){
            $buy        = [];
            $sell       = [];
            $message    = [];
            $argue      = [];
            $argue_list = [];
            $finance    = [];
            $finance_list = [];
            $now        = time();
            $goods_id = array_column($data, 'goods_id');
            $goods_model = GoodsModel::self();
            $goods = $goods_model->where(['id' => ['in',$goods_id]])->column('pic_url', 'id');
            foreach($data as $val){
                $more = [
                    'goods_title' => $goods[$val['goods_id']],
                    'pic' => $goods[$val['goods_id']]
                ];
                //卖家一方收到的消息
                $sell['title']      = 'auto deal not cancel seller title';//"订单号:{$val['order_sn']}已自动取消";
                $sell['create_time']= $now;
                $sell['user_id']    = $val['sell_id'];
                $sell['to_user_id'] = $val['buy_id'];
                $sell['user_type'] = 0;
                //买家一方收到的消息
                $buy['title']       = 'auto deal not cancel buyer title';//"订单号:{$val['order_sn']}已自动取消";
                $buy['create_time'] = $now;
                $buy['user_id']    = $val['buy_id'];
                $buy['to_user_id'] = $val['sell_id'];
                $buy['user_type'] = 1;
                $sell['object_id'] = $buy['object_id'] = $val['id'];
                $sell['more'] = $buy['more'] = json_encode($more);
                //取消交易处理的结果
                $argue['order_sn']      = $val['order_sn'];
                $argue['order_state']   = ORDER_CANCEL;
                $argue['user_type']     = 0;
                $argue['create_time']   = $now;
                $argue['reason']        = 'auto deal not cancel reason';//"系统默认取消订单"
                $argue['result']        = 'auto deal not cancel result';//"卖家{$val['sell_name']}在三天无应答，系统默认取消订单";
                //流水帐
                $finance['order_sn']    = 'JY_'.gen_uniqSN();
                $finance['cash']        = $val['actual'];
                $finance['state']       = 2;
                $finance['user_id']     = $val['buy_id'];
                $finance['user_name']   = $val['buy_name'];
                $finance['cap_type']    = FINANCE_REFUND;
                $finance['pay_type']    = 0;
                $finance['bal_pay_type']= 1;
                $finance['no'] = $val['order_sn'];
                $finance['create_time'] = $now;
                $finance['status']      = 1;
                unset($info);
                $message[]     = $buy;
                $message[]     = $sell;
                $argue_list[]  =$argue;
                $finance_list[]=$finance;
            }
            $om->startTrans();
            try{
                $om->field('id,state,order_sn,over_time')
                   ->where('state',ORDER_CLOSE)
                   ->where(['over_time'=>['<',$now]])
                   ->setField('state',ORDER_CANCEL);
                $gm = GoodsModel::self();
                $cm = CustomerModel::self();
                foreach($data as $val){
                    $gm->field('id,stock')->where('id',$val['goods_id'])->setInc('stock',$val['num']);//加商品库存
                    $cm->field('id,balance')->where('id',$val['buy_id'])->setInc('balance',$val['actual']);//加买家余额
                    //之后的版本可能会影响卖家的信誉
                }
                FinanceModel::self()->insertAll($finance_list);
                MessageModel::self()->send_message($message);
                ArgueModel::self()->insertAll($argue_list);
                $om->commit();
            } catch (Exception $ex) {
                $om->rollback();
                throw Exception($ex);
            }
        }

    }
    /*
     * 到期卖家未处理申诉
     * 订单状态，发送站内消息，客服介入处理
     */
    public function NotAppealOrder()
    {
        $om   = OrderModel::self();
        $data = $om->expireOrderDeal(ORDER_APPEAL);
        if(count($data)>0){
            $buy     = [];
            $sell    = [];
            $message = [];
            $argue   = [];
            $argue_list = [];
            $now = time();
            $goods_id = array_column($data, 'goods_id');
            $goods_model = GoodsModel::self();
            $goods = $goods_model->where(['id' => ['in',$goods_id]])->column('pic_url', 'id');
            foreach($data as $val){
                $more = [
                    'goods_title' => $goods[$val['goods_id']],
                    'pic' => $goods[$val['goods_id']]
                ];
                //卖家一方收到的消息
                $sell['title']      = 'auto deal not appeal seller title';
                $sell['create_time']= $now;
                $sell['user_id']       = $val['sell_id'];
                $sell['to_user_id'] = $val['buy_id'];
                $sell['user_type'] = 0;
                //买家一方收到的消息
                $buy['title']       = 'auto deal not appeal buyer title';
                $buy['create_time'] = $now;
                $buy['user_id']    = $val['buy_id'];
                $buy['to_user_id'] = $val['sell_id'];
                $buy['user_type'] = 1;

                $sell['object_id'] = $buy['object_id'] = $val['id'];
                $sell['more'] = $buy['more'] = json_encode($more);
                //取消交易处理的结果
                $argue['order_sn']   = $val['order_sn'];
                $argue['order_state']= ORDER_INTERVENE;
                $argue['user_type']  = 0;
                $argue['create_time']= $now;
                $argue['reason']     = 'auto deal not appeal reason';
                $argue['result']     = 'auto deal not appeal result';
                $message[]   = $buy;
                $message[]   = $sell;
                $argue_list[]=$argue;
            }
            $om->startTrans();
            try{
                $om->field('id,state,order_sn,over_time')
                   ->where('state',ORDER_APPEAL)
                   ->where(['over_time'=>['<',time()]])
                   ->setField('state',ORDER_INTERVENE);
                ArgueModel::self()->insertAll($argue_list);
                MessageModel::self()->send_message($message);
                $om->commit();
            } catch (Exception $ex) {
                $om->rollback();
                throw Exception($ex);
            }
        }
    }
    /*
     * 到期未确认收货订单
     * 发送站内消息,钱打到卖家余额,增加卖家订单数,增加流水帐
     */
    public function NotConfirmOrder()
    {
        $om = OrderModel::self();
        $data = $om->expireOrderDeal(ORDER_RECIEVE);
        $config_model = ConfigModel::self();
        $trans_config = $config_model->getValue('transaction');
        $expire_config = $config_model->getValue('order_expire_time');
        $trans = json_decode($trans_config, true);
        $expire = json_decode($expire_config, true);
        if(count($data)>0){
            $buy     = [];
            $sell    = [];
            $message = [];
            $finance = [];
            $finance_lists = [];
            $now     = time();
            $goods_id = array_column($data, 'goods_id');
            $goods_model = GoodsModel::self();
            $goods = $goods_model->where(['id' => ['in',$goods_id]])->column('pic_url', 'id');
            foreach($data as $val){
                $more = [
                    'goods_title' => $goods[$val['goods_id']],
                    'pic' => $goods[$val['goods_id']]
                ];
                //卖家一方收到的消息
                $sell['title']       = 'auto deal not confirm seller title';//"订单号:{$val['order_sn']}已确认收货";
                $sell['create_time'] = $now;
                $sell['user_id']        = $val['sell_id'];
                $sell['to_user_id'] = $val['buy_id'];
                $sell['user_type'] = 0;

                $buy['title']       = 'auto deal not confirm buyer title';//"订单号:{$val['order_sn']}已自动确认收货";
                $buy['create_time'] = $now;
                $buy['user_id']    = $val['buy_id'];
                $buy['to_user_id'] = $val['sell_id'];
                $buy['user_type'] = 1;

                $sell['object_id'] = $buy['object_id'] = $val['id'];
                $sell['more'] = $buy['more'] = json_encode($more);

                $vuser_model = VipUserModel::self();
                $vip = $vuser_model->getOneUserVip($val['sell_id'], 1);
                if ($vip) {
                    $trans['precent'] = $vip['precent'];
                    $trans['base'] = $vip['base'];
                }
                $free = round($val['actual']*$trans['precent']+$trans['base'],2);
                $finance['order_sn'] = 'JY_'.gen_uniqSN();
                $finance['cash']     = $val['actual'] - $free;
                $finance['charge']   = $free;
                $finance['state']    = 2;
                $finance['user_id']  = $val['sell_id'];
                $finance['user_name']= $val['sell_name'];
                $finance['cap_type'] = 3;
                $finance['pay_type'] = $val['pay_type'];
                $finance['bal_pay_type'] = 1;
                $finance['no']     = $val['order_sn'];
                $finance['remark'] = json_encode([number_format($free, 2, '.', ',')]);
                $finance['create_time'] = $now;
                $finance['status']      = 1;
                $message[]      = $buy;
                $message[]      = $sell;
                $finance_lists[]=$finance;
            }
            $om->startTrans();
            try{
                $om->field('id,state,order_sn,over_time')
                   ->where('state',ORDER_RECIEVE)
                   ->where(['over_time'=>['<',time()]])
                   ->setField(['state' => ORDER_SUCCESS, 'over_time' => time() + $expire['comment_expire']]);
                $cm = CustomerModel::self();
                foreach($data as $val){
                   $cm->where('id',$val['sell_id'])->setInc('balance',$val['actual'] - round($val['actual']*$trans['precent']+$trans['base'],2));//钱打到卖家余额
                   $cm->where('id',$val['sell_id'])->setInc('order_num',1);//卖家销量增加
                }
                MessageModel::self()->send_message($message);
                FinanceModel::self()->insertAll($finance_lists);
                $om->commit();
            } catch (Exception $ex) {
                $om->rollback();
                throw Exception($ex);
            }
        }
    }
    /*
     * 到期未评价订单
     * 自动好评，增加卖家信誉,不需要发站内消息
     */
    public function NotCommentOrder()
    {
        $om = OrderModel::self();
        $data = $om->expireOrderDeal(ORDER_SUCCESS);
        $start=[
                'des_start'=>5,
                'server_start'=>5,
                'speed_start'=>5
            ];
        $more       = json_encode($start);
        $content    = lang("auto comment");//"系统默认好评";
        $start_num  = 5;
        $now        = time();
        $comment    = [];
        $comment_lists = [];
        foreach($data as $val){
            $comment['order_id']    = $val['id'];
            $comment['parent_id']   = 0;
            $comment['more']        = $more;
            $comment['object_id']   = $val['goods_id'];
            $comment['content']     = $content;
            $comment['user_id']     = $val['buy_id'];
            $comment['nick_name']   = $val['buy_name'];
            $comment['to_user_id']  = $val['sell_id'];
            $comment['start']       = $start_num;
            $comment['table_type']  = 2;
            $comment['create_time'] = $now;
            $comment['status']      = 1;
            $comment_lists[]        = $comment;
        }
        $om->startTrans();
        try{
            $om->field('id,state,order_sn,over_time')
                   ->where('state',ORDER_SUCCESS)
                   ->where(['over_time'=>['<',time()]])
                   ->setField('state',ORDER_REPLY);
            $cm = CustomerModel::self();
            $com = CommentModel::self();
            $gm = GoodsModel::self();
            foreach($data as $val){
                $total = $com->where(['to_user_id'=>$val['sell_id'],'table_type'=>2,'parent_id'=>0])->count();
                $seller = $cm->getOneUserInfo($val['sell_id'],'start_rating,seller_reputation');
                $cnt = $com->where(['object_id'=>$val['goods_id'],'parent_id'=>0,'table_type'=>2])->count();
                $goods = $gm->where(['id'=>$val['goods_id']])->field('start_rating')->find();
                $up_goods['start_rating'] = ($goods['start_rating']*$cnt+5)/($cnt+1);//商品星级
                $update['seller_reputation'] = $seller['seller_reputation'] + 1;//卖家信誉加1
                $update['start_rating'] = ($seller['start_rating']*$total+5)/($total+1);//卖家星级评分
                $gm->where('id',$val['goods_id'])->update($up_goods);
                $cm->where('id',$val['sell_id'])->update($update);
            }
            $com->insertAll($comment_lists);
            $om->commit();
        } catch (Exception $ex) {
            $om->rollback();
            throw Exception($ex);
        }
    }

    public function ExpireRecharge(){
        $recharge_model = RechargeModel::self();
        $now            = time();
        $recharge       = $recharge_model->where(['over_time'=>['<',$now],'state'=>0])->select();
        $cancel         = 'auto cancel expire recharge';
        $message_list   = [];
        foreach($recharge as $val){
            $message['user_id']  = $val['user_id'];
            $more = [
                'charge_no' => $val['charge_no'],
                'recharge_type' => $val['pay_type'],
            ];
            $message['title'] = 'auto cancel expire recharge send message title';
            $message['create_time'] = $now;
            $message['type'] = 3;
            $message['more'] = json_encode($more);
            $message_list[]   = $message;
        }
        $message_model = MessageModel::self();
        $recharge_model->startTrans();
        try{
            $recharge_model->where(['over_time'=>['<',$now],'state'=>0])->setField(['state'=>2,'cancel'=>$cancel]);
            $message_model ->send_message($message_list);
            $recharge_model->commit();
        } catch (Exception $ex) {
            $recharge_model->rollback();
            throw Exception($ex);
        }
    }
}
