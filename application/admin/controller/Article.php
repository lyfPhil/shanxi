<?php
namespace app\admin\controller;
use app\main\model\ArticleModel;
use app\admin\model\CateModel;
use app\main\model\CityModel;
use app\admin\model\UserModel;
use app\main\model\TagModel;
use think\Request;
use app\main\service\CommService;
class Article extends Admin
{
    public function show(){
        $search = Request::instance()->param();
        $map = array();
        //搜索条件
        if (isset($search['title']) && $search['title'] != '') {
            $map['a.title'] = array(array('like', '%'.stohk(trim($search['title'])).'%'),array('like', '%'.hktos(trim($search['title'])).'%'),'or');
        }
        if (isset($search['cateid']) && $search['cateid'] != '') {
            $map['cateid'] = $search['cateid'];
        }
        if(isset($search['status']) && $search['status'] !=''){
            $map['status'] = $search['status'];
        }
        if(isset($search['rec']) && $search['rec'] !=''){
            $map['rec'] = $search['rec'];
        }
        /*
         * 时间筛选
         */
        if(isset($search['first_time']) && $search['first_time'] != '')
        {
            if(isset($search['end_time']) && $search['end_time'] != ''){
                $map['a.create_time'] = array('between',[strtotime($search['first_time']),  strtotime($search['end_time'])]);
            }  else {
                $map['a.create_time'] = array('>=',  strtotime($search['first_time']));
            }
        }else if(isset ($search['end_time']) && $search['end_time'] != '')
        {
            $map['a.create_time'] = array('<',strtotime($search['end_time']));
        }
        $list=ArticleModel::self()->getList($map, 'status desc,a.create_time desc', 6, $search);
        $uids = [];
        // foreach($list as $item)
        // {
        //     // if (!in_array($item['uid'], $uids)) 
        //     // {
        //     //     $uids[] = $item['uid'];
        //     // }
        //     $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
        //     $item['update_time'] = date('Y-m-d H:i:s',$item['update_time']);
        //     // $item['detail_url'] = Env::get('web.main').'/strategy/detail/'.$item['id'];
        // }
        // $users = UserModel::self()->getUserNamesByIds($uids);
        $page = $list->render();
        $cate =CateModel::self()->catetree(); 
        $this->assign('search', $search);
        $this->assign('cate', $cate);
        $this->assign('list', $list);
        // $this->assign('users', $users);
        $this->assign('page', $page);
        return view();
    }

    public function handle()
    {
        $data = input('post.');
        $am = ArticleModel::self();
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
        case 'recommend':
            $map=['rec'=>1];
            $result = $am->updateArray($map, $ids);
            break;
        case 'cancelrecommend':
            $map=['rec'=>0];
            $result = $am->updateArray($map, $ids);
            break;
        case 'offline':
            $map=['status'=>0];
            $result = $am->updateArray($map, $ids);
            break;
        case 'change':
            $map=['status'=>1];
            $result = $am->updateArray($map, $ids);
            break;
        }
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }

    public function create()
    {
        $cm = CateModel::self();
        $cate = $cm->catetree();
        $city = CityModel::self()->getcity();
        $this->assign([
            'cate'=>$cate,
            'city'=>$city,
        ]);
        return view('add');
    }

    public function store()
    {
        $data = input('post.');
        $valid = validate('article');
        if (!$valid->scene('add')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        $am = ArticleModel::self();
        // dump($data);die();
        // if(isset($data['image']) && $data['image'] != ''){
        //     $data['image'] = parse_url($data['image'], PHP_URL_PATH);
        // }else{
        //     $image = getImgSrc($data['content']);
        //     $data['image'] = parse_url($image[1][0],PHP_URL_PATH);
        // }

        $ret = $am->add($data);
        CommService::setArticleSearch('add', $ret);
        if (is_numeric($ret) && $data['keywords']) {
            $tagModel = TagModel::self();
            $tag = $tagModel->add($data['keywords'], $ret);
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }

    }

    public function edit()
    {
        $id = Request::instance()->param('id');
        $am = ArticleModel::self();
        $article = $am->get(['id'=>$id]);
        
        //处理封面图片路径
        // if (!empty($article['image'])) {
        //     $article['image'] = buildImageUrl($article['image']);
        // }
        //更新防盗链
        // if(!empty($article['content'])){
        //     $article['content'] = replaceImgLink($article['content']);
        // }
        $cm = CateModel::self();
        $category = $cm->catetree();
        $new_category = [];
        if (!empty($category)) {
            foreach ($category as $v) {
                // 选中
                if ($article['cateid'] == $v['id']) {
                    $v['selected'] = 'selected';
                } else {
                    $v['selected'] = '';
                }
                array_push($new_category, $v);
            }
        }
        $cityinfo = CityModel::self()->getcity();
        $this->assign('cityinfo', $cityinfo);
        $this->assign('arts', $article);
        $this->assign('cateres', $new_category);
        return view();
    }

    public function update()
    {
        $data = input('post.');
        $valid = validate('article');
        if (!$valid->scene('edit')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        if(!isset($data['rec'])){
            $data['rec'] = 0;
        }
         if(!isset($data['status'])){
            $data['status'] = 0;
        }
        $am = ArticleModel::self();
        // if(isset($data['image']) && $data['image'] != '')
        //     $data['image'] = parse_url($data['image'], PHP_URL_PATH);
        $ret = $am->edit($data);
        CommService::setArticleSearch('update', $data['id']);
        if (is_numeric($ret)) {
            $tagModel = TagModel::self();
            $tagModel->edit($data['keywords'], $data['id']);
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }
    }

    public function remove()
    {
        $id = Request::instance()->param('id');
        $result = ArticleModel::self()->remove($id);
        CommService::setArticleSearch('delete', $id);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    
}
