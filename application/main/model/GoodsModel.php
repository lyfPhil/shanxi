<?php
namespace app\main\model;

use app\main\model\GameModel;
use app\main\model\GameServiceModel;
use app\main\model\CertificationModel;
use app\main\model\GoodsPicModel;
use app\v1\service\FormatService;
use app\main\service\CommService;
use think\Model;
use think\Db;

class GoodsModel extends Model{
    protected $name = "tab_goods";
    protected $connection = 'db.main';
    protected $resultType = "collection";
    protected $dateFormat = false;//这个关闭自动转时间戳

    public static function self(){
        return new self();
    }
    /*
     * 单个商品信息
     */
    public function getOneGoodsByCondition($where,$field='*'){
        return $this->field($field)->where($where)->find();
    }

    public function getGoodsDetail($where){
        $goods = $this->field('id,title,text,goods_type,price,game_id,game_name,server_id,server_name,stock,ext_attr')
                 ->where($where)->find();
        if(!$goods){
            return 400;
        }
        $goods['ext_attr'] = json_decode($goods['ext_attr'],true);
        if ($goods['ext_attr'] != NULL) {
            foreach($goods['ext_attr'] as $key => $val) {
                $goods[$key] = $val;
            }
        }
        unset($goods['ext_attr']);
        $goods['goods_type_name'] = FormatService::formatGoodsTypesName($goods['goods_type']);
        $pic_model = GoodsPicModel::self();
        list($pic_list,$cnt) = $pic_model->getPicByGoodsId($where['id']);
        $goods['goods_pic'] = $pic_list;
        return $goods;
    }

    public function getGoodsByGameId($where, $page='1,5')
    {
        $map=[
            'state' => 1,
            'status'=> 1,
            'stock' => ['>', 0],
        ];
        $cnt=$this->where($where)->where($map)->count();
        $it = [];
        if ($cnt > 0)
        {
            $it = $this->where($where)
            ->field('id, title, pic_url, price, old_price,unit, game_id, game_name, server_name,paid_cnt,status')
            ->where($map)
            ->order('id desc')
            ->page($page)
            ->select();
        }
        return [$cnt, $it];
    }

    public function checkGoodsBase($param){
        $base = validate('Goodbase');
        if(!$base->check($param)){
            return $base->getError();
        }
        $data = [
            'price'     =>  $param['price'],
            'old_price' =>  $param['price'],
            'unit'      =>  1,
            'text'      =>  $param['text'],
            'title'     =>  $param['title'],
            'deliver_id'=>  $param['deliver_id'],
            'goods_type'=>  $param['goods_type'],
            'game_id'   =>  $param['game_id'],
            'server_id' =>  $param['server_id'],
            'stock'     =>  $param['stock'],
            'url'       =>  $param['url']
        ];
        return $data;
    }

    public function checkExtAttr($param){
        $type = FormatService::formatGoodsTypeToValidate($param['goods_type']);
        $extend = validate($type);
        if(!$extend->check($param)){
            return $extend->getError();
        }
        $ext_param = $extend->extattr();
        $data['ext_attr'] = [];
        foreach($param as  $key=>$it){
            if(in_array($key,$ext_param)){//相应类型的字段有时才加
                if($it!=''){
                    $data['ext_attr'][$key]=$it;
                }else{
                    $data['ext_attr'][$key]=lang('none');//允许空的情况不填为无
                }
            }
        }
        if(!empty($data['ext_attr'])){
            $data['ext_attr'] = json_encode($data['ext_attr']);
        }
        return $data;
    }

    public function checkIsSeller($user_id){
        $status = CertificationModel::self()->getUserCertStatus($user_id);
        if(empty($status)){
            return 901100;
        }else{
            if($status['status']==0){
                return 901105;
            }elseif($status['status']==2){
                return 901101;
            }elseif($status['status']==1){
                return 0;
            }
        }
        return 0;
    }

    public function checkGame($game_id,$goods_type){
        $game = GameModel::self()
                ->field('game_name,goods_type,game_type')
                ->where(['id'=>$game_id,'game_status'=>1,'type'=>1])
                ->find();
        if(empty($game)){
            return 400;
        }
        $game_goods_type = explode(',',$game['goods_type']);
        if(!in_array($goods_type,$game_goods_type )){
            return 400;
        }
        $res['game_name'] = $game['game_name'];
        $res['game_type'] = $game['game_type'];
        $type_name = FormatService::formatGoodsTypes($goods_type);
        if(count($type_name)>1){
            return 400;
        }
        return $res;
    }

