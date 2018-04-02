<?php

namespace app\admin\validate;

use think\Validate;

class Generaluser extends Validate
{
    protected $rule = [
        'password' => 'min:6',
        'repassword' => 'min:6',
    ];
    protected $message = [
        'password.min'=>'密码最小为6位',
        'repassword.min'=>'密码最小为6位',
    ];

}
