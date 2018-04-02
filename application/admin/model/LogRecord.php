<?php
namespace app\admin\model;

use \think\Config;
use \think\Model;
use \think\Session;


/**
 * 操作日志记录
 */
class logRecord extends Model
{
    protected $updateTime = false;
    protected $name = 'sx_log_record';
    protected $connection = 'db.cms';

    protected $insert     = ['ip', 'user_id','browser','os'];
    protected $type       = [
        'create_time' => 'int',
    ];
    public static function self()
    {
        return new self();
    }

    /**
     * 记录ip地址
     */
    protected function setIpAttr()
    {
        return get_client_ip();
    }

    /**
     * 浏览器把版本
     */
    protected function setBrowserAttr()
    {
        return getBrowser().'-'.getBrowserVer();
    }

    /**
     * 系统类型
     */
    protected function setOsAttr()
    {
        return getOs();
    }

    /**
     * 用户id
     */
    protected function setUserIdAttr()
    {
        $user_id = 0;
        if (Session::has('userinfo','admin')) {
            $user = Session::get('userinfo','admin');
            $user_id = $user['id'];
        }
        return $user_id;
    }
 
    public function record($remark)
    {
        $this->save(['remark' => $remark, 'create_time'=>time()]);
    }


    public function UniqueIpCount()
    {   
        $data = $this->column('ip');
        $data = count( array_unique($data) );
        return $data;
    }
    public function getList($map=[],$order,$limit,$param=[])
    {
        return $this->alias('a')
                ->field('a.id,a.user_id,a.remark,a.ip,a.create_time,b.username')
                ->join('sx_admin_user b','a.user_id = b.id','LEFT')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$param]);
    }
    public function remove($ids)
    {
        if (is_array($ids))
            $this->where('id', 'in', $ids);
        else
            $this->where('id', $ids);
        return $this->delete();
    }
}
