<?php

namespace app\common\model;

class OrderGoods extends BaseModel
{

    public function goodsAppraises()
    {
        return $this->belongsTo('goods_appraises', 'orderGoodsId', 'orderGoodsId');
    }

    public function orderServices()
    {
        return $this->belongsTo('order_services', 'orderGoodsId', 'orderGoodsId')->order('createTime desc');
    }

    public function getYlOrderIdAttr($value, $data)
    {
        return Order::where(['orderId' => $data['orderId']])->value('ylOrderId');
    }

    public static function computeIncPrice($orderId)
    {
        return self::where(['orderId' => $orderId])->sum('incPrice');
    }
}
