<?php
namespace app\main\service;

use app\main\service\CommService;
use app\main\model\ConfigModel;
use app\main\service\UploadService;
class FormatService{

    public static function formatGameicon($game_id){
        return imgurl_add_sign('/game_icon_'.$game_id.'.jpg');
    }

    public static function formatNull($data){
        foreach($data as &$v)
        {
            if (is_null($v))
            {
                $v = "";
            }
        }
        return $data;
    }

    public static function formatGame($item){
        $res=[];
        if (isset($item['cover']) && $item['cover'] > 0)
        {
            $item['cover'] = CommService::getCover($item['cover'], 'path');
        }
        $item['icon'] = self::formatGameicon($item['id']);
        return self::formatNull($item);
    }

    public static function formatAdv($item){
        $res=[];
        if (!empty($item['icon_url']) && strlen($item['icon_url']) > 0)
        {
            $res['pic'] = imgurl_add_sign($item['icon_url']);
        }
        else if (!empty($item['icon']))
        {
            $res['pic'] = CommService::getCover($item['icon'], 'path');
        }
        $res['title'] = $item['title'];
        if ($item['ad_type'] != 4){
            $res['url'] = json_decode($item['url'],true);
            if(isset($res['url']['seller_reputation'])){
                //转换卖家信誉类型
                list($score_type,$score_cnt) = FormatService::formatReputation($res['url']['seller_reputation']);
                $res['url']['score_type'] = $score_type;
                $res['url']['score_cnt'] = $score_cnt;
                unset($res['url']['seller_reputation']);
            }
        }
        else{
            $res['url'] = $item['url'];
        }
        $res['start_time'] = $item['start_time'];
        $res['end_time'] = $item['end_time'];
        $res['ad_type'] = $item['ad_type'];
        return $res;
    }

    public static function ShieldInfo($info,$type){
        switch($type){
            case 'nickname':
                $head = mb_substr($info, 0, 1);
                $tail = mb_substr($info,-1, 1);
                $result = $head.' *** '.$tail;
                break;
            case 'idcard':
                $head = mb_substr($info, 0,3);
                $tail = mb_substr($info, -3,3);
                $result = $head.' *** '.$tail;
                break;
            case 'real_name':
                $head = mb_substr($info, 0,1);
                $result = $head.' ***';
                break;
            case 'card_num':
            case 'card_password':
                if (mb_strlen($info) <= 4) {
                    $head = mb_substr($info, 0,1);
                    $result = $head.' *** ';
                } else {
                    $head = mb_substr($info, 0,1);
                    $tail = mb_substr($info, -3,3);
                    $result = $head.' *** '.$tail;
                }
            break;
            case 'bank':
                $tail = mb_substr($info, -4, 4);
                $result = '**** **** **** '.$tail;
                break;
            case 'mobile':
                $head = mb_substr($info, 0,3);
                $tail = mb_substr($info, -2,2);
                $result = $head.' *** '.$tail;
                break;
            case 'email':
                break;
            default:
        }
        return $result;
    }
    public static function formatBankCardNum($banknum){
        $num = substr($banknum,-4,4);
        return '**** **** **** '.$num;
    }
    /*
     * 这个仅用于转化单个商品类型，返回字符串
     */
    public static function formatGoodsTypesName($type_id){
        $type=[
            GOODS_TYPE_ACCOUNT => '机票',
            GOODS_TYPE_PL      => '酒店',
            GOODS_TYPE_PROP    => '假期游',
            GOODS_TYPE_COIN    => '巴士票',
            GOODS_TYPE_PCARD   => '火车票',
            GOODS_TYPE_RECHANGE=> '周末游',
            GOODS_TYPE_PACKS   => '景点门票',
            GOODS_TYPE_OTHER   => '美食特产'
        ];
        return $type[$type_id];
    }
    /*
     * 这个用于返回游戏有哪些类型，返回的是数组，返回类型id和类型的名称
     */
    public static function formatGoodsTypes($typeIds){
        if(empty($typeIds)){
            return $goods_types=[];
        }
        $type_id = explode(',',$typeIds);
        $type=[
            GOODS_TYPE_ACCOUNT => lang('account'),
            GOODS_TYPE_PL      => lang('dailian'),
            GOODS_TYPE_PROP    => lang('prop'),
            GOODS_TYPE_COIN    => lang('coin'),
            GOODS_TYPE_PCARD   => lang('pointcard'),
            GOODS_TYPE_RECHANGE=> lang('rechange'),
            GOODS_TYPE_PACKS   => lang('giftpack'),
            GOODS_TYPE_OTHER   => lang('other'),
        ];
        $goods_types = [];
        $type_list = [];
        foreach($type_id as $val){
            $goods_types['id']=intval($val);
            $goods_types['type_name']=$type[$val];
            $type_list[]=$goods_types;
        }
        return $type_list;
    }

