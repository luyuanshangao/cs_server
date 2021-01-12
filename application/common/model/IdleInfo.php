<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

use app\api\controller\v1\Vop;

class IdleInfo extends BaseModel
{

    public function getSellUserAttr($value, $data)
    {
        $userData = User::get(['userId' => $data['userId']]);
        $returnData = [
            'userName' => $userData->userName,
        ];
        return $returnData;
    }
    public function getCollectNumAttr($value, $data)
    {
        $count = IdleCollection::where(['idleInfoId' => $data['idleInfoId']])->count();
        !$count ? $count = 0 : '';
        return $count;
    }
    public function getViewsAttr($value, $data)
    {
        $count = IdleViews::where(['idleInfoId' => $data['idleInfoId']])->value('views');
        !$count ? $count = 0 : '';
        return $count;
    }
    public function getPicArrAttr($value, $data)
    {
        try {
            $pic = explode(',', $data['picPath']);
        } catch (\Throwable $th) {
            $pic = '';
        }
        return $pic;
    }
    public function getFrontCodeAttr($value, $data)
    {
        $condition = [
            'dealStats'=>1,
            'idleInfoId'=>$data['idleInfoId'],
        ];
        $dealData = IdleDeal::get($condition);

        switch (true) {
            #verifyStatus -1 取消审核 0未审核 1已审核 2审核失败
            #groundStatus 1 上架 0下架
            #sellStatus   1 已卖出
 
            case $data['verifyStatus'] === 0:
                    #待审核
                    $frontCode = 1;
                break;
            case $data['verifyStatus'] === 2:
                    #审核失败
                    $frontCode = 2;
                break;
            case $data['verifyStatus'] === 1 && $data['groundStatus'] === 1:
                    #已上架
                    $frontCode = 3;
                break;
            case $data['verifyStatus'] === 1 && $data['groundStatus'] === 0:
                    #已下架
                    $frontCode = 4;
                break;
            case $data['verifyStatus'] === 1 && $data['groundStatus'] === 1 && $data['sellStatus'] === 1 && $dealData:
                    #已拍下
                    $frontCode = 5;
                break;
            case $data['verifyStatus'] === -1:
                #已取消审核
                    $frontCode = 6;
                break;
            
            default:
                # code...
                break;
        }
        return $frontCode;
    }


    /**
     * @name:        添加闲置信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function addInfo($userId, $data)
    {
        
        try {
            $picArr = json_decode($data['picPath'], true);
            $picPath = implode(',', $picArr);
        } catch (\Exception $e) {
            $picPath = '';
        }
        do {
            $infoSn = self::makeInfoSn();
        } while (self::where(['infoSn' => $infoSn ])->count() == 1);
        $createData = [
            'infoSn' => self::makeInfoSn(),
            'userId' => $userId,
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'picPath' => $picPath,
            'freightFee' => 0 ,
            'condition' => $data['condition'],
            'verifyStatus' => 0,
            'isVerify' => 0,
            'sellStatus' => 0,
            'createTime' => time(),
        ];
        $result = self::create($createData);
        if (!$result) {
            return false;
        }
        return $result;
    }

    private static function makeInfoSn()
    {
        $sn = 'X' . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5);
        return $sn;
    }
    /**
     * @name:        获取个人的闲置信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getInfo($userId, $idleInfoId)
    {
      
        $result = self::get([
            'userId' => $userId,
            'idleInfoId' => $idleInfoId,
        ]);
      
        return $result;
    }
    /**
     * @name:        获取个人的闲置信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getInfoBySn($userId, $infoSn)
    {
      
        $result = self::get([
            'userId' => $userId,
            'infoSn' => $infoSn,
        ]);
      
        return $result;
    }
    /**
     * @name:        获取公共闲置信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getDeail($idleInfoId)
    {
      
        $result = self::get([
            'idleInfoId' => $idleInfoId,
        ]);
      
        return $result;
    }


    /**
     * @name: 当前是否在审核中
     * @author: gz
     * @description:
     * @param {*} $idleInfoId
     * @return {*}
     */
    public static function isVerify($idleInfoId)
    {
            $verifyStatus = self::where(['idleInfoId' => $idleInfoId])->value('verifyStatus');
        if ($verifyStatus == 0) {
            return true;
        }
            return false;
    }

    /**
     * @name:取消审核
     * @author: gz
     * @description:
     * @param {*} $idleInfoId
     * @return {*}
     */
    public static function cacalVerify($idleInfoId)
    {
            $result = self::update(['verifyStatus' => '-1'], ['idleInfoId' => $idleInfoId]);
        if ($result) {
            return true;
        }
            return false;
    }

    /**
     * @name:判断上架中
     * @author: gz
     * @description:
     * @param {*} $idleInfoId
     * @return {*}
     */
    public static function isGround($userId, $idleInfoId)
    {
            $groundStatus = self::where()->value('groundStatus');
        if ($groundStatus) {
            return true;
        }
            return false;
    }

   /**
     * @name:修改上下架状态
     * @author: gz
     * @description:
     * @param {*} $idleInfoId
     * @return {*}
     */
    public static function upGround($idleInfoId, $status)
    {
        $result = self::update(['groundStatus' => $status], ['idleInfoId' => $idleInfoId]);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * @name:是否支付审核金
     * @author: gz
     * @description:
     * @param {*} $idleInfoId
     * @return {*}
     */
    public static function isIsVerify($userId, $idleInfoId)
    {
        $result = self::where(['idleInfoId' => $idleInfoId,'userId' => $userId])->value('isVerify');
        if ($result) {
            return true;
        }
        return false;
    }

   /**
     * @name:修改是否支付审核金状态
     * @author: gz
     * @description:
     * @param {*} $idleInfoId
     * @return {*}
     */
    public static function upIsVerify($idleInfoId, $status)
    {
        $result = self::update(['isVerify' => $status], ['idleInfoId' => $idleInfoId]);
        if ($result) {
            return true;
        }
        return false;
    }
}
