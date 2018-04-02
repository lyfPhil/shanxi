<?php
namespace app\main\model;
use think\Model;

class RecordModel extends Model{
    
    protected $name = 'tab_user_log_record';
    protected $connection = 'db.main';
    protected $dateFormat = false;
    
    public static function self(){
        return new self();
    }
    
    public function addRecord($data){
        $data['record'] = json_encode($data['record']);
        $data['create_time'] = strtotime(date('Y-m-d',time()));
        $map = [
            'user_id' => $data['user_id'],
            'type'    => $data['type'],
            'object_id' => isset($data['object_id']) ? $data['object_id'] : 0,
            'create_time'=> $data['create_time']
        ];
        if ($this->where($map)->find()) {
            $this->where($map)->setField(['status'=>1,'create_time'=>$data['create_time'],'record'=>$data['record']]);
        } else {
            $this->insert($data);
        }
        return 0;
    }
    
    public function getRecordList($user_id){
        $timeline = $this->where('user_id|to_user_id',$user_id)->order('create_time desc')->select();
        $timeline_list = [];
        foreach ($timeline as $val) {
            $val['record'] = json_decode($val['record'],true);
            $timeline_list[$val['create_time']]['create_time'] = $val['create_time'];
            $timeline_list[$val['create_time']]['record'][$val['type']]['title'] = $val['type'];
            $timeline_list[$val['create_time']]['record'][$val['type']]['detail'][] = $val;
        }
        //对象里转换为数组
        foreach ($timeline_list as $val) {
            foreach($val['record'] as $row) {
                $record[] = (object)$row;
            }
            $val['record'] = $record;
            $list[] = (object)$val;
        }
        return $list;
    }
}