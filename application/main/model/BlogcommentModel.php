<?php
namespace app\main\model;

use app\main\model\CustomerModel;
use app\main\model\BlogModel;
use think\Model;

class BlogcommentModel extends Model
{
    protected $name = 'sx_article_comment';
    protected $connection = 'db.cms';
    protected $dateFormat = false;


    public static function self(){
        return new self();
    } 
    public function getCommentCnt($id){
        $cnt = $this->where(['article_id'=>$id,'parent_id'=>0])->count();
        return $cnt;
    }
    public function addComment($data){
        $data['create_at'] = time();
        $data['status'] = 1;
        BlogModel::self()->where('id',$data['article_id'])->setInc('comment_cnt');
        $this->insert($data);
        return 0;
    }
    /*
     * 无限级评论
     */
    public function getCommList($id,$parent_id = 0,&$result = array()){
        $arr = $this
                ->field('id,article_id,parent_id,to_uid,from_uid,username,content,create_at')
                ->where(['article_id'=>$id,'parent_id'=>$parent_id,'type'=>1])
                ->order('create_at desc')->select(); 
         // return $arr;       
        if(empty($arr)){
            return array();
        }
        foreach ($arr as $cm) {  
            $thisArr=&$result[];
            $cm['reply'] = $this->getCommlist($cm['article_id'],$cm['id'],$thisArr); 
            unset($cm['article_id']);
            unset($cm['parent_id']);
            unset($cm['to_uid']);
            $thisArr = $cm;                                    
        }
        return $result;
    }
    
    public function getList($map, $order, $limit, $params=[])
    {   
        $list = $this->alias('c')
            ->field('c.*, a.title as article_name, a.image as article_image, a.uid as uid')
            ->join('sx_article a','a.id = c.article_id','LEFT')
            ->where($map)
            ->order($order)
            ->paginate($limit, false, ['query'=>$params]);
        return $list;
    }
    public function getInfo($id)
    {
        $item = $this->alias('c')
            ->field('c.*, a.title as article_name, a.image as article_image, a.uid as uid')
            ->join('sx_article a','a.id = c.article_id','LEFT')
            ->where('c.id', $id)
            ->find();
        return $item;
    }

    public function remove($ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
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

    public function edit($data)
    {
        return $this->where('id', $data['id'])->update($data);
    }

}
