<?php
namespace app\common\controller;

use think\Config;
use think\Controller;
use think\Lang;
use think\Request;
use app\common\service\LangService;

/**
 *
*/
class Common extends Controller
{

    public function _initialize()
    {
        $this->param = Request::instance()->param();
        // $now_lang = $this->getSetLang();
        // $this->assign('set_lang', $now_lang);
    }

    //
    public function getSetLang()
    {
        $lang = Lang::detect();
        if($lang == 'zh-cn') {
            return 'en-us';
        }
        return 'zh-cn';
    }
    protected function response($code, $msg = '', $data = [])
    {
        if (empty($msg)) {
            $msg = \think\Config::get('code.' . $code);
        }
        $msg = !empty($msg) ? LangService::trans($msg) : LangService::message($code);
        $data = ['code'=>$code, 'msg'=>$msg, 'data'=>$data];
        return json($data, 200);
    }
}
