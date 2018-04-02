<?php

namespace app\main\model;

use app\main\model\CustomerModel;
use \think\Model;

class CertificationModel extends Model
{
    protected $name = 'tab_certification';
    protected $connection = 'db.main';
    protected $dateFormat = false;
    
    public static function self()
    {
        return new self();
    }

    public function getUserCertStatus($user_id){
        return $this->field('status')->where('user_id',$user_id)->order('id desc')->find();
    }

    public function insertCertInfo($data){
        $cnt = $this->where(['idcard'=>$data['idcard'],'status'=>1])->count();
        if ($cnt>0) {
            return 900903;
        }
        $exist  = $this->field('status')->where(['user_id'=>$data['user_id'],'status'=>['<>',2]])->find();
        if ($exist&&$exist['status']==1) {
            return 901103;
        } elseif($exist&&$exist['status']==0) {
            return 901105;
        }
        $this->insert($data);
        return 0;
    }

    public function getCertInfo($user){
        $jude = CustomerModel::self()->getOneUserInfo($user['user_id'],'idcard');
        if($jude['idcard']==''){
            $data['idcard'] = 0;
        }else{
            $data['idcard'] = 1;
        }
        if($user['mobile']==''){
            $data['mobile'] = 0;
        }else{
            $data['mobile'] =1;
        }
        if($user['email']==''){
            $data['email'] = 0;//未绑定邮箱
        }else{
            if($user['status']==11||$user['status']==21){
                $data['email'] = 1;//已经激活
            }elseif($user['status']==10||$user['status']==20){
                $data['email'] = 2;//邮箱未激活
            }
        }
        $status = $this->getUserCertStatus($user['user_id']);
        if(empty($status)){
            $data['cert'] = 0;//没有申请验证
        }else{
            if($status['status']==1){
                $data['cert'] = 1;//实名卖家
            }elseif($status['status']==0){
                $data['cert'] = 2;//等待审核
            }elseif($status['status']==2){
                $data['cert'] = 3;//审核未通过
            }
        }
        return $data;
    }
    public function getCertList($map,$order,$limit,$param=[])
    {
        $res = $this->alias('a')->field('a.*,b.vid')
                ->join('pp_jy.tab_user b','a.user_id=b.id','LEFT')
                ->where($map)
                ->order($order)
                ->paginate($limit,false,['query'=>$param]);
        return $res;
    }
    public function getDetails($id)
    {
        return $this->field('*')->find('$id');
    }
    public function edit($id,$data)
    {
        $cm = CustomerModel::self();
        $this->startTrans();
        $cm->startTrans();
        switch ($data['status'])
        {
            case 0:
                $update = [
                    'seller_status'=>0
                ];
                break;
            case 1:
                $ret = $this->field('real_name,idcard,id_pic')->where('id',$id)->find();
                $update = [
                    'seller_status'=>1,
                    'idcard_pic'=> $ret['id_pic'],
                    'real_name' => $ret['real_name'],
                    'idcard'=>$ret['idcard']
                ];
                break;
            case 2:
                $update = [
                    'seller_status'=>0
                ];
                break;
        }
        try {
           $res=$this->where('id',$id)->update(['status'=>$data['status']]);
           $res = $this->where('id',$id)->field('user_id')->find();
           $ret=$cm->where('id',$res['user_id'])->update($update);
           MessageModel::self()->certificationSend($res['user_id'],time(),$data);
           $this->commit();
           $cm->commit();
        } catch (Exception $ex) {
            $this->rollback();
            throw Exception($ex);
            return false;
        }
        return true;
    }
}
