<?php
return array(
    // '配置项'=>'配置值'
    'SEND_MES_COUNT' => 3, // 发送短信次数限制
    'SEND_MES_URL' => 'http://www.exincn.com/sms/api/index.asp?Action=send&SmsFlag=0&UserName={$userName}&UserPass={$userPass}&Mobile={$phone}&Text={$content}', // 发送短信的网址GET
    'SEND_MES_USER' => 'ztdb', // 短信接口用户名
    'SEND_MES_PWD' => '123123', // 短信接口密码
    'SEND_MES_TXT' => '(O2O平台验证码,五分钟内有效）【O2O平台】', // 短信内容
    'VIEW_PATH' => './Tpl/',
    'DEFAULT_THEME' => 'Game',
    'TMPL_PARSE_STRING' => array(
        '__JS__' => '/Tpl/Game/js',
        '__CSS__' => '/Tpl/Game/css',
        '__IMG__' => '/Tpl/Game/image',
        '__WST_WEB__' => '/'
    ),
    'cancelOrderTime' => 604800,       //设置发货后，用户在规定的时间内才能订单投诉，初始值是 7天 （604800）
    /*  APP_DEBUG=>true,
     DB_FIELD_CACHE=>false,
     HTML_CACHE_ON=>false,
     define('WEB_HOST', 'http://dsh.yao2099.com'),
     define('TOKEN', 'weixin'),
     'TOKEN_ON' => false, // 是否开启令牌验证 默认关闭
     'TOKEN_NAME' => '__hash__', // 令牌验证的表单隐藏字段名称，默认为__hash__
     'TOKEN_TYPE' => 'md5', //令牌哈希验证规则 默认为MD5
     'TOKEN_RESET' => true, //令牌验证出错后是否重置令牌 默认为true */
    //微信 支付配置
    'WxPayConf_pub' => array(
        'APPID' => 'wxfb4f1d02331ee9b8',  //wx06f42dac86f84bc0
        'MCHID' => '1319378501',
        'KEY' => 'sZikSgv51MEfMoXYqBIIUrzUIOlz6E6G',
        'APPSECRET' => '2b760541991462f47226fc2085f9c406',      //632dfd6392e7ff42443c7cf1324d1116
        'JS_API_CALL_URL' => WEB_HOST . '/Game/Confirm/onlinkPay/',
        'SSLCERT_PATH' => WEB_HOST . '/ThinkPHP/Library/Vendor/WxPayPubHelper/cacert/apiclient_cert.pem',
        'SSLKEY_PATH' => WEB_HOST . '/ThinkPHP/Library/Vendor/WxPayPubHelper/cacert/apiclient_key.pem',
        'NOTIFY_URL' => WEB_HOST . '/index.php/Game/Confirm/notify',
        'CURL_TIMEOUT' => 30
    ),
    'qqLogin' => array(
        'APPID' => '101376462',         //       'APPID' => '101338405',
        'APPKEY' => '01a65af411e264bb26d5c4dcede284db',         //    'APPKEY' => '3330e17b9b471b0a09a9d96d8098ce93',
        'CALLBACK' => WEB_HOST . '/Game/Login/qqLoginCallBack',
        'SCOPE' => 'get_user_info,list_album,add_album,upload_pic,add_topic,add_weibo',
    ),
    'alipay_config' => array(
        'partner' =>'2088421759946612',   //这里是你在成功申请支付宝接口后获取到的PID；
        'private_key'=>'key/rsa_private_key.pem',// 商户的私钥（后缀是.pen）文件相对路径
        'ali_public_key_path'=>'key/alipay_public_key.pem',	// 支付宝公钥（后缀是.pen）文件相对路径
        'sign_type'=>strtoupper('RSA'),
        'input_charset'=> strtolower('utf-8'),
        'cacert'=> getcwd().'\\key\\cacert.pem',
        'transport'=> 'http',
    ),
    'alipay' => array(
        //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'seller_email' => 'sy@shouyougou.cn',
        //这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
        'notify_url' =>WEB_HOST. '/index.php/Game/AliPay/notifyurl',
        //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        //'return_url' =>WEB_HOST. '/index.php/Game/Index/index/r/order',
        'return_url' =>WEB_HOST. '/index.php/Game/AliPay/returnurl2', 
        //支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
        'successpage' => WEB_HOST.'/index.php/Game/Index/index/r/order',
        //支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
        'errorpage' => WEB_HOST.'/index.php/Game/Index/index/r/order',
    ),
    
);