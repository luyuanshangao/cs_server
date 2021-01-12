<?php

namespace app\common\model;

use think\Model;

class Rate extends Model
{
    public function updateRate()
    {
        $rate = USDRate();
        $this->where("id=1")->setField("USDRate", $rate);
        return true;
    }

    public function getRate()
    {
        $rate = $this->where("id=1")->value("USDRate");
        return $rate;
    }
}