    public function checkService($server_id,$game_id){
        $service = GameServiceModel::self()
                   ->field('service_name')
                   ->where(['id'=>$server_id,'game_id'=>$game_id,'status'=>1])
                   ->find();
        if (empty($service) && $server_id != 0) {
            return 400;
        }
        if ($server_id == 0) {
            $res['service'] = lang(ALL_GAME_SERVER);
        } else {
            $res['service'] = $service['service_name'];
        }
        return $res;
    }

    public function  addGoods($data,$pic)
    {
        $now = time();
        $data['create_time'] = $now;
        $data['state']       = 1;  //审核 0:未审核 1:在售中 2:审核失败
        $data['order_number']= 'GD_'.gen_uniqSN();
        $pic_model = GoodsPicModel::self();
        $record_model = RecordModel::self();
        $this->startTrans();
        try{
            $goods_id = $this->strict(false)->insertGetId($data);
            $pic_model->addGoodsPicByGoodsId($goods_id, $pic);
            $record['record'] = [
                'id'     => $goods_id,
                'pic_url'=> $data['pic_url'],
                'title'  => $data['title'],
                'goods_type' => $data['goods_type'],
                'server_name'=>$data['game_name'].'/'.$data['server_name']
            ];
            $record['type'] = 3;
            $record['user_id'] = $data['user_id'];
            $record_model->addRecord($record);
            $this->commit();
            /*CommService::setGoodsSearch('add',$goods_id);*/
        } catch (Exception $e) {
            $this->rollback();
            throw Exception($e);
        }
        return $goods_id;
    }

    public function updateGoods($goods_id,$data,$pic)
    {
        $this->startTrans();
        try{
            $this->where('id',$goods_id)->update($data);
            $goods_pic = Db::name('tab_goods_pic');
            $goods_pic->field('id')->where('goods_id',$goods_id)->delete();
            $pic_url = [];
            $time = time();
            foreach($pic as $val){
                if ($val != '') {
                    $pic_url[] = ['goods_id' => $goods_id,'url' => $val,'create_time' => $time];
                }
            }
            if (!empty($pic_url)) {
                $goods_pic->insertAll($pic_url);
            }
            $this->commit();
            /*CommService::setGoodsSearch('update',$id);*/
            return 0;
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
        }
    }

    public function getGoodsLists($where,$order,$isset,$page)
    {
        $by = 'weight desc';
        if(isset($order['by'])){
           $by =  $this->getGoodsByOrder($order['by']);
        }
        if(isset($where['goods_id'])){
           $goods_id = $where['goods_id'];
           unset($where['goods_id']);
        }
        $map['state']  =1; //在售
        $map['status'] = 1;//未删除
        $map['stock']  = ['>',0];
        if(isset($isset['deposit'])){
            $where['b.deposit'] = ['>',0];
            $cnt = $this->alias('a')->join('tab_user b','a.user_id=b.id')->where($where)->where($map)->count();
        }else{
            $cnt = $this->where($where)->where($map)->count();
        }
        if(isset($goods_id)){
            $where['a.id'] = ['<>',$goods_id];
        }
        $it = $this->alias('a')
        ->where($where)
        ->field('b.id,a.title,a.pic_url,a.price,a.old_price,a.unit,a.goods_type,a.game_name,a.server_name,a.user_id,a.user_name,a.paid_cnt,a.start_rating,a.state,b.nickname,b.avatar,a.id,b.seller_reputation,b.deposit,b.vid,b.flag')
        ->join('tab_user b','a.user_id=b.id')
        ->where($map)
        ->order($by)
        ->page($page)
        ->select();
        return [$cnt, $it];
    }

    protected function getGoodsByOrder($order){
        if($order=='low'){
            $order = 'a.price asc';
        }elseif($order=='high'){
            $order = 'a.price desc';
        }elseif($order=='reputation'){
            $order = 'b.seller_reputation desc';
        }elseif($order=='sales'){
            $order = 'a.paid_cnt desc';
        }elseif($order=='start'){
            $order = 'a.start_rating desc';
        }elseif($order=='new'){
            $order = 'a.id desc';
        }elseif($order=='hot'){
            $order = 'a.paid_cnt desc';
        }else{
            $order = '';
        }
        return $order;
    }

    public function getByGoodsId($goods_id){
        $map = [
           'id'=>$goods_id,
           'status'=>1,
        ];
        $goods = $this->where($map)
        ->field('id,pic_url,order_number,title,goods_type,game_id,game_name,server_name,goods_type,price,unit,text,create_time,mobile,deliver_id,stock,user_id,start_rating,state,user_name,state,ext_attr')
        ->find();
        return $goods;
    }


