<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller\Admin;
use app\main\model\VipModel;
use app\main\model\VipUserModel;
use app\user\model\UserModel;
use think\Loader;
use think\View;
class Vip extends Admin
{
    public function list(){
        $where = [];
        $vip_model = VipModel::self();
        $vip_list = $vip_model->getVipList($where,'',10);
        foreach ($vip_list as $item){
            $item['alias'] = lang($item['alias']);
        }
        $this->assign('list',$vip_list);
        return view();
    }
    /**
     * switch_on_off 开闭特权
     * @param $status  状态 0:关闭 1:开启
     */
    public function switch_on_off(){
        $status = $this->param['status'];
        $vip_model = VipModel::self();
        $vip = $vip_model->field('id,name')->where('id',$this->param['id'])->find();
        $ret = $vip_model->where('id',$this->param['id'])->setField('status',$status);
        if ($status == 1) {
            $record = lang('switch_on the vip').$vip['name'];
        } else {
            $record = lang('switch_off the vip').$vip['name'];
        }
        if ($ret == 1) {
            Loader::model('LogRecord')->record($record);
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }

    public function userlist(){
        $where = [];
        $search = $this->param;
        if (isset($search['vip_id']) && $search['vip_id'] != '') {
            $where['vip_id'] = $search['vip_id'];
        }
        if (isset($search['username']) && $search['username'] != '') {
            $where['username'] = ['like','%'.$search['username'].'%'];
        }
        if (isset($search['start_time']) && $search['start_time'] != '') {
            if ($search['end_time'] != '') {
                $where['start_time'] = ['between', [strtotime($search['start_time']), strtotime($search['end_time'])]];
            } else {
                $where['start_time'] = ['>', strtotime($search['start_time'])];
            }
        }
        $vuser_model = VipUserModel::self();
        $user_list = $vuser_model->getUserVipList($where,'id desc',10);
        $vip_model = VipModel::self();
        $vip = $vip_model->cache(true)->select();
        $vip_list = [];
        foreach ($vip as $key => $val) {
            $vip_list[$val['id']] = $val;
        }
        foreach ($user_list as $item) {
            $item['name'] = $vip_list[$item['vip_id']]['name'];
        }
        $page = $user_list->render();
        $this->assign('vip_list', $vip);
        $this->assign('list', $user_list);
        $this->assign('search', $search);
        $this->assign('page', $page);
        return view();
    }
    /**
     * userdetail vip详情 历史记录
     */
    public function userdetail(){
        $param = $this->param;
        $where['user_id'] = $param['user_id'];
        $where['type'] = $param['type'];
        $vip_model = VipModel::self();
        $vip = $vip_model->field('name')->where('type',$param['type'])->find();
        $vuser_model = VipUserModel::self();
        $list = $vuser_model->where($where)->select();
        $detail = [];
        foreach ($list as $val) {
            if ($val['id'] == $param['id']) {
                $detail = $val;
                $detail['vip_name'] = $vip['name'];
                break;
            }
        }
        $this->assign('list',$list);
        $this->assign('detail',$detail);
        return view();
    }

    public function adduser(){
        $vip_model = VipModel::self();
        $vip = $vip_model->field('id, name')->where('status',1)->select();
        $this->assign('vip', $vip);
        return view();
    }

    public function userIsExist(){
        $username = $this->param['username'];
        $user_model = UserModel::self();
        $user = $user_model->userIsExist($username);
        if (!$user) {
            return $this->response(404010);
        } else {
            return $this->response(200,'',$user);
        }
    }
    /**
     * saveuser 添加用户特权
     * @return [type] [description]
     */
    public function saveuser(){
        $param = $this->param;
        $user_model = UserModel::self();
        $where['id'] = $param['user_id'];
        $user = $user_model->userIsExist($param['username'], $where);
        if (!$user) {
            return $this->response(404010);
        }
        $vip_model = VipModel::self();
        $vip = $vip_model->where('id',$param['vip_id'])->find();
        if (!$vip) {
            return $this->response(201);
        }
        $data = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'vip_id' => $vip['id'],
            'type'   => $vip['type'],
            'start_time' => strtotime($param['start_time']),
            'end_time' => strtotime($param['end_time']) + 24*60*60-1
        ];
        $vuser_model = VipUserModel::self();
        $ret = $vuser_model->insert($data);
        if ($ret == 1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
    /**
     * deleteuser 撤销用户特权
     */
    public function undouser(){
        $param = $this->param;
        $vuser_model = VipUserModel::self();
        $ret = $vuser_model->where('id',$param['id'])->setField('status',0);
        if ($ret == 1) {
            return $this->response(200);
        } else {
            return $this->response(201);
        }
    }
}

