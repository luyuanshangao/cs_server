<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\library\redis\Cart as CartCache;
use app\common\library\redis\CacheKeyMap;

class Cart extends Base
{
    protected $noAuthArr = [];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单



    /**
     * @name:        加入购物车
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function addCart()
    {
        try {
            $dataArr  = $this->checkdate('Goods', 'post', 'cart');
            isset($dataArr['num']) ? $num = $dataArr['num'] : $num = 1;
            
           // $price = $this::toUsdt($this::obtainGoodsInfo($dataArr['skuNum'], 'price'));
            $cacheData = CartCache::getCart($this->userId, $dataArr['skuNum']);
            if ($cacheData) {
                $goods = json_decode($cacheData, true);
                $goods['num'] += $num;
                $jsonGoods = json_encode($goods);
            } else {
                $pic = $this::obtainGoodsInfo($dataArr['skuNum'], 'pic');
                if (!$pic) {
                    throw new \Exception("Error Processing Request", 1);
                }
                $goods = array(
                    'skuNum' => $dataArr['skuNum'],
                    'price' => $this::obtainGoodsInfo($dataArr['skuNum'], 'price'),
                    'num' => $num,
                    'pic' => $pic,
                    'userId' => $this->userId,
                    'spuName' => $dataArr['spuName'],
                    'createTime' => time(),
                );
             
                $jsonGoods = json_encode($goods);
            }
            CartCache::setCart($this->userId, $dataArr['skuNum'], $jsonGoods);
        } catch (\Exception $th) {
            return show(0);
        }
       
        return show(1);
    }

    /**
     * @name:        减少1购物车中商品数量
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function decrCart()
    {
        try {
            $dataArr  = $this->checkdate('Goods', 'post', 'cart');
            $cacheData = CartCache::getCart($this->userId, $dataArr['skuNum']);
            if ($cacheData) {
                 $goods = json_decode($cacheData, true);
                 $goods['num'] -= 1;
                 $jsonGoods = json_encode($goods);
                 CartCache::setCart($this->userId, $dataArr['skuNum'], $jsonGoods);
            }
        } catch (\Exception $th) {
            return show(0);
        }
        return show(1);
    }
    /**
     * @name:        增加1购物车中商品数量
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function incrCart()
    {
        try {
            $dataArr  = $this->checkdate('Goods', 'post', 'cart');
            $cacheData = CartCache::getCart($this->userId, $dataArr['skuNum']);
            if ($cacheData) {
                 $goods = json_decode($cacheData, true);
                 $goods['num'] += 1;
                 $jsonGoods = json_encode($goods);
                 CartCache::setCart($this->userId, $dataArr['skuNum'], $jsonGoods);
            }
        } catch (\Exception $th) {
            return show(0);
        }
        return show(1);
    }


    /**
     * @name:        从购物车删除某商品
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function delCart()
    {

        try {
            $dataArr  = $this->checkdate('Goods', 'post', 'delCart');
            $skuNumArrs  = json_decode($dataArr['skuNumArrs']);
            foreach ($skuNumArrs as $key => $skuNum) {
                CartCache::delCart($this->userId, $skuNum);
            }
        } catch (\Exception $th) {
            return show(0);
        }
        
        return show(1);
    }

    /**
     * @name:        购物车列表
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function getCart()
    {
      
        try {
            //$getArr  = $this->checkdate('Collection', 'get', 'list');
            $returnResult = [
                'total' => 0,
                'page_num' => 0,
                'list' => [],
            ];
            $list = [];
            $datas = CartCache::allCart($this->userId);
          
            foreach ($datas as $skuNum => $jsonGood) {
                    $goods = json_decode($jsonGood, true);
                    $goods['price_r'] =   $this::toRmb($goods['price']);
                    $goods['price'] =   $this::toUsdt($goods['price']);
                    $list[] = $goods;
            }
            array_multisort(array_column($list, 'createTime'), SORT_DESC, SORT_NUMERIC, $list);
            $returnResult = [
                'total' => $datas ? count($datas) : 0,
                'page_num' => $datas ? 1 : 0,
                'list' => $list,
            ];
        } catch (\Exception $th) {
            return show(0);
        }
       
        return show(1, $returnResult);
    }

    /**
     * @name:        返回购物车有几种商品
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function getCount()
    {
        $count  = 0;
        try {
            $count = CartCache::countCart($this->userId);
        } catch (\Exception $th) {
            return show(0);
        }
        
        return show(1, ['count'=>$count]);
    }

    /**
     * @name:        清空购物车
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function clearCart()
    {
        $resulrt = CartCache::delKey(CacheKeyMap::cartHash($this->userId));
      
        try {
            CartCache::delKey(CacheKeyMap::cartHash($this->userId));
        } catch (\Throwable $th) {
            return show(0);
        }
        return show(1);
    }
}
