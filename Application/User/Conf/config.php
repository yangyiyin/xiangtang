<?php

// +----------------------------------------------------------------------
// | Author: Jroy 
// +----------------------------------------------------------------------

/**
 * UCenter客户端配置文件
 * 注意：该配置文件请使用常量方式定义
 */

define('UC_APP_ID', 1); //应用ID
define('UC_API_TYPE', 'Model'); //可选值 Model / Service
define('UC_AUTH_KEY', 'a6~qh5P&DUi3-Av,%M<7cYTwVO_Rf:.S?+XLtEum'); //加密KEY
define('UC_DB_DSN', 'mysql://gd_ant_user:fpf5Vsdv2HfANSAB@121.40.162.180:3306/gd_ant'); // 数据库连接，使用Model方式调用API必须配置此项
define('UC_TABLE_PREFIX', 'ant_'); // 数据表前缀，使用Model方式调用API必须配置此项
