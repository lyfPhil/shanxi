<?php

namespace app\admin\validate;

use think\Validate;

class Cover extends Validate{
    
   protected $rule = [
        'start_time'   => 'require',
        'end_time'  => 'require',
    ];

    protected $message = [
        'start_time.require' => '开始日期必须写',
        'end_time.require' => '结束日期必须写',
    ];
    
    protected $scene = [
        'addAdv'   =>  [ 'start_time','end_time'],
        'editAdv'  =>  [ 'start_time','end_time'],
    ];
}
