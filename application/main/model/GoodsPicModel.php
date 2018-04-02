<?php

namespace app\main\model;

use think\Model;

class GoodsPicModel extends Model{
    protected $name = "tab_goods_pic";
    protected $connection = 'db.main';
    protected $dateFormat = false;//这个关闭自动转时间戳
    
    public static function self(){
        return new self();
    }
    
    /*
     * 根据goods_id查询图片并返回
     */
    public function getPicByGoodsId($goods_id){
        $pic      = [];
        $pic_temp = $this->field('goods_id,url')->where(['goods_id'=>$goods_id])->select();
        $cnt = 0;
        foreach($pic_temp as $val){
            $pic[] = imgurl_add_sign($val['url']);
            $cnt  += 1;
        }
        return [$pic,$cnt];
    }
    
    /*
     * 添加图片
     */
    public function addGoodsPicByGoodsId($goods_id,$pic){
        $pic_arr = [];
        $now = time();
        foreach($pic as $val){
            $pic_arr[] = [
                'goods_id'   =>$goods_id,
                'url'        =>$val,
                'create_time'=>$now
            ];
        }
        $this->insertAll($pic_arr);
    }
    
    /*
     * 更新图片，先删除再添加,暂时先这样处理
     */
    public function updateGoodsPicByGoodsId($goods_id,$pic){
        $this->deleteGoodsPicByGoodsId($goods_id);
        $this->addGoodsPicByGoodsId($goods_id, $pic);
    }
    /*
     * 删除图片
     */
    protected function deleteGoodsPicByGoodsId($goods_id){
        $this->where(['goods_id'=>$goods_id])->delete();
    }
}