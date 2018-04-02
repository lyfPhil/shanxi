<?php
namespace app\main\service;

use app\main\model\PictureModel;
use app\v1\service\FormatService;

class UploadService {

    public static function upload_img($filepath,$fname,$k='')
    {
        $md5 = md5_file($filepath);
        $pm = PictureModel::self();
        $info = $pm->getbyMd5($md5);
        $ext = pathinfo($fname, PATHINFO_EXTENSION); 
        $fsize = filesize($filepath);
        $ret = false;
        if(!is_numeric($k))
        {
            if ($info)
            {
                return $info;
            }
            if (!in_array($ext,['jpg','jpeg','png','gif','webp'])) {
                $ext = 'jpg';
            }
            $key = 'img/'.date('Ymd').'/'.md5shortbin(hex2bin($md5)).".".$ext;
            $ret = QiniuService::upload($key, $filepath);
        }else{
            $key = 'game_icon_'.$k.'.jpg';
            $ret = QiniuService::upload($key, $filepath);
            if ($ret)
            {
                $err = QiniuService::remove($key); 
                if (!$err)
                {
                    $ret = QiniuService::upload($key, $filepath);
                    $url = buildImageUrl('/'.$key);
                    $urls =array($url);
                    $res = QiniuService::refresh($urls);
                }
            }
        }
        if ($ret)
        {
            return false;
        }
        $key = '/'.$key;
        $pid = $pm->addPic($fname, $md5, $key, $fsize);
        $res=[
            'url'=>$key,
            'file_size'=> $fsize,
            'pic_id'=>$pid,
        ];
        return $res;
    }

    public static function upload_remote($url)
    {
        $cache_dir = '/www/cache/uploads';
        $imgUrl = htmlspecialchars($url);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);
        $r = parse_url($imgUrl);
        if (!isset($r['scheme']) || !in_array($r['scheme'], ['http', 'https']))
        {
            return false;
        }
        $path = $r['path'];
        $fname = basename($path);
        $ext = pathinfo($fname, PATHINFO_EXTENSION);
        $m = md5($imgUrl);
        $local_path = $cache_dir."/".substr($m, 0, 2)."/".md5shortbin(hex2bin($m)).".".$ext;
        if (file_exists($local_path) && filesize($local_path) > 0)
        {
            $res=self::upload_img($local_path, $fname);
            if (!$res)return $res;
            $res['fsize'] = filesize($local_path);
            $res['ext'] = $ext;
            $res['path'] = $fname;
            return $res;
        }

        if (!in_array($ext, ["png", "jpg", "jpeg", "gif", "bmp", "webp"]))
        {
            return false;
        }
        $dirname = dirname($local_path);
        if (!file_exists($dirname) && !mkdir($dirname, 0744, true)) {
            return false;
        } else if (!is_writeable($dirname)) {
            return false;
        }

        self::download_image($imgUrl, $local_path);        

        if (!file_exists($local_path)) {
            return false; 
        }

        $fsize = filesize($local_path);
        if ($fsize <= 0)
        {
            unlink($local_path);
            return false;
        }
        $res = self::upload_img($local_path, $fname);
        if (!$res)return $res;
        $res['fsize'] = $fsize;
        $res['ext'] = $ext;
        $res['path'] = $fname;
        return $res;
    }

    public static function download_image($image_url, $image_file){
        $fp = fopen ($image_file, 'w+');              
        $ch = curl_init($image_url);
        curl_setopt($ch, CURLOPT_FILE, $fp);        
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);     
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        curl_exec($ch);
        curl_close($ch);                             
        fflush($fp);
        fclose($fp);                                
    }
}
