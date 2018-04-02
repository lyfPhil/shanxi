<?php

namespace app\main\model;

use app\main\model\GoodsModel;
use app\main\model\CustomerModel;
use app\v1\service\FormatService;
use think\Model;
use think\Env;
class AdvModel extends Model{
    protected $name = "tab_adv";
    protected $connection = 'db.main';

    public static function self(){
        return new self();
    }
    /**
     * 获取广告分页列表 后台使用
     * @param array $map 筛选条件
     * @param string $order 排序条件
     * @param int $limit 分页大小
     * @param array $params 额外参数
     * @return object
     */
    public function getWebCoverPageList($map, $order, $limit, $params=[])
    {
        $where['pos_id'] =array('not in','18,19,20,21,22');
        $list = $this->field('id, pos_id, title, status, ad_type,sort, start_time, end_time')
            ->where($where)
            ->where($map)
            ->order($order)
            ->paginate($limit, false, ['query'=>$params]);
        return $list;
    }
    public function getAppCoverPageList($map, $order, $limit, $params=[])
    {
        $where['pos_id'] = array('in','10,20,21,22,60');
        $list = $this->field('id, pos_id, title, status, ad_type,sort, start_time, end_time')
            ->where($where)
            ->where($map)
            ->order($order)
            ->paginate($limit, false, ['query'=>$params]);
        return $list;
    }
    public function getHotWordPageList($map, $order, $limit, $params=[])
    {
        $where['pos_id'] = array('in','18,19');
        $list = $this->field('id, pos_id, title, url, status, sort, start_time, end_time')
            ->where($where)
            ->where($map)
            ->order($order)
            ->paginate($limit, false, ['query'=>$params]);
        return $list;
    }

    public function getPosAd($pos_id, $ad_type){
        $now = time();
        $map = [
            'status'=>1,
            'start_time'=>['<=', $now],
            'end_time'=>['>', $now],
        ];
        $this->where($map);
        if (is_array($pos_id))
        {
            $this->where('pos_id', 'in', $pos_id);
        }
        else
        {
            $this->where('pos_id', $pos_id);
        }
        if (is_array($ad_type))
        {
            $this->where('ad_type', 'in', $ad_type);
        }
        else
        {
            $this->where('ad_type',  $ad_type);
        }
        return $this->order('sort desc')->select();
    }
    
    public function getGoodsAdv($goods_temp,$goods_id){
        $gm = GoodsModel::self();
        $goods = $gm->getGoodsByIds($goods_id);
        $sell_id = [];
        foreach($goods as $item){
            $sell_id[]=$item['user_id'];
        }
        $adv_list = [];
        $cm = CustomerModel::self();
        $seller = $cm->getSellerBatch($sell_id);
        foreach($goods_temp as $val){
            $id = $val['url']['id'];
            if(in_array($id, array_keys($goods)))
            {
                $list['pic']    = $val['icon_url'] ? $val['icon_url'] : $goods[$id]['pic_url'];
                $list['title']  = $val['title']?$val['title']:$goods[$id]['title'];
                $list['ad_type'] = $val['ad_type'];
                $list['url'] = [
                    'id'            =>$id,
                    'type'          =>$val['url']['type'],
                    'goods_title'   =>$goods[$id]['title'],
                    'goods_type'    =>$goods[$id]['goods_type'],
                    'goods_type_name'=> FormatService::formatGoodsTypesName($goods[$id]['goods_type']),
                    'price'         =>$goods[$id]['price'],
                    'old_price'     =>$goods[$id]['old_price'],
                    'seller_name'   =>$seller[$goods[$id]['user_id']]['nickname'],
                    'seller_id'     =>$goods[$id]['user_id'],
                    'avatar'        => $seller[$goods[$id]['user_id']]['avatar'],
                    'deposit'       =>$seller[$goods[$id]['user_id']]['deposit']>0?1:0
                ];
                $adv_list[]=$list;
            }    
        }
        return $adv_list;
    }
    
