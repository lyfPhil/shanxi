<?php
namespace app\main\model;

use think\Model;

class CustomerServiceModel extends Model{

    protected $name = 'sx_customer_service';
    protected $connection = 'db.cms';

    public static function self(){
        return new self();
    }

    public function getCustomerConnection(){
        $field = 'type, icon, method, connect, remark';
        return $this->field($field)->where('status', 1)->select();
    }
}