    /*
     * 展示商品时用到
     */
    public static function formatExtAttr($extattr){
        if(empty($extattr)||$extattr=='NULL'){
            return [];
        }//$extattr 为空的情况下（一般不会出现这种问题）NULL的情况为无其它特殊属性
        $type_detail =[
            'role_name'     =>lang('role_name'),
            'role_level'    =>lang('role_level'),
            'bind'          =>lang('bind'),
            'pl_require'    =>lang('pl_require'),
            'pl_content'    =>lang('pl_content'),
            'time'          =>lang('time'),
            'prop_name'     =>lang('prop_name'),
            'coin_num'      =>lang('coin_num'),
            //'card_type' =>lang('card_type'),
            //'card_value'=>lang('card_value'),
            'charge'        =>lang('charge'),
            'pack_content'  =>lang('pack_content'),
            'use_method'    =>lang('use_method'),
            'send_type'     =>lang('send_type'),
        ];

        $extattr = json_decode($extattr, true);
        $temp = [];
        $data = [];
        $text='';
        foreach($extattr as $key=>$item){
            $temp['title'] = $type_detail[$key];
            if($key == 'bind'){//是绑定类型的话做类型转化
                if (is_array($item)) {
                    $bind = $item;
                } else {
                    $bind = explode(',',$item);
                }
                foreach ($bind as $val) {
                    if ($text=='') {
                        $text.= self::formatBindType($val);
                    } else {
                        $text.= ','.self::formatBindType($val);
                    }
                }
                $temp['text'] = $text;
            }elseif($key =='time'){//是时间的话时间戳转为时间日期
                if(is_nan(intval($item))){
                    $temp['text'] = date('Y-m-d',intval($item));
                }
                $temp['text'] = strval($item);
            }elseif($key=='send_type'){
                if($item==1){
                    $item = lang('manual send card');
                }else{
                    $item = lang('auto send card');
                }
                $temp['text'] = $item;
            }else{//其它原样输出
               $temp['text'] = lang(strval($item));
            }
            $data[]=$temp;
        }
        return $data;
    }

    public static function formatBindType($type_id){
        $type = [
            ACCOUNT_BIND_PHONE  =>  lang('security_phone'),
            ACCOUNT_BIND_MAIL   =>  lang('security_email'),
            ACCOUNT_BIND_PROBLEM=>  lang('security_problem'),
            ACCOUNT_BIND_CARD   =>  lang('security_idcard'),
            ACCOUNT_BIND_OTHER  =>  lang('security_other'),
            ACCOUNT_BIND_NONE   =>  lang('no_bind'),
        ];
        return $type[$type_id];
    }

    public static function formatPayType($paytype){
        $pay = [
            -1       =>lang('no pay'),
            0        =>lang('pay_default'),
        ];
        return $pay[$paytype];
    }

    public static function formatPayTypeToNo($pay){
        $num = [
            'default' => 0,
        ];
        return $num[$pay];
    }

    public static function formatRechargeType($num){
        $charge = [
            '0'=>lang('outline bank recharge'),
            '1'=>lang('outline_cash recharge'),
        ];
        return $charge[$num];
    }

    public static function formatCapTypeToTitleNo($captype, $no, $bal_pay_type){
        if ($captype == FINANCE_WITHDRAW) {
            //1代表提现失败
            if ($bal_pay_type == 1) {
                return lang('finance_withdraw:fail title no',[$no]);
            }
        }
        $title = [
            FINANCE_RECHARGE => lang('finance_recharge:title no', [$no]),
            FINANCE_PAY      => lang('finance_pay:title no', [$no]),
            FINANCE_INCOME   => lang('finance_income:title no', [$no]),
            FINANCE_WITHDRAW => lang('finance_withdraw:title no', [$no]),
            FINANCE_REFUND   => lang('finance_refund:title no', [$no]),
            FINANCE_PAY_DEPOSIT => lang('finance_pay_deposit:title:title'),
            FINANCE_REFUND_DEPOSTI => lang('finance_refund_deposit:title'),
        ];
        return $title[$captype];
    }

    public static function formatRemark($cap_type, $remark, $bal_pay_type, $state){
        $remark = json_decode($remark, true);
        if ($cap_type == FINANCE_RECHARGE) {
            $remark[0] = self::formatRechargeType($remark[0]);
        } elseif ($cap_type == FINANCE_PAY) {
            $remark[0] = self::formatPayType($remark[0]);
        }
        $result = [
            FINANCE_RECHARGE => lang('finance_recharge:remark', $remark),
            FINANCE_PAY      => lang('finance_pay:remark', $remark),
            FINANCE_INCOME   => lang('finance_income:remark', $remark),
            FINANCE_REFUND   => '',
            FINANCE_PAY_DEPOSIT => '',
            FINANCE_REFUND_DEPOSTI => '',
        ];
        if ($cap_type == FINANCE_WITHDRAW) {
            //1提现失败
            if ($bal_pay_type == 1) {
                return lang('finance_withdraw:fail remark', $remark);
            } else {
                //待处理状态
                if ($state == 2) {
                    return lang('finance_withdraw:success remark', $remark);
                } else {
                    return lang('finance_withdraw:wait remark', $remark);
                }
            }
        }
        return $result[$cap_type];
    }

