<?php

namespace app\queue\controller;

use think\Controller;
use app\common\model\Order;
use app\api\controller\v1\Vop;
use app\common\model\OrderServices;
use app\common\model\OrderGoods;
use app\common\model\User;
use app\common\model\Message;
use app\common\model\Rate;
use app\common\model\ExtensionDeal;
use app\common\model\ExtensionUser;
use app\common\library\redis\OrderDelayed;
use app\common\library\push\ServerPush;
use app\common\model\Assets;
use app\common\model\IdleDeal;
use app\common\model\IdleDealDispute;

class OrderQueue extends Controller
{

       /**
     * @name: 查询三方接口修改订单状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function editOrderStatusBySanfang($data = [])
    {
        
        //获取已支付 待发货订单
        $condition = [
            'orderStatus' => ['in',[0,1]],
            'isPay' => 1,
        ];

        $ylOrderIdArr = Order::where($condition)->column('ylOrderId,orderSn', 'userId');
        
        foreach ($ylOrderIdArr as $userId => $ylOrderId) {
            $result = Vop::selectylOrderQuery($ylOrderId['ylOrderId']);
            
            if (!$result) {
                continue;
            }
            if (!$result['orderState']) {
                Order::changeOrderStatusByYlOrderId($ylOrderId['ylOrderId'], -1);
            }
            
            //状态判断
            switch ($result['orderStatus']) {
                case 16:
                    //16.等待确认收货；17.配送退货；18.货到付款确认；
                    //修改为配送中
                    if ($result['orderStatus']) {
                        Order::changeOrderStatusByYlOrderId($ylOrderId['ylOrderId'], 1);
                    }
                    
                    //$userObj =  User::get(['userId' => $userId]);
                    //推送消息
                    // if ($userObj && $userObj->pClientId) {
                    //     try {
                    //          ServerPush::send($userObj->pClientId, '通知', '您有一条物流信息，请您及时查看！', ['page' => 1]);
                    //     } catch (\Throwable $th) {
                    //         return;
                    //     }
                    // }
                    break;
                case 19:
                case 21:
                    //19.已完成；21.收款确认；
                    //修改为完成  //根据三方接口自动确认收货
                    Order::changeOrderStatusByYlOrderId($ylOrderId['ylOrderId'], 2);
                    
                    //$userObj =  User::get(['userId' => $userId]);
                    //推送消息
                    // if ($userObj  && $userObj->pClientId) {
                    //     try {
                    //          ServerPush::send($userObj->pClientId, '通知', '您有一条订单消息，请您及时查看！', ['page' => 2]);
                    //     } catch (\Throwable $th) {
                    //         return;
                    //     }
                        
                    // }
                    break;
    
                default:
                    break;
            }
        }
        return;
    }

       /**
     * @name: 查询三方接口修改服务单状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function editOrderServiceStatusBySanFang($data = [])
    {

        
            //获取申请中的
            $codition = [
                'servicesStatus' => ['in',[0,1]]
            ];
            $objs = OrderServices::all($codition);
           
            if ($objs) {
                foreach ($objs as $key => $serviceObj) {
                    //获取概要信息
                    $result = Vop::serviceListPageQuery($serviceObj['ylOrderId']);
                    
                    if (!$result || !isset($result['serviceInfoList']) || !$result['serviceInfoList'] || !isset($result['serviceInfoList'][0])) {
                        continue ;
                    }
                    
                    if ($result['serviceInfoList'][0]) {
                        $value  = $result['serviceInfoList'][0];
                      
                        if ($value['afs_num'] == $serviceObj['afsNum']) {
                            //afsServiceStep
                            switch ($value['afsServiceStep']) {
                                //服务单环节。申请阶段(10),审核不通过(20),客服审核(21),商家审核(22),源链收货(31),商家收货(32),源链处理(33),商家处理(34),用户确认(40),完成(50),取消(60);
                                case 20://申请失败 退款失败
                                        //还原商品售后
                                        $orderGoodsObj = OrderGoods::get(['orderGoodsId' => $serviceObj->orderGoodsId,'userId' => $serviceObj->userId]);
                                        $orderGoodsObj->isServices = 0;
                                        $orderGoodsObj->save();
                                        //修改服务单状态
                                        $serviceObj->servicesStatus = -1;
                                        $serviceObj->save();
                                         Message::add($serviceObj->userId, '订单通知', '退款申请已拒绝', $serviceObj->orderSn . '订单，申请退款未通过客服审核，请您及时查看退款订单！');
                                        //$userObj =  User::get(['userId' => $serviceObj->userId]);
                                        //推送消息
                                    // if ($userObj  && $userObj->pClientId) {
                                    //     try {
                                    //          ServerPush::send($userObj->pClientId, '通知', '您有一条订单消息，请您及时查看！', ['page' => 3]);
                                    //     } catch (\Throwable $th) {
                                    //         return;
                                    //     }
                                        
                                    // }
                                    //return '修改服务单状态 ' . $serviceObj->servicesSn . ' 退款失败';
                                    break;
                                case 31:
                                    if (!$serviceObj->servicesStatus) {
                                         Message::add($serviceObj->userId, '订单通知', '退款申请已通过', $serviceObj->orderSn . '订单，申请退款已通过客服审核，请您及时查看退款进度！');
                                        $serviceObj->servicesStatus = 1;
                                        $serviceObj->save();
                                        //推送消息
                                        // $userObj =  User::get(['userId' => $serviceObj->userId]);
                                        // if ($userObj  && $userObj->pClientId) {
                                        //     try {
                                        //          ServerPush::send($userObj->pClientId, '通知', '您有一条订单消息，请您及时查看！', ['page' => 3]);
                                        //     } catch (\Throwable $th) {
                                        //         return;
                                        //     }
                                            
                                        // }
                                    }
                                        
                                    break;
                                case 21:
                                case 22:
                                        //获取增加获取客户发货信息 serviceDetailInfo
                                        $resultDetail =  Vop::serviceDetailInfo($serviceObj['afsNum']);
                                              //审核结果：直赔积分(11),直赔余额(12),直赔优惠卷(13),直赔京豆(14),直赔商品(21),上门换新(22),自营取件(31),客户送货(32),客户发货(33),闪电退款(34),虚拟退款(35),大家电检测(80),大家电安装(81),大家电移机(82),大家电维修(83),大家电其它(84);
                                        
                                    if ($resultDetail['approvedResult'] == 33) {  //客户发货的
                                        if ($serviceObj->pickwareType  == 4) {
                                            $serviceObj->pickwareType = 40;
                                            $serviceObj->servicesStatus = 3;
                                            $serviceObj->serviceAftersalesAddressInfoDTO = json_encode($resultDetail['serviceAftersalesAddressInfoDTO']);
                                            $serviceObj->save();
                                        }
                                    }
                                    if ($resultDetail['approvedResult'] == 31) {  //上门取件的 修改状态为 退款中
                                        if (!$serviceObj->servicesStatus) {
                                            $serviceObj->servicesStatus = 1;
                                            $serviceObj->save();
                                        }
                                    }
   
                                    break;
                                case 40: //退款
                                         //退款查询
                                        $resultRefund = Vop::aftersaleOrderInfo($serviceObj->afsNum, $serviceObj->ylOrderId);
                                    if ($resultRefund) {
                                        if ($resultRefund[0]['refundableAmount']) {
                                            $serviceObj->servicesStatus = 2;
                                            $serviceObj->save();
                                            try {
                                                OrderServices::refund($serviceObj->servicesId);
                                                 Message::add($serviceObj->userId, '订单通知', '退款已完成', $serviceObj->orderSn . '订单，退款订单已完成，请您及时查看退款订单！');
                                            } catch (\Throwable $th) {
                                                 Message::add($serviceObj->userId, '订单通知', '退款失败', $serviceObj->orderSn . '订单，退款订单未通过审核，请您及时查看退款订单！');
                                            }
                                            // $userObj =  User::get(['userId' => $serviceObj->userId]);
                                            // //推送消息
                                            // if ($userObj  && $userObj->pClientId) {
                                            //     try {
                                            //          ServerPush::send($userObj->pClientId, '通知', '您有一条订单消息，请您及时查看！', ['page' => 3]);
                                            //     } catch (\Throwable $th) {
                                            //         return;
                                            //     }
                                                
                                            // }
                                        }
                                    }
                                    //return '修改服务单状态 ' . $serviceObj->servicesSn . ' 退款完成';
                                    break;
            
                                    
                                default:
                                    //return '服务单状态未改变 ' . $serviceObj->servicesSn;
                                    break;
                            }
                        }
                    }
                }
            }
    }

    public function delOrderDelayed($data = [])
    {
        for ($i = 0; $i < 100; $i++) {
            $task = OrderDelayed::getOrderDelayedTask();
            if (!$task) {
                die();
            }
            OrderDelayed::run($task);
        }
    }
   
     /**
     * @name: 推广收益-退款-可提现
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function checkPromotionIncome($data = [])
    {

        # 转为可提现
        //7天前锁定中的订单 修改为可提现的
        try {
            ExtensionDeal::upSevenDaysAgoLockToAlow();
        } catch (\Throwable $th) {
        }
    }

    /**
     * @name: 取消 试用 7天内未有10人下单的  推广权限
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function checkPromotionAuth($data = [])
    {
        try {
            ExtensionUser::checkPromotionAuth();
        } catch (\Throwable $th) {
        }
    }
    /**
     * @name: idleDeal 10天后自动完成
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function checkIdleDealTrueAuth($data = [])
    {
        try {
            $condition = [
                'dealStats' => 3,
            ];
            $list = IdleDeal::where($condition)->column('sendTime', 'idleDealId');
            foreach ($list as $idleDealId => $value) {
                    $chaTime = ceil((time() - $value['sendTime']) / 86400);
                if ($chaTime >= 10) {
                    IdleDeal::update(['dealStats' => 4,'trueTime' => time()], ['idleDealId' => $idleDealId]);
                }
            }
        } catch (\Throwable $th) {
        }
    }
    /**
     * @name: idleDeal 完成3天后 打款
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function checkIdleDealOverAuth($data = [])
    {
        try {
            $condition = [
                'dealStats' => 4,
                'paySell' => 0,
            ];
            $list = IdleDeal::where($condition)->column('trueTime,price,sellUserId', 'idleDealId');
            $AssetsModel = new Assets();
            foreach ($list as $idleDealId => $value) {
                    $chaTime = ceil((time() - $value['trueTime']) / 86400);
                if ($chaTime > 3) {
                    $AssetsModel->addUSDT($value['sellUserId'], $value['price'], '闲置尾款');
                    IdleDeal::update(['paySell' => 1], ['idleDealId' => $idleDealId]);
                }
            }
        } catch (\Throwable $th) {
        }
    }
    /**
     * @name: 纠纷退款
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function checkIdleDealDispute($data = [])
    {
        try {
            $condition = [
                'isClose' => 0,
            ];
            $list = IdleDealDispute::all($condition);
            foreach ($list as $key => $data) {
                if ($data['disputeResults'] > 0) {
                    //有结果
                    //找到对应deal
                    $IdleDealInfo = IdleDeal::get(['idleDealDisputeId' => $data['idleDealDisputeId']]);
                    if ($IdleDealInfo) {
                        $ttimg = ceil((time() - $data['createTime']) / 86400);
                        if ($ttimg > 3) {
                            //反钱
                            $AssetsModel = new Assets();
                            $AssetsModel->startTrans();
                            try {
                                if ($data['disputeResults'] == 1) {
                                    #1发起纠纷人胜
                                
                                        $AssetsModel->addUSDT($data['fromUserId'], $IdleDealInfo['price'] * 4 / 5, "纠纷退款");
                                        $IdleDealInfo->dealStats = 9;
                                        $IdleDealInfo->save();
                                        IdleDealDispute::update(['isRefund'=>1],['idleDealDisputeId'=> $data['idleDealDisputeId']]);
                                        
                                    
                                    
                                } else {
                                    #2发起纠纷人败
                                    $AssetsModel->addUSDT($data['toUserId'], $IdleDealInfo['price'] * 4 / 5, "纠纷退款");
                                    $IdleDealInfo->dealStats = 9;
                                    $IdleDealInfo->save();
                                    IdleDealDispute::update(['isRefund'=>1],['idleDealDisputeId'=> $data['idleDealDisputeId']]);
                                }
                                $AssetsModel->commit();
                            } catch (\Exception $th) {
                                $AssetsModel->rollback();
                            }
                        }
                    } else {
                        //发起了第二次纠纷 第一次纠纷结果无用直接关闭
                        IdleDealDispute::update(['isClose' => 1], ['idleDealDisputeId' => $data['idleDealDisputeId']]);
                    }
                }
            }
        } catch (\Throwable $th) {
        }
    }
    /**
     * @name: 修改汇率
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function upRate($data = [])
    {
        try {
            $Rate  = new  Rate();
            $Rate->updateRate();
            return;
        } catch (\Throwable $th) {
        }
    }
}
