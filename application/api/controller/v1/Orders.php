<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\AssetsType;
use app\common\model\Order as OrderModel;
use app\common\model\Assets;
use app\common\model\OrderGoods;
use app\common\model\OrderServices;

//a
class Orders extends Base
{
    protected $noAuthArr = ['change'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单


    /**
     * @name:        订单列表
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function listOrder()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'listOrder');

       
        switch (true) {
            case !isset($dataArr['type']):
                // 全部订单
                $condition = [
                'del' => 1
                ];
                break;

            case $dataArr['type'] == 1:
                // 待付款
                $condition = [
                'orderStatus' => -2,
                'isPay' => 0,
                'del' => 1
                ];
                break;

            case $dataArr['type'] == 2:
                // 待发货
                $condition = [
                'orderStatus' => 0,
                'isPay' => 1,
                'del' => 1
                ];
                break;

            case $dataArr['type'] == 3:
                // 待收货
                $condition = [
                'orderStatus' => 1,
                'isPay' => 1,
                'del' => 1
                ];
                break;

            case $dataArr['type'] == 4:
                // 已完成
                $condition = [
                'orderStatus' => 2,
                'isPay' => 1,
                'del' => 1
                ];
                break;

            case $dataArr['type'] == 5:
                // 已取消
                $condition = [
                'orderStatus' => -1,
                'del' => 1
                ];
                break;
            
            default:
                // 全部订单
                $condition = [
                'del' => 1
                ];
                break;
        }
        $condition['userId'] = $this->userId;

        $OrderModel  = new OrderModel();
        $fieldsArr = [
            'orderId',
            'orderSn',
            'orderStatus',
            'totalMoney',
            'payType',
            'payTypeName',
        ];
        $getArr = [
            'orderGoods'
        ];
        
        $this->getPageAndSize($dataArr);
        $total = $OrderModel->getCount($condition);
      
        $list = $OrderModel->getList($condition, $this->from, $this->size, $fieldsArr, $this->sort, $getArr);
        $returnArr = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnArr);
    }

    /**
     * @name:        订单详情
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function detailOrder()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'detailOrder');
        $condition = [
            'orderSn' => $dataArr['orderSn'],
            //'userId' => $this->userId,
            'del' => 1
        ];

        $fieldsArr = [
            'orderId',
            'orderSn',
            'orderStatus',
            'deliverMoney',
            'totalMoney',
            'totalMoney',
            'payType',
            'payTypeName',
            'payTime',
            'userName',
            'userPhone',
            'areaIdPath',
            'areaName',
            'addressDetails',
            'orderRemarks',
            'isPay',
            'createTime',
        ];
        $getArr = ['orderGoods'];
        $OrderModel  = new OrderModel();
        $data = $OrderModel->getInfoByMap($condition, $fieldsArr, $getArr);
        if ($data) {
            $data['deliverMoney'] ? '' : $data['deliverMoney'] = '包邮';
        }
        return show(1, $data);
    }

    /**
     * @name:        取消订单
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function cacolOrder()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'cacolOrder');
        $OrderModel  = new OrderModel();
        $OrderModel->cancelOrder($this->userId, $dataArr['orderSn']);
        return show(1);
    }



    /**
     * @name:        确认订单
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function confirmOrder()
    {

        $dataArr  = $this->checkdate('Order', 'post', 'confirmOrder');
        $OrderModel  = new OrderModel();
    

        //创建本地订单
        $result = $OrderModel->addOrder($this->userId, $dataArr);
        
        //本地订单创建完毕

        if (isset($result['code'])) {
            return show($result['code'], [], $result['message'] ? $result['message'] : '');
        }
        $returnArr = [
            'orderId' => $result->orderId,
            'orderSn' => $result->orderSn,
            'payType' => $result->payType,
            'totalMoney' => $result->totalMoney,
        ];
        return show(1, $returnArr);
    }
    
    /**
     * @name:        支付订单
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function payOrder()
    {
        $dataArr  = $this->checkdate('Order', 'post', 'payOrder');

        //支付密码验证
        if (!$this->clientInfo->payPassWord) {
            return show(1019);
        }
        
        if ($this->clientInfo->payPassWord !== setPassword($dataArr['payPassWord'])) {
            return show(1020);
        }
        
        //订单信息
        $OrderModel  = new OrderModel();
        $orderConfition = [
            'orderSn' => $dataArr['orderSn'],
            'userId' => $this->userId
        ];
       
        $orderInfo = $OrderModel::get($orderConfition);

        if (!$orderInfo) {
            return show(1021);
        }
        //账户余额
        $assetsCondition = [
            'assetsType' => $orderInfo->payType,
            'userId' => $this->userId
        ];
        $userAssets = Assets::get($assetsCondition);

        if (bccomp($userAssets['amount'], $orderInfo->totalMoneyWithPayType, 10) !== 1) {
            return show(1027);
        }
        
        //支付订单
        $result = $OrderModel->payOrder($this->userId, $dataArr['orderSn']);


        if (!$result) {
            return show(1018);
        }
        return show(1);
    }
    

    /**
     * @name:        支付方式
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function getPayType()
    {

        $payTypeObj = AssetsType::all(['canPayOrder' => 1]);
        $defaultAssetsType = $this->clientInfo->defaultAssetsType;
       
        foreach ($payTypeObj as &$value) {
            if ($value['assetsType'] == $defaultAssetsType) {
                $value['default'] = 1;
            } else {
                $value['default'] = 0;
            }
        }
        return show(1, $payTypeObj);
    }

    /**
     * @name:        查看物流
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function logisticsInfo()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'payOrder');
        $result = OrderModel::logisticsInfo($this->userId, $dataArr['orderSn']);
      
        if (!$result) {
            return show(1, []);
        }
        return show(1, $result);
    }

    /**
     * @name:        删除订单
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function delOrder()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'delOrder');
        OrderModel::delOrder($this->userId, $dataArr['orderSn']);
        return show(1);
    }

    /**
     * @name:        确认收货
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function confirmReceive()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'confirmReceive');
        OrderModel::confirmReceive($this->userId, $dataArr['orderSn']);
        return show(1);
    }

    /**
     * @name:        评价列表
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function appraisesList()
    {

        $dataArr  = $this->checkdate('Order', 'get', 'appraisesList');
        
        //已完成订单
        $conditionOrder = [
            'userId' => $this->userId,
            'orderStatus' => 2,
            'isPay' => 1,
            'del' => 1
        ];
        $orderIdArr = OrderModel::where($conditionOrder)->column('orderId');
        if (!$orderIdArr) {
            $returnArr = [
                'total' => 0,
                'page_num' => 0,
                'list' => [],
            ];
            return show(1, $returnArr);
        }
        switch (true) {
            case !isset($dataArr['type']):
                // 全部
                $condition = [
                'orderId' => ['in',$orderIdArr],
                ];
                break;
            case $dataArr['type'] == 1:
                // 待评价
                $condition = [
                'orderId' => ['in',$orderIdArr],
                'isAppraise' => 0
                ];
                break;
            case $dataArr['type'] == 2:
                // 已评价
                $condition = [
                'orderId' => ['in',$orderIdArr],
                'isAppraise' => 1
                ];
                break;
            
            default:
                // 全部
                $condition = [
                'orderId' => ['in',$orderIdArr],
                ];
                break;
        }

        $OrderGoodsModel = new OrderGoods();
        $this->getPageAndSize($dataArr);
        $returnArr['total'] = $OrderGoodsModel->getCount($condition);
        $returnArr['page_num'] = ceil($returnArr['total'] / $this->size);
        $returnArr['list'] = $OrderGoodsModel->getList($condition, $this->from, $this->size, true, $this->sort, ['goodsAppraises']);
        return show(1, $returnArr);
    }

    /**
     * @name:        评价商品
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function appraises()
    {
        $dataArr  = $this->checkdate('Order', 'post', 'appraises');
        
        $OrderModel = new OrderModel();
        $result = $OrderModel->goodsAppraises($this->userId, $dataArr);
        if (!$result) {
            return show(0);
        }
        return show(1);
    }

    /**
     * @name:        创建售后
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function services()
    {
        $dataArr  = $this->checkdate('Order', 'post', 'services');
 
        $OrderModel = new OrderModel();
        $result = $OrderModel->goodsServices($this->userId, $dataArr);
        if (!$result) {
            return show(1035);
        }
        return show(1);
    }
    /**
     * @name:        取消售后
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function cancelServices()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'cancelServices');
        
        $OrderModel = new OrderModel();
        $result = $OrderModel->cancelServicesOrder($this->userId, $dataArr);
        if (!$result) {
            return show(0);
        }
        return show(1);
    }

    /**
     * @name:        退货/售后列表
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function listServices()
    {

        $dataArr  = $this->checkdate('Order', 'get', 'listServices');
        
        //已完成订单
        $OrderModel = new OrderModel();
        $conditionOrder = [
            'userId' => $this->userId,
            'orderStatus' => 2,
            'isPay' => 1,
            'del' => 1
        ];
        $orderIds = $OrderModel->where($conditionOrder)->column('orderId');
      
        if (!$orderIds) {
            $returnArr = [
                'total' => 0,
                'page_num' => 0,
                'list' => [],
            ];
            return show(1, $returnArr);
        }

        //订单商品
        $OrderGoodsModel = new OrderGoods();
        $conditionOrderGoods = [
            'orderId' => ['in',$orderIds]
        ];
        $getArr = [
            'orderServices',
            'ylOrderId',
        ];
        $this->getPageAndSize($dataArr);
        $total = $OrderGoodsModel->getCount($conditionOrderGoods);
        $list = $OrderGoodsModel->getList($conditionOrderGoods, $this->from, $this->size, true, $this->sort, $getArr);
       
        
        foreach ($list as $key => &$value) {
            //是否支持售后
            $value['availableNumberComp'] = 0;
            //商品是否可以提交售后服务
            $resultAvailableNumberCompQuery = Vop::availableNumberCompQuery($value['ylOrderId'], $value['skuNum']);
          
            $outTime = $value['createTime'] + 172800;
            if ($resultAvailableNumberCompQuery && $outTime > time()) {
                //支持售后
                $value['availableNumberComp'] = 1;
            }
          
            
            //查询售后类型
            //支持的售后类型数组
            //$value['customerExpectComp'] = [];
            // $resultCustomerExpectCompQuery = Vop::customerExpectCompQuery($value['ylOrderId'], $value['skuNum']);
            // if ($resultCustomerExpectCompQuery) {
            //     //有返回
            //     $value['customerExpectComp'] = $resultCustomerExpectCompQuery;
            // }

            //是否可以取消申请
            $value['cancel'] = 0;
            //申请的售后单概要信息
            $value['serviceInfoList'] = [];
            //是否已经创建售后单
            if ($value['isServices']) {
                $value['orderServices']['sendSkuUpdate'] = json_decode($value['orderServices']['sendSkuUpdate'], true);
                $value['orderServices']['serviceAftersalesAddressInfoDTO'] = json_decode($value['orderServices']['serviceAftersalesAddressInfoDTO'], true);
      
                //查询售后单概要信息
                $resultServiceListPage = Vop::serviceListPageQuery($value['ylOrderId']);
               
                if ($resultServiceListPage) {
                        //取最新的
                        
                    $value['serviceInfoList'] = $resultServiceListPage['serviceInfoList'];
                  
                    foreach ($resultServiceListPage['serviceInfoList'] as $key => $serviceInfo) {
                        if ($key == 0) {
                            //是否可取消。0代表否，1代表是
                            $value['cancel'] = $serviceInfo['cancel'];
                        }
                    }
                }
            }
        }
        $returnArr = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnArr);
    }
    
    /**
     * @name: 售后明细
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function detailServices()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'detailServices');
        $result = Vop::serviceDetailInfo($dataArr['afsNum']);
        return  show(1, $result);
    }

    /**
     * @name:  填写客户发运信息
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function sendSkuUpdate()
    {
        $dataArr  = $this->checkdate('Order', 'get', 'sendSkuUpdate');
        $OrderServices = new OrderServices();
        $result = $OrderServices->setSendSkuUpdate($this->userId, $dataArr);
        if (!$result) {
            return show(0);
        }
        return  show(1);
    }
    public function change()
    {
        $data = $this->request->get();
     
        $re = RMB2USD($data['rmb']);
        return show(1, $re);
    }
}
