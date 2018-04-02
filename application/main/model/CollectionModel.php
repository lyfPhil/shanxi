<?php

namespace app\main\model;

use app\main\model\GoodsModel;
use app\main\model\GameModel;
use app\main\model\ArticleModel;
use app\main\model\CustomerModel;
use app\main\model\RecordModel;
use app\v1\service\FormatService;
use think\Model;
use think\Env;

class CollectionModel extends Model{
    protected $name = "tab_collection";
    protected $dateFormat = false;
    protected $connection = 'db.main';

    public static function self(){
        return new self();
    }
    public function objectIsExist($data){
        $map = [
          'status'  =>1,
          'id'      =>$data['object_id']
        ];
        switch($data['type']){
            case COLLECT_TYPE_GOODS:
                $res = GoodsModel::self()->field('id, user_id, pic_url, title, goods_type, game_name, server_name')->where($map)->find();
            break;
            case COLLECT_TYPE_GAME:
                $res = GameModel::self()->field('id,game_name,icon')->where($map)->find();
            break;
            case COLLECT_TYPE_STRATEGY:
                $res = ArticleModel::self()->field('id')->where($map)->find();
            break;
            case COLLECT_TYPE_SELLER:
                $res = CustomerModel::self()->field('id')->where($map)->find();
            break;
        }
        if (empty($res)) {
           return 400;
        }
        return $res;
    }

    public function isCollect($data){
        $exist = $this->where($data)->find();
        if ($exist) {
            return 400;
        }
        return 0;
    }

    public function addCollection($data,$object){
        $data['collect_time'] = time();
        if ($data['type'] == COLLECT_TYPE_GOODS || $data['type'] == COLLECT_TYPE_GAME) {
            if ($data['type'] == COLLECT_TYPE_GOODS) {
                $record['type'] = 2;
                $record['user_id'] = $data['user_id'];
                $record['object_id'] = $object['id'];
                $record['to_user_id'] = $data['to_user_id'];
                $object['server_name'] = $object['game_name'].'/'.$object['server_name'];
                unset($object['game_name']);
            } else {
                $record['type'] = 1;
                $record['user_id'] = $data['user_id'];
                $record['object_id'] = $object['id'];
            }
            $record['record'] = $object;
            $this->startTrans();
            try{
                $record_model = RecordModel::self();
                $record_model->addRecord($record);
                $this->insert($data);
                $this->commit();
                return 0;
            } catch (Exception $ex) {
                $this->rollback();
                throw Exception($ex);
            }
        }
        $this->insert($data);
        return 0;
    }

    public function delCollection($data){
        $this->where($data)->delete();
        $del_record['user_id'] = $data['user_id'];
        $del_record['object_id'] = $data['object_id'];
        switch ($data['type']) {
            case COLLECT_TYPE_GOODS:
                $del_record['type'] = 2;
            break;
            case COLLECT_TYPE_GAME:
                $del_record['type'] = 1;
            break;
            default:
            return 0;
        }
        RecordModel::self()->where($del_record)->setField('status',0);
        return 0;
    }
    public function getCollectList($where,$page){
        $cnt = $this->where($where)->count();
        $this->order('id desc');
        switch($where['type']){
            case COLLECT_TYPE_GOODS:
                $collect = $this->getByGoods($where,$page);
            break;
            case COLLECT_TYPE_GAME:
                $collect = $this->getByGame($where, $page);
            break;
            case COLLECT_TYPE_STRATEGY:
                $collect = $this->getByStrategy($where,$page);
            break;
            case COLLECT_TYPE_SELLER:
                $collect = $this->getBySeller($where,$page);
            break;
        }
        return [$cnt,$collect];
    }

