<?php
namespace app\main\model;

use app\v1\service\FormatService;
use app\main\model\GameModel;
use think\Model;
use think\Env;
use traits\model\SoftDelete;

class BlogModel extends Model
{
    protected $name = 'sx_blog';
    protected $connection = 'db.cms';
    protected $dataFormat = false;
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    
    public static function self(){
        return new self();
    }

    public function getStrategyList($where,$order,$page){
        $by = '';
        if(isset($order['by'])){
            if($order['by']=='hot'){
                $map['hot'] = 1;
            }elseif($order['by']=='recommend'){
                $map['recommend'] = 1;
            }else{
                $by = $this->getStrategyOrderBy($order['by']);
            }
        }
        $map['status'] = 1;//已审核
        $strategy = $this->where($where)->where($map)->page($page)->order($by)->order('create_at desc')->select();
        $cnt = $this->where($where)->where($map)->count();
        $its = [];
        $city_id = array_column($strategy, 'city_id');
        $game_model = GameModel::self();
        $game = $game_model->getGameIconBatch($city_id);
        foreach($strategy as $item)
        {
            if(in_array($item['city_id'], array_keys($game))){
            $list_item = [
                    'id'    =>$item['id'],
                    'title' =>$item['title'],
                    'pic'   => imgurl_add_sign($item['image']),
                    'desc'  =>$item['description'],
                    'from'  => $item['source'],
                    'city_id'=>$item['city_id'],
                    'from_type'=>$item['from_type'],
                    'icon'  => $game[$item['city_id']],
                    'uptime'=>$item['create_at'],
                    'view'  =>$item['pv'],
                    'comment'=>$item['comment_cnt'],
                    'collected_cnt'=>$item['collected_cnt'],
                    'detail_url'=>Env::get('web.host_m').'/strategy/detail/'.$item['id'],
                ];
            $its[] = $list_item;
            }
        }
        return [$cnt,$its];
    }

    protected function getStrategyOrderBy($order){
        if($order=='new'){
            $order = 'create_at desc';
        }elseif($order=='cnt'){
            $order = 'comment_cnt desc';
        }elseif($order=='view'){
            $order = 'pv desc';
        }elseif($order=='collected'){
            $order = 'collected_cnt desc';
        }else{
            $order = '';
        }
        return $order;
    }

    public function getDetail($id){
        $strategy = $this->field('id,title,image,content,pv,source,create_at,city_id,comment_cnt')
                       ->where(['id'=>$id,'status'=>1])->find();
        if(empty($strategy)){
            return 404320;
        }
        $this->where('id',$id)->setInc('pv');
        $strategy['image'] = imgurl_add_sign($strategy['image']);
        $game_model = GameModel::self();
        $game = $game_model->field('icon')->where('id',$strategy['city_id'])->find();
        $strategy['icon'] = $game['icon'];
        return $strategy;
    }
    public function status($time)
    {
        return [
            'title'=>'游戏攻略数',
            'total'=>$this->count(),
            'newly'=>$this->where('create_at', '>',$time)->count(),
            'pending'=>$this->where('status', 0)->count(),
        ];
    }

    public function getList($map, $order, $limit, $params=[])
    {
        $list = $this->alias('a')
            ->field('a.*, c.alias as category_alias, c.title as category_name')
            ->join('sx_category c','a.category_id = c.id','LEFT')
            ->where($map)
            ->order($order)
            ->paginate($limit, false, ['query' => $params]);
        return $list;
    }

    public function updateArray($map, $ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);

        return $this->update($map);
    }

    public function remove($ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
        return $this->delete();
    }

    public function add($data)
    {
        // 保存图片 转换通用斜杠符号
        if (isset($data['image'])){
            $data['image'] = str_replace("\\", "/", $data['image']);
        }
        $data['create_at'] = time();
        return $this->insertGetId($data);
    }

    public function edit($data)
    {
        if (!empty($data['image'])) {
            $data['image'] = str_replace("\\", "/", $data['image']);
        }
        $data['update_at'] = time();
        return $this->where('id', $data['id'])->update($data);
    }
}
