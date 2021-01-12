<?php

namespace app\api\validate;

class Header extends \think\Validate
{
    //规则

    protected $rule = [
        ['sign','require', 'sign不能为空'],

    ];
}