    public function getGameCollect($where){
        $this->alias('a')
            ->field('a.id,a.object_id,a.type,b.game_name,b.type as game_type,b.icon')
            ->join('tab_game b','a.object_id=b.id')
            ->where('a.type',$where['type'])
            ->where('a.user_id',$where['user_id']);
        if (isset($where['game_type'])){
            $this->where('b.type', $where['game_type']);
        }
        $collect = $this->select();
        $lists = [];
        foreach($collect as $val){
            $val['game_id'] = $val['object_id'];
            unset($val['object_id']);
            $lists[]=$val;
        }
        return $lists;
    }

    protected function getByGoods($where,$page){
        $collect = $this->alias('a')
             ->field('a.id,a.object_id,a.type,a.collect_time,b.title,b.pic_url,b.state,b.game_name,b.goods_type,b.server_name,b.price,b.paid_cnt')
             ->join('tab_goods b','a.object_id=b.id')
             ->where('a.type',$where['type'])
             ->where('a.user_id',$where['user_id'])
             ->page($page)
             ->select();
        $collect_list = [];
        foreach($collect as $item){
            $item['pic_url'] = imgurl_add_sign($item['pic_url']);
            $item['goods_type_name'] = FormatService::formatGoodsTypesName($item['goods_type']);
            $collect_list[]=$item;
        }
        return $collect_list;
    }

    protected function getByGame($where,$page){
        $collect = $this->alias('a')
                ->field('a.id,a.object_id,a.type,a.collect_time,b.game_name,b.icon')
                ->join('tab_game b','a.object_id=b.id')
                ->where('a.type',$where['type'])
                ->where('a.user_id',$where['user_id'])
                ->page($page)
                ->select();
        $goodsmodel = GoodsModel::self();
        $goods_list = [];
        foreach($collect as $val){
            list($cnt,$goods) = $goodsmodel->getGoodsByGameId(['game_id'=>$val['object_id']],$page='1,3');
            $val['goods_cnt'] = $cnt;
            $val['goods_list'] = $goods;
            $goods_list[]=$val;
        }
        return $goods_list;
    }

    protected function getByStrategy($where,$page){
        $collect = $this->alias('a')
                   ->field('a.id,a.object_id,a.type,a.collect_time,b.title,b.game_id,b.create_at,b.image,b.description,b.pv,b.comment_cnt,b.source,b.status')
                   ->join('pp_cms.sx_article b','a.object_id=b.id')
                   ->where('a.type',$where['type'])
                   ->where('a.user_id',$where['user_id'])
                   ->page($page)
                   ->select();
        $lists = [];
        foreach($collect as $item)
        {
            $list_item = [
                    'id'        => $item['id'],
                    'object_id' => $item['object_id'],
                    'title'     => $item['title'],
                    'pic'       => imgurl_add_sign($item['image']),
                    'desc'      => $item['description'],
                    'from'      => $item['source'],
                    'game_id'   => $item['game_id'],
                    'icon'      => FormatService::formatGameicon($item['game_id']),
                    'collect_time'=> $item['collect_time'],
                    'view'      => $item['pv'],
                    'comment'   => $item['comment_cnt'],
                    'status'    => $item['status'],
                    'detail_url'=> Env::get('web.host_m').'/strategy/detail/'.$item['object_id'],
                ];
            $lists[] = $list_item;
        }
        return $lists;
    }
    protected function getBySeller($where,$page){
        $collect = $this->alias('a')
                   ->field('a.id,a.object_id,a.type,a.collect_time,b.id,b.nickname,b.avatar,b.seller_reputation,b.order_num,b.start_rating,b.deposit')
                   ->join('tab_user','a.object_id=b.id')
                   ->where('a.type',$where['type'])
                   ->where('a.user_id',$where['user_id'])
                   ->page($page)
                   ->select();
        $seller_list = [];
        foreach($collect as $val){
            list($val['score_type'],$val['$score_cnt']) = FormatService::formatReputation($val['seller_reputation']);
            unset($val['seller_reputation']);
            $seller_list[]=$val;
        }
        return $seller_list;
    }

    public function getCollectCnt($user_id){
        $cnt = $this->field('type,count(id) as cnt')->where('user_id',$user_id)->group('type')->select();
        return $cnt;
    }
}
