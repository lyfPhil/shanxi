<?php
// +----------------------------------------------------------------------
// | TPR [ Design For Api Develop ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2017 http://hanxv.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axios <axioscros@aliyun.com>
// +----------------------------------------------------------------------

if (!function_exists('middleware')) {
    /**
     * 实例化验证器
     * @param string    $name 验证器名称
     * @param string    $layer 业务层名称
     * @param bool      $appendSuffix 是否添加类名后缀
     * @param string    $common
     * @return \think\Validate
     */
    function middleware($name = '', $layer = 'middleware', $appendSuffix = false,$common="common")
    {
        return \think\Loader::validate($name, $layer, $appendSuffix,$common);
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * 获取客户端IP地址
     * @param int $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param bool $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    function get_client_ip($type = 0, $adv = false) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($adv){
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown',$arr);
                if(false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            }elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}

if(!function_exists('arraySort')){
    function arraySort($array,$sortRule="",$order="asc"){
        /**
         * $array = [
         *              ["book"=>10,"version"=>10],
         *              ["book"=>19,"version"=>30],
         *              ["book"=>10,"version"=>30],
         *              ["book"=>19,"version"=>10],
         *              ["book"=>10,"version"=>20],
         *              ["book"=>19,"version"=>20]
         *      ];
         */
        if(is_array($sortRule)){
            /**
             * $sortRule = ['book'=>"asc",'version'=>"asc"];
             */
            usort($array, function ($a, $b) use ($sortRule) {
                foreach($sortRule as $sortKey => $order){
                    if($a[$sortKey] == $b[$sortKey]){continue;}
                    return (($order == 'desc')?-1:1) * (($a[$sortKey] < $b[$sortKey]) ? -1 : 1);
                }
                return 0;
            });
        }else if(is_string($sortRule) && !empty($sortRule)){
            /**
             * $sortRule = "book";
             * $order = "asc";
             */
            usort($array,function ($a,$b) use ($sortRule,$order){
                if($a[$sortRule] == $b[$sortRule]){
                    return 0;
                }
                return (($order == 'desc')?-1:1) * (($a[$sortRule] < $b[$sortRule]) ? -1 : 1);
            });
        }else{
            usort($array,function ($a,$b) use ($order){
                if($a== $b){
                    return 0;
                }
                return (($order == 'desc')?-1:1) * (($a < $b) ? -1 : 1);
            });
        }
        return $array;
    }
}

if(!function_exists('arrayDataToString')){
    function arrayDataToString(&$array=[]){
        if(is_array($array)){
            foreach ($array as &$a){
                if(is_array($a)){
                    $a = arrayDataToString($a);
                }
                if(is_int($a)){
                    $a = strval($a);
                }
                if(is_null($a)){
                    $a = "";
                }
            }
        }else if(is_int($array)){
            $array = strval($array);
        }else if(is_null($array)){
            $array = "";
        }
        return $array;
    }
}


if(!function_exists('getBrowser')){
    function getBrowser()
    {
        $agent = $_SERVER["HTTP_USER_AGENT"];
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) //ie11判断
        {
            return "ie";
        } else if (strpos($agent, 'Firefox') !== false) {
            return "firefox";
        } else if (strpos($agent, 'Chrome') !== false) {
            return "chrome";
        } else if (strpos($agent, 'Opera') !== false) {
            return 'opera';
        } else if ((strpos($agent, 'Chrome') == false) && strpos($agent, 'Safari') !== false) {
            return 'safari';
        } else if (strpos($agent, 'MicroMessenger') !== false) {
            return 'weixin';
        } else {
            return 'unknown';
        }

    }
}

if(!function_exists('getBrowserVer')){
    function getBrowserVer()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            //当浏览器没有发送访问者的信息的时候
            return 'unknow';
        }
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE\s(\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } elseif (preg_match('/FireFox\/(\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } elseif (preg_match('/Opera[\s|\/](\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } elseif (preg_match('/Chrome\/(\d+)\..*/i', $agent, $regs)) {
            return $regs[1];
        } elseif ((strpos($agent, 'Chrome') == false) && preg_match('/Safari\/(\d+)\..*$/i', $agent, $regs)) {
            return $regs[1];
        } else {
            return 'unknow';
        }

    }
}

if(!function_exists('getOs')){
    function getOs()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $os    = false;

        if (preg_match('/win/i', $agent) && strpos($agent, '95')) {
            $os = 'Windows 95';
        } else if (preg_match('/win 9x/i', $agent) && strpos($agent, '4.90')) {
            $os = 'Windows ME';
        } else if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent)) {
            $os = 'Windows 98';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent)) {
            $os = 'Windows Vista';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent)) {
            $os = 'Windows 7';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent)) {
            $os = 'Windows 8';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent)) {
            $os = 'Windows 10'; #添加win10判断
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent)) {
            $os = 'Windows XP';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent)) {
            $os = 'Windows 2000';
        } else if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent)) {
            $os = 'Windows NT';
        } else if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent)) {
            $os = 'Windows 32';
        } else if (preg_match('/linux/i', $agent)) {
            $os = 'Linux';
        } else if (preg_match('/unix/i', $agent)) {
            $os = 'Unix';
        } else if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent)) {
            $os = 'SunOS';
        } else if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent)) {
            $os = 'IBM OS/2';
        } else if (preg_match('/Mac/i', $agent) && preg_match('/10_12_2/i', $agent)) {
            $os = 'Mac OS X 10_12_2';
        }else if (preg_match('/Mac/i', $agent)) {
            $os = 'Mac OS X';
        }else if (preg_match('/PowerPC/i', $agent)) {
            $os = 'PowerPC';
        } else if (preg_match('/AIX/i', $agent)) {
            $os = 'AIX';
        } else if (preg_match('/HPUX/i', $agent)) {
            $os = 'HPUX';
        } else if (preg_match('/NetBSD/i', $agent)) {
            $os = 'NetBSD';
        } else if (preg_match('/BSD/i', $agent)) {
            $os = 'BSD';
        } else if (preg_match('/OSF1/i', $agent)) {
            $os = 'OSF1';
        } else if (preg_match('/IRIX/i', $agent)) {
            $os = 'IRIX';
        } else if (preg_match('/FreeBSD/i', $agent)) {
            $os = 'FreeBSD';
        } else if (preg_match('/teleport/i', $agent)) {
            $os = 'teleport';
        } else if (preg_match('/flashget/i', $agent)) {
            $os = 'flashget';
        } else if (preg_match('/webzip/i', $agent)) {
            $os = 'webzip';
        } else if (preg_match('/offline/i', $agent)) {
            $os = 'offline';
        } else {
            $os = 'unknow';
        }
        return $os;
    }
}
