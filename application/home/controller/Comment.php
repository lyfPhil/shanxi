<?php
/**
 * @Author: Marte
 * @Date:   2018-03-21 20:57:44
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-03-22 13:34:45
 */
namespace app\home\controller;
use app\main\model\ArtcommentModel;
use think\Controller;
/**
*
*/
class Comment extends Controller
{

    public function save()
    {
        $data=input('post.');
        dump($data);die();
        $res=ArtcommentModel::self()->insertGetId($data);
        if ($res) {
            $code = array('code' => 200);
            return json_encode($code);
        }
        
    }

    public function get($artid)
    {
        // $data=input('post.');
        $res=db('tourists')->where(['artid'=>$artid,'status'=>1])->slecte();
        // $code = array('code' => 200);
        return $res;
    }
}