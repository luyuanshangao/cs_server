<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

use app\api\controller\v1\Vop;
use think\Db;

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

        switch (true) {
            #verifyStatus -1 取消审核 0未审核 1已审核 2审核失败
            #groundStatus 1 上架 0下架
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
            case $data['verifyStatus'] === -1:
                #已取消审核
                    $frontCode = 5;
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
        try {
            $desPicPathArr = json_decode($data['desPicPath'], true);
            $desPicPath = implode(',', $desPicPathArr);
        } catch (\Exception $e) {
            $desPicPath = '';
        }
        
        $createData = [
            'userId' => $userId,
            'title' => $data['title'],
            'description' => $data['description'],
            'desPicPath' => $desPicPath,
            'picPath' => $picPath,
            'verifyStatus' => 0,
            'isVerify' => 0,
            'groundStatus' => 0,
            'createTime' => time(),
        ];
   
        try {
            Db::startTrans();
            $resultA = self::create($createData);
           
            $resultB = IdleInfoSkuStock::_create($data['skuData'],$resultA['idleInfoId']);

            if (!$resultA || !$resultB) {
                throw new \Exception("Error Processing Request", 1);
            }
            Db::commit();
        } catch (\Exception $th) {
            Db::rollback();
           return false;
        }
        return true;
    }

    public static function updateInfo($IdleInfo,$dataArr){

        try {
            $picArr = json_decode($dataArr['picPath'], true);
            $picPath = implode(',', $picArr);
        } catch (\Exception $e) {
            $picPath = '';
        }
        try {
            $desPicPathArr = json_decode($dataArr['desPicPath'], true);
            $desPicPath = implode(',', $desPicPathArr);
        } catch (\Exception $e) {
            $desPicPath = '';
        }

        $updateData = [
            'title'=> $dataArr['title'], 
            'description'=> $dataArr['description'], 
            'desPicPath' => $desPicPath,
            'title'=> $dataArr['title'], 
            'verifyStatus'=> 0, 
            'picPath'=>$picPath, 
        ];
     
        //审核失败 不需要支付审核金
        $IdleInfo['verifyStatus'] == 2 ? $updateData['isVerify']= 0 : $updateData['isVerify']= 1;
        try {
            Db::startTrans();
            $resultA = self::update($updateData,['idleInfoId'=>$dataArr['idleInfoId']]);
            $num = IdleInfoSkuStock::destroy(['idleInfoId'=>$dataArr['idleInfoId']]);
            $resultB = IdleInfoSkuStock::_create($dataArr['skuData'],$dataArr['idleInfoId']);
            if (!$resultA || !$resultB) {
                throw new \Exception("Error Processing Request", 1);
            }
            Db::commit();
        } catch (\Exception $th) {
           Db::rollback();
           return false;
        }
    
        return true;
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
    public static function getInfoById($userId, $idleInfoId)
    {
      
        $result = self::get([
            'userId' => $userId,
            'idleInfoId' => $idleInfoId,
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
