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
    'Success'                           => 'Success',
    'none'                              => 'None',
    'email active'                      => 'Email Activation',
    'email findback password'           => 'You are finding back password through email,please input the captcha code in 5 minute',

    'refund time'                       => '{:num} days',           //退押金时间
    'refund deal time'                  => 'In 1-{:deal} working days',  //客服处理时间
    'withdrawal deal time'              => 'Arrived in 1-{:deadline} working days',//提现处理时间
    //提款时的提示框
    'withdrawal description'            => 'Can withdraw {:count} times in {:day} days,and every withdraw will deduct {:precent}% fee.',

    'recharge expire time'              => '{:day} days',

    'all_goods_type'                    => 'All',
    'other_service'                     => 'Other server',
    'all game_service'                  => 'All',
    'out of withdrawal config'          => 'You have only one chance to withdraw per day. Please try again tomorrow.',
    'actual arrival'                    => 'Will deduct fee{:free},actual arrive {:actual}',

    'have already logout'               => 'Out of logining',
    'please login'                      => 'Please login',
    'please set your pay password'      => 'Please set your transaction password',
    'Payment Expired'                   => 'Payment have already expired',
    'can not buy your own goods'        => 'Can not buy your own goods',
    'lack of balance'                   => 'Lack of balance',
    'you are not a seller'              => 'You are not a seller',
    'buyer have not comment yet'        => 'Buyer have not comment yet',

    'can not refund deposit'            =>'Please apply for a deposit refund while you have no on-sell product, and all orders are completed over 10 days.',
    'no new version'                    => 'This is the latest version!',
    'can not submit too many time'      =>'Can not commit too many time a day',

    'wrong idcard'                      =>'Wrong Idcard',
    'wrong image'                       =>'請上傳小於5M，格式爲jpg,png,gif,webp的圖片',
    //404开头 不存在数据的(出现在这里的一般为非法请求或者是没有数据的)
    'account is not exist'                  =>'Account is not exist',

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
    'username wrong format'                =>'Please input the right email or telephone number',

    'old password is eq new password'      =>'The new password is the same as the old password',
    'password or account is wrong'         =>'Wrong password or account',
    'token expired'                        =>'令牌失效',
    'account has been closed'              =>'該賬號已被禁用，如有疑問請聯繫客服',

    'mobile wrong format'                  =>'Please enter valid phone number',
    'mobile is already exist'              =>'This phone number is already used for an existing account',
    'please bind mobile'                   =>'Please bind your telephone number',

    'email wrong format'                   =>'Please enter valid email',
    'email active is expired'              =>'The email verification have been expired',
    'email is already exist'               =>'This email is already used for an existing account',
    'email do not active'                  =>'The email have not actived yet',
    'email active fail'                    =>'The email had actived fail',
    'email already actived'                =>'The email have been actived',

    'verify_code wrong format'             =>'Please enter valid code',
    'verify_code is expired'               =>'Please enter valid code',
    'please get code after 60s'            =>'Can not get verification code twice in 60s',
    'a day can not get code five time'     =>'Can not get code today',
    'captcha is require'                    =>'Please enter captcha',
    'captcha wrong format'                 =>'Please enter valid captcha',
    'jy_pass is require'                   =>'Please set your transaction password',
    'jy_pass is wrong'                     =>'Wrong transaction password',
    'jy_pass wrong count'                  =>'Can input transaction password {:count} times',
    'can not input jy_pass'                =>'The transaction password will be locked in 3h,you can:',
    'idcard_pic is require'                =>'Please upload your Idcard picture',
    'wrong idcard'                         =>'Please enter valid ID Card number',
    'idcard is exist'                      =>'This ID number is already used for an existing account',

    'do not publish goods twice in 60 sec' =>'Can not release the same goods in 60s',
    'goods is already collect'             =>'You have collected the goods',
    'goods is off shelf'                   =>'The goods is off shelf',
    'seller auth is require'               =>'You can sell goods on TPGame after being a seller.',
    'seller auth is false'                 =>'Seller authenticate was failure, please authenticate agian',

    'wait for seller auth'                 =>'Seller authenticate is commited,please wait for auditing',
    'bank card is exist'                   =>'The bank card is exist',
    'card_num and password is require'     =>'Please input card\'password and card\'number',
    'can not create the same order in 60s' => 'Can not create the same order in 60s',
    //'card total num is less then buy num'   =>'发卡的数量小于购买数量',
    'can not modify point card'            => 'This goods still have order waiting for payment,can not edit',
    'charge is require'                    =>'Please input the content of charge',
    'nickname is already exist'            =>'This nickname is already used.',
    'can only edit the nickname once'      =>'You can edit nickname only one chance.',
    'stock not enough to on shelf'         =>'This goods is lacking of stock to on shelf',
    'goods:need to pay deposit'            =>'You can not release this type of goods until you pay deposit',
    'stock is not enough'                  =>'Lack of stock',
    'price can not lower then'             =>'The money can not lower then {:lower}',
    'price can not higher then'            =>'The money can not higher then {:higher}',
    'price can not lower then fee'         =>'The price is too lower to deduct the fee',
    'jy_pass and idcard_pic is require'    =>'Please set your transaction password and upload your Idcard picture',

    'service start between 1-5'            =>'Please select the description is consistent',
    'desc start between 1-5'               =>'Please select the service attitude',
    'speed start between 1-5'              =>'Please select the processing speed',
    'card_num can not more then 100'       =>'The number of point card can not more then 100',
    'card_num can not more then num'       =>'The number of point card can not more then {:num}',
    'flie is too big'                      =>'The upload file is too large',
    'already collected'                    =>'Have been collected',
    'bank card num can not less then 6'    =>'The bank card number can not less then 6',
    'bank card num can not more then 20'   =>'The bank card number can not more then 20',
    //消息模块
    'pay desposit title'                     =>'You have paid the security deposit successfully.',//卖家交了押金的标题
    //'pay desposit content'                   =>'You have payed desposit at {:time}',//卖家交了押金的内容
    'refund deposit title'                   =>'The application for refund of security deposit was processed and has been returned to your wallet.',//退押金的标题
    //'refund deposit content'                 =>'You desposit have refunded to your balance',//退押金的内容

    /*******************************买家已经下单*****************************/
    'buy create order title'                 =>'You received an order waiting for payment.',
    /*******************************买家付款 自动发卡*******************************/
    'buyer payed autosendcard title'         =>'The order has been paid, and the point card is sent to buyer.',
    /*******************************卖家不同意取消订单******************************/
    'you disagree cancling order title'      =>'You have rejected the cancellation of this order, please ship as soon as possible.',
    /*******************************买家已经付款，提醒卖家发货*************************/
    'remind the seller to ship title'        =>'The order has been paid, please ship as soon as possible.',
        /*******************************买家已經提取卡密********************************/
    'buyer ectractpass title'                =>'Point card has been extracted by buyer.',
    /*******************************买家已经收货，提醒卖家查看余额********************/
    'buyer reciver order title'              =>'Order completed. Your will receive the money soon.',
    /******************************买家已经评论，提醒卖家看评论和回复***************/
    'buyer comment order title'              =>'Buyer has made a comment on you, please reply it.',
    /*****************************买家提起申诉，提醒卖家处理*************************/
    'remind seller to deal appeal title'     =>'Buyer submitted a dispute case of this order.',
    /*********************************买家想要关闭交易，提醒卖家处理*******************/
    'remind seller to deal cancel title'     =>'Buyers apply to cancel this order, and waiting for your processing. ',
    /******************************卖家不同意订单申诉客服介入仲裁********************************/
    'you disagree appeal title'              =>'You did not approve your dispute request. Customer service will be involved in processing.',

    /********************************卖家取消买家已付款订单*****************************/
    'you cancel payed order refund title'    =>'You cancel order',
    /*********************************卖家同意取消订单**************************/
    'you agree cancel refund title'          =>'You agree canceling order',
    /*********************************卖家同意订单申诉**********************/
    'you agree appeal refund title'         =>'You agree appeal',
    /*********************************取消未付款订单***********************/
    'buyer cancel no pay order title'       =>'The buyer has cancelled this order.',

    'seller cancel no pay order title'      =>'You cancel order',

    //对于买家会收到的信息
    ///************************************买家支付 自动发卡************************/
    'payed autosendcard title'              =>'The order has been paid, and the point card is sent to you.',
    /************************************卖家修改了价格***************************/
    'seller change order price title'       =>'Seller modified the price.',
    /************************************卖家不同意取消***************************/
    'seller disagree cancle title'         =>'Seller did not approve your cancellation request.',
    /**********************************卖家已经发货，提醒买家*********************/
    'remind buyer to recive title'          =>'Your order has shipped.',
    /*********************************已经收货，提醒买家评论***********************/
    'remind buyer to comment title'         =>'Order completed. Please leave feedback for seller.',
    /*********************************提醒买家申诉已经提交*************************/
    /*'appeal have commit title'              =>'The appeal was commited',
    /********************************关闭交易请求已提交***************************/
    'cancel have commit title'              =>'Awaiting for the order cancellation process.',
    /********************************客服介入仲裁*********************************/

    'seller disagree appeal title'          =>'Seller did not approve your dispute request. Customer service will be involved in processing.',
    /********************************卖家取消已经付款未发货的订单******************/
    'seller cancel payed order refund'      =>'Seller cancelled the order. The money was refunded to your wallet.',
    /********************************卖家同意买家交易关闭***************************/
    'seller agree cancel order refund'      =>'Seller agreed with your cancellation request. The money was refunded to your wallet.',
    /*********************************卖家同意申诉请求*******************************/
    'seller agree appeal refund'            =>'Seller agreed with your dispute request. The money was refunded to your wallet.',

    /***********************************未付款订单取消*************************************/
    /*'cancel no pay order by buyer title'    =>'Cancel order',
    'cancel no pay order by buyer content'  =>'You cancel order.\norder no:{:order_sn}\ngoods:{:title}',*/

    'cancel no pay order by seller title'   =>'Seller cancel order',
    /**********************************客服处理申诉***************************************/
    'customer deal appeal title'           =>'Customer dealed appeal',
    //取消订单理由
    //买家
    'do not want to buy'                    =>'I dont\'t want to buy.',
    'wrong info buy agian'                  =>'I have entered the wrong information.',
    'seller out of stock'                   =>'Seller goods is out of stock.',
    'close reseaon other'                   =>'Other reason.',
    //卖家
    'I out of stock'                        =>'Goods has been sold out.',
    'buyer buy wrong goods'                 =>'Buyer placed the wrong order.',
    'can not connect buyer'                 =>'I can\'t contact with the buyer.',
    //取消交易结果
    'seller cancel order result'            =>"Seller ({:username}) cancelled this order.\nOrder close.",//
    'buy cancel order result'               =>"Buyer ({:username}) cancelled this order.\nOrder close.",
    'buy cancel order commit result'        =>'Buyer ({:username}) want to cancel this order.',
    'seller agree cancel result'           =>'Seller ({:username}) accepted the cancellation.',
    'seller disagree cancel result'        =>'Seller ({:username}) reiected the cancellation.',
    //买家申诉理由
    'desc no match'                         =>'The product I recived does not match the description.',
    'seller not send on time'               =>'Seller didnt\'t ship on time.',
    'seller malicious harassment'           =>'Seller kept sending harasing messages.',
    'sellers exist theft false transactions'=>'Fake transactions.',
    'appeal other'                          =>'Other reason',
    //申诉结果显示
    'buyer commit appeal result'            =>'Buyer ({:username}) make a dispute request.',
    'seller disagree appeal result'        =>"Seller ({:username}) reject the dispute.\nOur Customer Service will handle it.",
    'seller agree appeal result'            =>'Seller ({:username}) accepted to refund, and this order was closed.',
    //充值理由
    'not want to charge'                    =>'I dont\'t want to top-up.',
    'wrong info charge again'               =>'I have entered the wrong information.',
    'no bank nearby'                        =>'No bank nearby so I can not transfer money.',
    'cancel charge other'                   =>'Other reasons.',
   //自动取消未付款订单卖家收到的消息
    'auto deal not pay seller title'       =>'The order is cancelled for no payment.',
    //自动取消未付款订单买家收到的消息
    'auto deal not pay buyer title'        =>'The order is cancelled for no payment.',

    'auto deal not pay reason'             =>'Buyer do not pay on time',//原因
    'auto deal not pay result'             =>'The order have canceled automatically',//结果
    //自动处理过期取消交易卖家收到的消息
    'auto deal not cancel seller title'    =>'System have canceled the order,reason:you do not deal appeal on time',
    'auto deal not cancel seller content'  =>"You do not deal buyer {:buy_name} cancel commit,system caceled the order automatically,the money will return to buyer's wallet.\norder no:{:order_sn}\ngoods:{:title}",
    //自动处理过期取消交易买家收到的消息
    'auto deal not cancel buyer title'     =>'System have canceled the order,reason:seller do not deal appeal on time',
    'auto deal not cancel buyer content'   =>"Seller does not deal your cancel commit,system caceled the order automatically,the money will return to your wallet.\norder no:{:order_sn}\ngoods:{:title}",

    'auto deal not cancel reason'          =>'Seller ({:username}) do not deal cancel in time',
    'auto deal not cancel result'          =>'This order was cancelled automatically because no reply from seller in 3 days.',
    //自动处理过期申诉
    'auto deal not appeal seller title'    =>'You did not handle the dispute order, customer service will be involved in processing.',

    'auto deal not appeal buyer title'     =>'Seller did not handle the dispute order, customer service will be involved in processing.',

    'auto deal not appeal reason'          =>'Seller do not deal appeal on time',
    'auto deal not appeal result'          =>'Customer service will intervene',
    //自动处理过期未收货卖家收到的信息
    'auto deal not confirm seller title'   =>'Transaction was success,the money will transfer to your wallet',
    //自动处理过期未收货买家收到的信息
    'auto deal not confirm buyer title'    =>'Your order is confirmation timeout.',
    //系统默认好评
    'auto comment'                         =>'Goods!',
    'auto cancel expire recharge send message title' => 'Cancel recharge automatically',
    'auto cancel expire recharge'             =>'Cancel recharge automatically',
    //账号类型
    'account'       =>'Accounts',
    'dailian'       =>'Power Leveling',
    'prop'          =>'Items',
    'coin'          =>'Game Currency',
    'pointcard'     =>'Game Cards',
    'rechange'      =>'Recharge',
    'giftpack'      =>'Game Packages',
    'other'         =>'Others',
    //商品特殊属性
    'role_name'     =>'Character name',
    'role_level'    =>'Character level',
    'bind'          =>'Account binding',
    'pl_require'    =>'Level up Request',
    'pl_content'    =>'Level up Content',
    'time'          =>'Level up Time',
    'prop_name'     =>'Items Name',
    'coin_num'      =>'Game Currency Quantity',
    'charge'        =>'Recharge Amount',
    'pack_content'  =>'Package contents',
    'use_method'    =>'Instructions',
    'send_type'     =>'Delivery Method',
    //发卡方式
    'auto send card'    =>'Send card automatically',
    'manual send card'  =>'Mamual Control',
    //账号绑定
    'security_phone'    =>'Mobile phone',
    'security_email'    =>'Email',
    'security_problem'  =>'Security question',
    'security_idcard'   =>'ID Card No.',
    'security_other'    =>'Other',
    'no_bind'           =>'None',
    //支付方式
    'no pay'            =>'No payment',
    'pay_default'       =>'Wallet Balance',
    //充值方式（充值记录）
    'outline bank recharge'=>'Recharge by bank card',
    'outline_cash recharge' => 'Recharge by bank through cash',
    //资金明细
    'finance_recharge'=> 'Top-up/ID:{:id}/Method:{:recharge_type}',
    'finance_pay'     => 'Order Payment/ID:{:id}/Method:{:pay_type}',
    'finance_income'  =>'Order Income/ID:{:id}/It is deducted handling fee THB {:free}.',
    'finance_refund'  =>'Order Refunds/ID:{:id}',
    'finance_withdraw:wait' => 'Withdrawal/ID:{:id}/After deducting the withdrawal fee THB {:free}, you will recceive THB {:cash}.',
    'finance_withdraw:success' => 'Withdrawal/ID:{:id}/After deducting the withdrawal fee THB {:free}, you will recceive THB {:cash}. After successful withdrawal, please check your bank account .',
    'finance_withdraw:fail' => "Failed to withdraw/ID:{:id}/Fail to withdraw:{:reason}\nThe money is refunded to your wallet.",
    'finance_pay_deposit' =>'Security Deposit Payment',
    'finance_refund_deposit' =>'Security Deposit Refund',
    //
    'withdrawal:desc1' => 'You have only one chance to withdraw pre day. ',
    'withdrawal:desc2' => 'According to bank regulations, banks charge a withdrawal fee of {:precent}% of the transaction amount.',
    'withdrawal:desc3' => 'You can withdraw at least THB {:lower} and the withdrawal limit THB {:higher}.',
    'withdrawal:desc4' => 'Withdrawal amount will be handled within 1-3 business days.',
    //
    'none'              =>'None',
    //後台充值消息通知
    'recharge success'  =>'Your top-up application has been done. The application number is {:charge_no}.',
    'recharge reject'  =>"Failed to top-up for the reason: {:reason}.\nYour Top-up number is {:charge_no} via {:recharge_type}. The top-up amount is THB {:cash}.",

    'withdraw success' =>'Your withdrawals application has been processed. Please check your bank account in time.',
    'withdraw reject'  => 'Your withdrawals application has been failed. The money was refunded to your wallet.',
    //卖家认证通知
    'certification pass'=> 'Congratulations! You become one of our TPGAME seller!',
    'certification no pass' =>"Failed to complete the seller authentication for the reason: {:reason}.\nPlease try it again with the correct information.",

    'can not get auth' => 'Failed to get authorization.',
    'redirect user to input email' => 'Finish creating your account for the full experience.',
    'bind third_email to account'  => 'This {:third_type} email is already used for an existing account. Are you sure to sign in with this account?',
];