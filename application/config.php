<?php

return [
    'default_return_type'               => 'html',	
    'lang_switch_on'         => false,
    'default_lang'           => 'zh-hk',
	'template'                          =>  [
        'view_suffix'  => 'html',

	],
    'cache'                             => [
        'type'   => 'File',
        'path'   => RUNTIME_PATH.'system/doc/',
        'prefix' => '',
        'expire' => 0,
    ],
 
    'view_replace_str'       => [
        '__STATIC__'    => dirname($_SERVER['SCRIPT_NAME']) == DS ? '' : dirname($_SERVER['SCRIPT_NAME']),
    ],
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    'default_module'         => 'home',
    'deny_module_list'       => ['common','main', 'tasks', 'admin'],
];
