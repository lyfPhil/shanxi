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
$act = isset($argv[1]) ? $argv[1] : 'start';
putenv("Q_ACTION={$act}");
putenv("Q_ARGV=" . json_encode($argv));
require ROOT_PATH . '/thinkphp/base.php';
\think\Route::bind('tasks/job/index'); //绑定到tasks\job模块
\think\App::run()->send();
