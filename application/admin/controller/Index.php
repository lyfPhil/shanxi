<?php
namespace app\admin\controller;

use think\Controller;
use think\Loader;
use think\Request;
use think\Url;
use think\Session;
use think\Config;
use app\admin\model\UserModel;
use app\main\model\BlogModel;
use app\main\model\GoodsModel;
use app\main\model\GameModel;
use app\main\model\OrderModel;
use app\main\model\FinanceModel;



class Index extends Admin
{
    /**
     * 后台登录首页
     */
    public function index()
    {
        $now = time();
        $time = $now -($now %86400);
        // $data = [
        //     'article' => BlogModel::self()->status($time),
        //     'member'  => UserModel::self()->status($time),
        //     'goods'   => GoodsModel::self()->status($time),
        //     'order'   => OrderModel::self()->status($time),
        //     'orderFinance' => OrderModel::self()->orderFinanceStatus($time),
        //     'finance' => FinanceModel::self()->financeStatus($time),
        //     'today_finance' => FinanceModel::self()->todayFinanceStatus($time),
        //     'platform_fund' => UserModel::self()->allUserFinance(),
        //     'order_charge'  => FinanceModel::self()->orderCharge($time)
        // ];
        // foreach ($data as $k => $v) {
        //     $this->assign($k, $v);
        // }
        $serverInfo = array(
            //获取服务器信息（操作系统、Apache版本、PHP版本）
            'server_version' => $_SERVER['SERVER_SOFTWARE'],
            //获取MySQL版本信息
            // 'mysql_version' => $this->getMySQLVer(),
            //获取服务器时间
            'server_time' => date('Y-m-d H:i:s', time()),
            //上传文件大小限制
            'max_upload' => ini_get('file_uploads') ? ini_get('upload_max_filesize') : '已禁用',
            //脚本最大执行时间
            'max_ex_time' => ini_get('max_execution_time').'秒',
        );
        //视图
        $this->assign('serverInfo',$serverInfo);
        return view();
    }
   
}
