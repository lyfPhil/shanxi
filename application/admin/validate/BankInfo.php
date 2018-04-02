<?php
namespace app\admin\validate;

use think\Validate;

class BankInfo extends Validate{
    
    protected $rule = [
        'bank_name' => 'require',
    ];
    protected $message = [
        'bank_name.require'=>'银行名称必须填写',
    ];
    protected $scene = [
        'add' => ['bank_name'],
        'update' => ['bank_name'],
    ];
}
