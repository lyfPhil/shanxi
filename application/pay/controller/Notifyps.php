<?php
namespace app\pay\controller;

use app\common\controller\ApiBase;
use think\Validate;

use app\pay\service\PayssionService;

use app\main\model\PayModel;

//Payssion notify back
class Notifyps  extends ApiBase{
    public function index()
    {
        $validate = validate('Notifyps');
        if(!$validate->check($this->param)){
            header('HTTP/1.1 400');
            return exit(0);
        }
        $check = PayssionService::notifyCheck($this->param);
        if (!$check)
        {
            header('HTTP/1.1 403');
            return exit(0);
        }
        $state = $this->param['state'];
        $order_id = $this->param['order_id'];
        $state_type = 0;
        switch($state)
        {
            case 'completed':
                $state_type=2;
                break;
            case 'pending':
                $state_type=1;
                break;
            case 'expired':
                $state_type=3;
                break;
            case 'failed':
            case 'error':
            default:
                $state_type=0;
                break;
        }
        $pm = PayModel::self();
        $trans_id = $this->param['transaction_id'];
        $info = $pm->getByTransId($trans_id);
        if ($info)
        {
            if ($info['currency'] == $this->param['currency'] && 
                $info['amount'] == $this->param['amount'] &&
                $info['state'] != $state_type )
            {
                $pm->updateState($info, $state_type);
            }
            return $this->response(200);
        }
        header('HTTP/1.1 403');
        return exit(0);
    }
}
