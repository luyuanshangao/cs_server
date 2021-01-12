<?php

namespace app\api\validate;

class Extension extends \think\Validate
{
    //规则
    protected $rule = [
        ['beginTime','dateFormat:Y-m-d', '日期格式错误'],
        ['endTime','dateFormat:Y-m-d', '日期格式错误'],
        ['page','number', '页数为数字'],
        ['size','number', '页数大小为数字'],
        ['type','require|number', '类型不能为空|类型为数字'],

    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'myFans' => ['beginTime','endTime'],
        'allFans' => ['page','size','type'],
        'profitUserList' => ['page','size'],
        'myAssetsList' => ['page','size'],
        'getChallengeReward' => ['type'],
        'miningCens' => ['type'],

    ];
}
