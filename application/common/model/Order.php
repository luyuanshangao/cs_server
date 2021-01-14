<?php

namespace app\common\model;

use app\api\controller\v1\Vop;
use app\common\library\redis\OrderDelayed;
use app\common\library\redis\GoodsInfo as GoodsInfoRedisModel;
use app\common\library\redis\GoodsInfo;
use app\common\library\redis\CacheKeyMap;

class Order extends BaseModel
{
    public $rate = '6.7';
    public $goodsArr = [];
    public $goodsJson = [];
    public $addressObj = '';
    public $_payTypeName = '';
    public $goodsMoneyRmb = '';
    public $areaLimitGoods = []; //限制购买商品编号
    public $stockLimit = []; //没有库存的商品编号
    public $goodsArrSumPrice = '';
    
    protected function initialize()
    {
        parent::initialize();
        $rate = new Rate();
        $this->rate = $rate->getRate();
    }

    /**
     * @name:        根据订单号改变订单状态
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function changeOrderStatusByOrderSn($orderSn, $orderStatus)
    {
        $orderObj = $this::get(['orderSn' => $orderSn]);
        $orderObj->orderStatus  = $orderStatus;
        $orderObj->save();
        return true;
    }

    /**
     * @name:        根据三方订单号改变订单状态
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function changeOrderStatusByYlOrderId($ylOrderId, $orderStatus)
    {
        $orderObj = self::get(['ylOrderId' => $ylOrderId]);
        if ($orderObj->orderStatus == $orderStatus) {
            return;
        }
        $orderObj->orderStatus  = $orderStatus;
        $orderObj->save();
        switch ($orderStatus) {
            case 1:
                    //发货通知
                    Message::add($orderObj->userId, '物流通知', '订单已发货', $orderObj->orderSn . '订单，商品已经发货请您及时查看物流状态！');
                break;
            case 2:
                    //完成通知
                    Message::add($orderObj->userId, '物流通知', '订单已完成', $orderObj->orderSn . '订单，商品已配送完成，请您对商品进行评价，分享您的宝贵意见！');
                break;

            default:
                # code...
                break;
        }
        return true;
    }

    /**
     * @name:        根据三方订单号完成订单
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function closedOrderByYlOrderId($ylOrderId)
    {
        $orderObj = self::get(['ylOrderId' => $ylOrderId]);
        $orderObj->orderStatus  = 2;
        $orderObj->save();
        return true;
    }

    /**
     * 获取商品信息
     */
    public function orderGoods()
    {

        return $this->hasMany('order_goods', 'orderId', 'orderId')->field('goodsUsdtPriceInc as goodsPrice,skuNum,spuName,pic,goodsNum,isServices');
    }

