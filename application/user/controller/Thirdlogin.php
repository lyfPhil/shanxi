<?php
namespace app\user\controller;

use app\common\controller\ThirdApi;

use app\common\controller\ApiBase;
use app\user\model\UserModel;
use app\main\model\CustomerModel;
use app\user\model\ThirdUserModel;
use app\user\service\LoginService;
use app\v1\service\FormatService;
use app\common\service\LangService;
use Abraham\TwitterOAuth\TwitterOAuth;
use Facebook\Facebook;
use League\OAuth2\Client\Provider\Google;
use think\Env;
use think\Config;
use think\Cache;
use think\Cookie;
class Thirdlogin extends ThirdApi{
    /**
     * third_account 查看第三登录账号
     */
    protected $email_url = '/account/thirdlogin#email';
    protected $already_url = '/account/thirdlogin#already';

    public function third_account(){
        if (!$this->is_login()) {
            return $this->wrong(400100);
        }
        $where['user_id'] = $this->userInfo['user_id'];
        $where['status'] = 1;
        $third_account = [
            0 => ['id' => '', 'third_type' => 0, 'nickname' => ''],
            1 => ['id' => '', 'third_type' => 1, 'nickname' => ''],
        ];
        $third_user_model = ThirdUserModel::self();
        $account = $third_user_model->field('id, third_type, nickname,status')->where($where)->group('third_type')->select();
        foreach($account as $val) {
            $third_account[$val['third_type']]['id'] = $val['id'];
            $third_account[$val['third_type']]['third_type'] = $val['third_type'];
            $third_account[$val['third_type']]['nickname'] = $val['nickname'];
        }
        return $this->response(['ret' => $third_account]);
    }
    /*
     * Twitter登录页面
     */
    public function connect_twitter(){
        if (!checkParam($this->param, ['device_type', 'bind', 'url'])) {
            return $this->wrong(400);
        }
        $param['device_type'] = $this->param['device_type'];
        $param['bind'] = $this->param['bind'];
        if ($param['bind'] == 1) {
            if (!$this->is_login()) {
                return $this->wrong(400100);
            }
            $param['user_id'] = $this->param['user_id'];
            $param['token'] = $this->param['token'];
        }
        $param['url'] = $this->param['url'];
        $state = base64_encode(json_encode($param));
        //twitter配置
        $twitter_config = Config::get('setting.twitter');
        $key = $twitter_config['key'];
        $secert = $twitter_config['secert'];
        $callback = Env::get('web.host').$twitter_config['callback'].'?state='.$state;
        //twitter
        $twitter  = new TwitterOAuth($key, $secert);
        //twitter是先获取一个request_token
        $request_token = $twitter->oauth('oauth/request_token', array('oauth_callback' => $callback));
        if (isset($request_token['error'])) {
            return $this->wrong(400130);
        }
        //获取request_token后，生成登录地址
        $url = $twitter->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
        $res = ['url' => $url];
        return $this->response($res);
    }
    /**
     * m_twittercallback description
     * 手机端已经拿到user_id,screen_name,和token
     */
    public function m_twittercallback(){
        if (!checkParam($this->param,['third_id','oauth_token','oauth_token_secret','nickname','bind'])) {
            return $this->wrong(400);
        }
        $third_user['third_id'] = $this->param['third_id'];
        $third_user['third_type'] = Twitter;
        $access_token['oauth_token'] = $this->param['oauth_token'];
        $access_token['oauth_token_secret'] = $this->param['oauth_token_secret'];
        $access_token['screen_name'] = $this->param['nickname'];
        if ($this->param['bind'] == 1) {
            if (!$this->is_login()) {
                return $this->wrong(400100);
            } else {
                $third_user['nickname'] = $access_token['screen_name'];
                $this->thirdBindAccount($third_user, $this->param);
            }
        } else {
            $twitter_config = Config::get('setting.twitter');
            $key = $twitter_config['key'];
            $secert  = $twitter_config['secert'];
            $twitter = new TwitterOAuth($key, $secert);
            $this->twtiterLogReg($third_user, $access_token, $twitter, $this->param);
        }
    }
    /*
     * 网页Twitter回调函数
     */
    public function twittercallback(){
        if (isset($this->param['denied'])) {
            $url = Env::get('web.main').'/account/password#400';
                return $this->redirect($url);
        }
        //获取得到的登录页面返回的oauth_token,oauth_verifier,自定义参数state
        if (!checkParam($this->param, ['oauth_token', 'oauth_verifier' ,'state'])) {
            $url = Env::get('web.main').'/account/password#400';
            return $this->redirect($url);
        }
        $oauth_verifier = $this->param['oauth_verifier'];
        $oauth_token = $this->param['oauth_token'];
        //twitter
        $twitter_config = Config::get('setting.twitter');
        $key = $twitter_config['key'];
        $secert  = $twitter_config['secert'];
        $twitter = new TwitterOAuth($key, $secert);
        //根据得到的获取用户oauth_token和用户第三方唯一id
        $access_token = $twitter->oauth('oauth/access_token', ['oauth_verifier' => $oauth_verifier,'oauth_token' => $oauth_token]);
        //已经获得唯一
        $third_user['third_id'] = $access_token['user_id'];
        $third_user['third_type'] = Twitter;

        $state = $this->param['state'];
        $param = json_decode(base64_decode($state), true);
        if($param['bind'] == 1) {
            $res = LoginService::checkToken($param['user_id'], $param['device_type'], $param['token']);
            if (is_numeric($res)) {
                //网页跳转链接
                $url = Env::get('web.main').'/account/password#400';
                return $this->redirect($url);
            }
        }
        if ($param['bind'] == 1) {
            $third_user['nickname'] = $access_token['screen_name'];
            $this->thirdBindAccount($third_user, $param);
        } else {
            $this->twtiterLogReg($third_user, $access_token, $twitter, $param);
        }
    }
    /*
     * twittet的注册登录
     */
    protected function twtiterLogReg($third_user, $access_token, $twitter, $param){
        $third_user_model = ThirdUserModel::self();
        $third_user_exsit = $third_user_model->thirdPartyUserIsExsit($third_user);
        $user_model = UserModel::self();
        $third_type = $third_user['third_type'];
        $device_type = strtolower($param['device_type']);
        if ($third_user_exsit) {
            //存在则直接登录
            $third_user_exsit['third_type'] = $third_user['third_type'];
            $ret = $user_model->thirdPartyUserLogin($third_user_exsit);
        } else {
            //不存在则用新的oauth_token和oauth_token_secret去请求用户信息注册
            $twitter->setOauthToken($access_token['oauth_token'], $access_token['oauth_token_secret']);
            $user_info = $twitter->get('account/verify_credentials',['include_email' => 'true' ]);
            $third_user['email'] = isset($user_info->email) ? $user_info->email : '';
            $third_user['avatar'] = $user_info->profile_image_url;
            $third_user['nickname'] = $user_info->screen_name;
            if ($third_user['email'] == '') {
                $key = $third_type.'_'.$third_user['third_id'];
                Cache::set($key, $third_user, 60*60);
                if ($device_type == 'web') {
                    $url = Env::get('web.main')."/account/thirdlogin#email?third_id={$third_user['third_id']}&third_type={$third_user['third_type']}&url={$param['url']}";
                    return $this->redirect($url);
                } else {
                    return $this->response(['code' => 909100, 'ret' => $third_user]);
                }
            }
            //邮箱已经存在的则是跳到是否绑定的步骤
            $exsit = $user_model->where('email', $third_user['email'])->count();
            if ($exsit >= 1) {
                $key = $third_type.'_'.$third_user['third_id'];
                Cache::set($key, $third_user, 60*60);
                if ($device_type == 'web') {
                    $url = Env::get('web.main')."/account/thirdlogin#already?third_id={$third_user['third_id']}&third_type={$third_user['third_type']}&url={$param['url']}";
                    //$url_param = "third_id={$third_user['third_id']}&third_type={$third_user['third_type']}";
                    //$url = Env::get('web.main').$this->already_url.$url_param;
                    return $this->redirect($url);
                } else {
                    return $this->response(['code' => 909103,'ret' => $third_user]);
                }
            }
            //邮箱不存在则直接注册新账号
            $ret = $user_model->thirdPartyUserEmailReg($third_user);
        }

        if ($ret == 0) {
            $user = user_info();
            $data['device_type'] = $param['device_type'];
            $data['url'] = isset($param['url']) ? $param['url'] : '' ;
            $this->returnUserInfo($user, $data);
        } else {
            return $this->wrong(400,'请重新尝试');
        }
    }
    /*
     * facebook登录页面
     */
    public function connect_facebook(){
        if (!checkParam($this->param, ['device_type','bind', 'url'])) {
            return $this->wrong(400);
        }
        $param['device_type'] = $this->param['device_type'];
        $param['bind'] = $this->param['bind'];
        if ($param['bind'] == 1) {
            //绑定的话就要判断登录，把user_id和token放进参数中
            if (!$this->is_login()) {
                return $this->wrong(400100);
            }
            $param['user_id'] = $this->param['user_id'];
            $param['token'] = $this->param['token'];
        }
        $param['url'] = $this->param['url'];
        $fb_config = Config::get('setting.facebook');
        $fb = new Facebook($fb_config);
        $callback = Env::get('web.host').$fb_config['callback'];
        //设置state参数
        $state = base64_encode(json_encode($param));
        $helper = $fb->getRedirectLoginHelper();
        $helper->getPersistentDataHandler()->set('state', $state);
        //设置权限
        $permissions = ['email', 'public_profile'];
        $loginUrl = $helper->getLoginUrl($callback, $permissions);
        $res = ['url' => $loginUrl];
        return $this->response($res);
    }
    /*
     * 手机端facebook回调
     */
    public function m_facebookcallback(){
        if (!checkParam($this->param,['access_token', 'bind', 'device_type'])) {
            return $this->wrong(400);
        }
        $param = $this->param;
        if ($this->param['bind'] == 1) {
            if (!$this->is_login()) {
                return $this->wrong(400100);
            }
        }
        $fb_config = Config::get('setting.facebook');
        $fb = new Facebook($fb_config);
        $access_token = $this->param['access_token'];
        $me = 'me?fields=id,name,email,first_name,last_name,third_party_id,picture{url},accounts{emails,phone}';
        $response = $fb->get($me, $access_token);
        $body = $response->getDecodedBody();
        //第三方个人信息
        $third_user['third_id'] = $body['id'];
        $third_user['nickname'] = $body['name'];
        $third_user['avatar'] = $body['picture']['data']['url'];
        $third_user['email'] = isset($body['email']) ? $body['email'] : '';
        $third_user['third_type'] = Facebook;
        //判断是否绑定
        if ($param['bind'] == 1) {
            $this->thirdBindAccount($third_user, $param);
        } else {
            $this->thirdLoginReg($third_user, $param);
        }
    }
    /*
     * facebook回调
     */
    public function facebookcallback(){
        if (isset($this->param['error'])) {
            if ($this->param['error'] == 'access_denied') {
                $url = Env::get('web.main').'/account/password#400';
                return $this->redirect($url);
            } else {
                $url = Env::get('web.main').'/account/password#400';
                return $this->redirect($url);
            }
        }
        //参数判断
        if (!isset($this->param['state'])) {
            $url = Env::get('web.main').'/account/password#400';
            return $this->redirect($url);
        }
        $state = $this->param['state'];
        $param = json_decode(base64_decode($state), true);
        if (!isset($param['device_type']) || !isset($param['bind'])) {
            $url = Env::get('web.main').'/account/password#400';
            return $this->redirect($url);
        }
        if($param['bind'] == 1) {
            $res = LoginService::checkToken($param['user_id'], $param['device_type'], $param['token']);
            if (is_numeric($res)) {
                $url = Env::get('web.main').'/account/password#400';
                return $this->redirect($url);
            }
        }
        //Facebook
        $fb_config = Config::get('setting.facebook');
        $fb = new Facebook($fb_config);
        //helper,设置state
        $helper = $fb->getRedirectLoginHelper();
        $helper->getPersistentDataHandler()->set('state', $state);
        //获取access_token
        $access_token = $helper->getAccessToken();
        $me = 'me?fields=id,name,email,first_name,last_name,third_party_id,picture{url},accounts{emails,phone}';
        $response = $fb->get($me, $access_token);
        $body = $response->getDecodedBody();
        //第三方个人信息
        $third_user['third_id'] = $body['id'];
        $third_user['nickname'] = $body['name'];
        $third_user['avatar'] = $body['picture']['data']['url'];
        $third_user['email'] = isset($body['email']) ? $body['email'] : '';
        $third_user['third_type'] = Facebook;
        //判断是否绑定
        if ($param['bind'] == 1) {
            $this->thirdBindAccount($third_user, $param);
        } else {
            $this->thirdLoginReg($third_user, $param);
        }
    }
    /*
     * google登录页面
     */
    public function connect_google(){
        if (!checkParam($this->param, ['device_type', 'bind'])) {
            return $this->wrong(400);
        }
        //初始化变量
        $param['device_type'] = $this->param['device_type'];
        $param['bind'] = $this->param['bind'];
        if ($param['bind'] == 1) {
            //绑定的话就要判断登录，把user_id和token放进参数中
            if (!$this->is_login()) {
                return $this->wrong(400100);
            }
            $param['user_id'] = $this->param['user_id'];
            $param['token'] = $this->param['token'];
        }
        //google
        $google_config = Config::get('setting.google');
        //回调地址
        $google_config['redirectUri'] = Env::get('web.host').$google_config['redirectUri'];
        $google = new Google($google_config);
        //设置state即参数
        $option['state'] = base64_encode(json_encode($param));
        //获取登录地址
        $loginUrl = $google->getAuthorizationUrl($option);
        $res = ['url' => $loginUrl];
        return $this->response($res);
    }
    /**
     * m_googlecallback 手机谷歌回调
     */
    public function m_googlecallback(){
        if (!checkParam($this->param,['access_token', 'bind', 'device_type'])) {
            return $this->wrong(400);
        }
        $param = $this->param;
        if ($param['bind'] == 1) {
            if (!$this->is_login()){
                return $this->wrong(400100);
            }
        }
        $google_config = Config::get('setting.google');
        $google = new Google($google_config);
        $userDetails = $google->getResourceOwner($token);
        //第三方用户信息
        $third_user['third_id'] = $userDetails->getId();
        $third_user['nickname'] = $userDetails->getName();
        $third_user['avatar'] = $userDetails->getAvatar();
        $third_user['email']  = $userDetails->getEmail();
        $third_user['third_type'] = Google;
        if ($param['bind'] == 1) {
            $this->thirdBindAccount($third_user, $param);
        } else {
            $this->thirdLoginReg($third_user, $param);
        }
    }
    /*
     * 网页google回调
     */
    public function googlecallback(){
        //获取state处理 判断
        if (!isset($this->param['state'])) {
            return $this->wrong(400, '没有state');
        }
        $state = $this->param['state'];
        $param = json_decode(base64_decode($state), true);
        if (!isset($param['device_type']) || !isset($param['bind'])) {
            //网页跳转链接
            return $this->wrong(400,'没有device_type和bind');
        }
        if($param['bind'] == 1) {
            $res = LoginService::checkToken($param['user_id'], $param['device_type'], $param['token']);
            if (is_numeric($res)) {
                //网页跳转链接
                return $this->wrong(400100);
            }
        }
        //google
        $google_config = Config::get('setting.google');
        $google = new Google($google_config);
        //获取token
        $token = $google->getAccessToken('authorization_code', ['code' => $this->param['code']]);
        $userDetails = $google->getResourceOwner($token);
        //第三方用户信息
        $third_user['third_id'] = $userDetails->getId();
        $third_user['nickname'] = $userDetails->getName();
        $third_user['avatar'] = $userDetails->getAvatar();
        $third_user['email']  = $userDetails->getEmail();
        $third_user['third_type'] = Google;
        if ($param['bind'] == 1) {
            $this->thirdBindAccount($third_user, $param);
        } else {
            $this->thirdLoginReg($third_user, $param);
        }
    }
    /**
     * 第三方绑定
     */
    protected function thirdBindAccount($third_user, $param){
        $third_user_model = ThirdUserModel::self();
        $where['third_type'] = $third_user['third_type'];
        $where['user_id'] = $param['user_id'];
        $where['status'] = 1;
        //判断用户是否已经绑定该第三方，根据type和user_id和status即可
        $is_bind = $third_user_model->where($where)->count();
        if ($is_bind >= 1) {
            if ($param['device_type'] == 'web') {
                 $url =  Env::get('web.main').'/account/thirdlogin#400?url=%2Faccount%2Fpassword';
                 return $this->redirect($url);
            } else {
                return $this->wrong(400, '你已经绑定该第三方了');
            }
        }
        //该第三方是否被别人注册过，根据third_id 和 third_type 和status即可
        $map['third_id'] = $third_user['third_id'];
        $map['third_type'] = $third_user['third_type'];
        $map['status'] = 1;
        $third_bind = $third_user_model->where($map)->count();
        if ($third_bind >= 1) {
            if ($param['device_type'] == 'web') {
                $url =  Env::get('web.main').'/account/password#909203?url=%2Faccount%2Fpassword';
                return $this->redirect($url);
            } else {
                return $this->response(['code' => 909203],200,LangService::message(909203));
            }
        }
        $ret = $third_user_model->thirdPartyUserBind($param['user_id'], $third_user);
        if (is_array($ret)) {
            if ($param['device_type'] == 'web') {
                $url = Env::get('web.main').'/account/password';
                return $this->redirect($url);
            } else {
                return $this->response(['code' => 200, 'ret' => $ret]);
            }
        } else {
            return $this->wrong(400, '绑定失败');
        }
    }
    /**
     * unbind_third_account 解绑
     * @param id         第三方的主键
     * @param third_type 第三方类型
     */
    public function unbind_third_account(){
        if (!checkParam($this->param,['id','third_type'])) {
            return $this->wrong(400);
        }
        if (!$this->is_login()) {
            return $this->wrong(400100);
        }
        $third_user_model = ThirdUserModel::self();
        $where['id'] = $this->param['id'];
        $where['status'] = 1;
        $where['third_type'] = $this->param['third_type'];
        $where['user_id'] = $this->userInfo['user_id'];
        $is_exist = $third_user_model->where($where)->count();
        if ($is_exist != 1) {
            return $this->wrong(400);
        } else {
            $ret = $third_user_model->where($where)->setField('status',0);
            if ($ret == 1) {
                return $this->response(['ret' => 1]);
            } else {
                return $this->wrong(500);
            }
        }
    }
    /*
     * 第三方统一登录注册（twitter除外）
     */
    protected function thirdLoginReg($third_user, $param){
        $third_user_model = ThirdUserModel::self();
        $third_user_exsit = $third_user_model->thirdPartyUserIsExsit($third_user);
        $user_model = UserModel::self();
        if ($third_user_exsit) {
            //存在则直接登录
            $third_user_exsit['third_type'] = $third_user['third_type'];
            $ret = $user_model->thirdPartyUserLogin($third_user_exsit);
        } else {
            //获取不到邮箱的话走没有邮箱的步骤
            if ($third_user['email'] == '') {
                $key = $third_user['third_type'].'_'.$third_user['third_id'];
                Cache::set($key, $third_user, 60*60);
                if ($param['device_type'] == 'web') {
                    $url_param = "?third_id={$third_user['third_id']}&third_type={$third_user['third_type']}";
                    $url = Env::get('web.main').$this->email_url.$url_param;
                    return $this->redirect($url);
                } else {
                    return $this->response(['code' => 909100, 'ret' => $third_user]);
                }
            }
            //不存在则用邮箱注册账号
            $email_exsit = $user_model->where('email', $third_user['email'])->count();;
            if ($email_exsit >= 1) {
            //邮箱存在的情况下询问是否绑定账号
                $key = $third_user['third_type'].'_'.$third_user['third_id'];
                Cache::set($key, $third_user, 60*60);
                $third_type_name = FormatService::thirdTypeToString($third_user['third_type']);
                if ($param['device_type'] == 'web') {
                    $url_param = "?third_id={$third_user['third_id']}&third_type={$third_user['third_type']}";
                    $url = Env::get('web.main').$this->already_url.$url_param;
                    return $this->redirect($url);
                } else {
                    return $this->response(['code' => 909103, 'ret' => $third_user]);
                }
            } else {
            //邮箱不存在的情况下注册新账号
                $ret = $user_model->thirdPartyUserEmailReg($third_user);
            }
        }
        if ($ret == 0) {
            $user = user_info();
            $data['device_type'] = $param['device_type'];
            $data['url'] = isset($param['url']) ? $param['url'] : '' ;
            $this->returnUserInfo($user, $data);
        } else {
            return $this->wrong(400,'请重新尝试');
        }

    }
    /*
     * 第三方获取不到邮箱时问是否继续注册
     */
    public function input_email(){
        if (!checkParam($this->param, ['third_id', 'third_type', 'email', 'device_type'])) {
            return $this->wrong(400);
        }
        $third_id = $this->param['third_id'];
        $third_type = $this->param['third_type'];
        $email = $this->param['email'];
        $key = $third_type.'_'.$third_id;
        $third_user = Cache::get($key);
        if (!$third_user) {
            return $this->wrong(400, '请重新尝试');
        }
        $third_user['email'] = $email;
        $user_model = UserModel::self();
        $ret = $user_model->thirdNoEmailReg($third_user);

        if ($ret != 0) {
            if (strtolower($this->param['device_type']) == 'web') {
                return $this->wrong($ret);
            } else {
                return $this->response(['code' => $ret],200,LangService::message($ret));
            }
        }
        $user = user_info();
        $data['device_type'] = $this->param['device_type'];
        $token = LoginService::setToken($user, $data);

        if (is_int($token)) {
            return $this->wrong(400);
        }
        $res = [
            'user_id'   => $user['id'],
            'token'     => $token,
            'username'  => $user['username'],
            'nickname'  => $user['nickname'],
            'email'     => $user['email'],
            'mobile'    => $user['mobile'],
            'status'    => $user['status'],
            'is_seller' => $user['is_seller'],
            'country_code' => $user['country_code']?strval($user['country_code']):"",
            'avatar'    => buildImageUrl($user['avatar']),
            'last_login_time' => $user['last_login_time'],
            'im_user'   =>'',
            'im_pass'   =>'',
            'vid' => $user['vid']
        ];
        Cache::rm($key);
        if ($data['device_type'] == 'web') {
            $config = Config::get('token');
            $expire = $config['token_expire'];
            Cookie::set('token',$res['token'],$expire);
            Cookie::set('user_id',$res['user_id'],$expire);
            return $this->response(['ret' => $res]);
        } else {
            return $this->response(['code' => 200, 'ret' => $res]);
        }
    }
    /*
     * 已经存在邮箱问是否绑定邮箱
     */
    public function bind_account(){
        if (!checkParam($this->param, ['third_id', 'third_type', 'device_type'])) {
            return $this->wrong(400);
        }
        $third_id = $this->param['third_id'];
        $third_type = $this->param['third_type'];
        $key = $third_type.'_'.$third_id;
        $third_user = Cache::get($key);
        if (!$third_user || $third_user['email'] == '') {
            return $this->wrong(400);
        }
        $third_user_model = ThirdUserModel::self();
        $user_model = UserModel::self();
        $user = $user_model->where('email', $third_user['email'])->find();
        $customer_model = CustomerModel::self();
        $is_seller = $customer_model->getOneUserInfo($user['id'], 'seller_status,vid,flag');
        $user['is_seller'] = $is_seller['seller_status'];
        $user['vid'] = $is_seller['vid'];
        $ret = $third_user_model->thirdPartyUserBind($user['id'], $third_user);
        if (is_array($ret)) {
            $data['device_type'] = strtolower($this->param['device_type']);
            $token = LoginService::setToken($user, $data);

            if (is_int($token)) {
                return $this->wrong(400);
            }
            $res = [
                'user_id'   => $user['id'],
                'token'     => $token,
                'username'  => $user['username'],
                'nickname'  => $user['nickname'],
                'email'     => $user['email'],
                'mobile'    => $user['mobile'],
                'status'    => $user['status'],
                'is_seller' => $user['is_seller'],
                'country_code' => $user['country_code']?strval($user['country_code']):"",
                'avatar'    => buildImageUrl($user['avatar']),
                'last_login_time' => $user['last_login_time'],
                'im_user'  => '',
                'im_pass'  => '',
                'vid' => $user['vid'],
            ];
            Cache::rm($key);
            if ($data['device_type'] == 'web') {
                $config = Config::get('token');
                $expire = $config['token_expire'];
                Cookie::set('token',$res['token'],$expire);
                Cookie::set('user_id',$res['user_id'],$expire);
                return $this->response(['ret' => $res]);
            } else {
                return $this->response(['code' => 200, 'ret' => $res]);
            }
        } elseif($ret == 0) {
            return $this->wrong(400,'绑定失败');
        }
    }
    /**
     * returnUserInfo 处理用户信息并返回
     * @param  array $user 用户信息
     * @param  array $data 设备等
     * @return array|400    用户信息|400
     */
    protected function returnUserInfo($user, $data, $key = ''){
        $token = LoginService::setToken($user, $data);

        if (is_int($token)) {
            return $this->wrong(400);
        }
        $res = [
            'user_id'   => $user['id'],
            'token'     => $token,
            'username'  => $user['username'],
            'nickname'  => $user['nickname'],
            'email'     => $user['email'],
            'mobile'    => $user['mobile'],
            'status'    => $user['status'],
            'is_seller' => $user['is_seller'],
            'country_code' => $user['country_code']?strval($user['country_code']):"",
            'avatar'    => buildImageUrl($user['avatar']),
            'last_login_time' => $user['last_login_time'],
            'im_user'   =>'',
            'im_pass'   =>'',
            'vid' => $user['vid'],
        ];
        if ($key != '') {
            Cache::rm($key);
        }
        if ($data['device_type'] == 'web') {
            //网页跳转链接
            $url = Env::get('web.main')."/account/thirdlogin#getinfo?avatar={$res['avatar']}&last_login_time={$res['last_login_time']}&nickname={$res['nickname']}&url={$data['url']}";
            $config = Config::get('token');
            $expire = $config['token_expire'];
            Cookie::set('token',$res['token'],$expire);
            Cookie::set('user_id',$res['user_id'],$expire);
            return $this->redirect($url);
        } else {
            return $this->response(['code' => 200, 'ret' => $res]);
        }
    }
}