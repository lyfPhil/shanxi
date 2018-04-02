<?php
namespace app\main\service;

use think\Db;
use app\common\service\QueueService;

class CommService {
    public static function getCover($cover_id, $field = null){
        if(empty($cover_id)){
            return false;
        }
        $picture =Db::table('tab_picture')->where(array('status'=>1))->getById($cover_id);
        if($field == 'path'){
            if(!empty($picture['url'])){
                $picture['path'] = $picture['url'];
            }else{
                $picture['path'] = $picture['path'];
            }
        }
        return empty($field) ? $picture : $picture[$field];
    }

    public static function setGoodsSearch($action, $ids)
    {
        $job = '\app\tasks\job\SearcherJob';
        $args = [
            'model'=>'goods',
            'action'=>$action,
            'ids'=>$ids,
        ];
        //$jobId = QueueService::push($job, $args);
    }

    public static function setArticleSearch($action, $ids)
    {
        $job = '\app\tasks\job\SearcherJob';
        $args = [
            'model'=>'article',
            'action'=>$action,
            'ids'=>$ids,
        ];
        //$jobId = QueueService::push($job, $args);
    }
}
