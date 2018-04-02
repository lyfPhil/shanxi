<?php
namespace app\tasks\controller;

use app\common\controller\CliBase;
use app\main\service\QiniuService;
use app\main\model\PictureModel;
use app\user\model\UserModel;

use think\Db;
use think\Env;
use think\Config;

class Image extends CliBase
{
    private function up2qn($filePath, $md5="", $key="")
    {

        $ext = pathinfo($filePath, PATHINFO_EXTENSION); 
        if (empty($key))
        {
            if (!$md5)
                $md5 = md5_file($filePath);
            $key = '/img/'.date('Ymd').'/'.md5shortbin(hex2bin($md5)).".".$ext;
        }
        $ret = QiniuService::upload($key, $filePath);
        if ($ret)
        {
            return false;
        }
        return $key;
    }

    public function Index()
    {
        $pic = PictureModel::self();
        $it = $pic->where('status', 1)
            ->where('path', '<>', '')
            ->select();
        $prefix = "/www/web/jiaoyi";
        foreach ($it as $item)
        {
            $fpath = $prefix.$item['path'];
            if (file_exists($fpath))
            {
                $filesize = filesize($fpath);
                $ret = $this->up2qn($fpath, $item['md5']);
                echo "$fpath up ret= $ret\n";
                if ($ret)
                {
                    $data=[];
                    $data['url'] = $ret;
                    $data['fsize'] = $filesize;
                    $pic->where('id',$item['id'])->update($data);
                }
            }
        }
    }

    public function goods()
    {
        $gm = Db::name('tab_goods');
        $it = $gm->where('pic_url is not null')->select();
        $prefix = "/www/web/jiaoyi";
        foreach ($it as $item)
        {
            $fpath="/".$item['pic_url'];
            if (strpos($fpath, "icon")!== false)
            {
                echo "$fpath \n";
                $data=[
                    'pic_url'=>$fpath,
                ];
                $gm->where('id',$item['id'])->update($data);
                continue;
            }
        }
    }

    public function avatar()
    {
        $gm=UserModel::self();
        $it = $gm->select();
        $prefix = "/www/web/jiaoyi";
        foreach ($it as $item)
        {
            $fpath = $item['avatar'];
            if (strpos($fpath, "avatar")!== false)
            {
                echo "$fpath \n";
                $data=[
                    'avatar'=>"/".pathinfo($fpath, PATHINFO_BASENAME),
                ];
                $gm->where('id',$item['id'])->update($data);
                continue;
            }
        }
    }

    public function Icon()
    {
        $prefix = "/www/web/jiaoyi/Uploads/icon";
        $flists = scandir($prefix);
        foreach($flists as $f)
        {
            if (strpos($f, "game_icon")!==false)
            {
                $fpath = $prefix."/".$f;
                $ret = $this->up2qn($fpath, "", $f);
                var_dump($ret);
            }
        }
    }
};
