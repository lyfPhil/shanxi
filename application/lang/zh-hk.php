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

// 香港繁体字

return [
    'Success'                           => '操作成功',
    'none'                              => '冇',
    'email active'                      => '郵箱激活',
    'email findback password'           => '你正在使用郵箱找回密碼，請在5分鐘內將驗證碼填到輸入框內',

    "refund time"                       => '{:num}天',           //退押金时间
    "refund deal time"                  => '1-{:deal}個工作日內退還保證金',  //客服处理时间
    "withdrawal deal time"              => '1-{:deal}個工作日內到帳',//提现处理时间
    //提款时的提示框
    "withdrawal description"            => '每人{:day}天僅享有{:count}次提款機會，且將收取每筆提款金額{:precent}%的手續費。實際到賬金額爲扣除手續費後的金額。',

    "recharge expire time"              => '{:day}天',

    'all_goods_type'                    => '全部',
    'other_service'                     => '其它伺服器',
    'all game_service'                  => '全部伺服器',
    "out of withdrawal config"          => '每人{:day}天僅享有{:count}次提款機會',
    'actual arrival'                    => '将扣除手续费HKD {:free}，实际到账HKD {:actual}',

    "have already logout"               => '已登出',
    "please login"                      => '請登入',
    "please set your pay password"      => '請設置支付密碼',
    "Payment Expired"                   => '支付過期',
    "can not buy your own goods"        => '不能購買自己的商品 ',
    "no balance"                        => '您的餘額為零，不能提款',
    "lack of balance"                   => '您的餘額不足',
    "you are not a seller"              => '抱歉，您不是賣家',
    "buyer have not comment yet"        => '買家還沒評論',
    //"you can not withdrawal twice a day"=> '一天不能提款多次',
    "can not refund deposit"            =>'當前賬戶還有在售商品或交易中訂單，請下架所有在售商品，且在所有交易完成10天後再次申請。',
    "no new version"                    => '當前已是最新版本',
    "can not submit too many time"      =>'一天内不能提交太多次',
    //"please restart agian"              =>'請重試',
    "Have been landed in other places"  => '賬號已在其他設備上登入',
    //"wrong password"                    =>'錯誤的密碼',
    //"wrong state"                       =>'錯誤的狀態碼',
    "wrong idcard"                      =>'身份證號碼錯誤',
    'wrong image'                       =>'請上傳小於5M，格式爲jpg,png,gif,webp的圖片',
    //404开头 不存在数据的(出现在这里的一般为非法请求或者是没有数据的)
    "account is not exist"                  =>'用戶不存在',

    //90开头 参数错误 0代表登录注册相关 1代表商品相关
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
    //"username is required"                 =>'用戶名不能爲空',
    "username wrong format"                =>'請輸入正確的電郵或手機號碼',
    //"username is already exist"            =>'該用戶已被註冊',
    //"password is required"                 =>'請輸入密碼',
    "wrong password"                       =>'密碼錯誤',
    "two passwords are different"          =>'兩次輸入密碼不同',
    //"password can not less then 6"         =>'密碼不能少於6位',
    //"password can not more then 32"        =>'密碼不能多於32位',
    "old password is eq new password"      =>'新舊密碼一樣',
    "password or account is wrong"         =>'賬號或密碼出錯',
    "token expired"                        =>'令牌失效',
    "account has been closed"              =>'該賬號已被禁用，如有疑問請聯繫客服',
    //"mobile is require"                    =>'手機不能爲空',
    "mobile wrong format"                  =>'請輸入正確的手機號碼',
    "mobile is already exist"              =>'該手機號碼已註冊',
    "please bind mobile"                   =>'請綁定手機號碼',
    //"email is required"                    =>'郵箱不能爲空',
    "email wrong format"                   =>'請輸入正確的Email',
    "email active is expired"              =>'激活驗證碼已失效',
    "email is already exist"               =>'該Email已被註冊',
    "email do not active"                  =>'Email未激活',
    "email active fail"                    =>'Email激活失敗',
    //"verify_code is require"               =>'需要手機驗證碼',
    "verify_code wrong format"             =>'錯誤的手機驗證碼',
    "verify_code is expired"               =>'手機驗證碼已過期',
    "please get code after 60s"            =>'60秒內不能重複獲取手機驗證碼',
    "a day can not get code five time"     =>'今天內獲取手機驗證碼已到上限',
    "captcha is require"                    =>'请输入圖形驗證碼',
    "captcha wrong format"                 =>'圖形驗證碼錯誤',
    "jy_pass is require"                   =>'沒有設置支付密碼',
    "jy_pass is wrong"                     =>'支付密碼錯誤',
    "jy_pass wrong count"                  =>'還可以输入{:count}次支付密碼',
    "can not input jy_pass"                =>'輸入錯誤支付密碼次數已達上限，將鎖定密碼3小時，你可以：',
    "idcard_pic is require"                =>'沒有上傳身份證照片',
    "wrong idcard"                         =>'身份證號碼錯誤',
    "idcard is exist"                      =>'該身份證號碼已被綁定',
    //"goods is require"                     =>'商品不能爲空',
    "do not publish goods twice in 60 sec" =>'60秒內不能發佈重複的商品',
    "goods is already collect"             =>'該商品已收藏',
    "goods is off shelf"                   =>'該商品已下架',
    "seller auth is require"               =>'發佈商品前請進行賣家認證',
    "seller auth is false"                 =>'賣家認證未通過審核，請重新認證',
    //"seller auth is exist"                 =>'已實名認證',
    "wait for seller auth"                 =>'賣家認證申請已經提交，請耐心等待客服審核',
    "bank card is exist"                   =>'該銀行卡已存在',
    "card_num and password is require"     =>'請輸入卡號和卡密',
    "can not create the same order in 60s" => '60s內不能下重複的訂單',
    "card total num is less then buy num"   =>'發卡的數量小於購買數量',
    "can not modify point card"            => '該點數卡商品還有未付款訂單，不能編輯。',
    "charge is require"                    =>'請填寫代儲內容',
    "nickname is already exist"            =>'已经存在该昵称',
    "can only edit the nickname once"      =>'只能修改一次昵称',
    "stock not enough to on shelf"         =>'庫存應大於零',
    "stock is not enough"                  =>'庫存不足',
    "price can not lower then"             =>'金额不能少於{:lower}',
    "price can not higher then"            =>'金额不能多於{:higher}',
    "price can not lower then fee"         =>'價格過低，無法扣除手續費',
    "jy_pass and idcard_pic is require"    =>'請設置密碼和上傳照片',
    //"bank open_name is not eq real_name"   =>'銀行卡開戶姓名跟身份證驗證的真實姓名不符',
    //"can not identify bank card"           =>'无法识别银行',
    "email already actived"                =>'Email已激活',
    "service start between 1-5"            =>'請選擇1-5星的服務態度',
    "desc start between 1-5"               =>'請選擇1-5星的商品描述',
    "speed start between 1-5"              =>'請選擇1-5星的處理速度',
    "card_num can not more then 100"       =>'上传的點數卡帳密不能超過100條',
    "card_num can not more then num"       =>'上传的點卡帳密不能超過{:num}條',
    "flie is too big"                      =>'上传文件过大',
    'already collected'                    =>'已收藏',
    'bank card num can not less then 6'    =>'銀行卡號不能少於6位',
    'bank card num can not more then 20'   =>'銀行卡號不能多於20位',
    //消息模块
    "pay desposit title"                     =>"繳納保證金成功",//卖家交了押金的标题
    "pay desposit content"                   =>"您已在{:time}繳納保證金",//卖家交了押金的内容
    "refund deposit title"                   =>"保證金退還成功",//退押金的标题
    "refund deposit content"                 =>"您的保證金已退還到錢包中，如有疑問請聯繫客服。",//退押金的内容

    /*******************************买家已经下单*****************************/
    "buy create order title"                 =>"商品已拍下，未付款",
    "buy create order content"               =>"買家{:buy_name}已拍下您的商品：{:title}\n訂單號：{:order_sn}",
    /*******************************买家付款 自动发卡*******************************/
    "buyer payed autosendcard title"         =>'買家已經付款',
    "buyer payed autosendcard content"       =>"買家{:buy_name}於{:time}已對您的交易訂單號：{:order_sn}（商品：{:title}）付款，請留意。",
    /*******************************卖家不同意取消订单******************************/
    "you disagree cancling order title"      =>"您已拒絕訂單的取消，請儘快發貨",
    "you disagree cancling order content"    =>"您駁回買家{:buy_name}的關閉交易申請，訂單將返回待發貨狀態。\n訂單號：{:order_sn}\n商品：{:title}",
    /*******************************买家已经付款，提醒卖家发货*************************/
    "remind the seller to ship title"        =>"訂單已付款，請儘快發貨",
    "remind the seller to ship content"      =>"買家{:buy_name}已付款，請及時發貨。\n訂單號：{:order_sn}\n商品：{:title}",
        /*******************************买家已經提取卡密********************************/
    "buyer ectractpass title"                =>'買家已經提取點卡卡密',
    "buyer ectractpass content"              =>"買家{:buy_name}於{:time}已對您的交易訂單號：{:order_sn}（商品：{:title}）提取卡密，請留意。",
    /*******************************买家已经收货，提醒卖家查看余额********************/
    "buyer reciver order title"              =>"交易完成，款項已打到您的錢包",
    "buyer reciver order content"            =>"買家{:buy_name}已確認收貨，款項已入賬，如有問題請聯繫客服。\n訂單號：{:order_sn}\n商品：{:title}",
    /******************************买家已经评论，提醒卖家看评论和回复***************/
    "buyer comment order title"              =>"賣家已對您做出評價，回覆一下吧",
    "buyer comment order content"            =>"買家{:buy_name}已對您作出評價，請及時查看與回復。\n訂單號：{:order_sn}\n商品：{:title}",
    /*****************************买家提起申诉，提醒卖家处理*************************/
    "remind seller to deal appeal title"     =>"買家提出訂單申訴",
    "remind seller to deal appeal content"   =>"買家{:buy_name}對您提出申訴，請及時處理。\n訂單號：{:order_sn}\n商品：{:title}",
    /*********************************买家想要关闭交易，提醒卖家处理*******************/
    "remind seller to deal cancel title"     =>"買家申請取消訂單，等待您的處理",
    "remind seller to deal cancel content"   =>"買家{:buy_name}申請關閉交易，請及時處理。\n訂單號：{:order_sn}\n商品：{:title}",
    /******************************卖家不同意订单申诉客服介入仲裁********************************/
    "you disagree appeal title"              =>"您已駁回申訴，客服將會介入處理",
    "you disagree appeal content"            =>"您駁回買家{:buy_name}的申訴請求，客服介入仲裁后，將在三天內給出處理結果，請耐心等待。\n訂單號：{:order_sn}\n商品：{:title}",

    /********************************卖家取消买家已付款订单*****************************/
    "you cancel payed order refund title"    =>"您已同意取消訂單",
    "you cancel payed order refund content"  =>"您已取消買家{:buy_name}的已付款訂單,款項將退還給買家。\n訂單號：{:order_sn}\n商品：{:title}",
    /*********************************卖家同意取消订单**************************/
    "you agree cancel refund title"          =>"您同意訂單取消",
    "you agree cancel refund content"        =>"您同意買家{:buy_name}的關閉交易申請，款項將退還給買家。\n訂單號：{:order_sn}\n商品：{:title}",
    /*********************************卖家同意订单申诉**********************/
    "you agree appeal refund title"         =>"您同意申訴",
    "you agree appeal refund content"       =>"您同意買家{:buy_name}的申訴申請，款項將退還給買家。\n訂單號：{:order_sn}\n商品：{:title}",
    /*********************************取消未付款订单***********************/
    "buyer cancel no pay order title"       =>"買家已取消訂單",
    "buyer cancel no pay order content"     =>"買家{:buy_name}已取消訂單。\n訂單號：{:order_sn}\n商品：{:title}",

    "seller cancel no pay order title"      =>"您已取消訂單",
    "seller cancel no pay order content"    =>"您已取消買家{:buy_name}的訂單。\n訂單號：{:order_sn}\n商品：{:title}",

    //对于买家会收到的信息
    ///************************************买家支付 自动发卡************************/
    "payed autosendcard title"              =>'訂單已經發貨',
    "payed autosendcard content"            =>'您購買的點卡{:title}屬於自動髮卡類型，請及時提取卡號卡密',
    /************************************卖家修改了价格***************************/
    "seller change order price title"       =>"賣家已修改價格",
    "seller change order price content"     =>"卖家{:sell_name}修改了交易價格，请跟卖家确认后再行支付。\n訂單號：{:order_sn}\n商品：{:title}",
    /************************************卖家不同意取消***************************/
    "seller disagree cancle title"         =>"訂單的取消已被賣家駁回",
    "seller disagree cancle content"       =>"賣家{:sell_name}駁回您的關閉交易申請，如有疑問請跟賣家協商。\n訂單號：{:order_sn}\n商品：{:title}",
    /**********************************卖家已经发货，提醒买家*********************/
    "remind buyer to recive title"          =>"訂單已經發貨",
    "remind buyer to recive content"        =>"賣家{:sell_name}已發貨，請注意查收。所有向您索取賬號密碼或者简讯驗證碼，以及索取您購買的點卡卡密、代储點數返現金等行爲均是詐騙，切勿上當受騙！\n訂單號：{:order_sn}\n商品：{:title}",
    /*********************************已经收货，提醒买家评论***********************/
    "remind buyer to comment title"         =>"交易完成，對賣家評價一下",
    "remind buyer to comment content"       =>"您已確認收貨，款項將入賬給賣家，請對賣家與商品進行評價。\n訂單號：{:order_sn}\n商品：{:title}",
    /*********************************提醒买家申诉已经提交*************************/
    "appeal have commit title"              =>"您已提交申訴",
    "appeal have commit content"            =>"您的申訴已提交，請耐心等待賣家{:sell_name}處理。\n訂單號：{:order_sn}\n商品：{:title}",
    /********************************关闭交易请求已提交***************************/
    "cancel have commit title"              =>"您已提交關閉訂單",
    "cancel have commit content"            =>"您的關閉交易申請已提交，請耐心等待賣家{:sell_name}處理。\n訂單號：{:order_sn}\n商品：{:title}",
    /********************************客服介入仲裁*********************************/
    "seller disagree appeal title"          =>"賣家駁回申訴，客服將介入處理",
    "seller disagree appeal content"        =>"賣家{:sell_name}拒絕您的申訴請求，客服介入仲裁后，將在三天內給出處理結果，請耐心等待。\n訂單號：{:order_sn}\n商品：{:title}",
    /********************************卖家取消已经付款未发货的订单******************/
    "seller cancel payed order refund"      =>"賣家已取消訂單，款項已返回您的錢包",
    "seller cancel payed order refund content"=>"賣家{:sell_name}已取消您的訂單，款項將退還到您的錢包，注意查收，如有疑問請聯繫賣家。\n訂單號：{:order_sn}\n商品：{:title}",
    /********************************卖家同意买家交易关闭***************************/
    "seller agree cancel order refund"      =>"賣家已同意取消訂單，款項已返回您的錢包",
    "seller agree cancel order refund content"=>"賣家{:sell_name}同意關閉訂單，款項將退還到您的錢包，注意查收，如有疑問請聯繫客服。\n訂單號：{:order_sn}\n商品：{:title}",
    /*********************************卖家同意申诉请求*******************************/
    "seller agree appeal refund"            =>"賣家已同意申訴，款現已返回到您的錢包",
    "seller agree appeal refund content"    =>"賣家{:sell_name}已同意您的申訴，款項將退還到您的錢包，注意查收，如有疑問請聯繫客服。\n訂單號：{:order_sn}\n商品：{:title}",

    /***********************************未付款订单取消*************************************/
    "cancel no pay order by buyer title"    =>"您已取消訂單",
    "cancel no pay order by buyer content"  =>"您已取消訂單。\n訂單號：{:order_sn}\n商品：{:title}",

    "cancel no pay order by seller title"   =>"賣家已取消訂單",
    "cancel no pay order by seller content" =>"賣家{:sell_name}已取消訂單。\n訂單號：{:order_sn}\n商品：{:title}",
    /**********************************客服处理申诉***************************************/
    'customer deal appeal title'           =>"客服已處理訂單申訴。",
    'customer deal appeal content'         =>"您的訂單（訂單號：{:order_sn}）申訴結果如下：{:result}，理由：{:reason}如有疑問，請聯繫客服。",
    //取消订单理由
    //买家
    "do not want to buy"                    =>"我唔想買了",
    "wrong info buy agian"                  =>"信息填寫錯誤，重新下單",
    "seller out of stock"                   =>"賣家缺貨",
    "close reseaon other"                   =>"其他原因",
    //卖家
    "I out of stock"                        =>"沒貨了",
    "buyer buy wrong goods"                 =>"買家下錯單",
    "can not connect buyer"                 =>"聯繫唔到買家",
    //取消交易结果
    "seller cancel order result"            =>"賣家已經取消交易",//
    "buy cancel order result"               =>"買家已經取消交易",
    "buy cancel order commit result"        =>"買家提出取消交易，等待賣家處理",
    "seller agree cancel result"            =>"賣家同意取消交易，訂單款項已退回買家錢包",
    "seller disagree cancel result"         =>"賣家拒絕取消訂單，訂單返回發貨狀態",
    //买家申诉理由
    "desc no match"                         =>"描述不符",
    "seller not send on time"               =>"未按約定時間發貨",
    "seller malicious harassment"           =>"賣家惡意騷擾",
    "sellers exist theft false transactions"=>"賣家存在偷盜、虛假交易",
    "appeal other"                          =>"其他",
    //申诉结果显示
    "buyer commit appeal result"            =>"買家提出申訴，等待賣家處理",
    "seller disagree appeal result"         =>"拒絕申訴，等待客服處理",
    "seller agree appeal result"            =>"賣家同意申訴，款項退到買家錢包",
    //充值理由
    "not want to charge"                    =>"我不想繳款了",
    "wrong info charge again"               =>"信息填寫錯誤，重新申請",
    "no bank nearby"                        =>"附近無銀行，無法過數",
    "cancel charge other"                   =>"其他",
    //自动取消未付款订单卖家收到的消息
    "auto deal not pay seller title"       =>"系統已取消訂單，原因：買家超過時間未付款",
    "auto deal not pay seller content"     =>"由于买家{:buy_name}在规定时间内未付款，系统自动取消交易。\n訂單號：{:order_sn}\n商品：{:title}",
    //自动取消未付款订单买家收到的消息
    "auto deal not pay buyer title"        =>"系統已取消訂單，原因：超過時間未付款",
    "auto deal not pay buyer content"      =>"您在规定时间内未付款，系统自动取消交易。\n訂單號：{:order_sn}\n商品：{:title}",

    "auto deal not pay reason"             =>"买家规定时间内未付款",//原因
    "auto deal not pay result"             =>"訂單自動取消",//结果
    //自动处理过期取消交易卖家收到的消息
    "auto deal not cancel seller title"    =>"系統自動取消交易，原因：您在規定時間內未處理交易買家的關閉申請",
    "auto deal not cancel seller content"  =>"您在規定時間內未處理買家{:buy_name}的關閉交易請求，系統自動取消交易，訂單款項將退還到買家錢包。\n訂單號：{:order_sn}\n商品：{:title}",
    //自动处理过期取消交易买家收到的消息
    "auto deal not cancel buyer title"     =>"系統自動取消交易，原因：賣家在規定時間未處理關閉申請",
    "auto deal not cancel buyer content"   =>"由於賣家{:sell_name}在規定時間內未能處理您的關閉交易請求，系統自動取消交易，訂單金額將返回您的餘額，請留意查收，如有疑問請聯繫客服。\n訂單號：{:order_sn}\n商品：{:title}",

    "auto deal not cancel reason"          =>"賣家在規定時間內未處理關閉申請",
    "auto deal not cancel result"          =>"订单自动取消",
    //自动处理过期申诉
    "auto deal not appeal seller title"    =>"您未處理申訴，客服將會介入處理",
    "auto deal not appeal seller content"  =>"您在規定時間內未處理買家{:buy_name}的申訴請求，客服將會介入仲裁，如有疑問請聯繫客服。",

    "auto deal not appeal buyer title"     =>"賣家未處理申訴，客服將會介入處理",
    "auto deal not appeal buyer content"   =>"由於賣家{:sell_name}在規定時間內未能處理您的申訴請求，客服將會介入仲裁，請耐心等待結果，如有疑問請聯繫客服。\n訂單號：{:order_sn}\n商品：{:title}",

    "auto deal not appeal reason"          =>"賣家在規定時間內未處理申訴",
    "auto deal not appeal result"          =>"客服介入",
    //自动处理过期未收货卖家收到的信息
    "auto deal not confirm seller title"   =>"交易完成，款項已打到您的錢包",
    "auto deal not confirm seller content" =>"買家{:buy_name}在規定時間內未確認收貨，系統自動確認收貨，訂單款項已入帳到您的錢包，請注意查收，如有疑問請聯繫客服。\n訂單號：{:order_sn}\n商品：{:title}",
    //自动处理过期未收货买家收到的信息
    "auto deal not confirm buyer title"    =>"系統自動收貨",
    "auto deal not confirm buyer content"  =>"您在規定時間內未確認收貨，系統自動確認收貨，訂單款項將會過數給賣家，如有疑問請聯繫客服。\n訂單號：{:order_sn}\n商品：{:title}",
    //系统默认好评
    "auto comment"                         =>"系統默認好評",
    'auto cancel expire recharge send message title' => '自動取消過期未充值',
    'auto cancel expire recharge send message content' => "您在規定時間內未能繳款，系統自動取消該繳款申請。\n繳款號：{:charge_no}\n繳款方式：{:pay_type}",
    "auto cancel expire recharge"             =>"逾期未處理，系統自動取消",
    //账号类型
    'account'       =>'帳號',
    'dailian'       =>'代練',
    'prop'          =>'道具',
    'coin'          =>'遊戲幣',
    'pointcard'     =>'點數卡',
    'rechange'      =>'代儲',
    'giftpack'      =>'產包',
    'other'         =>'其他',
    //商品特殊属性
    'role_name'     =>'角色名',
    'role_level'    =>'等級',
    'bind'          =>'密保產品',
    'pl_require'    =>'賬號要求',
    'pl_content'    =>'代練內容',
    'time'          =>'完成時間',
    'prop_name'     =>'道具名稱',
    'coin_num'      =>'遊戲幣數量',
    'charge'        =>'代儲內容',
    'pack_content'  =>'產包內容',
    'use_method'    =>'使用方式',
    'send_type'     =>'發卡方式',
    //发卡方式
    'auto send card'    =>'自動發卡',
    'manual send card'  =>'手動發卡',
    //账号绑定
    'security_phone'    =>'密保手機',
    'security_email'    =>'密保郵箱',
    'security_problem'  =>'密保問題',
    'security_idcard'   =>'身份證',
    'security_other'    =>'其他',
    'no_bind'           =>'無綁定',
    //支付方式
    'no pay'            =>'未支付',
    'pay_default'       =>'餘額支付',
    'payssion_alipay'   =>'支付寶',
    'payssion_tenpay'   =>'微信支付',
    //充值方式（充值记录）
    'outline bank recharge'=>'銀行入數(有卡)',
    'outline_cash recharge' => '银行入數(現金)',
    //
    'none'              =>'無',
    'withdrawal'        =>'提款/提款號：{:draw_no}',
    'reject withdrawal' =>"提款失敗/提款號：{:draw_no}",
    'reject withdrawal remark' => "于 {:time} 的提款申請失敗，原因：{:reason} 。提款金額已打回您的錢包。",
    'withdraw success remark'  => "\n已在 {:time} 過數至您指定的银行账户，注意查收",
    'pay despoist'      =>'繳納保證金',
    'refund despoist'   =>'退還保證金',
    'order_num pay'     =>'訂單支付/訂單號：{:order_sn}',
    'order_num refund'  =>'訂單退款/訂單號：{:order_sn}',
    'order_num income'   =>"訂單收入/訂單號：{:order_sn}",
    'order_num income remark'=>"已扣手续费HKD：{:free}",
    'recharge_no'       =>"繳款/繳款號：{:recharge_no}",
    'recharge remark'   => "已通过{:pay_type}繳款",
    //後台充值消息通知
    'recharge success'  =>'繳款成功',
    'recharge reject'  =>'繳款失敗',
    'recharge success infomation'  =>'您的繳款號:{:charge_no}(繳款方式:{:pay_type})，繳款金額:{:cash},在{:time}已经成功入數到你的錢包,請查收!',
    'recharge reject infomation'   =>'您的繳款號:{:charge_no}(繳款方式:{:pay_type})，繳款金額:{:cash},由于{:reason}被駁回，如有疑問請聯繫客服',
    //提現消息通知
    'withdraw success' => '您的提款號:{:draw_no},提款金額:{:cash},扣除手續費:{:service_free},實際到賬:{:actual_cash},在{:time}已经成功過數到你的銀行賬號{:bank_card},請留意查收!',
    'withdraw reject'  => '您的提款號:{:draw_no},提款金額:{:cash},于{:time}提款失败,失败原因:{:cancel},如有疑問請聯繫客服',
    'refund despoist success' => '您的提款號:{:draw_no},退還保證金金額:{:cash},扣除手續費:{:service_free},實際到賬:{:actual_cash}',
    'refund despoist error'  => '您的提款號:{:draw_no},退還保證金金額:{:cash},于{:time}退還失败,失败原因:{:cancel},如有疑問請聯繫客服',
    //卖家认证通知
    'certification pass'=> '您于{:time}时提交的賣家認證材料，已通過審核,快去發佈你的第一件商品吧!',
    'certification no pass' =>'您于{:time}时提交的賣家認證材料，未通過審核，原因：{:reason},請重新提交申请',

    //帮助中心
    'account_problem' => '賬號問題',
    'trade_problem'   => '交易流程',
    'reg_problem'     => '用戶註冊',
    'login_problem'   => '用戶登錄',
    'verfiy_problem'  => '驗證機制',
    'edit_info'       => '資料修改',
    'pass_problem'    => '密碼問題',
    'cert_problem'    => '認證問題',
    'buyer_problem'   => '買家問題',
    'buy_goods'       => '購買商品',
    'connect_seller'  => '聯繫賣家',
    'confirm_comment' => '確認收貨和評價',
    'cancel_trade'    => '交易取消',
    'buyer_common_problem' => '買家常見問題',
    'seller_problem'  => '賣家問題',
    'be_seller'       => '成爲賣家',
    'seller_common_problem' => '賣家常見問題',
    'funds_problem'   => '款項問題',
    'pay_problem'     => '付款',
    'draw_problem'    => '提現',
    'trade_free'      => '交易手續費',
    'trade_skill'     => '交易技巧',
    'trade_anti_chet' => '交易防騙',
    'account_trade_problem' => '賬號交易問題',
    'appeal_report'   => '申訴檢舉',
    'trade_appeal'    => '交易申訴',
    'about_company'   => '網站相關',
    'introduce'       => '公司介紹',
    'term_of_service' => '服務條款',
    //第三方登录
    "can not get auth" => '獲取不了權限，不能登錄或註冊',
    "redirect user to input email" => '第一次註冊奇樂遊，請填寫有效的郵箱地址',
    "bind third_email to account"  => '您的{:third_type}郵箱已經在奇樂遊註冊賬號，是否綁定該賬號？',
    
];