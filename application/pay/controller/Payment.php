<?php
namespace app\pay\controller;

use app\common\controller\ApiBase;
use think\Validate;
use think\Config;
use app\user\service\LoginService;
use app\pay\service\PaymentService;
use app\main\model\PayModel;
use app\main\model\OrderModel;
use app\main\model\CustomerModel;
use app\user\model\UserModel;
use think\Cache;
class Payment  extends ApiBase{
    //返回支持的支付方式
    public function method()
    {
        if (!$this->is_login()) {
            return $this->wrong(400100);
        }
        $user = CustomerModel::self()->getOneUserInfo($this->userInfo['user_id'],'deal_password,idcard,balance');
        $pass_isset = $this->_PassIsset($user);
        $method = [];
        $default = [
            ['type'=>'default', 'name'=>lang('pay_default'),'icon'=>'', 'third'=>0,'cash'=>$user['balance'],],
        ];
        $res = [
            'pass_isset'   => $pass_isset,
            'qly_platform' => $default,
            'lists' => $method,
        ];
        return $this->response($res);
    }
    //返回支持的充值方式
    public function rechargeMethod(){
        if(!$this->is_login()) {
            return $this->wrong(400100);
        }
        $user = CustomerModel::self()->getOneUserInfo($this->userInfo['user_id'],'deal_password,idcard,balance');
        $device_type = $this->param['device_type'];
        $pass_isset = $this->_PassIsset($user);
        $method = [];
        $default = [
            ['type' => 0, 'name' => lang('outline bank recharge'),'icon' => RECHARGE_CARD, 'third' => 0],
        ];
        $res = [
            'pass_isset' => $pass_isset,
            'default' => $default,
            'lists' => $method,
        ];
        return $this->response($res);
    }
    /**
     * _PassIsset 检查是否有设置密码
     * @return $pass_isset int
     */
    private function _PassIsset($user){
        if ($user['deal_password']=='') {
            if ($this->userInfo['mobile']==''){
                $pass_isset = 1;//手机未认证 没有设置密码
            } elseif ($user['idcard']=='') {
                $pass_isset = 3;//身份证未认证 没有置密码
            } elseif ($user['idcard'] != '' && $this->userInfo['mobile'] != '') {
                $pass_isset = 4;//绑定手机、绑定身份证,但没设置密码
            }
        } else {
            $pass_isset = 0; //已设置交易密码
            $wrong = Cache::get('wrongpass_'.$this->userInfo['user_id']);
            if ($wrong >= 3) {
                $pass_isset = -1; //-1锁定
            }
        }
        return $pass_isset;
    }
    /**
     * createDefault 创建余额支付
     * @return [type] [description]
     */
    public function createDefault()
    {
        if(!$this->is_login()){
            return $this->wrong(400100);
        }
        $validate = new Validate([
            'order_id'  =>  'require',
            'order_sn'  =>  'require',
            'pay_type'  =>  'require',
        ]);
        $validate->message([
            'order_id.require'  =>  '400',
            'order_sn.require'  =>  '400',
            'pay_type.require'  =>  '400',
        ]);
        if(!$validate->check($this->param)){
            return $this->wrong($validate->getError());
        }
        $order_id = $this->param['order_id'];
        $user_id  = $this->userInfo['user_id'];
        $order_sn = $this->param['order_sn'];
        $pay_type = $this->param['pay_type'];
        $res = PaymentService::makeDefaultPay($user_id,$order_id, $pay_type, $order_sn);
        if(!is_array($res))
            return $this->wrong($res);
        $ret = [
            'goods_price'   =>$res['goods_price'],
            'account'       =>$res['amount'],
            'order_id'      =>$res['order_sn'],
            'pic_url'       =>$res['pic_url'],
            'desc'          =>$res['description']
        ];
        return $this->response($ret);
    }
    public function create()
    {
        if(!$this->is_login()){
            return $this->wrong(400100);
        }
        $validate = new Validate([
            'order_id'  =>  'require',
            'order_sn'  =>  'require',
            'pay_type'  =>  'require',
        ]);
        $validate->message([
            'order_id.require'  =>  '400',
            'order_sn.require'  =>  '400',
            'pay_type.require'  =>  '400',
        ]);
        if(!$validate->check($this->param)){
            return $this->wrong($validate->getError());
        }
        $order_id = $this->param['order_id'];
        $user_id = $this->userInfo['user_id'];
        $order_sn = $this->param['order_sn'];
        $pay_type = $this->param['pay_type'];
        $res = PaymentService::makePay($user_id, $order_id, $pay_type, $order_sn);

        if (!is_array($res))
        {
            return $this->wrong(400);
        }
        $ret = [
            'amount' => $res['amount'],
            'order_id'=> $res['order_sn'],
            'pay_url'=>$res['pay_url'],
            'transid'=>$res['transid'],
        ];
        return $this->response($ret);
    }
    /*
     * 余额支付
     */
    public function payByBalance(){
        if(!$this->is_login()){
            return $this->wrong(400100);
        }
        if(!$this->request->has('order_id')||!$this->request->has('password')){
            return $this->wrong(400);
        }
        $user_id = $this->userInfo['user_id'];
        $user = CustomerModel::self()->getOneUserInfo($user_id,'deal_password,balance');
        $password = $this->param['password'];
        $order_id = $this->param['order_id'];
        $is_check = LoginService::checkJyPass($password, $user_id);
        if($is_check != 0) {
            return $this->wrong($is_check['code'], $is_check['message']);
        }
        $id = explode('_',$order_id);
        $o = OrderModel::self();
        $order = $o->where(['id'=>$id[1],'buy_id'=>$user_id,'order_sn'=>$id[0]])->find();
        if (!$order||$order['state']!=1) {
            return $this->wrong(400);
        }
        if($user['balance']<$order['actual']) {
            return $this->wrong(400500);
        }
        $ret = $o->orderMove($id[1],ORDER_SEND);
        $res = ['ret'=>0];
        return $this->response($res);
    }

    public function getMethods()
    {
        if (!$this->is_login()) {
            return $this->wrong(400100);
        }
        if (!checkParam($this->param,['order_id','order_sn'])) {
            return $this->wrong(400);
        }
        $user = CustomerModel::self()->getOneUserInfo($this->userInfo['user_id'],'deal_password,idcard,balance');
        $pass_isset = $this->_PassIsset($user);
        $pay = [];
        $third_pay = [];
        $where['id'] = $this->param['order_id'];
        $where['order_sn'] = $this->param['order_sn'];
        $order_info = OrderModel::self()->getOrderByCondition($where,'actual');
        if (!$order_info) {
            return $this->wrong(400);
        }
            $config = Config::get('pay.pay_method');

            $pay = $config['default'];
            $third_pay = $config['third_pay'];

            foreach($pay as &$item) {
                $item['name'] = lang($item['name']);
                $item['icon'] = buildImageUrl($item['icon']);
                $item['cash'] = $user['balance'];
            }
            unset($item);
            foreach($third_pay as &$item) {
                $item['name'] = lang($item['name']);
                $item['icon'] = buildImageUrl($item['icon']);
            }
        $res = [
            'pass_isset'   => $pass_isset,
            'qly_platform' => $pay,
            'lists' => $third_pay,
            'amount' => $order_info['actual']
        ];
        return $this->response($res);
    }
}
