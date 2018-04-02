<?php

namespace app\main\model;

use think\Model;

class VersionModel extends Model{

    protected $name = 'sx_version';
    protected $connection = 'db.cms';

    public static function self()
    {
        return new self();
    }

    public function getNewVersion($now_version,$type, $lang){
        if ($lang == 'th') {
            $field = 'id,new_version,type,file_url, file_url2, update_log,target_size,constraint';
        } else {
            $field = 'id,new_version,type,file_url, file_url2, update_log_en as update_log,target_size,constraint';
        }
        $new_version= $this->field($field)->where(['type'=>$type,'state'=>1])->order('id desc')->find();
        if ($now_version >= strval($new_version['new_version'])) {
            return 400900;
        } else {
            return $new_version;
        }
    }

    public function getVersionList($map,$order,$limit,$params)
    {
        $list = $this->field('*')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$params]);

        return $list;
    }
    public function remove($ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
        return $this->delete();
    }

    }
