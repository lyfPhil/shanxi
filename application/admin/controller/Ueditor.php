<?php
namespace app\admin\controller;

use think\Request;
use app\main\service\UploadService;
use app\v1\service\FormatService;


class Ueditor extends Admin
{
    public function index()
    {
        $action = Request::instance()->param('action');
        switch($action)
        {
        case 'config':
            return $this->config();
            break;
        case 'uploadimage':
            return $this->_upload();
            break;
        case 'catchimage':
            return $this->_catch();
            break;
        }
        return $this->response(404);
    }
	public function config()
	{
        $config=[
            'imageActionName'=> 'uploadimage',
            'imageFieldName'=> 'images',
            'imageMaxSize'=> 2048000,
            'imageAllowFiles'=> ['.png', '.jpg', '.jpeg', '.gif', '.bmp'],
            'imageCompressEnable'=> true,
            'imageCompressBorder'=> 1600,
            'imageInsertAlign'=> 'none',
            'imageUrlPrefix'=> '',
            'imagePathFormat'=> '/img/{yyyy}{mm}{dd}/{rand=>8}',

            'scrawlActionName'=> 'uploadscrawl',
            'scrawlFieldName'=> 'upfile',
            'scrawlPathFormat'=> '/img/{yyyy}{mm}{dd}/{rand=>8}',
            'scrawlMaxSize'=> 2048000,
            'scrawlUrlPrefix'=> '',
            'scrawlInsertAlign'=> 'none',

            'snapscreenActionName'=> 'uploadimage',
            'snapscreenPathFormat'=> '/img/{yyyy}{mm}{dd}/{rand=>8}',
            'snapscreenUrlPrefix'=> '',
            'snapscreenInsertAlign'=> 'none',

            'catcherLocalDomain'=> ['127.0.0.1', 'localhost', 'img.baidu.com'],
            'catcherActionName'=> 'catchimage',
            'catcherFieldName'=> 'source',
            'catcherPathFormat'=> '/img/{yyyy}{mm}{dd}/{rand=>8}',
            'catcherUrlPrefix'=> '',
            'catcherMaxSize'=> 2048000,
            'catcherAllowFiles'=> ['.png', '.jpg', '.jpeg', '.gif', '.bmp'],

            'videoActionName'=> 'uploadvideo',
            'videoFieldName'=> 'upfile',
            'videoPathFormat'=> '/upload/video/{yyyy}{mm}{dd}/{time}{rand=>6}',
            'videoUrlPrefix'=> '',
            'videoMaxSize'=> 102400000,
            'videoAllowFiles'=> [
                '.flv', '.swf', '.mkv', '.avi', '.rm', '.rmvb', '.mpeg', '.mpg',
                '.ogg', '.ogv', '.mov', '.wmv', '.mp4', '.webm', '.mp3', '.wav', '.mid'],

            'fileActionName'=> 'uploadfile',
            'fileFieldName'=> 'upfile',
            'filePathFormat'=> '/upload/file/{yyyy}{mm}{dd}/{time}{rand=>6}',
            'fileUrlPrefix'=> '',
            'fileMaxSize'=> 51200000,
            'fileAllowFiles'=> [
                '.png', '.jpg', '.jpeg', '.gif', '.bmp',
                '.flv', '.swf', '.mkv', '.avi', '.rm', '.rmvb', '.mpeg', '.mpg',
                '.ogg', '.ogv', '.mov', '.wmv', '.mp4', '.webm', '.mp3', '.wav', '.mid',
                '.rar', '.zip', '.tar', '.gz', '.7z', '.bz2', '.cab', '.iso',
                '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.pdf', '.txt', '.md', '.xml'
            ],

            'imageManagerActionName'=> 'listimage',
            'imageManagerListPath'=> '/upload/image/',
            'imageManagerListSize'=> 20,
            'imageManagerUrlPrefix'=> '',
            'imageManagerInsertAlign'=> 'none',
            'imageManagerAllowFiles'=> ['.png', '.jpg', '.jpeg', '.gif', '.bmp'],

            'fileManagerActionName'=> 'listfile',
            'fileManagerListPath'=> '/upload/file/',
            'fileManagerUrlPrefix'=> '',
            'fileManagerListSize'=> 20,
            'fileManagerAllowFiles'=> [
                '.png', '.jpg', '.jpeg', '.gif', '.bmp',
                '.flv', '.swf', '.mkv', '.avi', '.rm', '.rmvb', '.mpeg', '.mpg',
                '.ogg', '.ogv', '.mov', '.wmv', '.mp4', '.webm', '.mp3', '.wav', '.mid',
                '.rar', '.zip', '.tar', '.gz', '.7z', '.bz2', '.cab', '.iso',
                '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx', '.pdf', '.txt', '.md', '.xml'
            ],
        ];
        return json($config);
	}

    public function uploadimage()
    {
        $file = request()->file('images');
        $filePath = $file->getRealPath();
        $fname = $file->getInfo('name');
        $res=UploadService::upload_img($filePath, $fname);
        if ($res === false)
        {
            return $this->response(500);
        }
        $path = $res['url'];
        $data =[
            'fileName' => $file->getFileName(),
            'saveName' => $path,
        ];
        return $this->response(200, lang('Success') , $data);  
    }
    
    public function uploadIcon()
    {
        $file = request()->file('icon');
        $filePath = $file->getRealPath();
        $fname = $file->getInfo('name');
        $game_id = Request::instance()->param('id');
        $res=UploadService::upload_img($filePath,$fname,$game_id);
        if ($res === false)
        {
            return $this->response(500);
        }
        $path = $res['url'];
        $data =[
            'fileName' => $file->getFileName(),
            'saveName' => $path,
        ];
        return $this->response(200, lang('Success') , $data);  
    }

    protected function _upload()
    {
        $file = request()->file('images');
        $filePath = $file->getRealPath();
        $fname = $file->getInfo('name');
        $res=UploadService::upload_img($filePath, $fname);
        if ($res === false)
        {
            return $this->response(500);
        }
        $size = 0;
        if (isset($res['fsize']))
        {
            $size = $res['fsize'];
        }
        else
            $size = $res['file_size'];
        $ext = pathinfo($fname, PATHINFO_EXTENSION);
        $data =[
            'state' => 'SUCCESS',
            'url' =>buildImageUrl($res['url']),
            'original' => $fname,
            'type' => $ext,
            'size' => $size,
        ];
        return json($data);
    } 

    protected function _catch()
    {
        $data = request()->param();
        $source = $data['source'];
        $list = [];
        foreach($source as $imgUrl)
        {
            $res = UploadService::upload_remote($imgUrl);
            if (!$res)
            {
                $list[] =[
                    'state' => 'ERROR',
                    'url' =>$imgUrl,
                    'source'=>$imgUrl,
                    'original' =>'',
                    'type' => '',
                    'size' => 0,
                ];
            }
            else{
                $list[] = [
                    'state' => 'SUCCESS',
                    'url' => buildImageUrl($res['url']),
                    'source'=>$imgUrl,
                    'original' => $res['path'],
                    'type' => $res['ext'],
                    'size' => $res['fsize'],
                ];
            }
        }
        return json([
            'state'=> count($list) ? 'SUCCESS':'ERROR',
            'list'=> $list
        ]);
    }
}
