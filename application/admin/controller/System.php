<?php

namespace app\admin\controller;

use app\main\model\ConfigModel;
use app\main\model\BankInfoModel;
use think\Request;


class System extends Admin{
    //站点信息
    public function site()
    {
        $data = ConfigModel::self()->getGroupList(1);
        // var_dump($data);die();
        if (!empty($data)) {
            foreach ($data as $v) {
                $this->assign($v['key'], $v['value']);
            }
        }
        return view();
    }
    //更新站点信息
    public function edit()
    {
        $request = Request::instance();
        if($request->isAjax()){
            $data = $request->param();
            if(!empty($data)){
                foreach($data as $key=>$value){
                    $ret = ConfigModel::self()->setValue($key,$value);
                }
            }
            if(is_numeric($ret)){
                return $this->response(200);
            }else{
                return $this->response(201);
            }
        }
    }
    //验证码设置
    public function captcha()
    {
        // 查询验证码配置参数
        $data = json_decode(ConfigModel::self()->getValue('captcha'), true);
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $this->assign($k, $v);
            }
        }
        // 查询开启验证码选项
        $open_captcha = ConfigModel::self()->getValue('open_captcha');
        $this->assign('open_captcha', json_decode($open_captcha, true));

        return view();
    }
    //更新验证码设置
    public function editCaptcha()
    {
        $request = Request::instance();
        if($request->isAjax())
        {
            $data = $request->param();
            //设置开启的验证码
            if(!isset($data['open_captcha']))
            {
                $data['open_captcha'] = [];
            }
            $retOne = ConfigModel::self()->setValue('open_captcha',json_encode($data['open_captcha']));
            unset($data['open_captcha']);
            //设置验证码参数配置
            $retTwo = ConfigModel::self()->setValue('captcha',  json_encode($data));
            if(is_numeric($retOne) && is_numeric($retTwo))
            {
                return $this->response(200);
            }else
            {
                return $this->response(201);
            }
        }
    }
    //邮件设置
    public function email()
    {
        $data = json_decode(ConfigModel::self()->getValue('mail'),true);
        if(!empty($data)){
            $this->assign('email',$data);
        }
        return view();
    }
    //更新邮件设置
    public function editEmail()
    {
        $request = Request::instance();
        if($request->isAjax())
        {
            $data = $request->param();
            $ret = ConfigModel::self()->setValue('mail',  json_encode($data));
            if(is_numeric($ret))
            {
                return $this->response(200);
            }else
            {
                return $this->response(201);
            }
        }
    }
    //汇率设置
    public function rate()
    {
        $data = json_decode(ConfigModel::self()->getValue('rate'),true);
        if(!empty($data)){
            $this->assign('rate',$data);
        }
        return view();
    }

    public function editRate()
    {
        $request = Request::instance();
        if($request->isAjax())
        {
            $data = $request->param();
            $ret = ConfigModel::self()->setValue('rate',json_encode($data));
            if(is_numeric($ret))
            {
                return $this->response(200);
            }else
            {
                return $this->response(201);
            }
        }
    }

    public function orderExpireTime(){
        $config_model = ConfigModel::self();
        $data = $config_model->getValue('order_expire_time');
        $time = json_decode($data,true);
        $list = [];
        foreach($time as $key => $val){
            $list[$key] = $val/(3600*24);
        }
        $this->assign('time',$list);
        return view();
    }

    public function editOrderExpireTime(){
        $request    = Request::instance();
        if($request->isAjax()){
            $param  = $request->param();
            $data   = [];
            foreach($param as $key => $val) {
                if(!is_numeric($val)){
                    return $this->response(201);
                }
                $data[$key] = $val*24*3600;
            }
        }
        $config_model  = ConfigModel::self();
        $ret = $config_model->setValue('order_expire_time',  json_encode($data));
        if($ret==1)
        {
            return $this->response(200);
        } else {
            return $this->response(201,'没有更改');
        }
    }

    public function deposit(){
        $config_model = ConfigModel::self();
        $data         = $config_model->getValue('deposit');
        $deposit      = json_decode($data,true);
        $this->assign('deposit',$deposit);
        return view();
    }

    public function editDeposit(){
        $request        = Request::instance();
        $param          = $request->param();
        $param['deal']  = $param['deal']*24*3600;
        $param['refund']= $param['refund']*24*3600;
        $data           = json_encode($param);
        $config_model   = ConfigModel::self();
        $ret = $config_model->setValue('deposit',$data);
        if ($ret==1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function withdrawal(){
        $config_model = ConfigModel::self();
        $data         = $config_model->getValue('withdrawal');
        $withdrawal   = json_decode($data,true);
        $this->assign('withdrawal',$withdrawal);
        return view();
    }

    public function editWithdrawal(){
        $request        = Request::instance();
        $param          = $request->param();
        $config_model   = ConfigModel::self();
        $param['deadline']   = $param['deadline']*24*3600;
        $param['day'] = $param['day']*24*3600;
        $data           = json_encode($param);
        $ret  = $config_model->setValue('withdrawal',$data);
        if ($ret==1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function transaction(){
        $config_model  = ConfigModel::self();
        $data          = $config_model->getValue('transaction');
        $trans         = json_decode($data,true);
        $this->assign('trans',$trans);
        return view();
    }

    public function editTransaction(){
        $request    =   Request::instance();
        $param      = $request->param();
        $data       = json_encode($param);
        $config_model = ConfigModel::self();
        $ret = $config_model->setValue('transaction', $data);
        if ($ret==1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function recharge(){
        $config_model = ConfigModel::self();
        $config       = $config_model->getValue('recharge');
        $recharge     = json_decode($config,true);
        $this->assign('recharge',$recharge);
        return view();
    }

    public function editRecharge(){
        $request    =   Request::instance();
        $param      = $request->param();
        $param['expire'] = $param['expire']*3600*24;
        $data       = json_encode($param);
        $config_model = ConfigModel::self();
        $ret = $config_model->setValue('recharge', $data);
        if ($ret==1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function qlybank(){
        $config_model = ConfigModel::self();
        $config       = $config_model->getValue('qly_bank');
        $qly_bank     = json_decode($config,true);
        $qly_bank['bank_icon'] = buildImageUrl($qly_bank['bank_icon']);
        $bank_model   = BankInfoModel::self();
        $bank         = $bank_model->where(['status'=>1])->field('id,bank_name,bank_icon')->select();
        $bank_list    = [];
        foreach($bank as $val){
            $val['bank_icon'] = buildImageUrl($val['bank_icon']);
            $bank_list[]=$val;
        }
        $this->assign('bank',$bank_list);
        $this->assign('qly_bank',$qly_bank);
        return view();
    }
    public function Shield()
    {
        $data = json_decode(ConfigModel::self()->getValue('ios_shield'),true);
        if(!empty($data)){
            $this->assign('shield',$data);
        }
        return view();
    }
    public function editShield()
    {
        $request = Request::instance();
        if($request->isAjax())
        {
            $data = $request->param();
            $ret = ConfigModel::self()->setValue('ios_shield', json_encode($data));
            if(is_numeric($ret))
            {
                return $this->response(200);
            }else
            {
                return $this->response(201);
            }
        }
    }
}
