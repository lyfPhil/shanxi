<?php
namespace app\admin\controller;

use think\Request;
use app\main\model\BlogModel;
use app\main\model\CategoryModel;
use app\main\model\TagModel;
use app\admin\model\UserModel;
use app\main\service\CommService;
use app\main\model\CityModel;
use think\Env;
class Blog extends Admin
{
    public function index()
    {
        $search = Request::instance()->param();
        $map = array();
        //搜索条件
        if (isset($search['title']) && $search['title'] != '') {
            $map['a.title'] = array(array('like', '%'.stohk(trim($search['title'])).'%'),array('like', '%'.hktos(trim($search['title'])).'%'),'or');
        }
        if (isset($search['category_id']) && $search['category_id'] != '') {
            $map['a.category_id'] = $search['category_id'];
        }
        if(isset($search['status']) && $search['status'] !=''){
                $map[$search['status']] = 1;
        }
        /*
         * 时间筛选
         */
        if(isset($search['first_time']) && $search['first_time'] != '')
        {
            if(isset($search['end_time']) && $search['end_time'] != ''){
                $map['a.create_at'] = array('between',[strtotime($search['first_time']),  strtotime($search['end_time'])]);
            }  else {
                $map['a.create_at'] = array('>=',  strtotime($search['first_time']));
            }
        }else if(isset ($search['end_time']) && $search['end_time'] != '')
        {
            $map['a.create_at'] = array('<',strtotime($search['end_time']));
        }
        $list=BlogModel::self()->getList($map, 'a.create_at desc', 15, $search);
        $uids = [];
        foreach($list as $item)
        {
            if (!in_array($item['uid'], $uids)) 
            {
                $uids[] = $item['uid'];
            }
            $item['create_at'] = date('Y-m-d H:i:s',$item['create_at']);
            $item['update_at'] = date('Y-m-d H:i:s',$item['update_at']);
            $item['detail_url'] = Env::get('web.main').'/strategy/detail/'.$item['id'];
        }
        $users = UserModel::self()->getUserNamesByIds($uids);
        $page = $list->render();
        $cate =CategoryModel::self()->getList(); 
        $this->assign('search', $search);
        $this->assign('cate', $cate);
        $this->assign('list', $list);
        $this->assign('users', $users);
        $this->assign('page', $page);
        return view();
    }
    public function handle()
    {
        $data = input('post.');
        $am = BlogModel::self();
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
            $map=['recommend'=>1];
            $result = $am->updateArray($map, $ids);
            break;
        case 'hot':
            $map=['hot'=>1];
            $result = $am->updateArray($map, $ids);
            break;
        case 'cancelrecommend':
            $map=['recommend'=>0];
            $result = $am->updateArray($map, $ids);
            break;
        case 'cancelhot':
            $map=['hot'=>0];
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
        $cm = CategoryModel::self();
        $category = $cm->getList();
        $city = CityModel::self()->getcity();
        $this->assign('source',$city);
        $this->assign('category', $category);
        return view();
    }

    public function store()
    {
        $data = input('post.');
        $valid = validate('article');
        if (!$valid->scene('add')->check($data))
        {
            return $this->response(201, lang($valid->getError()));
        }
        $am = BlogModel::self();
        $data['uid'] = $this->uid;
        if(isset($data['image']) && $data['image'] != ''){
            $data['image'] = parse_url($data['image'], PHP_URL_PATH);
        }else{
            $image = getImgSrc($data['content']);
            $data['image'] = parse_url($image[1][0],PHP_URL_PATH);
        }
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
        $am = BlogModel::self();
        $article = $am->get(['id'=>$id]);
        
        //处理封面图片路径
        if (!empty($article['image'])) {
            $article['image'] = buildImageUrl($article['image']);
        }
        //更新防盗链
        if(!empty($article['content'])){
            $article['content'] = replaceImgLink($article['content']);
        }
        $city = CityModel::self()->getcity();
        $cm = CategoryModel::self();
        $category = $cm->getList();
        $new_category = [];
        if (!empty($category)) {
            foreach ($category as $v) {
                // 选中
                if ($article['category_id'] == $v['id']) {
                    $v['selected'] = 'selected';
                } else {
                    $v['selected'] = '';
                }
                array_push($new_category, $v);
            }
        }
        $this->assign('source',$city);
        $this->assign('article', $article);
        $this->assign('category', $new_category);
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
        if(!isset($data['recommend'])){
            $data['recommend'] = 0;
        }
         if(!isset($data['hot'])){
            $data['hot'] = 0;
        }
        $am = BlogModel::self();
        if(isset($data['image']) && $data['image'] != '')
            $data['image'] = parse_url($data['image'], PHP_URL_PATH);
        $data['city_id'] = $data['city_id'];
        // $source = CityModel::self()->field('name')->where('id',$data['source'])->find(); 
        // $data['source'] = $source['name'];
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
        $result = BlogModel::self()->remove($id);
        CommService::setArticleSearch('delete', $id);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function removeimage()
    {
        $id = Request::instance()->param('id');
        $data=['id'=>$id, 'image'=>''];
        $result = BlogModel::self()->edit($data);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
        
    }

}

