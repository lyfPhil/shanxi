<?php
namespace app\pay\validate;
use think\Validate;

class Notifyps extends Validate{
        
    protected $rule = [
        'pm_id'=>'require',
        'amount'=>'require',
        'currency'=>'require',
        'order_id'=>'require',
        'state'=>'require',
        'notify_sig'=>'require',
    ];
    
    protected $message = [
        'pm_id.require'=>'param error',
        'amount.require'=>'param error',
        'currency.require'=>'param error',
        'order_id.require'=>'param error',
        'state.require'=>'param error',
        'notify_sig.require'=>'param error',
    ];
    
}
