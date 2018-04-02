<?php
namespace app\admin\controller;
use \app\admin\model\CateModel;
use \app\admin\model\CateTypeModel;
use \app\main\model\ArticleModel;
class Cate extends Admin
{

    public function show(){
        $cate = new Catemodel();
        if (request()->isPOST()) {
            $data = input('post.');
            foreach ($data as $key => $value) {
                $res = $cate->update(['id'=>$key ,'sort'=>$value]);
            }
            if ($res!==false) {
               $this->success('更新排序成功！',url('show'));
            }else{
                $this->error('更新排序失败！');
            }
            return;
        }
        $catedata = $cate->catetree();
        $this->assign('catedata',$catedata);
        // $cateType=new CateType();
        // $cateTypeData=$cateType->getType($id);
        // var_dump($cateTypeData);die();
        // $this->assign('cateTypeData',$cateTypeData);
        return $this->fetch();
    }

    public function create(){
        $cate = new CateModel();
        $catedata = $cate->catetree();
        if (request()->isPOST()) {
            $data = input('post.');
            $validate = \think\Loader::validate('cate');
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            $res = $cate->save($data);
            if ($res) {
               $this->success('新增栏目成功！',url('show'));
            }else{
            $this->error('新增栏目失败！');
            }
            return ;
        }
        $cateType=new CateTypeModel();
        $cateTypeData=$cateType->getCateType();
        $this->assign('cateTypeData',$cateTypeData);
        $this->assign('cateres',$catedata);
        return $this->fetch();
    }

    public function store(){
        $data=input('post.');
        // $valid = validate('cate');
        // if (!$valid->scene('add')->check($data))
        // {
        //     return $this->response(201, lang($valid->getError()));
        // }
        $ret=CateModel::self()->add($data);
        if (is_numeric($ret)) {
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }
    }

    public function edit($id){
        $cateres = CateModel::self()->catetree();
        $catedata = CateModel::self()->find($id);
        $cateTypeData=CateTypeModel::self()->getCateType();
        $this->assign(array(
            'catedata'=>$catedata,
            'cateres'=>$cateres,
            'cateTypeData'=>$cateTypeData
        ));
        return $this->fetch();
    }

    public function update()
    {
        $data = input('post.');
        // $valid = validate('article');
        // if (!$valid->scene('edit')->check($data))
        // {
        //     return $this->response(201, lang($valid->getError()));
        // }
        $cm = CateModel::self();
        $ret = $cm->edit($data);
        if (is_numeric($ret)) {
            return $this->response(200);
        } else {
            return $this->response(201,$ret);
        }
    }

    public function remove(){
        $id = input('id');
        // 检查栏目下是否有子栏目
        $checkSon = CateModel::self()->where('pid', $id)->find();
        if ($checkSon) {
            return $this->response(201,'该栏目下有子栏目，不能删除');
        }
        // 检查栏目下是否有内容
        $checkContent = ArticleModel::self()
            ->where('cateid', $id)
            ->find();
        if ($checkContent) {
            return $this->response(201,'该栏目下有文章内容，不能删除');
        }
        $result = CateModel::self()->remove($id);
        if ($result !== false) {
            return $this->response(200);
        } else {
            return $this->response(201,'删除失败');
        }
    }

    public function reorder()
    {
        $data = input('post.');
        // if ($data['']) {
        //     return $this->response(201);
        // }
        $list = array_combine($data['ids'],$data['sort']);
        $cm = CateModel::self();

        $vals =[];
        foreach($list as $k=>$v){
            $vals[]=['id'=>$k, 'sort'=>$v];
        }
        $result = $cm->saveAll($vals);
        if ($result !== false) {
            return $this->response(200);
        }
        return $this->response(201);
    }


    public function cate_type(){
        $cateType=new CateType();
        $cateTypeData=$cateType->getCateType();
        $this->assign('cateTypeData',$cateTypeData);
        return $this->fetch();
    }

    public function add_type(){
         if (request()->isPOST()) {
            $data = input('post.');
            $cateType=new CateType();
            $res = $cateType -> save($data);
            if ($res) {
               $this->success('新增栏目类型成功！',url('cate_type'));
            }else{
            $this->error('新增栏目类型失败！');
            }
            return;
        }
        // $this->assign('cateTypeData',$cateTypeData);
        return $this->fetch();
    }

    public function del_type($id){
        $cateType = new CateType();
        $res=$cateType -> delCateType($id);
        if ($res) {
           $this->success('删除栏目类型成功！',url('cate_type'));
        }else{
            $this->error('删除栏目类型失败！');
        }
        return $this->fetch();
    }

    public function edit_type($id){
        $cateType=new CateType();
        if (request()->isPOST()) {
            $data = input('post.');
            $res = $cateType -> updateType($data);
            if ($res) {
               $this->success('栏目类型修改成功！',url('cate_type'));
            }else{
            $this->error('栏目类型修改失败！');
            }
            return;
        }

        $cateTypeData=$cateType->getType($id);
        // var_dump($cateTypeData);die();
        $this->assign('cateTypeData',$cateTypeData);
        return $this->fetch();
    }
}
