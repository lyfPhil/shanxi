<?php
namespace app\main\model;

use app\main\model\OrderModel;
use app\main\model\GoodsModel;
use app\common\service\QueueService;
use app\v1\service\FormatService;
use app\common\service\CryptService;
use think\Model;
use think\Env;
class MessageModel extends Model
{
    protected $name = 'tab_mail';
    protected $connection = 'db.main';
    protected $dateFormat = false;

    public static function self(){
        return new self();
    }
    protected function send_im($message){
        $pop_up = isset($message['pop_up'])? $message['pop_up'] : 0;
        $voice = isset($message['voice']) ? $message['voice'] : 0;
        $temp = ['t' => 0,'title'=>lang($message['title']),'pop_up'=>$pop_up, 'voice' => $voice];
        $args = [
            'action'  => 'SENDCUSTOM',
            'from_id' => 44985,
            'to_id'   => $message['user_id'],
            'desc'    => $message['title'],
            'data'    => json_encode($temp),
            'url' => ''
        ];
        $job = '\app\tasks\job\TimJob';
        QueueService::push($job, $args);
    }

    /**
     * getOneTransMsg 获取单个人的交易信息
     * @param  $where 条件
     * @param  $page  页数
     * @param  $field 字段
     * @return
     */
    public function getOneTransMsg($where, $page, $field = '*'){
        $message_list = $this->field($field)->where($where)->page($page)->order('id desc')->select();
        return $message_list;
    }

    public function send_message($message){
        if (count($message)==0) {
            return 'no message';
        } elseif(count($message)==count($message,1)) {
            $this->send_im($message);
            /*if (!isset($message['type'])) {
                $map['user_id'] = $message['user_id'];
                $map['object_id'] = $message['object_id'];
                $map['type'] = 1;
                $this->where($map)->setField('status', 0);
            }*/
            $ret = $this->strict(false)->insert($message);
            return 0;
        } else {
            foreach($message as $val){
                /*if (!isset($val['type']) ) {
                    $map['user_id'] = $val['user_id'];
                    $map['object_id'] = $val['object_id'];
                    $map['type'] = 1;
                    $this->where($map)->setField('status', 0);
                }*/
                $this->send_im($val);
            }
            $this->strict(false)->insertAll($message);
            return 0;
        }
    }

    public function depositMessage($user_id){
        $time = time();
        $message = [
            'user_id' => $user_id,
            'title'   => 'pay desposit title',
            'create_time' => $time,
            'status' =>1
        ];
        $this->send_im($message);
        $this->insert($message);
    }

    public function refundDepositMessage($user_id){
        $message = [
            'user_id'    => $user_id,
            'title'      => 'refund deposit title',
            'create_time'=> time(),
            'status'     => 1
        ];
        $this->send_im($message);
        $this->insert($message);
    }
    /**********************************订单消息****************************************/

