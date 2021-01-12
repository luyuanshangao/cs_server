<?php

namespace app\queue\controller;

use think\Controller;

class CacheQueue extends Controller
{


    /**
     * @name: swoole 缓存商品池商品
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function cacheGoods()
    {
        //当前时间5秒后开始运行
        $runTime = time() + 5;
        db('system')->where('name', '=', 'update_pool_goods_info_cache')->update(['value' => $runTime]);
    }

    /**
     * @name: swoole 缓存分类下商品
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function cacheCateGoods()
    {
        //当前时间5秒后开始运行
        $runTime = time() + 5;
        db('system')->where('name', '=', 'update_cate_goods_info_cache')->update(['value' => $runTime]);
    }

    
    public function setCateGoodsSet($catId)
    {
       
        $condition =
        [
            "keyword" => '',
            "catId" => $catId,
            "pageIndex" => 1,
            "pageSize" => 100,
        ];

        $result = \app\api\controller\v1\Vop::GoodsSearch($condition);

        if (!$result) {
            return;
        }
        
        for ($i = $result['pageIndex']; $i <= $result['pageCount']; $i++) {
            try {
                $conditionS =
                [
                    "keyword" => '',
                    "catId" => $catId,
                    "pageIndex" => $i,
                    "pageSize" => 100,
                 ];
                $resultSearch = \app\api\controller\v1\Vop::GoodsSearch($conditionS);
                if (!$resultSearch) {
                    continue;
                }

                foreach ($resultSearch['hitResult'] as $key => $goods) {
                    try {
                        if ($goods['wstate'] && $goods['wyn']) {
                            $info = \app\common\library\redis\GoodsInfo::getAllGoodsInfoBySkuNum($goods['sku_num']);
                                
                            if ($info) {
                                continue;
                            } else {
                                $lPushData['sku_num'] = $goods['sku_num'];
                                \app\common\library\redis\GoodsCache::lPushData($lPushData, time(), 'all');
                            }
                        }
                    } catch (\Throwable $th) {
                        continue;
                    }
                }
            } catch (\Throwable $th) {
                continue;
            }
        }
    }
}
