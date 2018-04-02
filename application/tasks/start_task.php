<?php
ini_set('display_errors', true);
error_reporting(E_ERROR);
set_time_limit(0);

// 定义应用目录
define('ROOT_PATH', '/www/web/ppgame/');
define('THINK_PATH', ROOT_PATH.'thinkphp/');
define('APP_PATH', ROOT_PATH. 'application/');
define('CONF_PATH', ROOT_PATH.'/config/');
define('RUNTIME_PATH', '/www/cache/ppgame/');

define('MODE_NAME', 'cli');	// 自定义cli模式

// 处理自定义参数
putenv("ARGV=" . json_encode($argv));
require ROOT_PATH . '/thinkphp/base.php';
\think\Route::bind('tasks');
\think\App::run()->send();
