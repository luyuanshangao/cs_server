<?php

namespace app\admin\validate;

class AdminLogin extends \think\Validate
{
    //规则

    protected $rule = [
        ['password','require|length:6,25', '密码不能为空|密码长度必须在6-25位之间'],
        ['username','require|length:2,25', '用户名不能为空|用户名长度必须在2-25位之间'],
        ['code','require|alphaNum', '验证码不能为空|验证码错误'],
       
      
    ];
    
    //场景不同场景验证不同的字段
    protected $scene = [
        'login' => ['password','username','code'],
        
    ];
}
