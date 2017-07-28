<?php
return array(
    'VAR_PAGE' => 'p',
    'PAGE_SIZE' => 15,
    'DB_TYPE' => 'mysql',
    'DB_HOST' => '127.0.0.1',
    'DB_NAME' => 'ywyxdatabase',

    'DB_USER' => 'root',
    'DB_PWD' => '',
    'DB_PREFIX' => 'oto_',
    'DEFAULT_CITY' => '440100',
    'URL_MODEL' => 2,
    'DATA_CACHE_SUBDIR' => true,
    'DATA_PATH_LEVEL' => 2,
    'SESSION_PREFIX' => 'oto_mall',
    'COOKIE_PREFIX' => 'oto_mall',
    'LOAD_EXT_CONFIG' => 'ext_config',
    'MODULE_ALLOW_LIST' => array(
        'Home',
        'Admin',
        'Wx',
        'Api',
        'Game',
        'GameAPI',
        'Native',
        'ImproveAPI'
    ), // 允许访问的模块列表
    //'DEFAULT_MODULE' => 'game', //默认模块    
                                                                            // 图片上传相关信息
    'VIEW_ROOT_PATH' => '/Upload/',             //原配置   'VIEW_ROOT_PATH' => '/Upload/',
    // 加密解密KYE
    'authCodeKey' => '@#ERGHJ$%^&*()_+PLKM IJB!@#$1qsxc@WEDC3efv$THN',
    
    /**
     * @author peng
     * @date 2017-01
     * @descreption peng
     */
    'SDK_FAHUO_URL'=>'http://localhost.sdk.cn/agent.php/Api/Recharge',
    'BIND_SDKAGENT_URL'=>'http://localhost.sdk.cn/agent.php/Api/ValidateAgent',
    'CHECK_GAME_URL'=>'http://localhost.sdk.cn/agent.php/Api/ValidateGame',
    'CHECK_ACCOUNT_URL'=>'http://localhost.sdk.cn/agent.php/Api/ValidateGame/checkAccount',
    //'SDK_FAHUO_URL'=>'http://tui.shouyougou.cn/agent.php/Api/Recharge',
//    'BIND_SDKAGENT_URL'=>'http://tui.shouyougou.cn/agent.php/Api/ValidateAgent',
//    'CHECK_GAME_URL'=>'http://tui.shouyougou.cn/agent.php/Api/ValidateGame',
//    'CHECK_ACCOUNT_URL'=>'http://tui.shouyougou.cn/agent.php/Api/ValidateGame/checkAccount'
//'TMPL_EXCEPTION_FILE'=>'./Tpl/Public/error.html',
    /**
     * @author peng
     * @date 2017-02
     * @descreption 是否开启TT版本和乐8的自动充值，在本地不测试的时候不要开启
     */
     'TT_AUTO_RECHARGE'=>false,
     'LE8_AUTO_RECHARGE'=>false,
)
;

/*
 * 原数据
'DB_NAME' => 'ywyxdatabase',
'DB_USER' => 'ywuserdata',
'DB_PWD' => 'user6yhn*IK<0p;/',
*/
