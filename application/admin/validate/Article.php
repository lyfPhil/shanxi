<?php
namespace app\admin\validate;
use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'title' => 'require|max:150',
        'count' => 'number',
    ];

    protected $message = [
        'title.require'  => '标题名称必须',
        'title.max'      => '标题名称最多不能超过150个字符',
        'count.number'   => '浏览次数必须为整数',
    ];
    
    protected $scene = [
        'add'   =>  ['title', 'count'],
        'edit'  =>  ['title', 'count'],
    ];
}
