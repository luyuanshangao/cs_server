<?php

namespace app\admin\model;

use app\common\model\BaseModel;

class OrderGoods extends BaseModel
{

    public function getOrderGoods($orderId)
    {
        return $this->where(['orderId' => $orderId])->select();
    }
}
