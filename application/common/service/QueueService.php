<?php
namespace app\common\service;

use think\Config;

class QueueService{
    private static $init=false;
    protected static $workers=[];
    public static function _init()
    {
        if (self::$init)
            return true;
        $config = Config::get('QUEUE');
        if (!$config)
            return false;
        $select = isset($config['select']) ? $config['select'] : 0;
        $password = isset($config['password']) ? $config['password'] : null;
        $persistent = isset($config['persistent']) ? $config['persistent'] : false;
        $timeout = isset($config['timeout']) ? $config['timeout'] : 30;
        $server = $config['host'] . ":" . $config['port'];
        \Resque::setBackend($server, $select, $password, $persistent, $timeout);
        if(isset($config['prefix']) && !empty($config['prefix'])){
            \Resque_Redis::prefix($config['prefix']);
        }
        self::$init=true;
        return self::$init;
    }
    /**
     * $qname 队列名称
     * $job 对应处理的job class app\class\jobs\
     * $args 参数
     * return jobid 
     */

    public static function push($job, $args, $qname = 'default')
    {
        if (!self::_init())
        {
            return false;
        }
        $jobId = \Resque::enqueue($qname, $job, $args, true);
        return $jobId;
    }

}

