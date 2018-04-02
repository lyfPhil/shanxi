<?php
namespace app\admin\validate;
use think\Validate;

class Group extends Validate
{
    protected $rule = [
        'name'  =>  'require|max:64',
    ];

    protected $message = [
        'name.require' => 'Group Name required',
        'name.max'     => 'Group Name length over 64',
    ];
    
    protected $scene = [
        'add'   =>  ['name'],
        'edit'  =>  ['name'],
    ];
}
