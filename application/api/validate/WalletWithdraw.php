<?php

namespace app\api\validate;

class WalletWithdraw extends \think\Validate
{
    //规则
    protected $rule = [
        ['page','number', '页数为数字'],
        ['size','number', '页数大小为数字'],
        ['assetsType','require|number', '币种类型必须|币种类型为数字'],
        ['amount','require|gt:0','数量必须|数量必须大于0'],
        ['walletAddr','require','接收地址必须'],
        ['payPassword','require|length:6|number','支付密码必须|支付密码长度错误|支付密码错误'],
    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'assetsWithdraw' => ['amount','assetsType','walletAddr','payPassword'],

    ];
}
