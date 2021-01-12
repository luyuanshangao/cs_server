<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Order as OrderModel;
use app\admin\model\OrderServices;
use app\common\model\AssetsType;

class Order extends Base
{
    protected $noAuthArr = ['getStatus','getPayType','getCustomerExpectStatus','getServicesStatus'];
    //接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];    //权限白名单

    /**
     * @name: Order列表
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function list()
    {
        
        $data = $this->request->get();
        $OrderModel = new OrderModel();
        $this->getPageAndSize($data);
        $this->getSort($data);
        $condition = $this->filterParam(['orderSn','payType','payTime','orderStatus'], $data);
        $total = $OrderModel->getCount($condition);
        $list = $OrderModel->getList($condition, $this->from, $this->size, true, 'createTime desc', ['orderGoods','goodsNumSum']);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: Order列表
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function servicelist()
    {
        
        $data = $this->request->get();
        $OrderServicesModel = new OrderServices();
        $this->getPageAndSize($data);
        $condition = $this->filterParam(['servicesSn','afsNum','orderSn','servicesStatus'], $data);
        $total = $OrderServicesModel->getCount($condition);
        $list = $OrderServicesModel->getList($condition, $this->from, $this->size, true, 'createTime desc');
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: Order获取订单状态及数量
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getStatus()
    {
        
        $data = config('adminSetting.order_status');
        $OrderModel = new OrderModel();
        //统计各订单数量
        foreach ($data as &$value) {
            $value['count']  = $OrderModel->getOrderCountByCondition(['orderStatus' => $value['value']]);
        }

        return show(1, $data);
    }

    /**
     * @name: 获取售后单状态及数量
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getServicesStatus()
    {
        
        $data = config('adminSetting.services_order_status');
        $OrderServicesModel = new OrderServices();
        //统计各订单数量
        foreach ($data as &$value) {
            $value['count']  = $OrderServicesModel->getOrderServicesCountByCondition(['servicesStatus' => $value['value']]);
        }

        return show(1, $data);
    }
    /**
     * @name: 获取售后服务类型
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getCustomerExpectStatus()
    {
        
        $data = config('adminSetting.services_customerExpect');
        return show(1, $data);
    }

    /**
     * @name: Order获取支付方式及数量
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getPayType()
    {
        
    
        $data = AssetsType::all(['canPayOrder' => 1]);
        $OrderModel = new OrderModel();
        foreach ($data as $key => $value) {
            $returnData[] = [
                    'value' => $value['assetsType'],
                    'name' => $value['assetsName'],
                    'count' => $OrderModel->getOrderCountByCondition(['payType' => $value['assetsType']])
                ];
        }
      
        return show(1, $returnData);
    }
}
