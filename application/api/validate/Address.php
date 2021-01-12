<?php

namespace app\api\validate;

class Address extends \think\Validate
{
    //规则
    protected $rule = [
        ['page','number', '页数为数字'],
        ['size','number', '页数大小为数字'],
        ['addressId','require|number', '地址必须|地址为数字'],
        ['userName','require|max:20', '收货人必须|收货人名称过长'],
        ['userPhone','require|max:11|number', '联系电话必须|联系电话过长|联系电话为数字'],
        ['areaIdPath','require', '所在地区必须'],
        ['addressDetails','require|max:50', '详细地址必须|详细地址过长'],
        ['isDefault','require|in:1,0', '是否默认必须|默认错误'],
        ['ip','require|ip', '参数错误|参数错误'],
    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'list' => ['page','size'],
        'add' => ['userName','userPhone','areaIdPath','addressDetails','isDefault'],
        'update' => ['addressId','userName','userPhone','areaIdPath','addressDetails','isDefault'],
        'get' => ['addressId'],
        'del' => ['addressId'],
        'getPcct' => ['ip'],
    ];
}
