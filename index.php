<?php

// +----------------------------------------------------------------------
// | Author: Jroy 
// +----------------------------------------------------------------------
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');
error_reporting(E_ALL ^ E_NOTICE);
/**
 * 系统调试设置
 * 项目正式部署后请设置为false
 */
define ( 'APP_DEBUG', true );

define ( 'API_DEBUG', true);


/**
 * 应用目录设置
 * 安全期间，建议安装调试完成后移动到非WEB目录
 */
define ( 'APP_PATH', './Application/' );

if(!is_file(APP_PATH . 'User/Conf/config.php') && !is_file(APP_PATH . 'Common/Conf/config.php')){
	header('Location: ./install.php');
	exit;
}

/**
 * 缓存目录设置
 * 此目录必须可写，建议移动到非WEB目录
 */
define ( 'RUNTIME_PATH', './Runtime/' );

header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE');

/**
 * 引入核心入口
 * ThinkPHP亦可移动到WEB以外的目录
 */
require './ThinkPHP/ThinkPHP.php';

/**
mkdir -m 777 /data/www/walle/xiangtang/Runtime
mkdir -m 777 /data/www/walle/xiangtang/Uploads
mkdir -m 777 /data/www/walle/xiangtang/pages
 */