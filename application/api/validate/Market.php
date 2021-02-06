<?php

namespace app\api\validate;

class Market extends \think\Validate
{
    //规则
    protected $rule = [
        ['symbol','require','参数错误'],
        ['amount','require|number','参数错误|参数错误'],
        ['side','require','参数错误'],
       
    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'quickChange' => ['symbol','amount','side'],
    ];
}