    public function getSellerAdv($seller_temp,$seller_id){
        $cm   = CustomerModel::self();
        $sell = $cm->getSellerBatch($seller_id);
        $sell_list = [];
        foreach($seller_temp as $val){
            $id = $val['url']['id'];
            if(in_array($id,array_keys($sell))){
                $list['pic']    = $val['icon_url']?$val['icon_url']:$sell[$id]['avatar'];
                $list['title']  = $val['title']?$val['title']:$sell[$id]['nickname'];
                $list['ad_type'] = $val['ad_type'];
                list($score_type,$score_cnt) = FormatService::formatReputation($sell[$id]['seller_reputation']);
                $list['url'] = [
                    'id'            =>$id,
                    'type'          =>$val['url']['type'],
                    'seller_name'   =>$sell[$id]['nickname'],
                    'score_type'    =>$score_type,
                    'score_cnt'     =>$score_cnt,
                    'start_rating'  =>$sell[$id]['start_rating'],
                    'order_num'     =>$sell[$id]['order_num'],
                    'deposit'       =>$sell[$id]['deposit']>0?1:0
                ];
                $sell_list[]=$list;
            }
        }
        return $sell_list;
    }
    public function getGameStrategyAdv($strategy_temp,$strategy_id){
        $strategy = [];
        foreach ($strategy_temp as $val) {
            $list['pic']    = $val['icon_url'];
            $list['title']  = $val['title'];
            $list['ad_type']= $val['ad_type'];
            $list['url']    = $val['url'];
            $list['url']    = [
                'id'        =>$val['url']['id'],
                'type'      =>$val['url']['type'],
                'title'     =>$val['url']['title'],
                'keywords'  =>$val['url']['keywords'],
                'detail_url'=>Env::get('web.host_m').'/strategy/detail/'.$val['url']['id'],
            ];
            $strategy[]     = $list;
        }
        return $strategy;
    }
    public function getGameAdv($game_temp,$game_id){
        $game_list = [];
        $game_model = GameModel::self();
        $game = $game_model->getGameIconBatch($game_id);
        foreach($game_temp as $val){
            $list['pic']    = $val['icon_url'] ? $val['icon_url'] : $game[$val['url']['id']];
            $list['title']  = $val['title'];
            $list['ad_type']= $val['ad_type'];
            $list['url']    = [
                'id'        => $val['url']['id'],
                'type'      => $val['url']['type'],
                'text'      => $val['url']['text'],
                'icon'      => $game[$val['url']['id']],
            ];
            $game_list[]         = $list;
        }
        return $game_list;
    }
    
    public function getPromote($promote_temp){
        $promote = [];
        foreach($promote_temp as $val){
            $list = [
                'pic'       =>$val['icon_url'],
                'title'     =>$val['title'],
                'url'       =>$val['url'],
                'ad_type'   =>$val['ad_type'],
                'start_time'=>$val['start_time'],
                'end_time'  =>$val['end_time'],
            ];
            $promote[]=$list;
        }
        return $promote;
    }
    /*
     * 只跟位置有关(一个位置可能有游戏，商品，卖家的话就要做处理)
     */
    public function getAdv($it){
        $game       = [];
        $goods      = [];
        $seller     = [];
        $promote    = [];
        $strategy   = [];
        foreach($it as $val){
            if($val['ad_type']!=4){
                $val['url']         = json_decode($val['url'],true);
                if($val['ad_type']==2){
                    $goods_id[]     = $val['url']['id'];
                    $goods_temp[]   = $val;
                }elseif($val['ad_type']==1){
                    $game_temp[]    = $val;
                    $game_id[]      = $val['url']['id'];
                }elseif($val['ad_type']==5){
                    $seller_id[]    = $val['url']['id'];
                    $seller_temp[]  = $val;
                }elseif($val['ad_type']==3){
                    $strategy_id[]  = $val['url']['id'];
                    $strategy_temp[]= $val;
                }
            }else{
                $promote_temp[]=$val;
            }
        }
        if(!empty($goods_temp)){
            $goods  = $this->getGoodsAdv($goods_temp,$goods_id);
        }
        if(!empty($seller_temp)){
            $seller = $this->getSellerAdv($seller_temp,$seller_id);
        }
        if(!empty($game_temp)){
            $game   = $this->getGameAdv($game_temp,$game_id);
        }
        if(!empty($promote_temp)){
            $promote = $this->getPromote($promote_temp);
        }
        if(!empty($strategy_temp)){
            $strategy = $this->getGameStrategyAdv($strategy_temp,$strategy_id);
        }
        $adv = array_merge($promote,$goods,$seller,$game,$strategy);
        return $adv;
    }
    
    public function getPosAdByPos($pos_id){
        $now = time();
        $map = [
            'status'    =>1,
            'start_time'=>['<=', $now],
            'end_time'  =>['>', $now],
            'pos_id'    =>$pos_id
        ];
        return $this->where($map)->select();
    }
    
    public function getPosAdByType($ad_type){
        $now = time();
        $map = [
            'status'=>1,
            'start_time'=>['<=', $now],
            'end_time'=>['>', $now],
        ];
        $this->where($map);
        
        if (is_array($ad_type))
        {
            $this->where('ad_type', 'in', $ad_type);
        }
        else
        {
            $this->where('ad_type',  $ad_type);
        }
        return $this->select();
    }
    
    public function addPosAd($data)
    {
        $ad_type = $data['ad_type'];
        if (in_array($ad_type, [0, 1, 2, 3, 5]))
        {
            $url=$data['url'];
            $data['url'] = json_encode($url);
        }
        // 保存图片 转换通用斜杠符号
        if (isset($data['icon_url'])){
            $data['icon_url'] = str_replace("\\", "/", $data['icon_url']);
        }
        return $this->save($data);
    }

    public function editAdv($data)
    {
        $ad_type = $data['ad_type'];
        if (in_array($ad_type, [0, 1, 2, 3, 5]))
        {
            $url=$data['url'];
            $data['url'] = json_encode($url);
        }
        // 保存图片 转换通用斜杠符号
        if (isset($data['icon_url'])){
            $data['icon_url'] = str_replace("\\", "/", $data['icon_url']);
        }
        return $this->where('id',$data['id'])->update($data);
    }
    public function remove($ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
        return $this->delete();
    }
    public function updateArray($data,$ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
        return $this->update($data);
    }

}
