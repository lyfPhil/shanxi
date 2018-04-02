<?php

require_once(__DIR__.'/common/helper.php');

// 应用公共文件
function is_user_login(){
    $user = \think\Session::has('user');
    return  empty($user) ? false:true;
}

function user_info(){
    $user = \think\Session::get('user');
    return $user;
}

function user_current_id(){
    $user = user_info();
    return $user['id'];
}

function getLastUrl(){
    return \think\Session::get('last_url');
}

function make_password($password,$auth){
    return md5("##!(qly)@!".md5($auth.$password));
}

function makeAppVersion($app,$update_type,$version_type="release"){
    $temp_base = $app['base_version'];

    if(!empty($app['last_version'])){
        list($temp_main,$temp_next,$temp_debug) = explode(".",$app['last_version']);
    }else{
        $temp_main = $temp_base;
        $temp_next = 0;
        $temp_debug = 0;
    }
    $main = $temp_main;$next = 0;$debug=0;
    switch ($update_type){
        case 2:
            $main = ++$app['base_version'];
            break;
        case 1:
            $next = ++$temp_next;
            break;
        case 0:
            $next = $temp_next;
            $debug = ++$temp_debug;
            break;
    }

    return makeVersion($main,$next,$debug,$version_type);
}

function makeVersion($main,$next="0",$debug="0",$type="release")
{
    return $main . "." . $next . "." . $debug . "." . date("ymd") . "_" . $type;
}

function domain(){
    $domain = \think\Env::get('web.host');
    $last_str = substr($domain,-1);
    if($last_str!= "/"){
        $domain .= "/";
    }
    return $domain;
}

function getMonthBeginEndDay($year,$month,$format='timestamp'){
    $month = sprintf('%02d',$month);
    $ymd = $year."-".$month."-01";
    $begin = strtotime($ymd." 00:00:00");
    $end   = strtotime("$ymd +1 month -1 seconds");
    if($format=='timestamp'){
        return [
            'begin'=>$begin,
            'end'=>$end
        ];
    }else{
        return [
            'begin'=>date($format,$begin),
            'end'=>date($format,$end),
        ];
    }
}

function getDayBeginEndTime($date,$format='timestamp'){
    $begin = strtotime($date." 00:00:00");
    $end   = strtotime("$date +1 day -1 seconds");
    if($format=='timestamp'){
        return [
            'begin'=>$begin,
            'end'=>$end
        ];
    }else{
        return [
            'begin'=>date($format,$begin),
            'end'=>date($format,$end),
        ];
    }
}

function getHourBeginEndTime($date ,$hour,$format='timestamp'){
    $hour = sprintf('%02d',$hour);
    $begin = strtotime($date." ".$hour.":00:00");
    $end   = strtotime($date." ".$hour.":00:00 +1 hour -1 seconds");
    if($format=='timestamp'){
        return [
            'begin'=>$begin,
            'end'=>$end
        ];
    }else{
        return [
            'begin'=>date($format,$begin),
            'end'=>date($format,$end),
        ];
    }
}

function isValidPhone($data)
{
    $valids=[
        'China'     =>'/^86((13\d|14\d|15[^4\D]|17[13678]|18\d)\d{8}|170[^346\D]\d{7})$/',
        'Taiwan'    =>'/^88609\d{8}$/',
        'Macao'     =>'/^8536\d{7}$/',
        'HongKong'  =>'/^852((5[^08\D]|6\d|7[0-3]|8[1-9]|)\d{6}|9[0-8][1-9]\d{5})$/',
        'Thailand'  =>'/^66(0[89])\d{8}$/'
    ];
    foreach($valids as $k=>$v)
    {
        if (preg_match($v, $data))
        {
            return $k;
        }
    }
    return False;
}
function md5shortbin($a)
{
    $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV';
    $d = '';
    for($f = 0;$f < 8;$f++)
    {
        $g = ord( $a[ $f ] );
        $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ];
    }
    return $d;
}

