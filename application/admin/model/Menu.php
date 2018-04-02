<?php
namespace app\admin\model;

use think\Model;
use think\Session;

class Menu extends Model
{
    /**
     * 后台菜单
     *
     * $class string 导航高亮css ，class名
     * @return array
     */
    function createMenu($class = 'active')
    {
        // 查询当前路由
        $request = \think\Request::instance();
        $map['module']     = strtolower($request->module());
        $map['controller'] = strtolower($request->controller());
        $map['action']     = strtolower($request->action());
        $am = AuthModel::self();
        $userRow = Session::get('userinfo', 'admin');
        $group_id = $userRow['group_id'];
        $isadmin = $userRow['administrator'];
        $rule = $am->getInfo($map);

        // 获取当前路由的所有父级别ID
        $list    = $am->getList();
        $praents = getParents($list, $rule['id']);
        $ids     = array();
        if (!empty($praents)) {
            foreach ($praents as $v) {
                array_push($ids, $v['id']);
            }
        }
        $access = Session::get('user_access');
        if (!$access)
        {
            $access = AccessModel::self()->getAccess($group_id);
        }

        // 遍历数组 增加active标识
        $new_list = array();
        foreach($list as $v) {
            if (!$isadmin && !in_array($v['id'], $access))
            {
                continue;
            }
            if(is_object($v))
            {
                $v = $v->getData();
            }
            // 给父级ID添加ACTIVE
            if(in_array($v['id'], $ids)) {
                $v['active'] = 1; // 该栏目是否高亮
                $v['class']  = $class; // 导航高亮
                $v['open']  = 'open'; // 导航高亮
            } else {
                $v['active'] = 0;
                $v['class']  = '';
                $v['open']  = '';
            }

            // 生成URL
            if ($v['module'] != '#'){
                $v['url'] = url($v['module'] . DS . $v['controller'] . DS . $v['action']);
            } else {
                $v['url'] = '#';
            }

            // 组合新的数组
            if ($v['is_menu'] == 1){
                array_push($new_list, $v);
            }
        }
        // 格式化多维数组
        return list_to_tree($new_list, $pk='id', $parent_id = 'parent_id', $child = 'child', $root = 0);
    }
    /**
     * 后台消息提醒
     *
     */
    public function remindMassage()
    {
        $certification_num = \app\main\model\CertificationModel::self()->where('status',0)->count();
        $recharge_num = \app\main\model\RechargeModel::self()->where('state',0)->count();
        $withdraw_num = \app\main\model\WithdrawModel::self()->where(['state'=>0,'type'=>0])->count();
        //$deposit_num = \app\main\model\WithdrawModel::self()->where(['state'=>0,'type'=>1])->count();
        $total = $certification_num+$recharge_num+$withdraw_num;
        $massage = [
            'total' => $total,
            'certification_num' => $certification_num,
            'recharge_num' => $recharge_num,
            'withdraw_num' => $withdraw_num,
            //'deposit_num'  => $deposit_num
        ];
        return $massage;
    }

}
