<?php
/**
 * @Author: Marte
 * @Date:   2018-03-19 22:14:01
 * @Last Modified by:   Marte
 * @Last Modified time: 2018-03-19 22:19:04
 */
namespace  app\home\controller;
use think\Request;
use app\main\model\ArticleModel;
/**
*
*/
class Blog extends Common
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

    public function blog(){
        // $res=ArticleModel::self()->getStrategyList();
        
    }

}