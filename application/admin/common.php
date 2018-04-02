<?php

function info($msg = '', $code = '', $url = '',  $data = '', $wait = 3 )
{
    if (is_numeric($msg)) {
        $code = $msg;
        $msg  = '';
    }
    if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
        $url = $_SERVER["HTTP_REFERER"];
    } elseif ('' !== $url) {
        $url = preg_match('/^(https?:|\/)/', $url) ? $url : Url::build($url);
    }
    $result = [
        'code' => $code,
        'msg'  => $msg,
        'data' => $data,
        'url'  => $url,
        'wait' => $wait,
    ];
    return $result;
}

function get_offset($request)
{
    $offset = 0;
    if (isset($request['offset']) && is_numeric($request['offset']) ) {
        $offset = $request['offset'];
        unset($request['offset']);
    }
    $limit = 5;
    if (isset($request['limit']) && is_numeric($request['limit']) ) {
        $limit = $request['limit'];
        unset($request['limit']);
    }
    return [$offset, $limit];
}
/**
 * 组合多维数组
 *
 * @return array
 */
function unlimitedForLayer ($cate, $name = 'child', $parent_id = 0)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $parent_id) {
            $v[$name] = unlimitedForLayer($cate, $name, $v['id']);
            $arr[] = $v;
        }
    }
    return $arr;
}

/**
 * 组合一维数组
 *
 * @return array
 */
function unlimitedForLevel ($cate, $html = '--', $parent_id = 0, $level = 0)
{
    $arr = array();
    foreach ($cate as $k => $v) {
        if ($v['parent_id'] == $parent_id) {
            $v['level'] = $level + 1;
            $v['html']  = str_repeat($html, $level);
            $arr[] = $v;
            $arr = array_merge($arr, unlimitedForLevel($cate, $html, $v['id'], $level + 1));
        }
    }
    return $arr;
}

/**
 * 组合一维数组
 *
 * @return array
 */
function list_for_level($list, $html = '--', $pid = 0, $level = 0)
{
    $arr = array();
    foreach ($list as $k => $v) {
        if ($v['parent_id'] == $pid) {
            $v['level'] = $level + 1;
            $v['html']  = str_repeat($html, $level);
            $arr[] = $v;
            $arr = array_merge($arr, list_for_level($list, $html, $v['id'], $level + 1));
        }
    }
    return $arr;
}

/**
 * 传递一个子分类ID返回所有的父级分类
 *
 * @return array
 */
function getParents ($cate, $id)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['id'] == $id) {
            $arr[] = $v;
            $arr = array_merge(getParents($cate, $v['parent_id']), $arr); 
        }
    }
    return $arr;
}

/**
 * 传递一个父级分类ID返回所有子分类ID
 *
 * @return array
 */
function getChildsId ($cate, $parent_id)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $parent_id) {
            $arr[] = $v['id'];
            $arr = array_merge($arr, getChildsId($cate, $v['id']));
        }
    }
    return $arr;
}

/**
 * 传递一个父级分类ID返回所有子分类
 *
 * @return array
 */
function getChilds ($cate, $parent_id)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['parent_id'] == $parent_id) {
            $arr[] = $v;
            $arr = array_merge($arr, getChilds($cate, $v['id']));
        }
    }
    return $arr;
}


/**
 * 字符串截取，支持中文和其他编码
 *
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

/**
 * 把返回的数据集转换成Tree
 *
 * @param array $list 要转换的数据集
 * @return array
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

function set_sso($uid, $sso = false)
{
    $expire = 3600;
    if (!$sso)
    {
        $sso = [
            'sid'=> session_id(),
            'ip' => get_client_ip(),
            't' => time(),
        ];
    }
    return \think\Cache::set('backend_sso_'.$uid, $sso, $expire);
}

function get_sso($uid)
{
    return  \think\Cache::get('backend_sso_'.$uid);
}

function check_sso($uid)
{
    $sso = get_sso($uid);
    if (!$sso)
    {
        set_sso($uid);
        return True;
    }
    $lsid = session_id();
    if ($lsid === $sso['sid'])
    {
        set_sso($uid, $sso);
        return True;
    }
    return $sso;    
}
