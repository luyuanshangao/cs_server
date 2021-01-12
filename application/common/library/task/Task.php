<?php

namespace app\common\library\task;

use app\api\controller\v1\Vop;
use app\common\library\redis\GoodsInfo;

/**
 * @name: 基于swoole异步task任务
 * @author: gz
 * @description:
 * @param {type}
 * @return:
 */
class Task
{



    /**
     * @name: 设置商品缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function setGoodsCache($sku_num)
    {

        try {
            $priceArr = Vop::upGetPrice($sku_num);
            $resultInfo = Vop::getGoodsInfo($sku_num);
            $resultPic = Vop::getGoodsPic($sku_num);
            $picsArr = array_column($resultPic[$sku_num], 'pic_path');
            $info = [
                'sku_num' => $resultInfo['sku_num'],
                'spu_name' => $resultInfo['spu_name'],
                'pics' => json_encode($picsArr),
                'state' => $resultInfo['state'],
                'price' => $priceArr['price'],
                'originPrice' => $priceArr['originPrice'],
            ];
            if (!$info || !isset($info['price']) || !$info['price'] || !isset($info['spu_name']) || !$info['spu_name']  || !isset($info['pics']) ||  !$info['pics'] || !isset($info['state']) ||  !$info['state']) {
                throw new \Exception("");
            }

            foreach ($info as $key => $value) {
                GoodsInfo::setGoodsInfoOne($sku_num, $key, $value);
            }
           
            GoodsInfo::setGoodsKeySet($sku_num);
        } catch (\Exception $th) {
            GoodsInfo::delGoodsKey($sku_num);
            GoodsInfo::delKeyByName($sku_num);
            return;
        }
        return;
    }
}