    /**
     * sendOrderMessage 发送订单消息
     * @param  int    $order_id 订单id
     * @param  string $type     信息类型
     * @param  array  $order    订单信息
     * @param  string $user_id  用户id
     * @param  array  $extract  追加字段
     */
    public function sendOrderMessage($order_id, $type = '', $order = [], $user_id = '', $extract = []){
        $time = time();
        if ($order == []) {
           $order_model = OrderModel::self();
           $order       = $order_model->where('id',$order_id)->find();
        }
        $goods = GoodsModel::self()->where('id',$order['goods_id'])->field('pic_url')->find();
        $message_info = [
            'state'    => $order['state'],
            'order_sn' => $order['order_sn'],
            'title'    => $order['title'],
            'buy_id'   => $order['buy_id'],
            'sell_id'  => $order['sell_id'],
            'time'     => date('Y-m-d H:i:s',$time)
        ];
        switch($type){
            case 'create':
                $message = $this->orderCreate($message_info,$time);//创建订单
                $message['pop_up'] = 1;
            break;
            case 'payed':
                $message = $this->orderPayed($message_info,$time);//买家支付订单
                $message['pop_up'] = 1;
            break;
            case 'autosendcard':
                $message = $this->autoSendCard($message_info, $time);//支付后自动发卡
            break;
            case 'editprice':
                $message = $this->editPrice($message_info, $time);//卖家修改价格
                $message['pop_up'] = 1;
            break;
            case 'send':
                $message = $this->sellerSend($message_info, $time);//卖家发货
                $message['pop_up'] = 1;
            break;
            case 'confirm':
                $message = $this->buyerConfirm($message_info, $time);//买家确认收货
            break;
            case 'extractpass':
                $message = $this->extractPass($message_info, $time);//买家提取卡密
            break;
            case 'comment':
                $message = $this->buyerComment($message_info, $time);//买家评论
            break;
            case 'commitclose':
                $message = $this->buyerCommitClose($message_info, $time);//提交关闭交易请求
            break;
            case 'close':
                $message = $this->orderCloseMessage($message_info, $time, $user_id);//直接关闭交易
            break;
            case 'commitappeal':
                $message = $this->buyerCommitAppeal($message_info, $time);//买家提交申诉请求
            break;
            case 'disagreeappeal':
                $message = $this->sellerDisagreeAppeal($message_info, $time);//卖家拒绝申诉请求
            break;
            case 'disagreeclose':
                $message = $this->sellerDisagreeClose($message_info, $time);//卖家拒绝关闭交易
            break;
            case 'customerdealappeal':
                $message = $this->customerDealAppeal($message_info, $time, $extract);//客服处理申诉
            break;
        }
        $more = ['goods_title' => $order['title'], 'pic' => $goods['pic_url']];

        if (count($message) == count($message,1)) {
            $message['more'] = json_encode($more);
            $message['object_id']  = $order['id'];
            $message_list = $message;
        } else {
            foreach ($message as $val) {
                $val['object_id'] = $order['id'];
                $val['more'] = json_encode($more);
                $message_list[] = $val;
            }
        }
        $ret = $this->send_message($message_list);
        return $ret;
    }
    /*
     * 创建订单(通知卖家有人下单)
     */
    public function orderCreate($message_info,$time){
        $message['user_id'] = $message_info['sell_id'];
        $message['to_user_id'] = $message_info['buy_id'];
        $message['user_type'] = 0;
        $message['title'] = 'buy create order title';
        //$message['content']  = lang("buy create order content",$message_info);
        $message['create_time'] = $time;
        return $message;
    }
    /*
     * 订单支付通知卖家发货
     */
    public function orderPayed($message_info,$time){
        $message['user_id']    = $message_info['sell_id'];
        $message['to_user_id'] = $message_info['buy_id'];
        $message['user_type'] = 0;
        $message['title']   = 'remind the seller to ship title';//买家已经付款，提醒卖家发货
        //$message['content'] = lang("remind the seller to ship content",$message_info);
        $message['create_time'] = $time;
        return $message;
    }
    /*
     *自动发卡 买家收到的信息
     */
    public function autoSendCard($message_info, $time){
        $buy_message = [
            'user_id'    => $message_info['buy_id'],
            'to_user_id' => $message_info['sell_id'],
            'user_type' => 1,
            'title'   => 'payed autosendcard title',
            'create_time' => $time
        ];
        $sell_message = [
            'user_id'    => $message_info['sell_id'],
            'to_user_id' => $message_info['buy_id'],
            'user_type' => 0,
            'title'   => 'buyer payed autosendcard title',
            'create_time' => $time
        ];
        $message = [$buy_message, $sell_message];
        return $message;
    }
    /*
     * 订单修改价格
     */
    public function editPrice($message_info,$time){
        $message['user_id']    = $message_info['buy_id'];
        $message['to_user_id'] = $message_info['sell_id'];
        $message['user_type'] = 1;
        $message['title']   = 'seller change order price title';//卖家修改了价格
        $message['create_time'] = $time;
        return $message;
    }
    /*
     * 订单发货通知买家
     */
    public function sellerSend($message_info,$time){
        $message = [
            'user_id' => $message_info['buy_id'],
            'user_type' => 1,
            'to_user_id' => $message_info['sell_id'],
            'title' =>'remind buyer to recive title',
            'create_time' => $time,
        ];
        return $message;
    }
    /*
     * 买家确认收货
     */
    public function buyerConfirm($message_info,$time){
        $buy_message = [
            'user_id'   => $message_info['buy_id'],
            'user_type' => 1,
            'to_user_id' => $message_info['sell_id'],
            'title'     =>'remind buyer to comment title',
            'pop_up'    => 1,
            'create_time'=>$time,
        ];
        $sell_message = [
            'user_id' => $message_info['sell_id'],
            'user_type' => 0,
            'to_user_id' => $message_info['buy_id'],
            'title'  =>'buyer reciver order title',
            'pop_up' => 1,
            'create_time' => $time
        ];
        $message = [$buy_message,$sell_message];
        return $message;
    }
    /*
     *买家提取卡密卡号
     */
    public function extractPass($message_info, $time){
        $message = [
            'user_id'   => $message_info['sell_id'],
            'user_type' => 0,
            'to_user_id' => $message_info['buy_id'],
            'title'  => 'buyer ectractpass title',
            'create_time' => $time,
        ];
        return $message;
    }
    /*
     * 买家已经评论
     */
    public function buyerComment($message_info, $time){
        $message = [
            'user_id'   => $message_info['sell_id'],
            'user_type' => 0,
            'to_user_id' => $message_info['buy_id'],
            'title' =>'buyer comment order title',
            'create_time' =>$time,
        ];
        return $message;
    }
    /*
     * 买家提交取消交易申请
     */
    public function buyerCommitClose($message_info,$time){
        $buy_message = [
            'user_id'   => $message_info['buy_id'],
            'user_type' => 1,
            'to_user_id' => $message_info['sell_id'],
            'title' =>'cancel have commit title',//关闭交易请求已发送
            'create_time' =>$time,
        ];
        $sell_message = [
            'user_id'   =>  $message_info['sell_id'],
            'user_type' => 0,
            'to_user_id' => $message_info['buy_id'],
            'title'     => 'remind seller to deal cancel title',
            'create_time' => $time
        ];
        $message = [$buy_message,$sell_message];
        return $message;
    }
     /*
     * 卖家不同意取消交易
     */
    public function sellerDisagreeClose($message_info,$time){
        $buy_message = [
            'user_id' => $message_info['buy_id'],
            'user_type' => 1,
            'to_user_id' => $message_info['sell_id'],
            'title' => 'seller disagree cancle title',
            'create_time' =>$time
        ];
        $sell_message = [
            'user_id'        =>$message_info['sell_id'],
            'user_type' => 0,
            'to_user_id' => $message_info['buy_id'],
            'title'       =>'you disagree cancling order title',
            //'content'     =>lang("you disagree cancling order content",$message_info),
            'create_time' =>$time
        ];
        $message = [$buy_message,$sell_message];
        return $message;
    }
    /*
     * 买家提交申诉
     */
    public function buyerCommitAppeal($message_info,$time){
        $sell_message = [
            'user_id'   => $message_info['sell_id'],
            'user_type' => 0,
            'to_user_id' => $message_info['buy_id'],
            'title'     =>'remind seller to deal appeal title',
            'create_time'=>$time
        ];
        return $sell_message;
    }
    /*
     * 卖家不同意取消申诉
     */
    public function sellerDisagreeAppeal($message_info,$time){
        $buy_message = [
            'user_id' => $message_info['buy_id'],
            'title' => 'seller disagree appeal title',
            'user_type' => 1,
            'to_user_id' => $message_info['sell_id'],
            'create_time' =>$time,
        ];
        $sell_message = [
            'user_id' => $message_info['sell_id'],
            'title'  => 'you disagree appeal title',
            'user_type' => 0,
            'to_user_id' => $message_info['buy_id'],
            'create_time'=>$time
        ];
        $message = [$buy_message,$sell_message];
        return $message;
    }
    /*
     * 取消订单发送消息
     */
    public function orderCloseMessage($message_info,$time,$user_id=''){
        $buy_message['user_id']  = $message_info['buy_id'];
        $sell_message['user_id'] = $message_info['sell_id'];
        $sell_message['create_time'] = $time;
        $buy_message['create_time'] = $time;
        switch($message_info['state']){
            case 1:
                if ($user_id==$message_info['buy_id']) {
                    $sell_message['title']  = 'buyer cancel no pay order title';
                    $sell_message['user_type'] = 0;
                    $sell_message['to_user_id'] = $message_info['buy_id'];
                    return $sell_message;
                } elseif($user_id==$message_info['sell_id']) {
                    $buy_message['title']   = 'cancel no pay order by seller title';
                    $sell_message['title']  = 'seller cancel no pay order title';
                    $buy_message['user_type'] = 1;
                    $sell_message['user_type'] = 0;
                    $buy_message['to_user_id'] = $message_info['sell_id'];
                    $sell_message['to_user_id'] = $message_info['buy_id'];
                }
            break;
            case 2:
                $sell_message['title']   = 'you cancel payed order refund title';
                $buy_message['title']    = 'seller cancel payed order refund';//卖家取消已付款订单
                $buy_message['user_type'] = 1;
                $sell_message['user_type'] = 0;
                $buy_message['to_user_id'] = $message_info['sell_id'];
                $sell_message['to_user_id'] = $message_info['buy_id'];
            break;
            case 7:
                $sell_message['title']   = 'you agree cancel refund title';
                $buy_message['title']    = 'seller agree cancel order refund';//卖家同意取消订单
                $buy_message['user_type'] = 1;
                $sell_message['user_type'] = 0;
                $buy_message['to_user_id'] = $message_info['sell_id'];
                $sell_message['to_user_id'] = $message_info['buy_id'];
            break;
            case 6:
                $sell_message['title']   = 'you agree appeal refund title';
                $buy_message['title']    = 'seller agree appeal refund';//卖家同意申诉
                $buy_message['user_type'] = 1;
                $sell_message['user_type'] = 0;
                $buy_message['to_user_id'] = $message_info['sell_id'];
                $sell_message['to_user_id'] = $message_info['buy_id'];
            break;
        }
        $message = [$buy_message,$sell_message];
        return $message;
    }
    /**
     * 客服处理申诉
     */
    public function customerDealAppeal($message_info, $time, $extract) {
        $message_info = $message_info + $extract;
        $buy_message = [
            'user_id' => $message_info['buy_id'],
            'user_type' => 1,
            'to_user_id' => $message_info['sell_id'],
            'time'    => $time,
            'title'   => 'customer deal appeal title',
            'pop_up'  => 1
        ];
        $sell_message = [
            'user_id' => $message_info['sell_id'],
            'user_type' => 0,
            'to_user_id' => $message_info['buy_id'],
            'time'    => $time,
            'title'   => 'customer deal appeal title',
            'pop_up'  => 1
        ];
        $message = [$buy_message,$sell_message];
        return $message;
    }
    //充值成功消息通知
    public function rechargeSend($charge_no,$now,$param){
        $recharge = RechargeModel::self()->where('charge_no',$charge_no)->find();
        $info = [
            'charge_no' => $recharge['charge_no'],
            'recharge_type'  => $recharge['pay_type'],
            'cash'      => $recharge['cash'],
            'time'      => time()
        ];
        switch ($param['state'])
        {
            case 1:
                $message = [
                    'user_id' =>$recharge['user_id'],
                    'title' => 'recharge success',
                    'type' => 3,
                    'create_time' => $now,
                    'more' => json_encode($info),
                    'voice' => 1,
                    'pop_up' => 1
                ];
                break;
            case 2:
                $info['reason'] = $param['cancel'];
                $message = [
                    'user_id' => $recharge['user_id'],
                    'title' => 'recharge reject',
                    'type' => 3,
                    'create_time' => $now,
                    'more' => json_encode($info),
                ];
                break;
        }
        $this->strict(false)->insertGetId($message);
        $this->send_im($message);
    }
    //提現的消息通知
    public function withdrawSend($draw_no,$now,$param)
    {
        $withdraw = WithdrawModel::self()->where('draw_no',$draw_no)->find();
        $info = [
            'draw_no' => $withdraw['draw_no'],
            'cash'    => $withdraw['cash'],
            'time'    => $now
        ];
        switch ($param['state'])
        {
            case 1:
                $info['service_free']=$withdraw['service_free'];
                $info['actual_cash'] =$withdraw['actual_cash'];
                $bank_card= CryptService::decrypt($withdraw['bank_card']);
                $info['bank_card'] = FormatService::ShieldInfo($bank_card, 'bank');
                $message = [
                    'user_id'      =>$withdraw['user_id'],
                    'type' => 3,
                    'create_time'=>$now,
                ];
                if($withdraw['type'] == '0'){
                    $message['title'] = 'withdraw success';
                }
                break;
            case 2:
                $info['cancel'] = $param['cancel'];
                $message = [
                    'user_id'      =>$withdraw['user_id'],
                    'type' => 3,
                    'create_time'=>$now,
                ];
                if($withdraw['type'] == '0'){
                    $message['title'] = 'withdraw reject';
                }
                break;
        }
        $message['more'] = json_encode($info);
        $this->insertGetId($message);
        $this->send_im($message);
    }
    //卖家认证消息通知
    public function certificationSend($user_id,$now,$data)
    {
        $certification = CertificationModel::self()->where('user_id',$user_id)->find();
        $time  = $certification['create_time'];
        $info = ['time'=>$time];
        switch ($data['status'])
        {
            case 1:
                $message = [
                    'user_id'      =>$certification['user_id'],
                    'title'     =>'certification pass',
                    'type' => 3,
                    'create_time'=>$now,
                ];
                break;
            case 2:
                $info['reason'] = $data['reason'];
                $message = [
                    'user_id' =>$certification['user_id'],
                    'title' => 'certification no pass',
                    'type' => 3,
                    'create_time'=>$now,
                ];
                break;
        }
        $message['more'] = json_encode($info);
        $this->insertGetId($message);
        $this->send_im($message);
    }
}