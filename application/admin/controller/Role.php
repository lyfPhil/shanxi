<?php
namespace app\admin\controller;

use app\user\model\UserModel;
use app\admin\model\AuthGroup;

class Role extends Admin
{

    function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 列表
     */
    public function index()
    {
        return view();
    }

    public function getList()
    {
        if(!request()->isAjax()) {
            $this->error(lang('Request type error'), 4001);
        }

        $request = request()->param();
        $data = model('Role')->getList( $request );
        return $data;
    }

}
