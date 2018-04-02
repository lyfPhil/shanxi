<?php

namespace app\admin\controller;

use app\main\model\BankInfoModel;
use app\main\model\CompanyBankModel;
use think\Request;
use think\Db;
class Bankinfo extends Admin{
    /**
     * index 普通银行列表
     */
    public function index()
    {
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['bank_name'] = array(array('like', '%'.trim($search['keywords']).'%'),'or');
        }
        if (isset($search['status']) && $search['status'] != '') {
            $map['status'] = $search['status'];
        }
        $list = BankInfoModel::self()->getBankInfoList($map,'status asc,id desc',10,$search);
        foreach ($list as $item) {
            if (!empty($item['bank_icon'])) {
                $item['bank_icon'] = buildImageUrl($item['bank_icon']);
            }
        }
        $page = $list->render();
        $this->assign('search',$search);
        $this->assign('page',$page);
        $this->assign('list',$list);
        return view();
    }

    public function edit()
    {
        $param = Request::instance()->param();
        $detail = BankInfoModel::self()->getdetails($param['id']);
        //处理图片
        if (!empty($detail['bank_icon'])) {
            $detail['bank_icon'] = buildImageUrl($detail['bank_icon']);
        }
        $this->assign('detail',$detail);
        return view();
    }

    public function update()
    {
        $param = Request::instance()->param();
        $data = [
            'bank_name' => $param['bank_name'],
            'code'   => $param['code'],
            'status' => $param['status']
        ];
        if (isset($param['bank_icon'])) {
            $data['bank_icon'] = $param['bank_icon'];
        }
        $bm = BankInfoModel::self();
        $ret = $bm->where('id',$param['id'])->update($data);
        if (is_numeric($ret)) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    /**
     * [turn_on_off 禁用开启]
     * @return [type] [description]
     */
    public function turn_on_off()
    {
        $ids = Request::instance()->param('id');
        $bank_model = BankInfoModel::self();
        $bank = $bank_model->where('id',$ids)->field('status')->find();
        $status = abs($bank['status'] - 1);
        $ret = $bank_model->where('id',$ids)->setField('status',$status);
        if ($ret != FALSE) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    public function create()
    {
        return view();
    }
    public function store()
    {
        if(request()->isAjax()){
            $params = Request::instance()->param();
            //验证器
            $valid = validate('BankInfo');
            if (!$valid->scene('add')->check($params)) {
                return $this->response(201,lang($valid->getError()));
            }
            $ret = BankInfoModel::self()->add($params);
            if (is_numeric($ret)) {
                return $this->response(200);
            } else {
                return $this->response(201);
            }
        }
    }
    /**
     * qly_bank 公账银行卡列表
     */
    public function qly_bank(){
        $bank_model = CompanyBankModel::self();
        $qly_bank = $bank_model->select();
        $list = [];
        foreach ($qly_bank as $val) {
            if ($val['bank_icon'] != '') {
                $val['bank_icon'] = buildImageUrl($val['bank_icon']);
            }
            $list[] = $val;
        }
        $this->assign('qly_bank',$list);
        return view();
    }
    /**
     * edit_qly_bank 修改公账银行卡信息
     */
    public function edit_qly_bank(){
        $param = Request::instance()->param();
        $bank_model = CompanyBankModel::self();
        $bank_info = $bank_model->where('id',$param['id'])->find();
        if ($bank_info['bank_icon'] != '') {
            $bank_info['bank_icon'] = buildImageUrl($bank_info['bank_icon']);
        }
        $this->assign('detail',$bank_info);
        return view();
    }
    /**
     * create_qly_bank 创建公账银行卡
     * @return 新增页面
     */
    public function create_qly_bank(){
        return view();
    }
    /**
     * update_qly_bank 修改公账银行卡信息
     */
    public function update_qly_bank()
    {
        $param = $this->param;
        $data = [
            'receiver' => $param['receiver'],
            'bank_name' => $param['bank_name'],
            'bank_card' => $param['bank_card'],
        ];
        if (isset($param['bank_icon'])) {
            $data['bank_icon'] = $param['bank_icon'];
        }
        $bank_model = CompanyBankModel::self();
        $ret = $bank_model->where('id',$param['id'])->update($param);
        if ($ret==1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    /**
     * remove_qly_bank 禁用开启银行卡
     */
    public function remove_qly_bank(){
        $param = Request::instance()->param();
        $bank_model = CompanyBankModel::self();
        $bank_info = $bank_model->field('status')->where('id',$param['id'])->find();
        $status = abs($bank_info['status'] - 1);
        $ret = $bank_model->where('id',$param['id'])->setField('status',$status);
        if($ret == 1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    /**
     * store_qly_bank 保存新增公账银行
     */
    public function store_qly_bank(){
        if(request()->isAjax()){
            $params = Request::instance()->param();
            $bank_model = CompanyBankModel::self();
            $ret = $bank_model->insert($params);
            if ($ret == 1) {
                return $this->response(200);
            } else {
                return $this->response(201);
            }
        }
    }
}
