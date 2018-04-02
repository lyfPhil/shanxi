<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 中文语言包

return [
    'Success'                           => '操作成功',
    'none'                              =>'无',
    //邮箱标题
    'email active'                      =>'邮箱激活',
    'email findback password'           =>'您正在使用邮箱找回密码，请在5分钟内将验证码填写到输入框内',

    "refund time"                       =>'{:num}天',           //退押金时间
    "refund deal time"                  =>'1-{:deal}个工作日内',  //客服处理时间
    "withdrawal deal time"              =>'1-{:deadline}个工作日内账',//提现处理时间
    //提现时的提示框
    "withdrawal description"            =>'每人{:day}天仅享有{:count}次提现机会，且每笔将收取提现金额{:precent}%的手续费。实际到账金额为扣除手续费后的金额。',

    "recharge expire time"              =>'{:day}天',

    'all_goods_type'                    => '全部',
    'other_service'                     => '其它伺服区',
    'all game_service'                  => 'All',
    "out of withdrawal config"          => '每人{:day}天仅享有{:count}次提现机会',
    'actual arrival'                    => '将扣除手续费HKD {:free}，实际到账HKD {:actual}',

    "have already logout"               => '已经退出登录',
    "please login"                      =>'请先登录',
    "please set your pay password"      =>'请设置交易密码',
    "Payment Expired"                   =>'支付过期',
    "can not buy your own goods"        =>'不能购买自己的商品',
    "no balance"                        =>'您的余额未零，不能提现',
    "lack of balance"                   =>'您的余额不足',
    "you are not a seller"              =>'抱歉，您不是卖家',
    "buyer have not comment yet"        =>'买家还没评论',
    //"you can not withdrawal twice a day"=>'一天不能提现两次',
    "can not refund deposit"                    =>'当前账户还存在在售商品或交易中的订单，请在没有在售商品，且在所有交易完成的10天后再申请退回保证金。',
    "no new version"                    =>'当前已经是最新版本',
    "can not submit too many time"      =>'一天内不能提交太多次',
    "Have been landed in other places"  =>'账号已经在别的地方登录',
    "wrong password"                    =>'错误的密码',
    "wrong state"                       =>'错误的状态码',
    "wrong idcard"                      =>'错误的身份证号码',
    'wrong image'                       =>'请上传小于5M，格式为jpg,png,gif,webp的图片',
    //404开头

    "account is not exist"                  =>'不存在该用户',

    //90开头
    /* 错误信息：
        00  不能为空
        01  格式错误
        02  已过期
        03  重复 已经存在
        04  不相同
        05  已经收藏
        06  不能少于6位
        07  不能多于多少位
        08  未激活
    */
    //"username is required"                 =>'用户名不能为空',
    "username wrong format"                =>'请输入正确的邮箱或手机号码',
    //"username is already exist"            =>'该用户已经被注册',
    "password is required"                 =>'请输入密码',
    "wrong password"                       =>'错误的登录密码',
    "two passwords are different"          =>'两次输入密码不同',
    //"password can not less then 6"         =>'密码不能少于6位',
    //"password can not more then 32"        =>'密码不能多于32位',
    "old password is eq new password"      =>'新旧密码一样',
    "password or account is wrong"         =>'密码或者账号出错',
    "token expired"                        =>'令牌失效',
    "account has been closed"              =>'该账号已经被封，如有疑问请联系客服',
    //"mobile is require"                    =>'手机不能为空',
    "mobile wrong format"                  =>'请输入正确的手机号码',
    "mobile is already exist"              =>'该手机号码已经被注册',
    "please bind mobile"                   =>'请绑定手机号码',
    //"email is required"                    =>'邮箱不能为空',
    "email wrong format"                   =>'请输入正确的邮箱',
    "email active is expired"              =>'过期的邮箱验证',
    "email is already exist"               =>'该邮箱已经被注册',
    "email do not active"                  =>'邮箱未激活',
    "email active fail"                    =>'邮箱激活失败',
    //"verify_code is require"               =>'需要短信验证码',
    "verify_code wrong format"             =>'错误的短信验证码',
    "verify_code is expired"               =>'短信验证码已经过期',
    "please get code after 60s"            =>'60秒内不能重复获取短信验证码',
    "a day can not get code five time"     =>'今天内获取短信验证码已经到达上限',
    //"captcha is require"                    =>'图形验证码不能为空',
    "captcha wrong format"                 =>'错误的图形验证码',
    "jy_pass is require"                   =>'没有设置支付密码',
    "jy_pass is wrong"                     =>'错误的支付密码',
    "jy_pass wrong count"                  =>'还可以输入{:count}次支付密码',
    "can not input jy_pass"                =>'输入错误支付密码次数已达上限，将锁定密码3小时，你可以：',
    "idcard_pic is require"                =>'没有上传身份证照片',
    "wrong idcard"                         =>'错误的身份证号码',
    "idcard is exist"                      =>'该身份证号码已经被绑定',
    //"goods is require"                     =>'商品不能为空',
    "do not publish goods twice in 60 sec" =>'60秒内不能发布重复的商品',
    "goods is already collect"             =>'该商品已经被收藏',
    "goods is off shelf"                   =>'该商品已经下架',
    "seller auth is require"               =>'发布商品前请进行卖家认证',
    "seller auth is false"                 =>'卖家认证未通过审核，请重新认证',
    //"seller auth is exist"                 =>'已经实名认证',
    "wait for seller auth"                 =>'卖家认证申请已经提交，请耐心等待客服审核',
    "bank card is exist"                   =>'已经存在改银行卡',
    "card_num and password is require"     =>'请输入卡号和卡密',
    "can not create the same order in 60s" => '60s内不能下重复的订单',
    "card total num is less then buy num"   =>'发卡的数量小于购买数量',
    "can not modify point card"            => '该点卡还有未付款订单，不能编辑',
    "charge is require"                    =>'请填写充值内容',
    "nickname is already exist"            =>'已经存在该昵称',
    "can only edit the nickname once"      =>'只能修改一次昵称',
    "stock not enough to on shelf"         =>'库存少于零不能上架',
    "stock is not enough"                  =>'库存不足',
    "price can not lower then"             =>'金额不能低于{:lower}',
    "price can not higher then"            =>'金额不能高于{:higher}',
    "price can not lower then fee"         =>'价格过低，无法扣除手续费',
    "jy_pass and idcard_pic is require"    =>'请设置密码和上传照片',
    "bank open_name is not eq real_name"   =>'银行卡开户姓名跟身份证验证的真实姓名不符',
    //"can not identify bank card"           =>'无法识别银行',
    "email already actived"                =>'邮箱已经激活',
    "service start between 1-5"            =>'请选择1-5星服务星级',
    "desc start between 1-5"               =>'请选择1-5星描述星级',
    "speed start between 1-5"              =>'请选择1-5星速度星级',
    "card_num can not more then 100"       =>'上传的点卡帐密不能超过100条',
    "card_num can not more then num"       =>'上传的点卡帐密不能超过{:num}条',
    "flie is too big"                      =>'上传文件过大',
    'already collected'                    =>'已经收藏',
    'bank card num can not less then 6'    =>'银行卡号不能少于6位',
    'bank card num can not more then 20'   =>'银行卡号不能多于20位',
    //消息模块
    "pay desposit title"                     =>"已成功繳納保證金",//卖家交了押金的标题
    "refund deposit title"                   =>"保证金退还成功",//退押金的标题

    /*******************************买家已经下单*****************************/
    "buy create order title"                 =>"商品已拍下，未付款",
    /*******************************买家付款 自动发卡*******************************/
    "buyer payed autosendcard title"         =>'买家已经付款',
    /*******************************卖家不同意取消订单******************************/
    "you disagree cancling order title"      =>"您已拒绝订单的取消，请尽快发货",
    /*******************************买家已经付款，提醒卖家发货*************************/
    "remind the seller to ship title"        =>"订单已付款，请尽快发货",
    /*******************************买家以经提取卡密********************************/
    "buyer ectractpass title"                =>'买家已经提取点卡卡密',
    /*******************************买家已经收货，提醒卖家查看余额********************/
    "buyer reciver order title"              =>"交易完成，款项已打到您的钱包",
    /******************************买家已经评论，提醒卖家看评论和回复***************/
    "buyer comment order title"              =>"卖家已对您做出评价，回复一下吧",
    /*****************************买家提起申诉，提醒卖家处理*************************/
    "remind seller to deal appeal title"     =>"买家提出订单申诉",
    /*********************************买家想要关闭交易，提醒卖家处理*******************/
    "remind seller to deal cancel title"     =>"买家申请取消订单，等待您的处理",
    /******************************卖家不同意订单申诉客服介入仲裁********************************/
    "you disagree appeal title"              =>"您已驳回申诉，客服将会介入处理",

    /********************************卖家取消买家已付款订单*****************************/
    "you cancel payed order refund title"    =>"您已同意取消订单",
    /*********************************卖家同意取消订单**************************/
    "you agree cancel refund title"          =>"您同意订单取消",
    /*********************************卖家同意订单申诉**********************/
    "you agree appeal refund title"         =>"您同意申诉",
    /*********************************取消未付款订单***********************/
    "buyer cancel no pay order title"       =>"买家已取消订单",

    "seller cancel no pay order title"      =>"您已取消订单",

    //对于买家会收到的信息
    /************************************买家支付 自动发卡************************/
    "payed autosendcard title"              =>'订单已经发货',
    /************************************卖家修改了价格***************************/
    "seller change order price title"       =>"卖家已修改价格",
    /************************************卖家不同意取消***************************/
    "seller disagree cancle title"         =>"订单的取消已被卖家驳回",
    /**********************************卖家已经发货，提醒买家*********************/
    "remind buyer to recive title"          =>"订单已经发货",
    /*********************************已经收货，提醒买家评论***********************/
    "remind buyer to comment title"         =>"交易完成，对卖家评价一下",
    /*********************************提醒买家申诉已经提交*************************/
    "appeal have commit title"              =>"您已提交申诉",
    /********************************关闭交易请求已提交***************************/
    "cancel have commit title"              =>"您已提交关闭订单",
    /********************************客服介入仲裁*********************************/
    "seller disagree appeal title"          =>"卖家驳回申诉，客服将介入处理",
    /********************************卖家取消已经付款未发货的订单******************/
    "seller cancel payed order refund"      =>"卖家已取消订单，款项已返回您的钱包",
    /********************************卖家同意买家交易关闭***************************/
    "seller agree cancel order refund"      =>"卖家已同意取消订单，款项已返回您的钱包",
    /*********************************卖家同意申诉请求*******************************/
    "seller agree appeal refund"            =>"卖家已同意申诉，款现已返回到您的钱包",

    /***********************************未付款订单取消*************************************/
    "cancel no pay order by buyer title"    =>"您已取消订单",

    "cancel no pay order by seller title"   =>"卖家已取消订单",
    /**********************************客服处理申诉***************************************/
    'customer deal appeal title'           =>"客服已处理订单申诉",
    'customer deal appeal content'         =>"您的订单（订单号：{:order_sn}）申诉结果如下：{:result}，理由：{:reason}如有疑问，请联系客服。",
    //取消订单理由
    //买家
    "do not want to buy"                    =>"我不想买了",
    "wrong info buy agian"                  =>"信息填写错误，重新拍",
    "seller out of stock"                   =>"卖家缺货",
    "close reseaon other"                   =>"其他原因",
    //卖家
    "I out of stock"                        =>"没货了",
    "buyer buy wrong goods"                 =>"买家拍错商品",
    "can not connect buyer"                 =>"联系不上买家",
    //取消交易结果
    "seller cancel order result"            =>"卖家({:username})已经取消交易",//
    "buy cancel order result"               =>"买家({:username})已经取消交易",
    "buy cancel order commit result"        =>"买家({:username})提出取消交易，待卖家处理",
    "seller agree cancel result"           =>"卖家({:username})同意取消交易，订单钱款已退回买家余额",
    "seller disagree cancel result"        =>"卖家({:username})拒绝取消订单，订单返回发货状态",
    //买家申诉理由
    "desc no match"                         =>"描述不符",
    "seller not send on time"               =>"未按约定时间发货",
    "seller malicious harassment"           =>"卖家恶意骚扰",
    "sellers exist theft false transactions"=>"卖家存在偷盗、虚假交易",
    "appeal other"                          =>"其他",
    //申诉结果显示
    "buyer commit appeal result"            =>"买家({:username})提出申诉，等待卖家处理",
    "seller disagree appeal result"        =>"卖家({:username})拒绝申诉，等待客服处理",
    "seller agree appeal result"            =>"卖家({:username})同意申诉，余额退到买家余额",
    //充值理由
    "not want to charge"                    =>"不想充值了",
    "wrong info charge again"               =>"信息填写错误，重新申请",
    "no bank nearby"                        =>"附近没有银行，无法转账",
    "cancel charge other"                   =>"其他",
   //自动取消未付款订单卖家收到的消息
    "auto deal not pay seller title"       =>"系统已取消订单，原因：买家超过时间未付款",
    //自动取消未付款订单买家收到的消息

    "auto deal not pay reason"             =>"买家({:username})一天内未付款",//原因
    "auto deal not pay result"             =>"订单自动取消",//结果
    //自动处理过期取消交易卖家收到的消息
    "auto deal not cancel seller title"    =>"系统自动取消交易，原因：您在规定时间内未处理交易买家的关闭申请",
    //自动处理过期取消交易买家收到的消息
    "auto deal not cancel buyer title"     =>"系统自动取消交易，原因：卖家在规定时间未处理关闭申请",

    "auto deal not cancel reason"          =>"卖家({:username})在规定时间内未处理",
    "auto deal not cancel result"          =>"订单自动取消",
    //自动处理过期申诉
    "auto deal not appeal seller title"    =>"您未处理申诉，客服将会介入处理",

    "auto deal not appeal buyer title"     =>"卖家未处理申诉，客服将会介入处理",

    "auto deal not appeal reason"          =>"卖家({:username})在规定时间内未处理申诉",
    "auto deal not appeal result"          =>"客服介入",
    //自动处理过期未收货卖家收到的信息
    "auto deal not confirm seller title"   =>"交易完成，款项已打到您的钱包",
    //自动处理过期未收货买家收到的信息
    "auto deal not confirm buyer title"    =>"系统自动收货",
    //系统默认好评
    "auto comment"                         =>"系统默认好评",
    'auto cancel expire recharge send message title' => '自动取消过期未充值',
    "auto cancel expire recharge"             =>"逾期未处理，系统自动取消",
    //账号类型
    'account'       =>'账号',
    'dailian'       =>'代练',
    'prop'          =>'道具',
    'coin'          =>'游戏币',
    'pointcard'     =>'点卡',
    'rechange'      =>'充值',
    'giftpack'      =>'礼包',
    'other'         =>'其他',

    //商品特殊属性
    'role_name'     =>'角色名',
    'role_level'    =>'角色等级',
    'bind'          =>'账号绑定',
    'pl_require'    =>'账号要求',
    'pl_content'    =>'代练内容',
    'time'          =>'完成时间',
    'prop_name'     =>'道具名称',
    'coin_num'      =>'游戏币数量',
    //'card_type'     =>'',
   // 'card_value'    =>'',
    'charge'        =>'充值内容',
    'pack_content'  =>'礼包内容',
    'use_method'    =>'使用方式',
    'send_type'     =>'发卡方式',
    //发卡方式
    'auto send card'    =>'自动发卡',
    'manual send card'  =>'手动发卡',
    //账号绑定
    'security_phone'    =>'密保手机',
    'security_email'    =>'密保邮箱',
    'security_problem'  =>'密保问题',
    'security_idcard'   =>'身份证',
    'security_other'    =>'其它',
    'no_bind'           =>'无绑定',
    //支付方式
    'no pay'            =>'未付款',
    'pay_default'       =>'余额支付',
    //充值方式（充值记录）
    'outline bank recharge'=>'银行转账(有卡)',
    'outline_cash recharge' => '银行转账(现金)',
    //资金明细
    'finance_recharge' => '充值/充值号：{:id}/已通过{:recharge_type}缴款',
    'finance_pay'      => '订单支付/订单号：{:id}/支付方式：{:pay_type}',
    'finance_income'   => '订单收入/订单号：{:id}/已扣除手续费：{:free}',
    'finance_withdraw:wait' => '提现/提现号：{:id}/将扣除手续费{:free}，实际到帐{:cash}。',
    'finance_withdraw:fail' => '提现/提现号：{:id}/于{:time}的提款申请失败，原因：{:reason}。提款金额已打回到您的钱包。',
    'finance_refund'   => '订单退款/订单号：{:id}',
    'finance_pay_deposit' => '缴纳押金',
    'finance_refund_deposit' => '退还押金',

    'withdrawal:desc1' => 'You have only one chance to withdraw per day. ',
    'withdrawal:desc2' => 'According to bank regulations, banks charge a withdrawal fee of 1% of the transaction amount.',
    'withdrawal:desc3' => 'You can withdraw at least THB 100 and the withdrawal limit THB 50000.',
    'withdrawal:desc4' => 'Withdrawal amount will be handled within 1-3 working days.',

    'none'              =>'無',
    //後台充值消息通知
    'recharge success'  =>'您的繳款號:{:charge_no}(繳款方式:{:recharge_type})，繳款金額:{:cash},在{:time}已经成功入數到你的錢包,請查收!',
    'recharge reject'  =>'您的繳款號:{:charge_no}(繳款方式:{:recharge_type})，繳款金額:{:cash},由于{:reason}被駁回，如有疑問請聯繫客服。',
    //提現消息通知
    'withdraw success' => '您的提款號:{:draw_no},提款金額:{:cash},扣除手續費:{:service_free},實際到賬:{:actual_cash},在{:time}已经成功過數到你的銀行賬號{:bank_card},請留意查收!',
    'withdraw reject'  => '您的提款號:{:draw_no},提款金額:{:cash},于{:time}提款失败,失败原因:{:cancel},如有疑問請聯繫客服',
    //卖家认证通知
    'certification pass'=> '您于{:time}时提交的賣家認證材料，已通過審核,快去發佈你的第一件商品吧!',
    'certification no pass' =>'您于{:time}时提交的賣家認證材料，未通過審核，原因：{:reason},請重新提交申请',
    //帮助中心
    'account_problem' => '账号问题',
    'trade_problem'   => '交易流程',
    'reg_problem'     => '用户注册',
    'login_problem'   => '用户登录',
    'verfiy_problem'  => '验证机制',
    'edit_info'       => '资料修改',
    'pass_problem'    => '密码问题',
    'cert_problem'    => '认证问题',
    'buyer_problem'   => '买家问题',
    'buy_goods'       => '购买商品',
    'connect_seller'  => '联系卖家',
    'confirm_comment' => '确认收货和评价',
    'cancel_trade'    => '交易取消',
    'buyer_common_problem' => '买家常见问题',
    'seller_problem'  => '卖家问题',
    'be_seller'       => '成为卖家',
    'seller_common_problem' => '卖家常见问题',
    'funds_problem'   => '款项问题',
    'pay_problem'     => '付款',
    'draw_problem'    => '提现',
    'trade_free'      => '交易手续费',
    'trade_skill'     => '交易技巧',
    'trade_anti_chet' => '交易防骗',
    'account_trade_problem' => '账号交易问题',
    'appeal_report'   => '申诉检举',
    'trade_appeal'    => '交易申诉',
    'about_company'   => '网站相关',
    'introduce'       => '公司介绍',
    'term_of_service' => '服务条款',
    //第三方登录
    "can not get auth" => '获取不了权限，不能登录或注册',
    "redirect user to input email" => '第一次注册奇乐游，请填写有效的邮箱地址',
    "bind third_email to account"  => '您的{:third_type}邮箱已经在奇乐游注册账号，是否绑定该账号？',
   ];
