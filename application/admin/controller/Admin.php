<?php
namespace app\admin\controller;

use app\common\controller\Common;
use think\Controller;
use think\Session;
use think\Request;
use think\Url;
use app\admin\model\AuthModel;
use app\admin\model\AccessModel;

class Admin extends Common
{
	protected $uid = 0;
	protected $group_id = 0;
	protected $isadmin = 0;
    protected $ruleinfo=[];

	function _initialize()
	{
        parent::_initialize();

        if( !Session::has('userinfo', 'admin') ) {
            $this->error('Please login first', url('admin/login/index'));
        }
        $userRow = Session::get('userinfo', 'admin');
        $this->uid = $userRow['id'];
        $sso=check_sso($this->uid);
        if ($sso !== True && is_array($sso))
        {
            $error = lang('Login at other place', ['time'=>date('Y-m-d H:i:s', $sso['t']), 'ip'=>$sso['ip']]);
            Session::clear('admin');
            return $this->error($error, url('admin/login/index'));
        }
        //验证权限
        $request = Request::instance();
        $this->ruleinfo['module']     = strtolower($request->module());
        $this->ruleinfo['controller'] = strtolower($request->controller());
        $this->ruleinfo['action']     = strtolower($request->action());

        $this->group_id = $userRow['group_id'];
        $this->isadmin = $userRow['administrator'];
        if(!$this->checkRule()) {
            $this->error(lang('Without the permissions page'));
        }
	}

    private function checkRule(){
        if ($this->isadmin)
        {
            return true;
        }
        $rule=AuthModel::self()->where($this->ruleinfo)->find();
        if ($rule)
        {
            $access = Session::get('user_access','admin');
            if (!$access)
            {
                $access = AccessModel::self()->getAccess($this->group_id);
                Session::set('user_access', $access,'admin');
            }
            $ret =in_array($rule['id'], $access);
            return $ret;
        }
        return true;
    }

    public static function goLogin()
    {
            Session::clear();
            //$this->redirect( url('admin/login/index') );
    }

}

