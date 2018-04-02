<?php
namespace app\pay\controller;

use app\common\controller\ApiBase;

use app\pay\service\PayssionService;
use app\main\model\PayModel;

//Payssion notify back
class Backps  extends ApiBase{
    public function index()
    {
        $validate = validate('Notifyps');
        if(!$validate->check($this->param)){
            return $this->wrong(400);
        }
        $check = PayssionService::notifyCheck($this->param);
        if (!$check)
        {
            return $this->wrong(403);
        }
        $order_id = $this->param['order_id'];
        $trans_id = $this->param['transaction_id'];
        $res = PayssionService::getDetail($order_id, $trans_id);
        if (!is_array($res))
        {
            return $this->wrong(400);
        }

        $state = $res['state'];
        $state_type = 0;
        $ret_code = 400;
        switch($state)
        {
            case 'completed':
                $state_type=2;
                $ret_code = 200;
                break;
            case 'pending':
                $state_type=1;
                break;
            case 'expired':
                $state_type=3;
                $ret_code = 400300;
                break;
            case 'failed':
            case 'error':
            default:
                $state_type=0;
                break;
        }
        $pm = PayModel::self();
        $info = $pm->getByTransId($trans_id);
        if ($info)
        {
            if ($info['currency'] == $this->param['currency'] && 
                $info['amount'] == $this->param['amount'] &&
                $info['state'] != $state_type )
            {
                $pm->updateState($info, $state_type);
            }
        }
        if($ret_code == 200)
        {
            return $this->response(['ret'=>0]);
        }
        else
        {
            return $this->wrong($ret_code);
        }

    }
}
