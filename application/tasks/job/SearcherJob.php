<?php
namespace app\tasks\job;

use think\Config;
use app\main\model\GoodsModel;
use app\main\model\ArticleModel;
use app\common\service\SearchService;
use app\v1\service\FormatService;

class SearcherJob
{
    private $view = null;
    private $goods_xs=null;
    private $art_xs=null;
    public function setUp()
	{
        $this->goods_xs = SearchService::getxs('qly_goods');
        $this->art_xs = SearchService::getxs('qly_article');
	}

	public function perform()
	{
        $model = $this->args['model'];
        $ids = $this->args['ids'];
        $action = strtolower($this->args['action']);
        if ($model == 'goods')
        {
            $this->goodsProc($ids, $action);
        }
        elseif($model == 'article')
        {
            $this->artProc($ids, $action);
        }
        else
        {
            echo "SearcherJob [$model] not support\n";
        }
	}
	
	public function tearDown()
	{

	}    

    protected function goodsProc($ids, $action)
    {
        if ($action == 'delete')
        {
            $this->goods_xs->delDoc($ids);
        }
        else
        {
            $it = GoodsModel::self()
                ->field('id, title, pic_url, price, old_price, game_id, game_name, server_name, goods_type, paid_cnt, server_id')
                ->where('id', 'in', $ids)->select();
            if ($action == 'add')
            {
                foreach($it as $item)
                {
                    $this->goods_xs->addDoc(FormatService::formatNull($item->getdata()));
                }
            }
            elseif ($action == 'update')
            {
                foreach($it as $item)
                {
                    $this->goods_xs->updateDoc(FormatService::formatNull($item->getdata()));
                }
            }

        }
    }

    protected function artProc($ids, $action)
    {
        if ($action == 'delete')
        {
            $this->art_xs->delDoc($ids);
        }
        else
        {
            $it = ArticleModel::self()
                ->field('id, title, keywords, description, category_id, image, game_id, source, update_at, from_type, comment_cnt')
                ->where('id', 'in', $ids)
                ->select();
            if ($action == 'add')
            {
                foreach($it as $item)
                {
                    $this->art_xs->addDoc(FormatService::formatNull($item->getdata()));
                }
            }
            elseif ($action == 'update')
            {
                foreach($it as $item)
                {
                    $this->art_xs->updateDoc(FormatService::formatNull($item->getdata()));
                }
            }

        }
    }
}
