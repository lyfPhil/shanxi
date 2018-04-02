<?php
namespace app\main\model;

use app\main\model\CustomerModel;
use think\Model;

class PrevidModel extends Model{

    protected $name = "tab_previd";
    protected $connection = 'db.main';

    public static function self(){
    	return new self();
    }
    public function getVid($type=0)
    {
        $where=['type'=>$type, 'st'=>0];
        $info = $this->lock(true)->where($where)
            ->find();
        $data =['st'=>1];
        $this->where('id', $info['id'])->update($data);
        return $info['vid'];
    }
    public function lockVid($vid, $user_id)
    {
        $data=[
            'user_id'=>$user_id,
            'st' => 2,
        ];
        $where = [
            'vid'=>$vid,
            'st'=>1,
        ];
        return $this->where($where)->update($data);
    }

    public function unlockVid($vid)
    {
        $data=[
            'st' => 0,
            'user_id' => 0
        ];
        $where = [
            'vid'=>$vid,
            'st'=>1,
        ];
        return $this->where($where)->update($data);
    }
    /**
     * bindvid 绑定靓号
     * 绑定靓号，普通vid不需要解绑,为了解绑的时候有个记录
     * @param  [type] $vid     [description]
     * @param  [type] $user_id [description]
     */
    public function bindVid($vid, $user_id){
        $where = [
            'vid' => $vid,
            'st'  => 0,
            'user_id' => 0,
            'type' => 1
        ];
        $update = [
            'user_id' => $user_id,
            'st' => 2
        ];
        $this->where($where)->update($update);
    }
    /**
     * unbindVid 撤销靓号(撤销的同时把原本的vid)
     * 撤销靓号，将原本的普通vid赋予给tab_user里的vid
     * @return [type] [description]
     */
    public function unbindVid($where){
        $where = [
            'vid' => $where['vid'],
            'user_id' => $where['user_id'],
            'st' => 2,
            'type' => ['in',[1,2]]
        ];
        $update = [
            'user_id' => 0,
            'st'  => 0
        ];
        $this->startTrans();
        try {
            $this->where($where)->update($update);
            $origin = $this->field('vid')->where(['user_id' => $where['user_id'],'type' => 0])->find();
            $customer_model = CustomerModel::self();
            $flag = $customer_model->where('id',$where['user_id'])->column('flag');
            $new_flag = $flag | !2;
            $customer_model->where('id',$where['user_id'])->setField(['vid'=>$origin['vid'],'flag' => $new_flag]);
            $this->commit();
            return 0;
        } catch (Exception $ex) {
            $this->rollback();
        }
    }
}
