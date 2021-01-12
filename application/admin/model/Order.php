<?php

namespace app\admin\model;

use app\common\model\BaseModel;

class Order extends BaseModel
{


    /**
     *获取器 获取商品信息
     */
    public function getGoodsInfoAttr($value, $data)
    {

        $OrderGoodsModel = new OrderGoods();
        return $OrderGoodsModel->getOrderGoods($data['orderId']);
    }
    /**
     *获取器 获取商品信息
     */
    public function orderGoods()
    {
        
        return $this->hasMany('order_goods', 'orderId', 'orderId');
    }

 

    /**
     * 查找用户订单
     */
    public function getOrder($condition, $from, $size)
    {

        
        
        $total = $this->getOrderCountByCondition($condition);
        $list = $this->getOrderListByCondition($condition, $from, $size);
        $re = [];
        foreach ($list as $order) {
            $re[] = $order->append(['goods_info'])->toArray();
        }
      
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $size),
            'list' => $re,
        ];
        return $returnResult;
    }

        /**
     * 根据条件来获取列表的数据的总数
     * @param array $param
     */
    public function getOrderCountByCondition($condition = [])
    {

        return $this->where($condition)
            ->count();
//echo $this->getLastSql();
    }

    /**
     * 根据来获取列表的数据
     * @param array $param
     */
    public function getOrderListByCondition($condition = [], $from = 0, $size = 10, $field = true, $order = '')
    {


        $result = $this->where($condition)
            ->field($field)
            ->limit($from, $size)
            ->order($order)
            ->select();
//echo $this->getLastSql();exit;
        return $result;
    }
}
