<?php
namespace app\main\model;

use think\Model;

class ConfigModel extends Model
{
    protected $name = 'tab_config';
    protected $connection = 'db.main';
    protected static $instance; 
    
    public static function self(){
        return new self();
    } 

    public function getList()
    {
        return $this->order('id desc')->paginate(10);
    }

    public function add($data)
    {
        if ($this->getConfig($data['key']))
        {
            return "Key exists";
        }
        return $this->insertGetId($data);
    }

    public function edit($data)
    {
        $info = $this->get($data['id']);
        if ($info['state'] == 1) {
            if ($info['key'] != $data['key'] || $info['type'] != $data['type']) {
                return "Not Allow Change";
            }
        }
        if ($data['key'] != $info['key'])
        {
            if ($this->getConfig($data['key']))
            {
                return "Key exists";
            }
        }

        return $this->where('id', $data['id'])->update($data);
    }

    public function getConfig($key)
    {
        return $this->where('key', $key)->find();
    }

    public function getValue($key)
    {
        return $this->where('key', $key)->value('value');
    }
    //获取配置信息
    public function getGroupList($flag)
    {
        return $this->where('flag', $flag)->order('reorder desc')->select();
    }
    //设置配置信息
    public function setValue($key,$value)
    {
        return $this->where('key',$key)->setField('value',$value);
    }
}
