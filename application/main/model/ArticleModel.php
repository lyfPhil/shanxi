<?php
namespace app\main\model;
use think\Model;
use traits\model\SoftDelete;

class ArticleModel extends Model
{
    protected $name = 'sx_article';
    protected $connection = 'db.cms';
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    public static function self(){
      return new self();
    }
  	// protected static function init()
   //  {
    	// ArticleModel::event('before_insertGetId',function($article){
     //    if($_FILES['thumb']['tmp_name']){
     //          $file = request()->file('thumb');
     //          $info = $file->move(ROOT_PATH . 'public_admin' . DS . 'uploads');
     //          if($info){
     //              // $thumb=ROOT_PATH . 'public' . DS . 'uploads'.'/'.$info->getExtension();
     //              $thumb=DS . 'uploads'.'/'.$info->getSaveName();
     //              $article['thumb']=$thumb;
     //          }
     //      }
     //  });
     //  ArticleModel::event('before_insert',function($article){
     //    if($_FILES['thumb']['tmp_name']){
     //          $file = request()->file('thumb');
     //          $info = $file->move(ROOT_PATH . 'public_admin' . DS . 'uploads');
     //          if($info){
     //              // $thumb=ROOT_PATH . 'public' . DS . 'uploads'.'/'.$info->getExtension();
     //              $thumb=DS . 'uploads'.'/'.$info->getSaveName();
     //              $article['thumb']=$thumb;
     //          }
     //      }
     //  });

  	  // ArticleModel::event('before_update',function($article){
     //    if($_FILES['thumb']['tmp_name']){
     //    		$arts=ArticleModel::find($article->id);
     //    		$thumbpath=$_SERVER['DOCUMENT_ROOT'].$arts['thumb'];
     //          if(file_exists($thumbpath)){
     //          	@unlink($thumbpath);
     //          }
     //          $file = request()->file('thumb');
     //          $info = $file->move(ROOT_PATH . 'public_admin' . DS . 'uploads');
     //          if($info){
     //              // $thumb=ROOT_PATH . 'public' . DS . 'uploads'.'/'.$info->getExtension();
     //              $thumb=DS . 'uploads'.'/'.$info->getSaveName();
     //              $article['thumb']=$thumb;
     //          }

     //      }
     //  });

  	  // ArticleModel::event('before_delete',function($article){

    	// 	$arts=ArticleModel::find($article->id);
    	// 	$thumbpath=$_SERVER['DOCUMENT_ROOT'].$arts['thumb'];
     //      if(file_exists($thumbpath)){
     //      	@unlink($thumbpath);
     //      }
     //    });
     //  }

      public function getList($map, $order,$limit, $params=[]){
          $list = $this->alias('a')
            ->field('a.*,b.catename,c.name')
            ->join('sx_cate b','a.cateid=b.id','LEFT')
            ->join('sx_city c','a.city_id=c.id','LEFT')
            ->where($map)
            ->order($order)
            ->paginate($limit, false, ['query' => $params]);
        return $list;
      }

      public function updateArray($map, $ids)
      {
        $map['update_time']=time();
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

    public function add($data){
        // // 保存图片 转换通用斜杠符号
        // if (isset($data['image'])){
        //     $data['image'] = str_replace("\\", "/", $data['image']);
        // }
        $data['create_time'] = time();
        return $this->insertGetId($data);
    }
    public function edit($data){

        $data['update_time'] = time();
        return $this->where('id', $data['id'])->update($data);
    }

    

}
