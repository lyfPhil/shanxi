<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller\Admin;
use app\main\model\AdvModel;
use app\main\model\GoodsModel;
use app\main\model\CustomerModel;
use app\v1\service\FormatService;
use app\main\model\GameModel;
use app\main\model\ArticleModel;
use app\main\model\GoodsTypeModel;

/**
 * 添加广告位置的方法，如添加网页端商品广告，则在AD_POS[2][web]中加一个位置id，然后在WEB_COVER里添加id
 */

class Cover extends Admin
{

    const COVER_AD_TYPE =[
        '0'=> "Other",
        '1'=> 'Game',
        '2'=> 'Goods',
        '3'=> 'Raiders',
        '4'=> 'Promotion',
        '5'=> 'Seller',
    ];
    /**
     * 1* 首页 2* app市场 3* web手游 4* web代练 5* web充值 6* 攻略  7* 筛选
     */
    const WEB_COVER = [
        '10'=> 'Homepage banner image','11'=>'Homepage banner illustration','13' => 'Hot commodity on the right',
        '14'=>'Search hot search games','15'=>'Homepage banner game on right','16' => 'Homepage banner bottom of the figure（Recommended point card）','17' => 'Homepage(Gold medal seller)','18'=> 'Home Hot Games',
        '61'=>'Web Raiders banner image','62' => 'Web Raiders Hot Games','88'=>'Game screening（Ad on the right）'
    ];
    const APP_COVER = [
        '1' => 'Raiders random ad spots image','10'=> 'Homepage banner image','20' => 'Market banner image','21' => 'Middle of the market(Preferential goods)',
        '22' => 'Middle of the market(The seller recommended)','60' => 'Raiders banner image'
    ];
    /**
     * 根据位置定的广告位
     */
    const AD_POS = [
    /********************************************游戏有哪些广告位置*****************************************/
        '1' => [
            'web' => [
                '13' => 'Hot commodity on the right','14'=>'Search hot search games',
                '16' => 'Homepage banner bottom of the figure（Recommended point card）','15'=>'Homepage banner game on right','18'=> 'Home Hot Games','61' => 'Web Raiders banner image','62' => 'Web Raiders Hot Games',
            ],
            'app' => [
                '10' => 'Homepage banner image', '20' => 'Market banner image', '60' => 'Raiders banner image',
            ]
        ],
    /********************************************商品有哪些广告位置*****************************************/
        '2' => [
            'web' => [
                '11'=>'Web Homepage banner image',
            ],
            'app' => [
                '10'=> 'Homepage banner image','20' => 'Market banner image','21' => 'Middle of the market(Preferential goods)'
            ]
        ],
    /********************************************攻略有哪些广告位置*****************************************/
        '3' => [
            'web' => [
                '11'=>'Web Homepage banner image','61'=>'Web Raiders banner image',
            ],
            'app' => [
                '10'=> 'Homepage banner image','20' => 'Market banner image','60' => 'Raiders banner image'
            ]
        ],
    /********************************************推广有哪些广告位置*****************************************/
        '4' => [
            'web' => [
                '11'=>'Web Homepage banner image','61'=>'Web Raiders banner image',
            ],
            'app' => [
                '1' => 'Raiders random ad spots image','10'=> 'Homepage banner image','20' => 'Market banner image','60' => 'Raiders banner image'
            ]
        ],
    /********************************************卖家有哪些广告位置*****************************************/
        '5' => [
            'web' => ['17'=>'Homepage(Gold medal seller)'],
            'app' => ['22' => 'Middle of the market(The seller recommended)']
        ],
    ];
    /**
     * 广告列表
     * @return
     */
    public function webCoverList()
    {
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['title'] = array('like', '%'.trim($search['keywords']).'%');
        }
        if (isset($search['status']) && $search['status'] != '') {
            $map['status'] = $search['status'];
        }
        if (isset($search['pos_id']) && $search['pos_id'] != '') {
            $map['pos_id'] = $search['pos_id'];
        }
        if (isset($search['ad_type']) && $search['ad_type'] != '') {
            $map['ad_type'] = $search['ad_type'];
        }
        $data = AdvModel::self()->getWebCoverPageList($map,'status desc, id desc', 10, $search);
        $page = $data->render();
        foreach($data as $it)
        {
            $it['start_time'] = date("Y-m-d", $it['start_time']);
            $it['end_time'] = date("Y-m-d", $it['end_time']);
            $it['ad_type_name'] = self::COVER_AD_TYPE[$it['ad_type']];
            $it['plate'] = floor($it['pos_id']/10);
        }
        $this->assign('ad_type', self::COVER_AD_TYPE);
        $this->assign('poslist', self::WEB_COVER);
        $this->assign('data',$data);
        $this->assign('search', $search);
        $this->assign('page', $page);
        return view();
    }

    public function appCoverList()
    {
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['title'] = array('like', '%'.trim($search['keywords']).'%');
        }
        if (isset($search['status']) && $search['status'] != '') {
            $map['status'] = $search['status'];
        }
        if (isset($search['pos_id']) && $search['pos_id'] != '') {
            $map['pos_id'] = $search['pos_id'];
        }
        if (isset($search['ad_type']) && $search['ad_type'] != '') {
            $map['ad_type'] = $search['ad_type'];
        }
        $data = AdvModel::self()->getAppCoverPageList($map,'status desc, id desc', 10, $search);
        $page = $data->render();
        foreach($data as $it)
        {
            $it['start_time'] = date("Y-m-d", $it['start_time']);
            $it['end_time'] = date("Y-m-d", $it['end_time']);
            $it['ad_type_name'] = self::COVER_AD_TYPE[$it['ad_type']];
            $it['plate'] = floor($it['pos_id']/10);
        }
        $this->assign('ad_type', self::COVER_AD_TYPE);
        $this->assign('poslist', self::APP_COVER);
        $this->assign('data',$data);
        $this->assign('search', $search);
        $this->assign('page', $page);
        return view();
    }
    /**
     * 编辑广告
     * @return
     */
    public function  edit()
    {
        $param = Request::instance()->param();
        $service_type = $param['service_type'];
        $details = AdvModel::self()->where('id',$param['id'])->find();
        $list = $map = [];
        //处理封面图片路径
        if (!empty($details['icon_url'])) {
            $details['icon_url'] = buildImageUrl($details['icon_url']);
        }
        $ad_pos = self::AD_POS[$param['ad_type']][$service_type];
        if($param['ad_type'] != '4' && !empty($details['url'])){
            switch($param['ad_type'])
            {
            case 1:
                if (isset($param['keywords']) && $param['keywords'] != '') {
                    $map['game_name'] = array('like', '%'.trim($param['keywords']).'%');
                }
                $game = GameModel::self()->field('id,game_name')->where($map)->order('sort')->select();
                $this->assign('games',$game);
                break;
            case 2:
                if (isset($param['keywords']) && $param['keywords'] != '') {
                    $map['title'] = array('like', '%'.trim($param['keywords']).'%');
                }
                if(isset($param['user_name']) && $param['user_name'] != ''){
                    $map['user_name'] = $param['user_name'];
                }
                if(isset($param['goods_type']) && $param['goods_type'] != ''){
                    $map['goods_type'] = $param['goods_type'];
                }
                $goods = GoodsModel::self()->getCoverGoodsList('id,title',$map);
                $goods_type = GoodsTypeModel::self()->getAllGoodsTypeName();
                $this->assign('type',$goods_type);
                $this->assign('goods',$goods);
                break;
            case 3:
                if (isset($param['keywords']) && $param['keywords'] != '') {
                    $map['title'] = array('like', '%'.trim($param['keywords']).'%');
                }
                $map['status'] = 1;
                $article = ArticleModel::self()->field('id,title')->where($map)->order('id')->select();
                $this->assign('articles',$article);
                break;
            case 5:
                if(isset($param['keywords']) && $param['keywords'] != ''){
                    $map['nickname'] = array('like','%'.trim($param['keywords']).'%');
                }
                $sellers = CustomerModel::self()->getSellerList('id,nickname',$map);
                $this->assign('sellers',$sellers);
                break;
            }
            $info = json_decode($details['url'],true);
            $this->assign('info',$info);
        }
        $details['price'] = isset($info['price'])?$info['price']:'';
        $details['start_time'] = date("Y-m-d",$details['start_time']);
        $details['end_time'] = date("Y-m-d",$details['end_time']);
        $this->assign('detail',$details);
        $this->assign('poslist',$ad_pos);
        $this->assign('service_type',$service_type);

        return $this->fetch('edit_'.$param['ad_type']);

    }
    /**
     * 新增游戏广告推荐
     * @return
     */
    public function gameTuiJian()
    {
        $service_type = Request::instance()->param('service_type');
        $ad_pos = self::AD_POS[1][$service_type];
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['game_name'] = array('like', '%'.trim($search['keywords']).'%');
        }
        $game = GameModel::self()->field('id,game_name')->where($map)->order('sort')->select();
        $this->assign('service_type',$service_type);
        $this->assign('poslist',$ad_pos);
        $this->assign('games',$game);
        return view();
    }
    /**
     * 新增商品推荐广告
     * @return
     */
    public function goodsTuiJian()
    {
        $service_type = Request::instance()->param('service_type');
        $ad_pos = self::AD_POS[2][$service_type];
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['title'] = array('like', '%'.trim($search['keywords']).'%');
        }
        if(isset($search['user_name']) && $search['user_name'] != ''){
            $map['user_name'] = $search['user_name'];
        }
        if(isset($search['goods_type']) && $search['goods_type'] != ''){
            $map['goods_type'] = $search['goods_type'];
        }
        $goods_type = GoodsTypeModel::self()->getAllGoodsTypeName();
        $goods = GoodsModel::self()->getCoverGoodsList('id,title',$map);
        $this->assign('service_type',$service_type);
        $this->assign('type',$goods_type);
        $this->assign('search',$search);
        $this->assign('poslist',$ad_pos);
        $this->assign('goods',$goods);
        return view();
    }
    /**
     * 新增游戏攻略推荐广告
     * @return
     */
    public function articleTuiJian()
    {
        $service_type = Request::instance()->param('service_type');
        $ad_pos = self::AD_POS[3][$service_type];
        $search = Request::instance()->param();
        $map = array();
        if (isset($search['keywords']) && $search['keywords'] != '') {
            $map['title'] = array('like', '%'.trim($search['keywords']).'%');
        }
        $map['status'] = 1;
        $article = ArticleModel::self()->field('id,title')->where($map)->order('id')->select();
        $this->assign('service_type',$service_type);
        $this->assign('poslist',$ad_pos);
        $this->assign('articles',$article);
        return view();
    }
    /**
     * 新增推广类广告
     * @return
     */
    public function advTuiJian()
    {
        $service_type = Request::instance()->param('service_type');
        $ad_pos = self::AD_POS[4][$service_type];
        $this->assign('service_type',$service_type);
        $this->assign('poslist',$ad_pos);
        return view();
    }
    /**
     * 新增卖家推荐广告
     * @return
     */
    public function sellerTuiJian()
    {
        $service_type = Request::instance()->param('service_type');
        $ad_pos = self::AD_POS[5][$service_type];
        $search = Request::instance()->param();
        $map = array();
        if(isset($search['keywords']) && $search['keywords'] != ''){
            $map['nickname'] = array('like','%'.trim($search['keywords']).'%');
        }
        $sellers = CustomerModel::self()->getSellerList('*',$map);
        $this->assign('service_type',$service_type);
        $this->assign('poslist',$ad_pos);
        $this->assign('sellers',$sellers);
        return view();
    }

    public function hotWordList()
    {
        $search = Request::instance()->param();
        $map = [];
        if(isset($search['status']) && $search['status'] != ''){
            $map['status'] = $search['status'];
        }
        $list = AdvModel::self()->getHotWordPageList($map,'status desc, id desc', 10, $search);
        foreach($list as $item)
        {
            $item['start_time'] = date('Y-m-d',$item['start_time']);
            $item['end_time'] = date('Y-m-d',$item['end_time']);
            $item['url'] = json_decode($item['url'],true);
            $item['url'] = implode(',', $item['url']);
        }
        $page = $list->render();
        $this->assign('search',$search);
        $this->assign('data',$list);
        $this->assign('page',$page);

        return view();

    }
    /**
     * 新增热搜词条
     * @return
     */
    public function hotWord()
    {
        return view();
    }
    public function defaultWord()
    {
        return view();
    }
    public function store()
    {
        $params = Request::instance()->param();
        if(isset($params['start_time']) && $params['start_time'] != ''){
            $params['start_time'] = strtotime($params['start_time']);
        }
        if(isset($params['end_time']) && $params['end_time'] != ''){
            $params['end_time'] = strtotime($params['end_time']);
        }
        $data = [
            'pos_id' => $params['pos_id'],
            'status' => $params['status'],
            'title' => $params['title'],
            'sort'  => $params['sort'],
            'ad_type' => $params['ad_type'],
            'start_time' => $params['start_time'],
            'end_time' => $params['end_time']
        ];
        $valid = validate('cover');
        if (!$valid->scene('addAdv')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        switch($params['ad_type'])
        {
            case 0:
                $data['url'] = explode(' ',$params['words']);
                $data['icon_url'] = "";
                $data['title'] = $params['title'];
                $ret = AdvModel::self()->addPosAd($data);
                break;
            case 1:
                $game = GameModel::self()->field('game_name')->where('id',$params['game_id'])->find();
                $temp = [
                    'id'=>$params['game_id'],
                    'type'=>'game',
                    'text'=>!empty($params['introduce'])?$params['introduce']:''
                ];
                $data['url'] = $temp;
                $data['title'] = !empty($params['title'])?$params['title']:$game['game_name'];
                $data['icon_url'] = isset($params['icon_url'])?$params['icon_url']:(FormatService::formatGameicon($params['game_id']));
                $data['icon_url'] = parse_url($data['icon_url'], PHP_URL_PATH);
                $ret = AdvModel::self()->addPosAd($data);
                break;
            case 2:
                $goods = GoodsModel::self()->field('id,title,pic_url,goods_type,old_price')->where('id',$params['goods'])->find();
                //判断价格
                if($params['price'] > $goods['old_price']){
                    return $this->error("優惠價格不得高於原價格".$goods['old_price']);
                }
                $temp = [
                    'id' => $goods['id'],
                    'type' => 'goods',
                    'goods_type' => $goods['goods_type'],
                    'price' => $params['price']

                ];
                $data['url'] = $temp;
                $data['title'] = !empty($params['title'])?$params['title']:$goods['title'];
                $data['icon_url'] = isset($params['icon_url'])?$params['icon_url']:null;
                $data['icon_url'] = parse_url($data['icon_url'], PHP_URL_PATH);
                $ret = AdvModel::self()->addPosAd($data);
                //修改商品价格
                if($ret && $params['price'] != ''){
                    $return = GoodsModel::self()->where('id',$params['goods'])->update(['price'=>$params['price']]);
                }
                break;
            case 3:
                $article = ArticleModel::self()->field('id,title,keywords,image')->where('id',$params['article'])->find();
                $temp = [
                    'id' => $article['id'],
                    'type' => 'article',
                    'title' => $article['title'],
                    'keywords' => $article['keywords']
                ];
                $data['url'] = $temp;
                $data['title'] = !empty($params['title'])?$params['title']:$article['title'];
                $data['icon_url'] = isset($params['icon_url'])?$params['icon_url']:$article['image'];
                $data['icon_url'] = parse_url($data['icon_url'], PHP_URL_PATH);
                $ret = AdvModel::self()->addPosAd($data);
                break;
            case 4:
                $data['url'] = $params['url'];
                $data['icon_url'] = $params['icon_url']?$params['icon_url']:'';
                $data['icon_url'] = parse_url($data['icon_url'], PHP_URL_PATH);
                $ret = AdvModel::self()->addPosAd($data);
                break;
            case 5:
                $seller = CustomerModel::self()->field('id,nickname,avatar')->where('id',$params['seller'])->find();
                $temp = [
                    'id' => $seller['id'],
                    'type' => 'seller'
                ];
                $data['url'] = $temp;
                $data['title'] = !empty($params['title'])?$params['title']:$seller['nickname'];
                $data['icon_url'] = isset($params['icon_url'])?$params['icon_url']:null;
                $data['icon_url'] = parse_url($data['icon_url'], PHP_URL_PATH);
                $ret = AdvModel::self()->addPosAd($data);
                break;
        }
        if(is_numeric($ret))
        {
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
    /**
     * 广告编辑更新
     * @return
     */
    public function update()
    {
        if(request()->isAjax())
        {
            $params = Request::instance()->param();
            $data = [
                'id' => $params['id'],
                'pos_id' => $params['pos_id'],
                'title' => $params['title'],
                'status' => $params['status'],
                'ad_type' => $params['ad_type'],
                'start_time' => strtotime($params['start_time']),
                'end_time' => strtotime($params['end_time']),
                'sort' => $params['sort']
            ];
            switch($params['ad_type'])
            {
                case 0:
                    $data['url'] = explode(' ',$params['words']);
                    $data['icon_url'] = "";
                    $ret = AdvModel::self()->editAdv($data);
                    break;
                case 1:
                    $game = GameModel::self()->field('game_name')->where('id',$params['game_id'])->find();
                    $info = [
                        'id'=>$params['game_id'],
                        'type'=>'game',
                        'text'=>$params['title']
                    ];
                    $data['url'] = $info;
                    $data['title'] = !empty($params['title'])?$params['title']:$game['game_name'];
                    if(isset($params['icon_url']) && $params['icon_url'] != ''){
                        $data['icon_url'] = parse_url($params['icon_url'], PHP_URL_PATH);
                    }
                    $ret = AdvModel::self()->editAdv($data);
                    break;
                case 2:
                    $goods = GoodsModel::self()->field('id,title,pic_url,goods_type,old_price')->where('id',$params['goods'])->find();
                    //判断价格
                    if($params['price'] > $goods['old_price']){
                        return $this->error("優惠價格不得高於原價格".$goods['old_price']);
                    }
                    $temp = [
                        'id' => $goods['id'],
                        'type' => 'goods',
                        'goods_type' => $goods['goods_type'],
                        'price' => $params['price']
                    ];
                    $data['url'] = $temp;
                    $data['title'] = !empty($params['title'])?$params['title']:$goods['title'];
                    if(isset($params['icon_url']) && $params['icon_url'] != ''){
                        $data['icon_url'] = parse_url($params['icon_url'], PHP_URL_PATH);
                    }
                    $ret = AdvModel::self()->editAdv($data);
                    //修改商品价格
                    if($ret && $params['price'] !=''){
                        $return = GoodsModel::self()->where('id',$params['goods'])->update(['price'=>$params['price']]);
                    }
                    break;
                case 3:
                    $article = ArticleModel::self()->field('id,title,keywords,image')->where('id',$params['article'])->find();
                    $temp = [
                        'id' => $article['id'],
                        'type' => 'article',
                        'title' => $article['title'],
                        'keywords' => $article['keywords']
                    ];
                    $data['url'] = $temp;
                    $data['title'] = !empty($params['title'])?$params['title']:$article['title'];
                    if(isset($params['icon_url']) && $params['icon_url'] != ''){
                        $data['icon_url'] = parse_url($params['icon_url'], PHP_URL_PATH);
                    }
                    $ret = AdvModel::self()->editAdv($data);
                    break;
                case 4:
                    $data['url'] = $params['url'];
                    if(isset($params['icon_url']) && $params['icon_url'] != ''){
                        $data['icon_url'] = parse_url($params['icon_url'], PHP_URL_PATH);
                    }
                    $ret = AdvModel::self()->editAdv($data);
                    break;
                case 5:
                    $seller = CustomerModel::self()->field('id,nickname,avatar')->where('id',$params['seller'])->find();
                    $temp = [
                        'id' => $seller['id'],
                        'type' => 'seller'
                    ];
                    $data['url'] = $temp;
                    $data['title'] = !empty($params['title'])?$params['title']:$seller['nickname'];
                    if(isset($params['icon_url']) && $params['icon_url'] != ''){
                        $$data['icon_url'] = parse_url($params['icon_url'], PHP_URL_PATH);
                    }
                    $ret = AdvModel::self()->editAdv($data);
                    break;
            }
            if(is_numeric($ret))
            {
                return $this->response(200);
            }else{
                return $this->response(201);
            }
        }
    }
    /**
     * 批量删除广告
     * @return
     */
    public function remove()
    {
        $ids = Request::instance()->param('id');
        $result = AdvModel::self()->remove($ids);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    /**
     * 删除广告图片
     * @return
     */
    public function removeimage()
    {
        $id = Request::instance()->param('id');
        $data=['id'=>$id, 'icon_url'=>''];
        $result = AdvModel::self()->updateArray($data,$id);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    /**
     * 批量修改广告的状态
     * @return
     */
    public function handle()
    {
        $data = Request::instance()->param();
        $am = AdvModel::self();
        $ids = $data['ids'];
        if (!$ids)
        {
            return $this->response(400);
        }
        $result = false;
        switch ($data['type']) {
        case 'delete':
            $result = $am->remove($ids);
            break;
        case 'pass':
            $map=['status' => '1'];
            $result = $am->updateArray($map, $ids);
            break;
        case 'down':
            $map=['status'=> '-1'];
            $result = $am->updateArray($map, $ids);
            break;
        }
        if($result !== false)
        {
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
     /**
     * 修改排序 sort字段 数字越大越靠前
     * @return
     */
    public function reorder()
    {
        $data = Request::instance()->param();
        $list = array_combine($data['ids'],$data['sort']);
        $gm = AdvModel::self();
        $vals =[];
        foreach($list as $k=>$v){
            $vals[]=['id'=>$k, 'sort'=>$v];
        }
        $result = $gm->saveAll($vals);
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }
    public function addHotWords()
    {
        $param = Request::instance()->param();
        $data['url'] = explode(',',$param['words']);
        $hot_words = [
            'pos_id' => $param['pos_id'],
            'title' => $param['title'],
            'url' => $data['url'],
            'ad_type' => 0,
            'start_time' => strtotime($param['start_time']),
            'end_time' => strtotime($param['end_time'])
        ];
        $ret = AdvModel::self()->addPosAd($hot_words);
        if(is_numeric($ret))
        {
            return $this->response(200);
        }else{
            return $this->response(201);
        }
    }
}