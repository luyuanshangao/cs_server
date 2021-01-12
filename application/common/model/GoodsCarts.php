<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

use app\api\controller\v1\Vop;

class GoodsCarts extends BaseModel
{
    /**
     * @name:        向购物车中添加n个商品
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function add($goodsData)
    {
        $obj = $this::get(['skuNum' => $goodsData['skuNum'],'userId' => $goodsData['userId']]);
        if ($obj) {
            $obj->setInc('num', $goodsData['num']);
            return true;
        }

        $result = $this->insert($goodsData);
        return true;
    }

    /**
     * @name:        减少购物车中商品数量,如果减少到0,则从购物车删除该商品
     * @author:      gz
     * @description:
     * @param        {type} $goods_id int 商品id
     * @return:
     */
    public function decr($userId, $skuNum, $num = 1)
    {
        $obj = $this::get(['skuNum' => $skuNum,'userId' => $userId]);
        
        if ($obj->num === 1) {
            $this->del($skuNum, $userId);
            return true;
        }
        $obj->setDec('num', 1);
        return true;
    }
    /**
     * @name:        增加购物车中商品数量
     * @author:      gz
     * @description:
     * @param        {type} $goods_id int 商品id
     * @return:
     */
    public function incr($userId, $skuNum, $num = 1)
    {
        $obj = $this::get(['skuNum' => $skuNum,'userId' => $userId]);
        $obj->setInc('num', 1);
        return true;
    }

    /**
     * @name:        从购物车删除某商品
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function del($skuNumArr = 1, $userId)
    {

        if ($skuNumArr) {
            $condition = [
                'skuNum' => ['in',$skuNumArr],
                'userId' => $userId
            ];
        } else {
            $condition = [
                'userId' => $userId
            ];
        }

        $this->where($condition)->delete();
    }

    /**
     * @name:        列出购物车所有的商品
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function items($userId)
    {
        return $this->where(['userId' => $userId])->select();
    }

    /**
     * @name:        返回购物车有几种商品
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function calcType($userId)
    {
        //因为有几个goods_id就有几个商品
        return $this->where(['userId' => $userId])->count();
    }



    /**
     * @name:        清空购物车
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function clear($userId)
    {
        return $this->where(['userId' => $userId])->delete();
    }
}
