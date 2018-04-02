<?php
namespace app\admin\validate;
use think\Validate;

class Config extends Validate
{
    protected $rule = [
        'key'   => 'require|alphaDash',
        'name'  => 'require',
        'value' => 'require',
    ];

    protected $message = [
        'key.require'   => '标识必须填写',
        'key.alphaDash' => '标识格式为字母和数字，下划线_及破折号-',
        'name.require'  => '名称必须填写',
        'value.require' => '值必须填写',
    ];

    protected $scene = [
        'add'  => ['key', 'name', 'value'],
        'edit' => ['key', 'name', 'value'],
    ];

}
