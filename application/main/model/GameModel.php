<?php

namespace app\main\model;

use app\v1\service\FormatService;
use think\Model;
use think\Db;

class GameModel extends Model{
    protected $name = "tab_game";
    protected $connection = 'db.main';
    protected $resultSetType = 'collection';

    public static function self(){
        return new self();
    }
    //获取游戏
    public function getGameListByCondition($where,$order,$page){
        $byrecom = '';
        $byletter = '';
        if(isset($order['by'])){
            $byrecom = $this->getByOrder($order['by']);
        }
        if(isset($order['initial'])){
            $byletter = $this->getByLetter($order['initial']);
        }
        $map = ['status'=>1,'type'=>1];
        $game = $this->field('id,game_name,goods_type,icon')
                     ->where($map)
                     ->where($where)
                     ->where($byrecom)->where($byletter)
                     ->order('sort desc')->page($page)->select();
        $cnt  = $this->where($map)->where($byrecom)->where($byletter)->where($where)->count();
        return [$game,$cnt];
    }
    //获取点卡
    public function getPointCardByCondition($order,$page='1,12'){
        $byrecommend = '';
        $byletter = '';
        if(isset($order['by'])){
            $byrecommend = $this->getByOrder($order['by']);
        }
        if(isset($order['initial'])){
            $byletter = $this->getByLetter($order['initial']);
        }
        $map = ['status'=>1,'type'=>2];
        $card = $this->field('id,game_name as card_name, icon')
                ->where($byrecommend)
                ->where($byletter)
                ->where($map)
                ->page($page)
                ->select();
        $cnt  = $this->where($map)->where($byrecommend)->where($byletter)->count();
        $card_list = [];
        foreach($card as $val){
            $val['icon'] = imgurl_add_sign($val['icon']);
            $card_list[]=$val;
        }
        return [$card_list,$cnt];
    }


    protected function getByOrder($order){
        if($order=='recommend'){
            $r = 'recommend_status = 1';
        }elseif($order=='hot'){
            $r = 'recommend_status = 2';
        }elseif($order=='new'){
            return $r = $this->order('id desc');
        }
        return $r;
    }
    protected function getByLetter($letter){
        $lower = strtolower($letter);
        $upper = strtoupper($letter);
        if((ord($letter)>='48' && ord($letter)<='57') or ord($letter) == '35' ){
            $i = "initial between '0' and '9' ";
        } elseif ($letter >= 'a' && $letter <= 'Z') {
            $i = "initial= '{$lower}' or initial = '{$upper}'";
        } else {
            $i = "initial = '{$letter}'";
        }
        return $i;
    }

    public function getGameIconBatch($game_id){
        $game = $this->field('id,icon')->where(['id'=>['in',$game_id]])->select();
        $game_list = [];
        foreach($game as $val){
            $game_list[$val['id']] = $val['icon'];
        }
        return $game_list;
    }
    //获取点卡类型的面值
    public function getCardValue($card_id){
        $service_model = GameServiceModel::self();
        $map = ['status'=>1,'game_id'=>$card_id,'type'=>2];
        $value = $service_model->field('id,service_name as card_value')->where($map)->order('id asc')->select();
        return $value;
    }

    //获取游戏服务区  10个为一大区
    public function getService($game_id,$device_type,$version){
        $map = [
            'game_id'=>$game_id,
            'status'=>1,
        ];
        $s = Db::name('tab_game_service');
        $parent = $s->where($map)->field('id,service_name')->order('id asc')->select();
        /*if($device_type!='android'&&$device_type!='ios'){
            return $parent;
        }
        $parent = FormatService::ShieldAndroid($parent, $device_type, $version);*/
        $num = 10;
        $service = [];
        $i = 0;
        foreach($parent as $key=>$val){
            if($key%$num==0){
                $i++;
            }
            $service[$i]['detail'][]=$val;
        }
        $cnt = count($service);
        $service_list=[];
        foreach($service as $val){
            foreach($val as $row){
                $count = count($row);
                $a['detail'] = $row;
                if($cnt>1){
                    $a['name'] = $row[0]['service_name'].'-'.$row[count($row)-1]['service_name'];
                }
                $service_list[]=$a;
            }
        }
        return $service_list;
    }
    /**
     * 获取游戏分页列表 后台使用
     * @param array $map 筛选条件
     * @param string $order 排序规则
     * @param int $limit 每页条数
     * @param array $params 额外参数
     * @return object 资源数组对象
     */
    public function getGamePageList($map=[], $order='', $limit='', $params=[])
    {
        $list = $this->field('*')
                ->where($map)
                ->order($order)
                ->paginate($limit, false, ['query'=>$params]);
        return $list;
    }
    /**
     * 获取所有游戏名称 后台使用
     * @param array $map 筛选条件
     * @return array 资源数组
     */
    public function getType($map)
    {
        $res = $this->field('id,game_name')
                ->where($map)
                ->order('id')
                ->select();
        return $res;
    }
    /**
     * 删除id对应的数据 后台使用
     * @param array $ids id数组
     * @return int
     */
    public function remove($ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
        return $this->setField('status', 0);
    }
    /**
     * 编辑更新 后台使用
     * @param array $data 游戏数据
     * @return int
     */
    public function edit($data)
    {
        $map['id'] = $data['id'];
        $ret = $this->where($map)->update($data);
        return $ret;
    }
    /**
     * 新增游戏 后台使用
     * @param array $data 游戏数据
     * @return int
     */
    public function add($data)
    {
        $data['create_time'] = time();
        $ret = $this->insertGetId($data);
        return $ret;
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
    public function getHotGames($map,$order='',$limit=10)
    {
        $where =[
           'recommend_status' => '2',
           'game_status' => '1',
            'status' => '1'
        ];
        $list = $this->field('*')
                ->where($where)
                ->where($map)
                ->order($order)
                ->paginate($limit,FALSE);
        return $list;
    }

}
