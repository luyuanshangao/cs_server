<?php

namespace app\api\validate;

class Wallet extends \think\Validate
{
    //规则
    protected $rule = [
        ['page','number', '页数为数字'],
        ['size','number', '页数大小为数字'],
        ['assetsType','number', '币种类型为数字'],
        ['detailType','in:0,1,2','明细类型错误'],
        ['amount','require|gt:0','数量必须|数量必须大于0'],
        ['walletAddr','require','接收地址必须'],
        ['payPassWord','require|length:6|number','支付密码必须|支付密码长度错误|支付密码错误'],
    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'getAssetsDetails' => ['page','size','assetsType','detailType'],
        'assetsWithdraw' => ['amount','assetsType','walletAddr','payPassWord'],

    ];
}