function md5short($str)
{
    $a = md5($str, true);
    return md5shortbin($a);
}
function charge_no(){
    list($usec,$sec) = explode(" ", microtime());
    $s = $usec.rand(1000, 9999);
    return md5short(uniqid($s));
}
function gen_uniqSN()
{
    list($usec, $sec) = explode(" ", microtime());
    $pre = date("YmdHi");
    $s=$usec.rand(1000, 9999);
    return md5short(uniqid($s)).$pre;
}

function get_page_param($param)
{
    $page=isset($param['page'])?$param['page']:1;
    $page_size=isset($param['page_size'])?$param['page_size']:12;
    if ($page <=0)$page = 1;
    if ($page_size > MAX_PAGE_SIZE)$page_size = MAX_PAGE_SIZE;
    else if ($page_size < MIN_PAGE_SIZE)$page_size = MIN_PAGE_SIZE;
    return [intval($page), intval($page_size)];
}

function get_verification_code($length=6)
{
    switch ($length) {
    case 4:
        $result = rand(1000, 9999);
        break;
    case 8:
        $result = rand(10000000, 99999999);
        break;
    case 6:
    default:
        $result = rand(100000, 999999);
        break;
    }
    return $result;
}

function get_im_id($user_id)
{
    $prefix="ppgame#*!@((#%!!__";
    return $username = "ppgame_".$user_id;
}

function get_im_pass($user_id)
{
    $prefix="ppgame#*!@((#%!!__";
    $username = "ppgame_".$user_id;
    $password = substr(md5($prefix.$username), 4, 16);
    return $password;
}

function get_tim_sig($user_id)
{
    $username = "ppgame_".$user_id;
    $sig = app\main\extra\TimService::getUserSig($username);
    return $sig;
}

function buildImageUrl($image_url)
{
    $url= str_replace("\\", "/", $image_url);
    if (substr($image_url, 0, 4) != 'http')
    {
        /*
        * 暂时去掉防盗链
        if (strpos($image_url, "?sign=") === false)
        {
            $image_url = app\main\service\QiniuService::addSign($image_url);
        }
        */
        $url= think\Env::get('web.imgprefix').$image_url;
    }
    return $url;
}

function imgurl_add_sign($image_url)
{
    /*
     * 暂时去掉防盗链
    if (substr($image_url, 0, 4) != 'http')
    {
        $image_url = app\main\service\QiniuService::addSign($image_url);
    }*/
    return $image_url;
}

function silceUrlToPic($url){
    $pic_temp = explode(',',$url);
    $pic = [];
    foreach($pic_temp as $item){
        if( $item!=''){
            $pic[] = parse_url($item, PHP_URL_PATH);
        }
    }
    $cnt = count($pic);
    if($cnt<1){
        return 400;
    }elseif($cnt>9){
        return 400;
    }else{
        return $pic;
    }
}
//检查是否有参数
function checkParam($param,$name)
{
    foreach($name as $val){
        if(!isset($param[$val])){
            return false;
        }
    }
    return true;
}

//匹配出所有的img source
function getImgSrc($data)
{
    $preg = '/<img.*?src="(.*?)".*?>/is';
    preg_match_all($preg, $data, $imgArr);

    return $imgArr;
}
/**
 * 更新图片防盗链
 */
function replaceImgLink($data)
{
    //匹配规则
    $imgArr = getImgSrc($data);
    //dump($imgArr);exit();
    $url = [];
    foreach ($imgArr[1] as $key => $value)
    {
        $url = parse_url($value);
        $host = think\Env::get('web.imgprefix');
        if (isset($url['scheme']) && in_array($url['scheme'], ['http', 'https']))
        {
            $source = $url['scheme']."://".$url['host'];
            if($host == $source)
            {
                $imgUrl = str_replace($value,buildImageUrl($url['path']),$value);
                $data = str_replace($value, $imgUrl, $data);
            }
        }
        else{
            $imgUrl = buildImageUrl($url['path']);
            $data = str_replace($value, $imgUrl, $data);
        }
    }
    return $data;
}

/**
 * 把返回的数据集转换成Tree
 *
 * @param array $list 要转换的数据集
 * @return array
 */
function help_list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
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
/**
 * is_vicon 返回是否点亮会员图标
 * @param  $vid
 */
function is_vicon($flag){
    return ($flag & FLAG_VID) == 0 ? 0 : 1;
}