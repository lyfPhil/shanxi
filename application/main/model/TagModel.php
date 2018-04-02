<?php
namespace app\main\model;

use think\Model;
use think\Db;

class TagModel extends Model
{
    protected $name = 'sx_article_tag';
    protected $connection = 'db.cms';
    protected $tagAccess = null;

    public static function self(){
        return new self();
    } 

    public function initialize()
    {
        parent::initialize();
        $this->tagAccess = Db::name('sx_article_tag_access', $connection=$this->connection);
    }

    public function add($tag, $article_id)
    {
        $tags = $this->parse($tag);
        if (! empty($tags) && ! empty($article_id)) {
            foreach ($tags as $v) {
                $this->bind($v, $article_id);
            }
        } else {
            return false;
        }
        return true;
    }

    public function bind($tagName, $article_id)
    {
        $exist = $this->where('title', $tagName)->find();
        if (!$exist) {
            $tag_id = $this->insertGetId(['title'=>$tagName, 'num'=>1]);
        } else {
            $tag_id = $exist['id'];
        }
        $data = ['tag_id'=>$tag_id, 'article_id'=>$article_id];
        return $this->tagAccess->insertGetId($data);
    }

    public function edit($tag, $article_id)
    {
        $tags = $this->parse($tag);
        if (!empty($tags) && ! empty($article_id)) {
            $this->startTrans();
            try{
                $this->tagAccess->where('article_id', $article_id)->delete();
                foreach ($tags as $v) {
                    $this->bind($v, $article_id);
                }
                $this->commit();
            }catch(\Exception $e){
                $this->rollback();
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    public function parse($tag = '')
    {
        $arr = [];
        // 解析TAG 规则:多个TAG使用英文','隔开
        if (! empty($tag)) {
            $str = str_replace('，', ',', trim($tag));
            $arr = explode(',', $str);
        }
        return $arr;
    }

}
