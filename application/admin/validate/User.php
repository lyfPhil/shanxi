<?php
namespace app\admin\validate;

use think\Validate;

class User extends Validate
{

    protected $rule =   [
        'username'  =>'require',
        'email' => 'email',
        'mobile'              => 'require',
        'password'              => 'length:6,16',
        'group_id' => 'require',
    ];

    protected $message  =   [
        'username'  =>'Username require',
        'mobile.require'      => 'Mobile require',
        'mobile.length'       => 'Please enter a correct mobile',
        'password.length'       => 'Please enter a correct password',
    ];

    protected $scene = [
        'add' => ['username', 'mobile','password', 'group_id', 'email'],
        'login' =>  ['username','password'],
        'edit' => ['username', 'group_id']
    ];

}


