<?php
/**
 * @Author: Marte
 * @Date:   2018-03-19 22:14:01
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-03-19 22:19:04
 */
namespace  app\home\controller;
use think\Request;
use app\main\model\BlogModel;
use app\main\model\BlogcommentModel;
use app\main\model\CityModel;
/**
*
*/
class Travel extends Common
{
    function index(){
        return view();
    }
 
 	function hotels(){
        return view();
    }
    function holidays(){
        return view();
    }
    function flights_hotels(){
        return view();
    }
    function trains(){
        return view();
    }
    function weekend(){
        return view();
    }
    function deals(){
        return view();
    }
    function bus(){
        return view();
    }

    function products(){
        
        return view();
    }

    public function single(){
        $id=input('id');
        $res=BlogModel::self()->getDetail($id);
        $comment=BlogcommentModel::self()->getCommList($id,0,$array[]);
        $cmcount=BlogcommentModel::self()->getCommentCnt($id);
        $city=CityModel::self()->getcity();
        // dump($comment);die();
        $this->assign([
            'res'=>$res,
            // 'page'=>$page,
            'comment'=>$comment,
            'city'=>$city,
            'cmcount'=>$cmcount,
        ]);
        return view();
    }

    public function signup(){

        return view();
    }

    public function blog(){
        $map['a.status']=1;
        $res=BlogModel::self()->getList($map,'create_at desc',5, $params=[]);
        $page=$res->render();
        $com['c.status']=1;
        $comment=BlogcommentModel::self()->getList($com,'create_at desc',5, $params=[]);
        $city=CityModel::self()->getcity();
        $art['recommend']=1;
        $rec=BlogModel::self()->getList($art,'create_at desc',5, $params=[]);
        // dump($res);die();
        $this->assign([
            'res'=>$res,
            'page'=>$page,
            'comment'=>$comment,
            'city'=>$city,
            'rec'=>$rec,
        ]);
        return view();
    }
}