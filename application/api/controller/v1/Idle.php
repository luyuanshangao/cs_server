<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\exception\ApiException;
use app\common\model\ExtensionUser;
use app\common\model\IdleInfo;
use app\common\model\IdleDeal;
use app\common\model\IdleCollection;
use app\common\model\Assets;
use app\common\model\User;
use app\common\model\IdleMessage;
use app\common\model\IdleMessageSon;
use app\common\model\IdleDealRefund;
use app\common\model\IdleDealDispute;
use app\common\model\IdleDealDisputeEvidence;
use app\common\model\UserAddress;
use app\common\model\IdleInfoSkuStock;

class Idle extends Base
{
    protected $noAuthArr = ['list'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单
    protected $verifyFee = 10;
    /**
     * @name:        初始化
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * 交易的权限控制
         */
        $extemsionId = ExtensionUser::where(['userId' => $this->clientInfo->userId])->value('extensionId');
        // if(!$extemsionId || $extemsionId < 4){
        //     throw new ApiException(1,[],'暂未达到等级');
        // }
    }

    /**
     * @name: 商品列表
     * @author: gz
     * @description: 
     * @param {*}
     * @return {*}
     */
    public function list()
    {

        $getArr  = $this->checkdate('Idle', 'get', 'list');
        $IdleInfoModel = new IdleInfo();
        $this->getPageAndSize($getArr);
        $condition = [
            'groundStatus' => 1,
        ];
        
        isset($getArr['search']) ? $condition['title'] = ['like','%' . $getArr['search'] . '%'] : '';
        $total = $IdleInfoModel->getCount($condition);
        $list = $IdleInfoModel->getList($condition, $this->from, $this->size, true, 'createTime desc', ['sellUser','picArr']);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        
        return show(1, $returnResult);
    }