    public function getGoods($where, $page='1,5')
    {
        $map=[
            'state'=>1,
            'status'=>1,
            'stock'=>['>', 0],
        ];
        $cnt=$this->where($where)->where($map)->count();
        $it = [];
        if ($cnt > 0)
        {
            $it = $this->where($where)
            ->field('id, title,pic_url,price,old_price,unit,goods_type,user_id,game_id,game_name,server_name,paid_cnt')
            ->where($map)
            ->order('id desc')
            ->page($page)
            ->select();
        }
        return [$cnt, $it];
    }

    public function getByState($where,$page)
    {
        $where['status'] = 1;
        $cnt = $this->where($where)->count();
        $goods = $this->field('id, title, state, pic_url, game_id, game_name, server_name, goods_type, unit, price, old_price, stock, state, paid_cnt, create_time')
             ->where($where)
             ->order('id desc')
             ->page($page)
             ->select();
        $goods_list = [];
        foreach ($goods as $val) {
            $val['goods_type_name'] = FormatService::formatGoodsTypesName($val['goods_type']);
            $goods_list[] = $val;
        }
        return [$goods_list,$cnt];
    }

    /*
     * 分组获取最新的N个商品
     * */
    public function getGroupGoods($topNum)
    {
        $subquery ='(select count(1) from tab_goods where goods_type = a.goods_type and id > a.id)';
        $it = $this->alias('a')
            ->field('a.id,a.title, a.goods_type')
            ->where('a.status', 1)
            ->where('a.goods_type', '>', 0)
            ->where("$topNum > $subquery")
            ->order('goods_type asc, id desc')
            ->select();
        return $it;
    }
    /*
     * 广告批量获取商品消息
     */
    public function getGoodsByIds($goods_id){
        $map = ['status'=>1,'state'=>1,'stock'=>['>',0]];
        $goods = $this->field('id,user_name,user_id,goods_type,title,pic_url,old_price,price')
                ->where($map)
                ->where('id','in',$goods_id)
                ->select();
        $goods_lists = [];
        foreach($goods as $val){
            $goods_lists[$val['id']]=$val;
        }
        return $goods_lists;
    }

    /**
     * checkOffShelf 检查商品是否已经全部下架和最后一个下架时间是否过了10天
     * @param  int $user_id 用户id错误返回方法大多东方闪电df
     * @return int
     */
    public function checkOffShelf($user_id)
    {
        $where['user_id'] = $user_id;
        //是否存在未下架商品
        $where['state'] = 1;
        $counts = $this->where($where)->count();
        if ($counts > 1) {
            return 400700;
        }
        //最后下架的商品是否已过10天
        $where['state'] = 3;
        $last = $this->where($where)->order('update_time desc')->field('update_time')->find();
        if ($last) {
            $check_time = $last['update_time'] + 24*3600*10;//要改为10天
            if ( time() < $check_time) {
                return 400700;
            }
        }
        return 0;
    }
    /**
     *  获取商品分页列表  管理后台使用
     * @param array $map 筛选条件
     * @param string $order 排序
     * @param int $limit 分页条数
     * @param array $params 额外参数
     * @return object $res 资源对象
     */
    public function getGoodsPageList($map,$order,$limit,$params=[])
    {
        $res = $this->field('id,title,state,deliver_id,game_name,goods_type,user_name,server_name,price,stock,create_time,over_time')
                ->where($map)
                ->order($order)
                ->paginate($limit,FALSE,['query'=>$params]);
        return $res;
    }
    /**
     * 根据条件获取商品列表的某些字段
     * 后台广告推广使用
     * @param array $where 筛选条件
     * @return array
     */
    public function getCoverGoodsList($field='*',$where)
    {
        $map = [
            'state'=>1,
            'status'=>1
        ];
        $res = $this->field($field)
                    ->where($map)
                    ->where($where)
                    ->order('id')
                    ->select();
        return $res;
    }
    //后台首页统计使用
    public function status($time)
    {
        return [
            'title'=>'发布商品数',
            'total'=>$this->count(),
            'newly'=>$this->where('create_time', '>', $time)->count(),
            'pending'=>$this->where('status',0)->count(),
        ];
    }
     /**
     * 批量更新 后台使用
     * @param array $data 更新的数据
     * @param array $ids id数组
     * @return int
     */
    public function updateArray($data, $ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);

        return $this->update($data);
    }
}
