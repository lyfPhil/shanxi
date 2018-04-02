<?php
/**
 * @Author: Marte
 * @Date:   2018-03-19 22:36:58
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-03-19 22:44:55
 */
namespace app\main\model;
use think\Model;

class CityModel extends Model
{
	protected $name = 'sx_city';
    protected $connection = 'db.cms';
    public static function self(){
        return new self();
    }

    public function getcity(){
        $city=$this->where('status',1)->select();
        return $city;
    }

}