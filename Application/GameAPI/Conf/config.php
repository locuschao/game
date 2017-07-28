<?php
return array(
	//'配置项'=>'配置值'
    'SEND_MES_COUNT'=>3,//发送短信次数限制
    'SEND_MES_URL'=>'http://www.exincn.com/sms/api/index.asp?Action=send&SmsFlag=0&UserName={$userName}&UserPass={$userPass}&Mobile={$phone}&Text={$content}',//发送短信的网址GET
    'SEND_MES_USER'=>'ztdb',//短信接口用户名
    'SEND_MES_PWD'=>'123123',//短信接口密码
    'SEND_MES_TXT'=>'(O2O平台验证码,五分钟内有效）【O2O平台】',//短信内容
	'VIEW_PATH' => './Tpl/',
	'DEFAULT_THEME' => 'Game',
	'TMPL_PARSE_STRING' => array(
		'__JS__'     => '/Tpl/Game/js',
		'__CSS__'     => '/Tpl/Game/css',
		'__IMG__'     => '/Tpl/Game/image',
		'__WST_WEB__'     => '/',
	),
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
    'WxPayConf_pub'=>array(
        'APPID' => 'wxc90f1726e1563446',
        'MCHID' => '1282208201',
        'KEY' => '20151231GZRHchenbiyan20140817LyH',
        'APPSECRET' => 'a6bf07cff3f5661771dc10d292cd35d4',
        'JS_API_CALL_URL' => WEB_HOST.'/index.php/Wx/Confirm/onlinkPay',
        'SSLCERT_PATH' => WEB_HOST.'/ThinkPHP/Library/Vendor/WxPayPubHelper/cacert/apiclient_cert.pem',
        'SSLKEY_PATH' => WEB_HOST.'/ThinkPHP/Library/Vendor/WxPayPubHelper/cacert/apiclient_key.pem',
        'NOTIFY_URL' =>  WEB_HOST.'/index.php/Wx/Confirm/notify',
        'CURL_TIMEOUT' => 30
    )
);