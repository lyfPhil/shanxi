<?php

namespace app\common\controller;

use app\common\service\LangService;
use app\common\service\ToolService;
use app\common\service\SignService;
use think\Cache;
use think\Config;
use think\Controller;
use think\Cookie;

use think\Env;
use think\Request;
use think\Response;
use think\Validate;
use think\Lang;
use app\user\service\LoginService;

class ApiBase extends Controller{
    /**
     * 当前请求方法：post,get...
     * @var
     */
    public $method;

    /**
     * 当前请求参数
     * @var mixed
     */
    public $param;

    /**
     * 接口请求配置
     * @var mixed
     */
    public $filter;

    /**
     * 当前访问路径
     * @var string
     */
    public $path='';

    /**
     * 调试状态
     * @var bool
     */
    public $debug = false;

    public $sign_status = false;
    public $code=200;

    public $data=[];

    public $isLogin = false;
    public $userInfo = [];

    protected $return_type = 'json';

    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $return_type = Env::get('response.return_type');
        if(!empty($return_type)){
            $this->return_type = $return_type;
        }
        $this->method  = strtoupper($this->request->method());
        if (!in_array($this->method, ['GET', 'POST', 'OPTIONS']))
        {
            return $this->wrong(405);
        }

        $header = $this->request->header();
        if (isset($header['origin']))
        {
            $origin = $header['origin'];
            if (preg_match('/^(http|https):\/\/(\w+).th1819\.com/', $origin)||
               preg_match('/^(http|https):\/\/(\w+).tpgame\.co\.th/', $origin))
            {
                header('Access-Control-Allow-Origin: '.$origin);
                header('Access-Control-Allow-Credentials: true');
            }
        }

        if ($this->method === 'OPTIONS')
        {
            header('Access-Control-Allow-Methods: PUT,POST,OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Accept, x-requested-with');
            die();
        }

        Config::set('app_debug',Env::get('debug.status'));
        Config::set('exception_handle','\\app\\common\\exception\\Http');
        $this->param   = $this->request->param();

        $this->path    = strtolower($this->request->module())."/".strtolower($this->request->controller())."/".$this->request->action();
        $this->debug   = Env::get("debug.status");
        $this->sign_status = Env::get('auth.sign_status');
        $lang = Cookie::has('lang') ? Cookie::get('lang') : 'th';
        Lang::set($lang);
        $this->filter(); //请求过滤

    }

    /**
     * 请求过滤
     * @return bool|mixed
     */
    protected function filter(){
        $this->filter_cookie();
        $this->sign();
        //通用参数过滤
        return true;
    }

    protected function is_login()
    {
        if ($this->isLogin && !empty($this->userInfo))
        {
            return true;
        }

        if (isset($this->param['token']) &&
            isset($this->param['user_id']) && 
            isset($this->param['device_type'])) {
            $res = LoginService::checkToken($this->param['user_id'], 
                $this->param['device_type'],  $this->param['token']);
            if (is_array($res))
            {
                $this->isLogin = true;
                $this->userInfo = $res;
                return true;
            }
        }
        return false;
    }

    protected function filter_cookie()
    {
        $_params=[
            'device_type',
            'token',
            'user_id',
            'version',
            'sign',
            'chan', //渠道
            't',
        ];
        foreach($_params as $k)
        {
            if (Cookie::has($k))
            {
                $this->param[$k] = cookie($k);
            }
        }
    }

    private function sign(){
        if($this->sign_status){
            if(!isset($this->param['t'])){
                $this->wrong(401);
            }
            if(!isset($this->param['sign'])){
                $this->wrong(401);
            }
            if(!isset($this->param['device_type'])){
                $this->wrong(401);
            }

            $sign_result = SignService::checkSign($this->param, $this->request->path());

            if(!$sign_result){
                $this->wrong(401);
            }
        }
    }

    /**
     * 请求错误情况下的回调
     * @param $code
     * @param string $message
     */
    protected function wrong($code,$message='')
    {
        $this->response([],strval($code),$message);
    }

    /**
     * 一般情况下的回调
     * @param array $data
     * @param int $code
     * @param string $message
     * @param array $header
     */
    protected function rep($data=[],$code=200,$message='',array $header=[]){
        $this->code = $code;
        $this->data = $data;
        $req['code'] = intval($code);
        $req['state'] = intval($code)==200?1:0;
        $req['data'] = (object)$data;
        $req['message'] = !empty($message)?LangService::trans($message):LangService::message($code);
        $this->send($req,$header);
    }

    /**
     * 回调的每个数据全部转为string类型
     * @param array $data
     * @param int $code
     * @param string $message
     * @param array $header
     */
    protected function response($data=[],$code=200,$message='',array $header=[]){
        //$data = arrayDataToString($data);
        $this->rep($data,$code,$message,$header);
    }

    /**
     * 回调数据给客户端，并运行后置中间件
     * @param $req
     * @param $header
     */
    private function send($req,$header=[]){
        Response::create($req,  $this->return_type, "200")->header($header)->send();
        if(function_exists('fastcgi_finish_request')){
            fastcgi_finish_request();
        }
        die();
    }

    /**
     * 方法不存在时的空置方法
     */
    public function __empty(){
        $this->wrong(404);
    }
}
