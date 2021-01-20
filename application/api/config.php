<?php

//配置文件
return [
    'app_debug' => true,    //true 打开错误调试模式 仅用于错误接口调试
    'sign_debug' => true,    // true sign不验证仅用于接口调试
    'exception_handle' => '\app\common\exception\ApiExceptionHandle', //异常处理类
];
