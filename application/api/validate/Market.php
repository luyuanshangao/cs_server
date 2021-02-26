<?php

namespace app\api\validate;

class Market extends \think\Validate
{
    //规则
    protected $rule = [
        ['symbol','require','参数错误'],
        ['amount','require|number','参数错误|参数错误'],
        ['side','require','参数错误'],
        ['type1','require|checkType','参数错误|参数错误'],
        ['type2','require|checkType','参数错误|参数错误'],
        ['amount','require|number','参数错误|参数错误'],
       
    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'quickChange' => ['symbol','amount','side'],
        'exchangeRate' => ['type1','type2','amount'],
    ];
    protected function checkType($value, $rule, $data){
        $sybolArr = [
            'CNY',
            'BTC',
            'ETH',
            'UNI',
            'USDT',
        ];
        try {
            if(!in_array($value,$sybolArr)){
                return false;
            }
        } catch (\Exception $th) {
            return false;
        }
       
        return true;
    }
}
