<?php
namespace app\admin\model;

use think\Config;
use think\Model;
use think\Session;

/**
 * 操作日志记录
 */
class AccessModel extends Model
{
    protected $name = 'sx_auth_access';
    protected $connection = 'db.cms';

    public static function self(){
        return new self();
    } 

    public function getAccess($group_id)
    {
        return $this->where(['group_id'=>$group_id])->column('rule_id');
    }

    public function store( $group_id, $data )
    {
        if(empty($data)) {
            $this->where('group_id', $group_id)->delete();
            return true;
        }
        $ret = false;
        $this->startTrans();
        try{
            $this->where('group_id', $group_id)->delete();
            $insertData = [];
            foreach($data as $val) {
                $insertData[] = ['group_id'=>$group_id, 'rule_id'=>$val];
            }
            $this->insertAll( $insertData );
            $ret = $this->commit();
        }catch (\Exception $e) {
            $this->rollback();
        }
        return $ret;
    }
    public function remove($group_id)
    {
        return $this->where('group_id', $group_id)->delete();
    }



}
