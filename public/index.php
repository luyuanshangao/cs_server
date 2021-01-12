<?php

if (strpos($_SERVER['REQUEST_URI'], 'tmp') !== false) {
    die;
}
if (strpos($_SERVER['REQUEST_URI'], 'wget') !== false) {
    die;
}

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
//允许跨域
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers:token,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");
//swoole是cli运行，执行地址path会无法正确找到，所以把base.php里面定义的常量提出来，设置为假，
//把源代码base.php里面的define('IS_CLI', PHP_SAPI == 'cli' ? true : false);代码去掉
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
//版本号文件路径
define('VERSION_PATH', __DIR__ . '/../');
//public路径目录
define('WEB_PATH', __DIR__ . '/../public/');
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
