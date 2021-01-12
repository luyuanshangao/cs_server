<?php

namespace app\api\validate;

class Collection extends \think\Validate
{
    //规则
    protected $rule = [
        ['page','number', '页数为数字'],
        ['size','number', '页数大小为数字'],
        ['skuNum','require|alphaNum', '商品编号必须|商品编号错误'],
        ['skuNumArr','require|checkJson', '商品编号必须|商品编号错误'],
        ['priceUsdt','number', '价格必须|价格错误'],
    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'list' => ['page','size'],
        'add' => ['skuNum','priceUsdt'],
        'del' => ['skuNumArr'],
    ];
    protected function checkJson($value, $rule, $data)
    {
        $checkData  = json_decode($value, true);
        if (!is_array($checkData)) {
            return false;
        }
        return true;
    }
}
