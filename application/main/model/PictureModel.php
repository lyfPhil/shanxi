<?php

namespace app\main\model;

use think\Model;
use think\Upload;

/**
 * 图片模型
 * 负责图片的上传
 */
class PictureModel extends Model{
    protected $name = "tab_picture";
    protected $connection = 'db.main';

    public static function self(){
        return new self();
    }

    public function getbyMd5($md5)
    {
        return $this->where('md5', $md5)->find();
    }

    public function addPic($name, $md5, $url, $fsize)
    {
        $items = [
            'create_time'=>time(),
            'md5'=>$md5,
            'url'=>$url,
            'path'=>$name,
            'fsize'=>$fsize,
            'status'=>1,
        ];
        return $this->insertGetId($items);
    }
}