    /**
     * @name:        创建订单
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function addOrder($userId, $dataArr)
    {
        $this->rate =  Rate::where(1)->value('USDRate');
        $dataArr['skuNumArr'] = json_decode($dataArr['skuNumArr'], true);
        
        $this->startTrans();
       
        try {
            //商品数量和
            $goodsNumSum = array_sum(array_column($dataArr['skuNumArr'], 'num'));
            //商品价格(USDT)
            $goodsMoney = $this->computePrice($dataArr['skuNumArr']);
            //$mopResult = bccomp('99', $this->goodsArrSumPrice, 5);
          
            //小于99usdt
            if ($goodsMoney < 15) {
                throw new \Exception('', 1037);
            }
            
            //收货地址信息
            $addressObj = UserAddress::get(['addressId' => $dataArr['addressId'],'userId' => $userId]);
            if (!$addressObj) {
                throw new \Exception("", 1040);
            }
            $this->addressObj = $addressObj;

            //地区购买限制
            //$areaLimitBool = $this->computeAreaLimit($dataArr['skuNumArr'], $addressObj);

            //库存
            //$statr = microtime(true);
            $stockBool = $this->computeStock($dataArr['skuNumArr'], $addressObj);

            //商品运费(USDT)
            $deliverMoney = $this->computeDeliver($dataArr['skuNumArr'], $addressObj);

            ################################
            //$deliverMoney = 0;
            ################################
            //支付方式名
            $payTypeName = $this->computePayType($dataArr['payType']);
            //订单总价(USDT)
            $totalMoneyUsdt = bcadd($goodsMoney, $deliverMoney, config('app.float_num'));
            
            //转为对应payType 价格
            ################################
            //$totalMoneyWithPayType = $totalMoneyUsdt;
            ################################
            
            $totalMoneyWithPayType = $this->computeMoneyTo($totalMoneyUsdt);
            foreach ($dataArr['skuNumArr'] as $value) {
                $spuName = GoodsInfoRedisModel::getGoodsInfoBySkuNum($value['skuNum'], ['spu_name']);
                if (!$spuName['spu_name']) {
                    $spu_name = Vop::getGoodsInfo($value['skuNum'], ['spu_name']);
                }
                $goodsInfo[$value['skuNum']] = $spuName;
            }
    
            $message = '';
            ################################
            // if ($areaLimitBool === false) {
            //     throw new \Exception("", 1031);
            // }
            if ($stockBool === false) {
                foreach ($this->stockLimit as $key => $value) {
                    $message .= $goodsInfo[$value]['spu_name'];
                }
                $message .= ' 库存不足';
                throw new \Exception($message, 1032);
            }
            ################################
            
            if ($payTypeName === false) {
                throw new \Exception("", 1033);
            }
            
            if ($goodsMoney === false || $deliverMoney === false) {
                throw new \Exception("", 1029);
            }
            
            //订单号
            do {
                $orderSn = date('Ymd') . builderRand();
            } while ($this->where('orderSn', $orderSn)->count() == 1);
            $createTime = time();
            $dataSaveArr = [
               'userId' => $userId,
               'orderSn' => $orderSn,
               'orderStatus' => '-2',
               'goodsNumSum' => $goodsNumSum,
               'goodsMoney' => $goodsMoney,
               'deliverMoney' => $deliverMoney,
               'totalMoneyWithPayType' => $totalMoneyWithPayType,    //realTotalMoney 根据payType转换的
               'totalMoney' => $totalMoneyUsdt, //usdt
               'payType' => $dataArr['payType'],
               'payTypeName' => $payTypeName,
               'userName' => $addressObj->userName,
               'userPhone' => $addressObj->userPhone,
               'areaIdPath' => $addressObj->areaIdPath,
               'areaName' => $addressObj->areaName,
               'addressDetails' => $addressObj->addressDetails,
               'orderRemarks' => isset($dataArr['orderRemarks']) ? $dataArr['orderRemarks'] : '',
               'createTime' => $createTime,
               'reservingDate' => isset($dataArr['reservingDate']) ? $dataArr['reservingDate'] : '-1',
               'reservedDateStr' => isset($dataArr['reservedDateStr']) ? $dataArr['reservedDateStr'] : '',
               'reservedTimeRange' => isset($dataArr['reservedTimeRange']) ? $dataArr['reservedTimeRange'] : '',
               'promiseDate' => isset($dataArr['promiseDate']) ?  $dataArr['promiseDate'] : '',
               'promiseTimeRange' => isset($dataArr['promiseTimeRange']) ?  $dataArr['promiseTimeRange'] : '',
               'timeRangeCode' => isset($dataArr['timeRangeCode']) ?  $dataArr['timeRangeCode'] : '',
            ];
           
            //创建本地订单
            $orderOjb = $this::create($dataSaveArr);
          
            //创建本地订单商品
            foreach ($this->goodsArr as &$value) {
                $value['orderId'] = $orderOjb->orderId;
                $value['orderSn'] = $orderOjb->orderSn;
                $value['userId'] = $userId;
                //$value['goodsPrice'] = round($value['goodsPrice'] / $this->rate, config('app.float_num'));
                $value['spuName'] = $goodsInfo[$value['skuNum']]['spu_name'];

                $cacheData =  GoodsInfoRedisModel::getGoodsInfoBySkuNum($value['skuNum'], ['pics']);
                if ($cacheData['pics']) {
                    $picsArr = json_decode($cacheData['pics'], true);
                    $pic = $picsArr[0];
                } else {
                    $pic = Vop::getGoodsPic($value['skuNum'])[$value['skuNum']][0]['pic_path'];
                }
                $value['pic'] = $pic;
                $value['createTime'] = time();
            }
            $OrderGoodsModel = new OrderGoods();
            $OrderGoodsModel->insertAll($this->goodsArr);

            //
            //第三方统一下单
            $createOrderArr = [
               $orderOjb,
               $this->addressObj,
               $this->goodsJson
            ];
           
            $resultVopOrderInfo = Vop::createOrder($createOrderArr);
            
            if (!$resultVopOrderInfo) {
                throw new \Exception("", 1017);
            }
            
            $resultVopOrderInfo['sku'] = json_encode($resultVopOrderInfo['sku']);
            //存入返回信息
            OrderYl::create($resultVopOrderInfo);
         
            //三方源链订单号保存
            $orderOjb->ylOrderId = $resultVopOrderInfo['ylOrderId'];
            //$orderOjb->ylOrderId =  date('YmdHis') . builderRand(5);
            $orderOjb->save();
           
            $this->commit();
            //取消订单加入redis延时队列
            OrderDelayed::addOrderDelayedTask(['userId' => $orderOjb['userId'],'orderSn' => $orderOjb['orderSn']], ($createTime + 1800), 'del');
            OrderDelayed::addOrderDelayedTask(['userId' => $orderOjb['userId'],'orderSn' => $orderOjb['orderSn']], ($createTime + 1500), 'send');
           
            return $orderOjb;
        } catch (\Exception $e) {
            $this->rollback();
            return ['code' => $e->getCode(),'message' => $e->getMessage()];
        }
    }

    /**
     * @name:        取消订单
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function cancelOrder($userId, $orderSn)
    {

        $confition = [
            'orderSn' => $orderSn,
            'userId' => $userId,
            'orderStatus' => '-2',
        ];
        $orderInfo = $this::get($confition);
        if (!$orderInfo) {
            return false;
        }
        $orderInfo->orderStatus = '-1';
        $orderInfo->save();
        # 清除redis队列中提示

        OrderDelayed::delOrderDelayedTask(
            json_encode([
                'action' => 'del',
                'time' => ($orderInfo['createTime'] + 1800),
                'data' => ['userId' => $orderInfo['userId'],'orderSn' => $orderInfo['orderSn']]
            ])
        );
        
        OrderDelayed::delOrderDelayedTask(
            json_encode([
                'action' => 'send',
                'time' => ($orderInfo['createTime'] + 1500),
                'data' => ['userId' => $orderInfo['userId'],'orderSn' => $orderInfo['orderSn']]
              ])
        );
         
        Vop::cancelOrder($orderInfo->ylOrderId);
        return true;
    }

    /**
     * @name:        支付订单
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function payOrder($userId, $orderSn)
    {
        
        $confition = [
            'orderSn' => $orderSn,
            'userId' => $userId,
            'orderStatus' => '-2',
        ];
        
        $this->startTrans();
        try {
            $orderInfo = self::where($confition)->find();
           
            if (!$orderInfo) {
                throw new \Exception("Error");
            }
            //第三方确认预占库存 实际是去支付订单

            $result =  Vop::occupyStockConfirm($orderInfo->ylOrderId);
            if (!$result) {
                throw new \Exception("Error");
            }

            //用户扣款
            $AssetsModel = new Assets();
            $actionName = 'cost' . $orderInfo->getData('payTypeName');
            $AssetsModel->{$actionName}($userId, $orderInfo->totalMoneyWithPayType, '支付订单');
            //修改订单状态
            $orderInfo->orderStatus = 0;
            $orderInfo->isPay = 1;
            $orderInfo->payTime = time();
            $orderInfo->save();

            //提示消息
            Message::add($userId, '资产变动通知', '消费支出', '您消费支出' . $orderInfo->totalMoneyWithPayType . $orderInfo->getData('payTypeName') . ',请查看！');
            
            $this->commit();
            //取消订单redis延时队列
            //['action'=>$action,'time'=>$time,'data' => $data,]
            
            OrderDelayed::delOrderDelayedTask(
                json_encode([
                    'action' => 'del',
                    'time' => ($orderInfo->getData('createTime') + 1800),
                    'data' => ['userId' => $orderInfo['userId'],'orderSn' => $orderInfo['orderSn']]
                ])
            );
            
              OrderDelayed::delOrderDelayedTask(
                  json_encode([
                    'action' => 'send',
                    'time' => ($orderInfo->getData('createTime') + 1500),
                    'data' => ['userId' => $orderInfo['userId'],'orderSn' => $orderInfo['orderSn']]
                  ])
              );

              //付款订单 推广信息
            $inviData = ExtensionInvitation::get(['userId' => $userId]);
            
            if ($inviData) {
                try {
                   //订单记录 等级变化
                    $incPrice = OrderGoods::computeIncPrice($orderInfo->orderId);
                    ExtensionDeal::createData($userId, $inviData['superiorId'], $orderInfo->orderId, $incPrice);
                    (new ExtensionUser())->changeGrade($inviData['superiorId']);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
              
             return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }

    /**
     * @name:        订单物流信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function logisticsInfo($userId, $orderSn)
    {
       
        $condition  = [
            'userId' => $userId,
            'orderSn' => $orderSn,
        ];
        $orderObj = self::get($condition);

        $result = Vop::orderTrackQuery($orderObj->ylOrderId);
       
        return $result;
    }

    /**
     * @name:        删除订单
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function delOrder($userId, $orderSn)
    {
        $condition  = [
            'userId' => $userId,
            'orderSn' => $orderSn,
        ];
        $orderObj = self::get($condition);
        $orderObj->del = 0;
        $orderObj->save();
        return true;
    }

    /**
     * @name:        确定收货
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function confirmReceive($userId, $orderSn)
    {
        $condition  = [
            'userId' => $userId,
            'orderSn' => $orderSn,
        ];
        try {
            $orderObj = self::get($condition);
            //查询三方订单信息
            $resultOrderQuery = Vop::selectylOrderQuery($orderObj->ylOrderId);
            if ($resultOrderQuery) {
                //厂家直送订单
                if ($resultOrderQuery['orderType'] == 5) {
                    //妥投
                    Vop::openConfirmreceivedQuery($orderObj->ylOrderId);
                }
            }
        } catch (\Exception $th) {
            //throw $th;
        }
        $orderObj->orderStatus = 2;
        $orderObj->save();
        //完成通知
        Message::add($orderObj->userId, '物流通知', '订单已完成', $orderObj->orderSn . '订单，商品已配送完成，请您对商品进行评价，分享您的宝贵意见！');
        
        return true;
    }

    /**
     * @name:        用户评价商品
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function goodsAppraises($userId, $dataArr)
    {
        
        //是否可评价
        $conditionOrder = [
            'userId' => $userId,
            'orderSn' => $dataArr['orderSn'],
            'orderStatus' => 2
        ];
       
        $orderObj = $this::get($conditionOrder);
       
        if (!$orderObj) {
            return false;
        }
       
        $condition  = [
            'userId' => $userId,
            'orderSn' => $dataArr['orderSn'],
            'skuNum' => $dataArr['skuNum'],
            'isAppraise' => 0,
        ];
       
        $orderGoodsObj  = OrderGoods::get($condition);
      
        if (!$orderGoodsObj) {
            return false;
        }
        
        $this->startTrans();
        try {
            $dataArr['orderGoodsId'] = $orderGoodsObj->orderGoodsId;
            
            $result = GoodsAppraises::addAppraises($userId, $dataArr);
           
            if (!$result) {
                throw new \Exception("Error");
            }
            $orderGoodsObj->isAppraise = 1;
            $orderGoodsObj->save();
            $this->commit();
            return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }

    /**
     * @name:        用户创建服务单
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function goodsServices($userId, $dataArr)
    {

        $this->startTrans();
        try {
            //收货地址信息
            $addressObj = UserAddress::get(['userId' => $userId,'areaIdPath' => $dataArr['addressId']]);
          
            $this->addressObj = $addressObj;
            list($procince,$city,$county,$towwn) = explode("_", $addressObj->areaIdPath);
            //订单商品查询
            $orderGoodsObj = OrderGoods::get(['userId' => $userId,'orderSn' => $dataArr['orderSn'],'skuNum' => $dataArr['skuNum']]);

            if (!$orderGoodsObj) {
                throw new \Exception("Error");
            }
            //三方订单号
            $ylOrderId = $this->where(['orderId' => $orderGoodsObj->orderId])->value('ylOrderId');
            //订单号
            do {
                $servicesSn = builderRand(12);
            } while (OrderServices::where('servicesSn', $servicesSn)->count() == 1);
            //图片
            if (isset($dataArr['questionPic'])) {
                $picArr = json_decode($dataArr['questionPic'], true);
                $questionPic = implode(',', $picArr);
            } else {
                $questionPic = '';
            }
            $dataSaveArr = [
                'userId' => $userId,
                'servicesSn' => $servicesSn,
                'servicesStatus' => '0',
                'orderSn' => $orderGoodsObj->orderSn,
                'orderGoodsId' => $orderGoodsObj->orderGoodsId,
                'ylOrderId' => $ylOrderId,
                'customerExpect' => 10,
                'questionDesc' => $dataArr['questionDesc'],
                'questionPic' => $questionPic,
                'customerContactName' => $addressObj->userName,
                'customerTel' => $addressObj->userPhone,
                'customerMobilePhone' => $addressObj->userPhone,
                'customerEmail' => config('app.email'),
                'customerPostcode' => '110000',
                'pickwareType' => isset($dataArr['pickwareType']) ? $dataArr['pickwareType'] : 4,//上门取件
                'pickwareAreaName' => $addressObj->areaName,
                'pickwareProvince' => $procince,
                'pickwareCity' => $city,
                'pickwareCounty' => $county,
                'pickwareVillage' => $towwn,
                'pickwareAddress' => $addressObj->addressDetails,
                'returnwareType' => 10,
                'returnwareAreaName' => $addressObj->areaName,
                'returnwareProvince' => $procince,
                'returnwareCity' => $city,
                'returnwareCounty' => $county,
                'returnwareVillage' => $towwn,
                'returnwareAddress' => $addressObj->addressDetails,
                'skuNum' => $dataArr['skuNum'],
                'spuName' => $orderGoodsObj->spuName,
                'skuNumber' => $dataArr['skuNumber'],
                'isRefund' => 0,
                'createTime' => time(),
            ];
           
            //创建本服务网订单
            $orderServicesOjb = OrderServices::create($dataSaveArr);
           
            //第三方服务单
            //参数创建

            $resultAfsApplyInfo = Vop::afsApplyCreate($orderServicesOjb);

            if (!$resultAfsApplyInfo) {
                throw new \Exception("Error");
            }
           
            //三方服务单号保存
            $orderServicesOjb->afsNum = 'SPS122345681419';
            $orderServicesOjb->save();

            //订单商品改为售后
            $orderGoodsObj->isServices = 1;
            $orderGoodsObj->save();
         
            ExtensionDeal::upLockRefund($orderGoodsObj->orderId);
           
                 $this->commit();
            return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }
    /**
     * @name: 取消售后
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function cancelServicesOrder($userId, $dataArr)
    {

        $this->startTrans();
        try {
            $orderGoodsObj = OrderGoods::get(['orderGoodsId' => $dataArr['orderGoodsId'],'userId' => $userId]);
            $orderGoodsObj->isServices = 0;
            $orderGoodsObj->save();
            $orderServicesObj = OrderServices::get(['afsNum' => $dataArr['afsNum'],'orderGoodsId' => $orderGoodsObj->orderGoodsId]);
            $orderServicesObj->servicesStatus = -2;
            $orderServicesObj->save();
            //三方取消
            $result =  Vop::auditCancelQuery(json_encode([$dataArr['afsNum']]));
            if ($result) {
                throw new \Exception("Error");
            }
            $this->commit();
            return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }

     /**
      * @name:        计算商品总价格
      * @author:      gz
      * @description:
      * @param        {type}
      * @return:
      */
    public function computePrice($skuNumArr)
    {
        
        $sumPrice = 0;
        $sumUsdtPrice = 0;
        foreach ($skuNumArr as $key => $value) {
            ###################################
            $redisKeyName = CacheKeyMap::goodsInfoHash($value['skuNum']);
            $goodsInfo = GoodsInfo::getAllGoodsInfoByName($redisKeyName);
            $price = Vop::getPrice($value['skuNum']);
            $goodsInfo['price'] = $price;
            //查看缓存价格 没有的走三方
            // if ((isset($goodsInfo['price']) && $goodsInfo['price'] == '') || !isset($goodsInfo['price'])) {
            //     $price = Vop::getPrice($value['skuNum']);
            //     $goodsInfo['price'] = $price;
            // }
            ###################################


            //原价RMB
            //$goodsInfo = Vop::getGoodsInfo($value['skuNum'], ['price']);
            $price = $goodsInfo['price'];
            if (!$price) {
                return false;
            }
           
            $designData = PriceRule::getIncPrice($price, $this->rate);
            //转为原价美元
            $oldUsdtPrice =  $designData['oldUsdtPrice'];
            //商品增加百分比
            $percent = $designData['percent'];
            //商品增加收益后的价格(USDT)售价
            $incPrice =  $designData['incPrice'];
            $usdtPricePercent = $designData['usdtPricePercent'];
            //收益(RMB)
            $syRmb = $price * ( $percent / 100);
            //收益(USDT)
            $syUsdt = $usdtPricePercent - $oldUsdtPrice;

            // //rmb总价
            $sumPrice +=  $incPrice * $value['num'];
            // //usdt总价
            $sumUsdtPrice +=  $usdtPricePercent * $value['num'];

            //订单商品数据
            $this->goodsArr[$key]['skuNum'] = $value['skuNum'];
            $this->goodsArr[$key]['goodsNum'] = $value['num'];
            $this->goodsArr[$key]['goodsPrice'] = $price;
            $this->goodsArr[$key]['goodsUsdtPrice'] = $oldUsdtPrice;
            $this->goodsArr[$key]['goodsUsdtPriceInc'] = $usdtPricePercent;
            $this->goodsArr[$key]['goodsPriceInc'] = $incPrice;
            $this->goodsArr[$key]['incRate'] = $percent;
            $this->goodsArr[$key]['usdtRate'] = $this->rate;
            $this->goodsArr[$key]['incPrice'] =  $syUsdt;

            
            
            //三方订单商品数据
            $goods[$key]['skuNum'] = $value['skuNum'];
            $goods[$key]['price'] = $price;//floatval($price);
            $goods[$key]['num'] = $value['num'];
            $goods[$key]['bNeedAnnex'] = true;
            $goods[$key]['bNeedGift'] = true;
        }

        //三方数据拼装
        $this->goodsJson = json_encode($goods);
        $this->goodsArrSumPrice =  $sumPrice;
       
        //返回usdt价格
        return $sumUsdtPrice;
    }
    /**
     * @name:        计算运费
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function computeDeliver($skuNumArr, $addressObj)
    {
        
        list($procince,$city,$county,$towwn) = explode("_", $addressObj->areaIdPath);
        $this->addressObj['procince']  = $procince;
        $this->addressObj['city']  = $city;
        $this->addressObj['county']  = $county;
        $this->addressObj['towwn']  = $towwn;
        $result  = Vop::getGoodsFreight(json_encode($skuNumArr), $procince, $city, $county, $towwn);
        return $result;
    }
    /**
     * @name:        计算库存
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function computeStock($skuNumArr, $addressObj)
    {
        $result  = Vop::getStock(json_encode($skuNumArr), $addressObj->areaIdPath);
        if (!$result) {
            return false;
        }
        foreach ($result as $value) {
            if ($value['stockStateId'] !== 33) {
                $this->stockLimit[] = $value['skuNum'];
            }
        }
        if ($this->stockLimit) {
            return false;
        }
        return true;
    }
    /**
     * @name:        计算购买限制
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function computeAreaLimit($skuNumArr, $addressObj)
    {
        $skuNumStr = implode(',', array_column($skuNumArr, 'skuNum'));
        list($procince,$city,$county,$towwn) = explode("_", $addressObj->areaIdPath);
        $result  = Vop::getGoodsAreaLimit($skuNumStr, $procince, $city, $county, $towwn);
        if (!$result) {
            return false;
        }
        foreach ($result as $value) {
            if ($value['isAreaRestrict']) {
                $this->areaLimitGoods[] = $value['sku_num'];
            }
        }
        if ($this->areaLimitGoods) {
            return false;
        }
        
        return true;
    }

    /**
     * @name:        计算支付方式
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function computePayType($payTypeId)
    {

        $result = \app\common\model\AssetsType::where(['assetsType' => $payTypeId,'canPayOrder' => 1])->value('assetsName');
        if (!$result) {
            return false;
        }
        $this->_payTypeName = $result;
        return $result;
    }

    /**
     * @name:       根据币安接口 usdt价钱转换
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function computeMoneyTo($usdtPrice)
    {
        //初始化币安接口
        vendor("Binance");
        $binance = new \Binance();
        $methodStr =  'USDT2' . $this->_payTypeName;
        $price = $binance->{$methodStr}($usdtPrice);
        return $price;
    }
}
