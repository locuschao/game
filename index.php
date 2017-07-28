<?php
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<'))
    die('require PHP > 5.3.0 !');
    // 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);
define('ACC_APP', true);
define('WEB_HOST', 'http://'.$_SERVER['HTTP_HOST']);
// 定义应用目录
define('APP_PATH', './Application/');
// 定义加密字符串
define('ENCRYPT', md5(time()));
/* 扩展目录 */
define('EXTEND_PATH', './Library/');
// 进入安装目录
if (is_dir("Install") && ! file_exists("Install/install.ok")) {
    header("Location:Install/index.php");
    exit();
}
// 引入ThinkPHP入口文件
require './ic_core.php';


