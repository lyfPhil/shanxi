<?php
namespace app\home\model;
use think\Model;
class Conf extends Model{
    public function getAllconf(){
        $confdata=$this->field('enname,cnname')->select();
        return $confdata;
    }
}



