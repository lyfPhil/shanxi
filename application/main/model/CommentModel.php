<?php
namespace app\main\model;

use app\main\model\CustomerModel;
use app\v1\service\FormatService;
use app\main\model\RecordModel;
use think\Model;

class CommentModel extends Model{
    protected $name = "tab_comment";
    protected $connection = 'db.main';
    protected $dateFormat = false;
    public static function self(){
        return new self();
    }

    public function getCommentsCnt($table_type,$where)
    {
        $this->where(['table_type'=>$table_type, 'status'=>1]);
        $this->where($where);
        return $this->count();
    }

    public function getTwoGoodsComments($table_type,$object_id)
    {
        $cnt = $this->getCommentsCnt($table_type,['object_id'=>$object_id, 'parent_id'=>0]);
        $list = [];
        if ($cnt > 0)
        {
            $map = [
                'table_type'=> $table_type,
                'object_id' => $object_id,
                'parent_id' => 0,
                'status' => 1
            ];
            $it = $this-> field('id,object_id,create_time,nick_name,start,content')
                -> where($map)
                -> order('id desc')
                -> limit(2)
                -> select();
            foreach ($it as $val) {
                $val['nick_name'] = FormatService::ShieldInfo($val['nick_name'], 'nickname');
                $list[] = $val;
            }
        }
        return [$cnt, $list];
    }

    public function sellerComment($page='1,5',$where){
        $seller = CustomerModel::self()->getOneUserInfo($where['to_user_id'],'seller_status');
        $map['to_user_id'] = $where['to_user_id'];
        if ($seller['seller_status'] == 1) {
            $map['parent_id'] = 0;
        } else {
            $map['parent_id'] = ['<>',0];
        }
        if (isset($where['start'])) {
            $map['start'] = $where['start'];
        }
        $cnt = $this->getCommentsCnt(TABLE_TYPE_GOODS, $map);
        $lists = [];
        $map['table_type'] = TABLE_TYPE_GOODS;
        $map['status'] = 1;
        if ($cnt > 0) {
              $lists = $this->where($map)
                    ->field('id,object_id,user_id,parent_id,create_time,nick_name,start,content')
                    ->order('create_time desc')
                    ->page($page)
                    ->select();
        }
        return [$cnt, $lists];
    }

    private function _handelSellerReputationAndStartRating($data){
        $cm = CustomerModel::self();
        $field = 'id, start_rating, seller_reputation';
        $seller = $cm->getOneUserInfo($data['to_user_id'], $field);
        $com_count = $this->where(['to_user_id'=>$data['to_user_id'],'table_type'=>2,'parent_id'=>0])->count();
        if ($data['start'] >= 4) {
            $update['seller_reputation'] = $seller['seller_reputation'] +1; //信誉加1
        } elseif ($data['start'] < 3) {
            $update['seller_reputation'] = $seller['seller_reputation'] - 1;//信誉减1
        }
        $update['start_rating'] = ($com_count*$seller['start_rating']+$data['start'])/($com_count+1);//星级评分
        $cm->where('id',$data['to_user_id'])->update($update);
    }

    private function _handleGoodsStartRating($data){
        $gm = GoodsModel::self();
        $goods = $gm->field('id,start_rating')->where('id',$data['object_id'])->find();
        $count = $this->where(['object_id'=>$data['object_id'],'table_type'=>2,'parent_id'=>0])->count();
        $goods_update['start_rating'] = ($count*$goods['start_rating']+$data['start'])/($count+1);
        $gm->where('id',$data['object_id'])->update($goods_update);
    }

    private function _addRecord($data){
        $record['user_id'] = $data['user_id'];
        $record['to_user_id'] = $data['to_user_id'];
        $record['type'] = 5;
        $record['record'] = [
            'start'=>$data['start'],
            'content'=>$data['content'],
            'nickname'=> FormatService::ShieldInfo($data['nick_name'], 'nickname')
        ];
        $record['object_id'] = $data['object_id'];
        RecordModel::self()->addRecord($record);
    }

    public function addComment($data){
        $data['create_time'] = time();
        $data['status'] = 1;
        $data['table_type'] = 2;
        $this->startTrans();
        try{
                //首先是卖家信誉和卖家星级评分的处理
                $this->_handelSellerReputationAndStartRating($data);
                //商品星级处理
                $this->_handleGoodsStartRating($data);
                //记录
                $this->_addRecord($data);
                $this->insert($data);
                OrderModel::self()->orderMove($data['order_id'],ORDER_REPLY);
                $this->commit();
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
        return 0;
    }

    public function addReply($data){
        $record['user_id'] = $data['user_id'];
        $record['to_user_id'] = $data['to_user_id'];
        $record['object_id'] = $data['object_id'];
        $record['type'] = 5;
        $record['record'] = [
            'content' => $data['content'],
            'nickname'=> FormatService::ShieldInfo($data['nick_name'], 'nickname'),
        ];
        $this->startTrans();
        try{
            RecordModel::self()->addRecord($record);
            OrderModel::self()->orderMove($data['order_id'],ORDER_COMPLETE);
            $this->insert($data);
            $this->commit();
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
        return 0;
    }

    public function getStartLists($where){
        $start = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
        $start_demo = $this->field('count(id) as cnt,start')
                     ->where('parent_id',0)
                     ->where($where)
                     ->group('start')->select();
        foreach($start_demo as $val){
            $start[$val['start']] = $val['cnt'];
        }
        $start_list = [
            'star_5'=>$start[5],
            'star_4'=>$start[4],
            'star_3'=>$start[3],
            'star_2'=>$start[2],
            'star_1'=>$start[1],
        ];
        return $start_list;
    }
    /*
     * 无限级评论,别人的代码
     */
    public  function getCommlist($object,$page='1,24',$where,$parent_id = 0,&$result = array()){
        if($parent_id==0&&isset($where['start'])){
            $this->where('start',$where['start']);
        }
        $arr = $this->where('table_type',2)
                ->page($page)
                ->field('id,parent_id,object_id,user_id,create_time,nick_name,start,content')
                ->where(['object_id'=>$object,'parent_id'=>$parent_id])
                ->order('create_time desc')->select();
        if(empty($arr)){
            return array();
        }
        foreach ($arr as $cm) {
            $thisArr=&$result[];
            $cm['reply'] = $this->getCommlist($cm['object_id'],$page,$where,$cm['id'],$thisArr);
            $thisArr = $cm;
        }
        return $result;
   }
   /**
    * 获取商品分页列表 后台使用
    * @param type $map
    * @param type $order
    * @param type $limit
    * @param type $params
    * @return type
    */
   public function getCommentPageList($map, $order, $limit, $params=[])
    {
        $list = $this->alias('c')
            ->field('c.*, a.title as goods_name')
            ->join('tab_goods a','a.id = c.object_id','LEFT')
            ->where($map)
            ->order($order)
            ->paginate($limit, false, ['query'=>$params]);
        return $list;
    }
    /**
     * 获取商品评论详情
     * @param int $id
     * @return
     */
    public function getGoodsCommentInfo($id)
    {
        $item = $this->alias('c')
            ->field('c.*, a.title as goods_name')
            ->join('tab_goods a','a.id = c.object_id','LEFT')
            ->where('c.id', $id)
            ->find();
        return $item;
    }
    /**
     * 批量删除商品评论
     * @param array $ids id数组
     * @return int
     */
    public function remove($ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
        return $this->delete();
    }
    /**
     * 批量更新
     * @param array $map 条件
     * @param array $ids 操作的数组ID
     * @return true/false
     */
    public function updateArray($map, $ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);

        return $this->update($map);
    }
}
