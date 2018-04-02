<?php

namespace app\main\model;

use think\Model;

class HelpArticleModel extends Model {
    protected $name="sx_help_article";
    protected $connection = 'db.cms';
    protected $dateFormat = false;

    public static function self()
    {
        return new self();
    }

    public function getHelpSubCat($subcat_id){
        return $this->where(['category_id' => $subcat_id, 'status' => 1])->field('id,title')->select();
    }

    public function getHelpArticleList($map,$order,$limit,$param)
    {
        $list = $this->alias('a')
                ->field('a.*,b.title as category_name')
                ->join('sx_help_category b','a.category_id = b.id','LEFT')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$param]);
        return $list;
    }
    public function add($data)
    {
        // 保存图片 转换通用斜杠符号
        if (isset($data['image'])){
            $data['image'] = str_replace("\\", "/", $data['image']);
        }
        $data['create_time'] = time();
        return $this->insertGetId($data);
    }
    public function edit($data)
    {
        // 保存图片 转换通用斜杠符号
        if (isset($data['image'])){
            $data['image'] = str_replace("\\", "/", $data['image']);
        }
        $data['create_time'] = time();
        return $this->where('id',$data['id'])->update($data);
    }
    public function remove($ids)
    {
        if(is_array($ids)){
            $this->where('id','in',$ids);
        }else{
            $this->where('id',$ids);
        }
        return $this->delete();
    }
    public function updateArray($map, $ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);

        return $this->update($map);
    }
    /**
     * [getAnnounceList description]
     * @param  [type] $where [description]
     * @param  [type] $page  [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function getAnnounceList($where, $page, $field = '*'){
        $list = $this->where($where)->field($field)->page($page)->order('id desc')->select();
        return $list;
    }
    /**
     * [getAnnouncePaginateList description]
     * @param  array  $where [description]
     * @param  string $limit [description]
     * @param  string $order [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function getAnnouncePaginateList($where = [], $limit = '20' , $order = 'id desc, status desc', $field = '*'){
        return $this->where($where)->field($field)->order($order)->paginate($limit, false);
    }
    /**
     * [getOneDetail description]
     * @param  [type] $where [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function getOneAnnounceDetail($where, $field = '*'){
        $detail = $this->where($where)->field($field)->find();
        return $detail;
    }
}
