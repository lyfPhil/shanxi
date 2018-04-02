<?php

namespace app\common\exception;

use Exception;
use think\Env;
use think\exception\Handle;
use think\Response;

class Http extends Handle{
    public function render(Exception $e)
    {
        //TODO::开发者对异常的操作
        //可以在此交由系统处理
        if(Env::get('debug.status')){
            return parent::render($e);
        }else{
            $code = '500';
            if (method_exists($e, 'getStatusCode'))
            {
                $code = strval($e->getStatusCode());
            }
            $msg = 'Something error';
            if ($code == '404')
            {
                $msg = 'Not Found';
            }
            $req['code']= $code;
            $req['message'] = $msg;
            $req['data'] = [];
            $return_type = Env::get('response.return_type');
            if(empty($return_type)){
                $return_type = "json";
            }
            Response::create($req,$return_type,$code)->send();
            die();
        }
    }
}
