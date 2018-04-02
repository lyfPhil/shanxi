<?php
namespace app\admin\validate;

use \think\Validate;

class Game extends Validate
{
    protected $rule = [
        'game_name' => 'require|max:150',
        'initial'   => 'require',
        'game_url'  => 'require',
        'download_url'  => 'require',
    ];

    protected $message = [
        'game_name.require'  => '游戏名称必须',
        'game_name.max' => '游戏名称长度不得超过150字符',
        'initial.require'      => '首字母不能为空',
        'game_url.require'   => '官网地址必须',
        'download_url' => '游戏下载地址必须',
    ];
    
    protected $scene = [
        'add'   =>  ['game_name', 'initial'],
        'edit'  =>  ['game_name', 'initial'],
    ];
}
