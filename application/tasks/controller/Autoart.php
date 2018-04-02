<?php
namespace app\tasks\controller;

use app\common\controller\CliBase;
use app\main\model\ArticleModel;

class Autoart extends CliBase
{
    public function autoAudit()
    {
        $am = ArticleModel::self();
        $update = time();
        $now = $update -($update %86400);
        $pubed = $am->field('id, create_at')
            ->where('status', 1)
            ->order('id desc')
            ->find();
        if ($pubed && $pubed['create_at'] > $now)
        {
            return;
        }
        //$last_id = $pubed['id'];
        $lists = $am->field('id, create_at, update_at, status')
            ->where('status', 0)
         //   ->where('id', '>', $last_id)
            ->order('id asc')
            ->select();
        $data = [];
        $cnt = 0;
        foreach ($lists as $item)
        {
            $_at = $now + $item['create_at']%86400;
            $create_at =  $_at>$update ? $update: $_at;
            $data[]=[
                    'id'=> $item['id'],
                    'create_at'=>$create_at,
                    'update_at'=>$update,
                    'status'=>1,
                ];       
            $cnt ++;
            if ($cnt >= 10)
            {
                break;
            }
        }

        if ($cnt > 0)
        {
            $am->saveAll($data);
        }
        return;
    }
}
