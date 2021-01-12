<?php

namespace app\common\model;

class PriceRule extends BaseModel
{

    public static function getPricePercent($price)
    {
        $condition = [
            'status' => 1,
            'minPrice' => ['lt',$price],
            'maxPrice' => ['gt',$price],
        ];
        $ruleObj = self::get($condition);
        if (!$ruleObj) {
            return 0;
        }
        return $ruleObj->incRate;
    }
    public static function getIncPrice($price,$rate){
        //转为原价美元
        $oldUsdtPrice =  bcdiv($price, $rate, 3);
        //商品增加百分比
        $percent = self::getPricePercent($price);
        //商品增加收益后的价格(RMB)
        //$pricePercent = ceil($price *(1 + $percent / 100) );
        //商品增加收益后的价格(USDT)售价
        $incPrice = $price * (1 + $percent / 100);
        $usdtPricePercent = ceil($incPrice / $rate * 100) / 100;
        return [
            'oldUsdtPrice'=>$oldUsdtPrice,
            'percent'=>$percent,
            'incPrice'=>$incPrice,
            'usdtPricePercent'=>$usdtPricePercent,
        ];
    }
}