    /**
     * @name: 商品管理列表
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function idleListsInfo()
    {

        $getArr  = $this->checkdate('Idle', 'get', 'idleListsInfo');
        $IdleInfoModel = new IdleInfo();
        $this->getPageAndSize($getArr);
        $returnResult = [
            'total' => 0,
            'page_num' => 0,
            'list' => [],
        ];

        switch ($getArr['type']) {
            case 1:
                #已发布列表
                    $condition = [
                        'userId' => $this->userId,
                    ];
                    $total = $IdleInfoModel->getCount($condition);
                    $list = $IdleInfoModel->getList($condition, $this->from, $this->size, true, 'createTime desc', ['collectNum','views','picArr','frontCode']);

                break;
            case 2:
                # 已卖出 我的订单
                    $IdleDealModel = new IdleDeal();
                    $condition = [
                        'dealStats' => ['in',[0,2,3,4,5,7,8,9]],
                        'sellUserId' => $this->userId,
                    ];
                    $total = $IdleDealModel->getCount($condition);
                    $list = $IdleDealModel->getList($condition, $this->from, $this->size, ['dealSn','idleDealId','idleInfoId','dealStats'], 'createTime desc', ['idleInfo']);
                   
                break;
            case 3:
                # 已买到 我的订单
                    $IdleDealModel = new IdleDeal();
                    $condition = [
                        'buyUserId' => $this->userId,
                        'del' => 0,
                    ];
                    $total = $IdleDealModel->getCount($condition);
                    $list = $IdleDealModel->getList($condition, $this->from, $this->size, ['dealSn','idleDealId','idleInfoId','dealStats'], 'createTime desc', ['idleInfo']);

                break;
            case 4:
                    # 收藏
                    $IdleCollectionModel = new IdleCollection();
                    $condition = [
                        'userId' => $this->userId,
                    ];
                    $idleInfoIds = $IdleCollectionModel->where($condition)->order('createTime desc')->column('idleInfoId');

                    if (count($idleInfoIds) <= 0) {
                        return show(1, $returnResult);
                    }
                    $condition = [
                        'idleInfoId' => [ 'in', $idleInfoIds] ,
                    ];
                    $total = $IdleInfoModel->getCount($condition);
                    $list = $IdleInfoModel->getList($condition, $this->from, $this->size, true, 'createTime desc', ['collectNum','views','picArr','frontCode']);

                break;
            
            default:
                return show(0);
                break;
        }
        
        
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
       
        return show(1, $returnResult);
    }




###############################################################################################
    /**
     * @name: 创建商品信息
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function createIdle()
    {

        // $sku = [
        //     [
        //         'price'=>'12',
        //         'pic'=>'/uploads/idleImg/20210106/a931128f7644276c9d0dc06b9ea129bf.JPG',
        //         'stock'=>'11',
        //         'freight'=>'11',
        //         'spData'=>[
        //             ['key'=>'颜色','value'=>'红色',],
        //             ['key'=>'容量','value'=>'500ml',],
        //         ],
                
            
        //     ],
        //     [
        //         'price'=>'24',
        //         'pic'=>'/uploads/idleImg/20210106/a931128f7644276c9d0dc06b9ea129bf.JPG',
        //         'stock'=>'24',
        //         'freight'=>'13',
        //         'spData'=>[
        //             ['key'=>'颜色','value'=>'红色'],
        //             ['key'=>'容量','value'=>'1000ml',],
        //         ],
                
            
        //     ],
        //     [
        //         'price'=>'15',
        //         'pic'=>'/uploads/idleImg/20210106/a931128f7644276c9d0dc06b9ea129bf.JPG',
        //         'stock'=>'12',
        //         'freight'=>'13',
        //         'spData'=>[
        //             ['key'=>'颜色','value'=>'绿色'],
        //             ['key'=>'容量','value'=>'500ml',],
        //         ],
                
            
        //     ],
        //     [
        //         'price'=>'30',
        //         'pic'=>'/uploads/idleImg/20210106/a931128f7644276c9d0dc06b9ea129bf.JPG',
        //         'stock'=>'12',
        //         'freight'=>'13',
        //         'spData'=>[
        //             ['key'=>'颜色','value'=>'绿色'],
        //             ['key'=>'容量','value'=>'1000ml',],
        //         ],
                
            
        //     ],
            
        // ];
        // return json($sku);die;
        $dataArr  = $this->checkdate('Idle', 'post', 'createIdle');
        $result = IdleInfo::addInfo($this->userId, $dataArr);
   
        if (!$result) {
            return show(0,[],'创建失败');
        }
        return show(1);
    }


   /**
     * @name: 是否需要拉起支付
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function isVerify()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'aginIdleInfo');
        $isVerify = IdleInfo::isIsVerify($this->userId, $dataArr['idleInfoId']);
        if ($isVerify) { //支付了
            $code = 0;
        } else {//没支付
            $code = 1;
        }
        return show(1, ['true' => $code]);
    }


   /**
     * @name: 支付审核费
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function payVerifyFee()
    {
        
        $dataArr  = $this->checkdate('Idle', 'post', 'payVerifyFee');

        #支付密码验证
        if (!$this->clientInfo->payPassWord) {
            return show(1019);
        }
        if ($this->clientInfo->payPassWord !== setPassword($dataArr['payPassWord'])) {
            return show(1020);
        }

        #查询信息是否存在
        $idleInfo = IdleInfo::getInfoById($this->userId, $dataArr['idleInfoId']);
        if (!$idleInfo) {
            return show(1049);
        }

        #查询钱包余额
        $AssetsModel = new Assets();
        $amountUsdt = $AssetsModel->where(['userId' => $this->userId,'assetsType' => 3])->value('amount');
        if (bccomp($amountUsdt, $this->verifyFee, 10) !== 1) {
            return show(1027);
        }
        try {
            #扣除审核金
            $AssetsModel->costUSDT($this->userId, $this->verifyFee, '支付审核金');

            #修改信息状态
            $idleInfo->isVerify = 1;
            $idleInfo->verifyFee = $this->verifyFee;
            $idleInfo->save();
        } catch (\Exception $th) {
            return show(0);
        }
        return show(1);
    }


    /**
     * @name: 下架
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function downIdleInfo()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'downIdleInfo');
        $idleInfoId = $dataArr['idleInfoId'];
        $idleInfo = IdleInfo::get(['userId' => $this->userId,'idleInfoId' => $idleInfoId]);
        if ($idleInfo['verifyStatus'] === 1 && $idleInfo['groundStatus'] === 1) {
            IdleInfo::upGround($idleInfoId, 0);
        }
        return show(1);
    }

    

    /**
     * @name: 编辑
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function editIdleInfo()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'editIdleInfo');

        #查询信息是否存在
        $IdleInfo = IdleInfo::getInfo($this->userId,$dataArr['idleInfoId']);
        if (!$IdleInfo) {
            return show(0,[],'编辑失败');
        }
        #上架中不能编辑
        if ($IdleInfo->groundStatus === 1) {
            return show(0,[],'编辑失败');
        }
        $result = IdleInfo::updateInfo($IdleInfo,$dataArr);
        if (!$result) {
            return show(0,[],'编辑失败');
        }
        return show(1);
    }


    /**
     * @name: 重新上架
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function aginIdleInfo()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'aginIdleInfo');
        $idleInfoId = $dataArr['idleInfoId'];
        $idleInfo = IdleInfo::get(['userId' => $this->userId,'idleInfoId' => $idleInfoId]);
        if (!$idleInfo) {
            return show(0);
        }
        #判断是否重新支付审核金
        $isVerify = IdleInfo::isIsVerify($this->userId, $idleInfoId);
        if (!$isVerify) {
            return show(1047);
        }
        $idleInfo->groundStatus = 1;
        $idleInfo->createTime = time(); //更新时间
        $idleInfo->save();
        return show(1);
    }


   /**
     * @name: 删除闲置信息
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function deleteIdleInfo()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'deleteIdleInfo');
        $idleInfoId = $dataArr['idleInfoId'];

        #查询信息是否存在
        $idleInfo = IdleInfo::getInfoById($this->userId, $idleInfoId);
        if (!$idleInfo) {
            return show(0);
        }
        # verifyStatus是 2
        if ($idleInfo['verifyStatus'] == 2) {
            return show(0);
        }
        $idleInfo->delete();
        return show(1);
    }


    /**
     * @name: 取消闲置的审核
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function cacalIdle()
    {

        $getArr  = $this->checkdate('Idle', 'get', 'cacalIdle');

        #查询信息是否存在
        $idleInfo = IdleInfo::getInfoById($this->userId, $getArr['idleInfoId']);
        if (!$idleInfo) {
            return show(0);
        }

        #是否在审核中
        if ($idleInfo['verifyStatus'] != 0) {
            return show(0);
        }

        #是否支付
        $result = IdleInfo::isVerify($idleInfo['idleInfoId']);
       
        if ($result) {
           #可以取消审核
            IdleInfo::cacalVerify($idleInfo['idleInfoId']);
            $idleInfo->verifyStatus = -1;
            $idleInfo->save();
           #退审核金
            $AssetsModel = new Assets();
            $AssetsModel->addUSDT($this->userId, $this->verifyFee, '退还审核金');
        }

        return show(1);
    }



     ########################################################################################################

    /**
     * @name: 闲置详情
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function idleDetail()
    {
        $getArr  = $this->checkdate('Idle', 'get', 'idleDetail');
        $idleInfoId = $getArr['idleInfoId'];
        $returnData = [
            'userData' => [],
            'idleData' => [],
            'messageData' => [],
        ];
        $IdleInfo = IdleInfo::getDeail($idleInfoId);
        if (!$IdleInfo) {
            return $returnData;
        }
        $skuData = IdleInfoSkuStock::skuDecr( $IdleInfo['idleInfoId']);
   
        #闲置信息
        try {
            $pic = explode(',', $IdleInfo['picPath']);
        } catch (\Exception $th) {
            $pic = '';
        }
        try {
            $desPic = explode(',', $IdleInfo['desPicPath']);
        } catch (\Exception $th) {
            $desPic = '';
        }
        
        $idleInfo = [
            'idleInfoId' => $IdleInfo['idleInfoId'],
            'title' => $IdleInfo['title'],
            'description' => $IdleInfo['description'],
            'desPicPath'=>$desPic,
            'picArr' => $pic,
            'releaseser' => $IdleInfo['userId'] == $this->userId ? 1 : 0 ,
        ];
        
        #用户信息
        $userData = User::get([
            'userId' => $IdleInfo['userId']
        ]);
        $userInfo = [
            'userName' => $userData['userName'],
            'comeTime' => ceil((time() - $userData['createTime']) / 86400),
            'selNum' => IdleDeal::sellNum($userData['userId']),
        ];


        
        $IdleMessageModel = new IdleMessage();
        $condition = [
            'idleInfoId' => $idleInfoId
        ];
        $list = $IdleMessageModel->getList($condition, 0, 5, true, 'createTime desc', ['userName','timeWord','messageSonList']);
        $messages =  IdleMessage::orderTime($list);

        $IdleCollectionModel = new IdleCollection();
        $IdleColleData = $IdleCollectionModel->get(['userId' => $this->userId,'idleInfoId' => $idleInfoId]);

        $returnData  = [
            'userData' => $userInfo,
            'idleData' => $idleInfo,
            'skuData' => $skuData,
            'messageData' => $messages,
            'isColle' => $IdleColleData ? 1 : 0
        ];
        
        return show(1, $returnData);
    }

    /**
     * @name: 闲置收藏
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function idleColle()
    {
        $getArr  = $this->checkdate('Idle', 'get', 'idleDetail');
        $IdleCollectionModel = new IdleCollection();
        $IdleColleData = $IdleCollectionModel->get(['userId' => $this->userId,'idleInfoId' => $getArr['idleInfoId']]);
        if ($IdleColleData) {
            $IdleColleData->delete();
        } else {
            $IdleCollectionModel->create([ 'userId' => $this->userId,'idleInfoId' => $getArr['idleInfoId'],'createTime' => time()]);
        }
        return show(1);
    }

    /**
     * @name: 闲置收藏删除
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function idleColleDelete()
    {
        $getArr  = $this->checkdate('Idle', 'post', 'idleColleDelete');
        try {
            $idleInfoIdArr = json_decode($getArr['idleInfoIdArr']);
            IdleCollection::destroy([
                'userId' => $this->userId,
                'idleInfoId' => ['in',$idleInfoIdArr],
            ]);
        } catch (\Exception $th) {
        }
        return show(1);
    }

    /**
     * @name: 所有留言
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function allMessage()
    {
          
        $getArr  = $this->checkdate('Idle', 'get', 'listMessage');
        $IdleMessageModel = new IdleMessage();
        $this->getPageAndSize($getArr);
        $condition = [
            'idleInfoId' => $getArr['idleInfoId']
        ];
        
        $total = $IdleMessageModel->getCount($condition);
      
        $list = $IdleMessageModel->getList($condition, $this->from, $this->size, true, 'createTime desc', ['userName','timeWord','messageSonList']);
        $new_list =  IdleMessage::orderTime($list);
      
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $new_list,
        ];
        
        return show(1, $returnResult);
    }


    ########################################################################################################


    /**
     * @name: 创建订单deal  购买
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function createDeal()
    {

        $dataArr  = $this->checkdate('Idle', 'post', 'createDeal');
        $IdleDealModel  = new IdleDeal();

        $result = $IdleDealModel->addDeal($this->userId, $dataArr);
        if (isset($result['code'])) {
            return show($result['code'], [], $result['message'] ? $result['message'] : '');
        }
        $returnArr = [
            'dealSn' => $result->dealSn,
            'idleDealId' => $result->idleDealId,
            'price' => $result->price,
        ];
        return show(1, $returnArr);
    }

    /**
     * @name:        支付订单Deal
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function payDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'payDeal');

        //支付密码验证
        if (!$this->clientInfo->payPassWord) {
            return show(1019);
        }
        
        if ($this->clientInfo->payPassWord !== setPassword($dataArr['payPassWord'])) {
            return show(1020);
        }
        
        //信息
        $IdleDealModel  = new IdleDeal();
        $dealConfition = [
            'idleDealId' => $dataArr['idleDealId'],
            'buyUserId' => $this->userId
        ];
       
        $dealInfo = $IdleDealModel::get($dealConfition);
        if (!$dealInfo) {
            return show(1021);
        }
        if ($dealInfo['dealStats'] !== 1) {
            return show(1053);
        }
        //账户余额
        $assetsCondition = [
            'assetsType' => 3,
            'userId' => $this->userId
        ];
        $userAssets = Assets::get($assetsCondition);

        if (bccomp($userAssets['amount'], $dealInfo->price, 10) !== 1) {
            return show(1027);
        }
                    
       $IdleInfoSkuStockInfo = IdleInfoSkuStock::get(['skuStockId'=>$dealInfo['skuStockId']]);
       if($IdleInfoSkuStockInfo['stock'] <=0){
           return show(0,[],'库存不足');
       }
        //支付
        $result = $IdleDealModel->payDeal($this->userId, $dealInfo,$IdleInfoSkuStockInfo);


        if (!$result) {
            return show(1018);
        }
        return show(1);
    }
    
    /**
     * @name:        Deal订单确认收货
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function trueDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'trueDeal');
        
        $IdleDeal = IdleDeal::get([
            'idleDealId' => $dataArr['idleDealId'],
            'buyUserId' => $this->userId,
            'dealStats' => 3,
        ]);
        if (!$IdleDeal) {
            return show(0);
        }

        $IdleDeal->dealStats = 4;
        $IdleDeal->trueTime = time();
        $IdleDeal->save();
        return show(1);
    }

    /**
     * @name:        订单Deal申请退款
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function refundDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'refundDeal');

        #查询deal 是否满足退款条件
        $condition = [
            'buyUserId' => $this->userId,
            'idleDealId' => $dataArr['idleDealId'],
            'dealStats' => 2,
        ];

        $IdleDealInfo = IdleDeal::get($condition);
        if (!$IdleDealInfo) {
            return show(0);
        }
        
        IdleDealRefund::createReund($this->userId, $IdleDealInfo, $dataArr);
        return show(1);
    }

        /**
     * @name:      订单Deal取消退款
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function cocalRefundDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'cocalRefundDeal');
        #查询deal 是否满足退款条件
        $condition = [
            'idleDealRefundId' => $dataArr['idleDealRefundId'],
            'idleDealId' => $dataArr['idleDealId'],
            'userId' => $this->userId,
            'status' => 1,
        ];
        $IdleDealRefundInfo = IdleDealRefund::get($condition);
        if (!$IdleDealRefundInfo) {
            return show(0);
        }
        $IdleDealRefundInfo->status = -1;
        $IdleDealRefundInfo->save();
        #修改deal状态
        $IdleDealInfo = IdleDeal::get(['idleDealId' => $dataArr['idleDealId']]);
        $IdleDealInfo->dealStats = 2;
        $IdleDealInfo->save();
        return show(1);
    }

    /**
     * @name:        查看退款原因Deal
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function lookRefundDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'lookRefundDeal');

        #查询deal 是否满足退款条件
        $condition = [
            'idleDealRefundId' => $dataArr['idleDealRefundId'],
            'idleDealId' => $dataArr['idleDealId'],
            'sellUserId' => $this->userId,
        ];

        $IdleDealInfo = IdleDealRefund::get($condition);
        if (!$IdleDealInfo) {
            return show(0);
        }
        #闲置信息
        try {
            $pic = explode(',', $IdleDealInfo['picPath']);
        } catch (\Throwable $th) {
            $pic = '';
        }
        $IdleDealInfo['picArr'] = $pic;
        $IdleDealInfo['isRefundTime'] = date('Y-m-d H:i:s', $IdleDealInfo['isRefundTime']);
        $IdleDealInfo['createTime'] = date('Y-m-d H:i:s', $IdleDealInfo['createTime']);
        
         
        return show(1, $IdleDealInfo);
    }



   /**
     * @name:     同意退款Deal
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function agreeRefundDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'agreeRefundDeal');
        #查询deal 是否满足退款条件
        $condition = [
            'idleDealRefundId' => $dataArr['idleDealRefundId'],
            'idleDealId' => $dataArr['idleDealId'],
            'sellUserId' => $this->userId,
            'status' => 1,
        ];
        $IdleDealRefundInfo = IdleDealRefund::get($condition);
        if (!$IdleDealRefundInfo) {
            return show(0);
        }
        $IdleDealRefundInfo->status = 2;
        $IdleDealRefundInfo->isRefund = 1;
        $IdleDealRefundInfo->isRefundTime = time();
        $IdleDealRefundInfo->save();
        #退款给用户
        $IdleDealInfo = IdleDeal::get(['idleDealId' => $dataArr['idleDealId'],]);
        $IdleDealInfo->dealStats = 8;
        $IdleDealInfo->save();
        $AssetsMdeol =  new Assets();
        $AssetsMdeol->addUSDT($IdleDealInfo['buyUserId'], $IdleDealInfo['price'], '闲置申请退款');
        #信息修改
        $IdleInfoSkuStockInfo = IdleInfoSkuStock::get(['skuStockId' => $IdleDealInfo->skuStockId]);
        $IdleInfoSkuStockInfo->stock += 1;
        $IdleInfoSkuStockInfo->save();
        return show(1);
    }

   /**
     * @name:     拒绝退款
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function voteRefundDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'agreeRefundDeal');
        #查询deal 是否满足退款条件
        $condition = [
            'idleDealRefundId' => $dataArr['idleDealRefundId'],
            'idleDealId' => $dataArr['idleDealId'],
            'userId' => $this->userId,
            'status' => 1,
        ];
        $IdleDealRefundInfo = IdleDealRefund::get($condition);
        if (!$IdleDealRefundInfo) {
            return show(0);
        }
        $IdleDealRefundInfo->status = 0;
        $IdleDealRefundInfo->save();
        #修改deal状态
        $IdleDealInfo = IdleDeal::get(['idleDealId' => $dataArr['idleDealId'],]);
        $IdleDealInfo->dealStats = 2;
        $IdleDealInfo->save();

        return show(1, $IdleDealInfo);
    }

    /**
     * @name:        取消订单
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function cacolDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'cacolDeal');
        $IdleDealModel  = new IdleDeal();
        $IdleDealModel->cancelIdleDeal($this->userId, $dataArr['idleDealId']);
        return show(1);
    }
    /**
     * @name:        关闭Deal
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function closeDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'closeDeal');
        
        $IdleDealModel  = new IdleDeal();
        $IdleDealModel->closeIdleDeal($this->userId, $dataArr['idleInfoId']);
        return show(1);
    }

    /**
     * @name:        发货Deal
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function sendDeal()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'sendDeal');
       
        $IdleDeal = IdleDeal::get([
            'idleDealId' => $dataArr['idleDealId'],
            'sellUserId' => $this->userId
        ]);
        if (!$IdleDeal) {
            return show(0);
        }
        if ($IdleDeal['dealStats'] !== 2) {
            return show(0);
        }

        $IdleDeal->logistics = $dataArr['idleDealId'];
        $IdleDeal->logisticsNum = $dataArr['logisticsNum'];
        $IdleDeal->dealStats = 3;
        $IdleDeal->sendTime = time();
        $IdleDeal->save();
     
        return show(1);
    }


    /**
     * @name:        申请退款完成 删除Deal
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function deleteDeal()
    {
        #买家仅在完成时 或申请退款完成时
        $dataArr  = $this->checkdate('Idle', 'post', 'deleteDeal');
        $IdleDeal = IdleDeal::get([
            'idleDealId' => $dataArr['idleDealId'],
            'userId' => $this->userId
        ]);
        if ($IdleDeal['dealStats'] == 8) {
            $IdleDeal->del = 1;
            $$IdleDeal->save();
        }
        return show(1);
    }

    #########################################################################################################################
    #纠纷部分

    /**
     * @name:  申请纠纷
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function createDispute()
    {

        $dataArr  = $this->checkdate('Idle', 'post', 'createDispute');
        
        #判断会否满足纠纷条件

        $idleDealId = $dataArr['idleDealId'];
        $idleInfoId = $dataArr['idleInfoId'];

        #闲置信息
        $idleInfo = IdleInfo::get(['idleInfoId' => $idleInfoId]);
        if (!$idleInfo) {
            return show(0);
        }
        #判断信息是否正确
        $IdleDealInfo = IdleDeal::get(['idleDealId' => $idleDealId]);
        if (!$IdleDealInfo) {
            return show(0);
        }
        if ($IdleDealInfo['idleInfoId'] != $idleInfo['idleInfoId']) {
            return show(0);
        }
        if ($IdleDealInfo['dealStats'] == 9) {
            return show(0);
        }
        #判断是买家还是卖家申请的纠纷
        if ($this->userId == $idleInfo['userId']) {
            #卖家发起的纠纷
            $isWho = 'sell';
            $fromUserId = $this->userId;
            $toUserId = $IdleDealInfo['buyUserId'];
            if ($IdleDealInfo['sellUserId'] != $this->userId) {
                return show(0);
            }
        } else {
            #买家发起的纠纷
            $isWho = 'buy';
            $fromUserId = $this->userId;
            $toUserId = $IdleDealInfo['sellUserId'];
            if ($IdleDealInfo['buyUserId'] != $this->userId) {
                return show(0);
            }
        }
        
        $IdleDealDisputeModel = new IdleDealDispute();
        $count = $IdleDealDisputeModel->where(['fromUserId' => $fromUserId,'toUserId' => $toUserId])->whereOr(['fromUserId' => $toUserId,'toUserId' => $fromUserId])->count();
        if ($count >= 2) {
            return show(1056);
        }

        #完成订单三天内 可以申请纠纷
        $ovTime = ceil((time() - $IdleDealInfo['trueTime']) / 86400);
        
        if ($IdleDealInfo['dealStats'] < 4 || $IdleDealInfo['dealStats'] > 5 || $ovTime >= 3) {
            return show(1056);
        }
        
       
        //订单号
        do {
                $disputeSn = $this->disputeSn();
        } while (IdleDealDispute::where('disputeSn', $disputeSn)->count() == 1);
        $saveData = [
            'disputeSn' => $disputeSn,
            'idleDealId' => $idleDealId,
            'idleInfoId' => $idleInfoId,
            'fromUserId' => $fromUserId,
            'toUserId' => $toUserId,
            'isWho' => $isWho,
            'disputeType' => $dataArr['disputeType'],
            'disputeDescribe' => $dataArr['disputeDescribe'],
            'createTime' => time(),
        ];
        $IdleDealDisputeData = IdleDealDispute::create($saveData);
        $IdleDealInfo->dealStats = 5;
        $IdleDealInfo->idleDealDisputeId = $IdleDealDisputeData->idleDealDisputeId;
        $IdleDealInfo->save();
        return show(1);
    }
    private static function disputeSn()
    {
        $sn = 'J' . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5);
        return $sn;
    }

    /**
     * @name: 上传举证
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function upEvidence()
    {
        $dataArr  = $this->checkdate('Idle', 'post', 'upEvidence');
        
        $IdleDealDisputeData = IdleDealDispute::get([
            'idleDealDisputeId' => $dataArr['idleDealDisputeId']
        ]);

        if (!$IdleDealDisputeData) {
            return show(0);
        }
        if (($IdleDealDisputeData['createTime'] + 86400) < time()) {
            return show(1057);
        }
        $saveData = [
            'idleDealDisputeId' => $dataArr['idleDealDisputeId'],
            'picPath' => $dataArr['picPath'],
            'content' => $dataArr['content'],
            'isWho' => $IdleDealDisputeData['iswho'],
            'userId' => $this->userId,
            'createTime' => time(),
        ];
        IdleDealDisputeEvidence::create($saveData);
        return show(1);
    }

   /**
     * @name: 举证详情
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function evidenceDetail()
    {
        $dataArr  = $this->checkdate('Idle', 'get', 'evidenceDetail');
        $IdleDealDisputeInfo = IdleDealDispute::get([
            'idleDealDisputeId' => $dataArr['idleDealDisputeId']
        ]);
        if ($IdleDealDisputeInfo['fromUserId'] !== $this->userId && $IdleDealDisputeInfo['toUserId'] !== $this->userId) {
            return show(0);
        }
        $IdleDealDisputeEvidenceModel = new IdleDealDisputeEvidence();
        $this->getPageAndSize($dataArr);
        $condition = [
            'idleDealDisputeId' => $dataArr['idleDealDisputeId'],
        ];
      
        $total = $IdleDealDisputeEvidenceModel->getCount($condition);
        $list = $IdleDealDisputeEvidenceModel->getList($condition, $this->from, $this->size, true, 'createTime desc', ['content']);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        
        return show(1, $returnResult);
    }


##################################################################################

    /**
     * @name: 卖家信息
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function sellUserInfo()
    {

        # 用户昵称 多久前在线 成交率  卖出 在售 买到  纠纷
        $dataArr  = $this->checkdate('Idle', 'post', 'sellUserInfo');
        $sellUserData  = User::get(['userId' => $dataArr['userId']]);
        if (!$sellUserData) {
            return show(1058);
        }
        $turnRate = IdleDeal::turnRate($dataArr['userId']);
        $sellNum = IdleDeal::sellNum($dataArr['userId']);
        $onlineNum = IdleDeal::onlineNum($dataArr['userId']);
        $buyNum = IdleDeal::buyNum($dataArr['userId']);
        $disputNum = IdleDeal::disputNum($dataArr['userId']);
        $returnResult = [
            'userName' => $sellUserData['userName'],
            'turnRate' => $turnRate,
            'sellNum' => $sellNum,
            'onlineNum' => $onlineNum,
            'buyNum' => $buyNum,
            'disputNum' => $disputNum,
        ];
        return show(1, $returnResult);
    }

   /**
     * @name: 卖家在售商品
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function onlineSellDeal()
    {

   
        $getArr  = $this->checkdate('Idle', 'get', 'onlineSellDeal');
        $this->getPageAndSize($getArr);
        $condition = [
            'userId' => $getArr['userId'],
            'verifyStatus' => 1,
            'groundStatus' => 1,
        ];
        $IdleInfoModel = new IdleInfo();
        $total = $IdleInfoModel->getCount($condition);
        $list = $IdleInfoModel->getList($condition, $this->from, $this->size, ['idleInfoId','infoSn','price','title','picPath'], 'createTime desc', ['picArr']);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }


##################################################################################

    /**
     * @name: 交易详情
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function dealDetail()
    {
        // deal状态
        // 地址信息{
        //     收货人名 手机 地址拼接
        // }
        // info的 title 描述 价格 运费包邮
        // 退款原因
        // 快递信息
        // 纠纷信息的状态{
            
        // }
        $dataArr  = $this->checkdate('Idle', 'post', 'dealDetail');

       
        $idleDealId = $dataArr['idleDealId'];
        $IdleDealModel = new IdleDeal();

        $idleDealInfo = $IdleDealModel->get(['idleDealId' => $idleDealId]);
        if (!$idleDealInfo) {
            return show(0);
        }
     
        if ($idleDealInfo['sellUserId'] !== $this->userId && $idleDealInfo['buyUserId'] !== $this->userId) {
            return show(0);
        }
        
        #收货地址信息
        $addressInfo = UserAddress::get(['addressId' => $idleDealInfo['addressId']]);
        #物品详情信息
       
        $idleInfo = IdleInfo::where(['idleInfoId' => $idleDealInfo['idleInfoId']])->field(['title','price','picPath','description','freightFee'])->find();
        try {
            $idleInfo['picArr'] = explode(',', $idleInfo['picPath']);
        } catch (\Throwable $th) {
            $idleInfo['picArr'] = [];
        }
        $skuStockInfo = IdleInfoSkuStock::get(['skuStockId'=>$idleDealInfo['skuStockId']]);
        $skuInfo = [
            'freight'=>$skuStockInfo['freight'],
            'pic'=>$skuStockInfo['pic'],
            'price'=>$skuStockInfo['price'],
        ];
        //         'spData'=>[
        //             ['key'=>'颜色','value'=>'红色',],
        //             ['key'=>'容量','value'=>'500ml',],
        //         ],
        $skuInfo['spData'] = unserialize($skuStockInfo['spData']);
        
       
        #退款
        $IdleDealRefundData = IdleDealRefund::where(['idleDealId' => $idleDealId,'status' => ['in',[1,2]]])->field(['idleDealRefundId'])->find();
        if (!$IdleDealRefundData) {
            $refund['isHas'] = 0;
            $refund['info'] = new \stdClass();
        } else {
            $refund['isHas'] = 1;
            $refund['info'] = $IdleDealRefundData;
        }
    
        #纠纷
        $IdleDealDisputeData = IdleDealDispute::where(['idleDealId' => $idleDealId])->order('createTime desc')->find();
        if (!$IdleDealDisputeData) {
            $dispute['isHas'] = 0;
            $dispute['info'] = new \stdClass();
        } else {
            $refund['isHas'] = 1;
            if (!$IdleDealDisputeData['disputeResults']) {
                #没审核
                $IdleDealDisputeData['disputeResults'] = 0;
            }
            $refund['info'] = [
                'disputeResult' => $IdleDealDisputeData['disputeResults'],
                'isWho' => $IdleDealDisputeData['isWho'],
            ];
        }
        #判断是否有纠纷

        $returnResult = [
            'dealStats' => $idleDealInfo['dealStats'],
            'dealSn' => $idleDealInfo['dealSn'],
            'remark' => $idleDealInfo['remark'],
            'price' => $idleDealInfo['price'],
            'logistics' => $idleDealInfo['logistics'],
            'logisticsNum' => $idleDealInfo['logisticsNum'],
            'payTime' => date("Y-m-d H:i:s", $idleDealInfo['payTime']),
            'createTime' => date("Y-m-d H:i:s", $idleDealInfo['createTime']),
            'addressInfo' => $addressInfo,
            'idleInfo' => $idleInfo,
            'skuInfo' => $skuInfo,
            'IdleDealRefundData' => $refund,
            'IdleDealDisputeData' => $dispute,
        ];

        return show(1, $returnResult);
    }
}