    public static function formatGoodsTypeToValidate($goods_type){
        $type = [
            '1'=>'Account',//账号
            '2'=>'Pl',//代练
            '3'=>'Prop',//道具
            '4'=>'Coin',//游戏币
            //'5'=>'Pcard'
            '6'=>'Recharge',//充值
            '7'=>'Pack',//礼包
            '8'=>'Other'//其它
        ];
        return $type[$goods_type];
    }

    public static function formatReputation($reputation){
        if($reputation<=250){
            $score_type = 0;
            if($reputation<=10){
                $score_cnt = 1;
            }elseif($reputation>10&&$reputation<=40){
                $score_cnt = 2;
            }elseif($reputation>40&&$reputation<=90){
                $score_cnt = 3;
            }elseif($reputation>90&&$reputation<=150){
                $score_cnt = 4;
            }else{
                $score_cnt = 5;
            }
            return [$score_type,$score_cnt];
        }elseif($reputation>250&&$reputation<=10000){
            $score_type = 1;
            if($reputation>250&&$reputation<=500){
                $score_cnt = 1;
            }elseif($reputation>500&&$reputation<=1000){
                $score_cnt = 2;
            }elseif($reputation>1000&&$reputation<=2000){
                $score_cnt = 3;
            }elseif($reputation>2000&&$reputation<=5000){
                $score_cnt = 4;
            }else{
                $score_cnt = 5;
            }
            return [$score_type,$score_cnt];
        }elseif($reputation>10000&&$reputation<=500000){
            $score_type = 2;
            if($reputation>10000&&$reputation<=20000){
                $score_cnt = 1;
            }elseif($reputation>20000&&$reputation<=50000){
                $score_cnt = 2;
            }elseif($reputation>50000&&$reputation<=100000){
                $score_cnt = 3;
            }elseif($reputation>100000&&$reputation<=200000){
                $score_cnt = 4;
            }else{
                $score_cnt = 5;
            }
            return [$score_type,$score_cnt];
        }else{
            $score_type = 3;
            if($reputation>500000&&$reputation<=1000000){
                $score_cnt = 1;
            }elseif($reputation>1000000&&$reputation<=2000000){
                $score_cnt = 2;
            }elseif($reputation>2000000&&$reputation<=5000000){
                $score_cnt = 3;
            }elseif($reputation>5000000&&$reputation<=10000000){
                $score_cnt = 4;
            }else{
                $score_cnt = 5;
            }
            return [$score_type,$score_cnt];
        }
    }
    public static function thirdTypeToString($third_type){
        $third_name = [
            0 => 'Twitter',
            1 => 'Facebook',
            2 => 'Google',
        ];
        return $third_name[$third_type];
    }
    /**
     * picUrlToQiNiu 上传图片url到七牛
     * @param  string   图片链接
     * @return string   上传到七牛后的路径
     */
    public static function picUrlToQiNiu($pic_url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pic_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $pic = curl_exec($ch);
        curl_close($ch);
        $temp_file = tempnam(sys_get_temp_dir(), 'php');
        $file = fopen($temp_file, 'a');
        fwrite($file, $pic);
        fclose($file);
        $res = UploadService::upload_img($temp_file, $pic_url);
        return $res['url'];
    }

    public static function formatGameType($game_type){
        $string = [
            0 => '端游',
            1 => '手游',
            2 => '页游'
        ];
        return $string[$game_type];
    }
    /*
     * 下面是测试用的
     */
    public static function ShieldAndroid($data,$device_type,$version){
        $config_model = ConfigModel::self();
        $config = json_decode($config_model->getValue('ios_shield'),true);
        if ($config['shield']== 'true') {
            $condition = strtolower($device_type) == 'ios' && $version >= $config['version'];
            if ($condition) {
                $data = self::_ShieldAndroid($data);
            }
        }
        return $data;
    }

    private static function _ShieldAndroid($data){
        if (is_array($data)) {
            $data_list = [];
            foreach ($data as $val) {
                if (isset($val['service_name']) && strstr($val['service_name'],'Android')) {
                    $val['service_name'] = str_replace('Android',lang('other_service'),$val['service_name']);
                } elseif (isset($val['server_name']) && strstr($val['server_name'],'Android')) {
                    $val['server_name'] = str_replace('Android',lang('other_service'),$val['server_name']);
                }
                $data_list[] = $val;
            }
            return $data_list;
        } else {
            if (strstr($data,'Android')) {
                $data = str_replace('Android',lang('other_service'),$data);
            }
            return $data;
        }
    }
}