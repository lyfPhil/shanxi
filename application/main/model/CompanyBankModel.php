<?php

namespace app\main\model;

use think\Model;
use think\Db;
Class CompanyBankModel extends Model{

    protected $name = "tab_qly_bank";
    protected $connection = 'db.main';

    public static function self(){
        return new self();
    }
    /**
     * getOneBankInfo 获取一个公账银行的信息
     * @param  array  $where 条件
     * @param  string $field 字段
     * @return array
     */
    public function getOneBankInfo($where, $field = '*'){
        $bank_info = $this->where($where)->field($field)->find();
        return $bank_info;
    }
    /**
     * getBankList 接口获取公账银行信息(所有)
     * @param  array  $where 条件
     * @param  string $field 字段
     * @param  string $order 排序
     * @return array
     */
    public function getBankList($where, $field = '*',$order = 'id desc'){
        $where['status'] = 1;
        $bank_list = $this->where($where)->field($field)->order($order)->select();
        return $bank_list;
    }
    /**
     * getBankPageList 后台银行列表分页
     * @param  array  $where 条件
     * @param  int    $limit 条数(默认10条)
     * @param  string $field 字段
     * @param  string $order 排序（状态降序，id升序）
     * @return
     */
    public function getBankPageList($where, $limit = '10', $field = '*', $order = 'status desc, id asc') {
        $bank_list = $this->where($where)->order($order)->paginate($limit);
        return $bank_list;
    }
}

