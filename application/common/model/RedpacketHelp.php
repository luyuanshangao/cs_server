<?php

namespace app\common\model;

use think\Cache;

class RedpacketHelp extends BaseModel
{

  
    
    public function alreadyHelpAmount($userId, $times)
    {
        return $this->where(['userId' => $userId,'times' => $times])->sum('amount');
    }
    public function alreadyHelpNum($userId, $times)
    {
        return $this->where(['userId' => $userId,'times' => $times])->count();
    }

    public function getTimeStrAttr($value, $data)
    {
   
        return date('Y/m/d H:i', $data['createTime']);
    }
    public function getUserNameAttr($value, $data)
    {
       
        $userName = User::where(['userId' => $data['helpUserId']])->value('userName');
        
        return $userName ? hideStr($userName) : '';
    }
    public function getUserNameNoAttr($value, $data)
    {
       
        $userName = User::where(['userId' => $data['helpUserId']])->value('userName');
        
        return $userName ;
    }
    public function getAmountFloatvalAttr($value, $data)
    {
   
        return floatval($data['amount']);
    }
    public function getIpAttr($value, $data)
    {
        $registerIp = User::where(['userId' => $data['helpUserId']])->value('registerIp');
        return $registerIp;
    }
    public function getDurationAttr($value, $data)
    {
        $duration = User::where(['userId' => $data['helpUserId']])->value('duration');
        return $duration;
    }
    public function getCreateTimeAttr($value, $data)
    {
        $createTime = User::where(['userId' => $data['helpUserId']])->value('createTime');
        return $createTime;
    }
    public function getAmountFloatAttr($value, $data)
    {
       
        return floatval($data['amount']);
    }


    public function addRedpacketHelp(){




    }

    /**
     * @name:通过被助力userId 查找其是否含有助力验证 有则助力成功
     * @author: gz
     * @description: 
     * @param {*} $userId
     * @return {*}
     */
    public static function isAgainHelp($topData,$endData){
  
         
         if($topData['verifyWordResult'] !== 1){
            return false;
         }
         
         #上一层助力是否需要进行邀请验证
         if($topData['verifyType'] !== 2){
            return false;
         }
         #上一层 被助力userId
         $toHelp = $topData['tohelp'];

         #上一层 助力红包是否还有效
         $checkResult = self::checkEffec($toHelp);
         list($dataRedpacket , $countHelpNum) = $checkResult;
         if(!$checkResult){
             return false;
         }

         
         #更新验证信息
        $expire = $dataRedpacket['expireTime'] - time();
        $topData['intvNum'] -= 1;
        if($expire > 0){
            Cache::set('helpVerify_'.$endData['tohelp'],$topData,$expire);
        }
        
         #上一层 助力红包 需要邀请的人数
         
         if($topData['intvNum'] == 0){
            #为上一层助力成功
            $resultAddHelp = Redpacket::addHelp($endData['tohelp'],$toHelp);
            if(!$resultAddHelp){
                return false;
            }
         }else{
             return false;
         }
        
        return true;
    }
   /**
     * @name: 添加助力需要的验证
     * @author: gz
     * @description:helpUserId助力用户id  userId被助力用户id
     * @param {type}
     * @return {type}
     */
    public static function addHelpVerify($helpUserId, $userId)
    {
       

        $checkResult = self::checkEffec($userId);
        if(!$checkResult){
            return false;
        }
        list($dataRedpacket , $countHelpNum) = $checkResult;

        #判断剩余助力人数 小于10   验证类型
        if( ($dataRedpacket['num'] -$countHelpNum) <= 10 ){
            # 多邀请验证+助记词验证
            $verifyType = 2;
            $intvNum = 1;
        }else{
            # 助记词验证
            $verifyType = 1;
            $intvNum = 0;
        }

        # 要验证的助记词
        $firstWord = random_int(0,11);
        do {
            $secendWord = random_int(0,11);
        } while ($secendWord == $firstWord);
        #要验证的第几个助记词
        $verifyWord = [$firstWord,$secendWord];
        $verifyWordFreq = 3;

        $expire = $dataRedpacket['expireTime'] - time();
        if($expire > 0){
            Cache::set('helpVerify_'.$helpUserId,[
                'tohelp'=>$userId,
                'verifyType'=>$verifyType,
                'intvNum'=>$intvNum,
                'verifyWord'=>$verifyWord,
                'verifyWordFreq'=>$verifyWordFreq,
                'verifyWordResult'=>0,
            ],$expire);
        }
       
        
        return true;

    }

    public static function checkEffec($userId){
         #判断被助力人红包是否有效
         $dataRedpacket = Redpacket::where(['userId'=>$userId])->order('createTime desc')->find();
         if (!$dataRedpacket) {
             return false;
         }
 
         #要助力的红包 过期
         if(time() > $dataRedpacket['expireTime']){
             return false;
         }
 
         #助力数等于红包数组数 助力完成
         $countHelpNum  = self::where([
             'userId' => $userId,
             'times' => $dataRedpacket['times'],
         ])->count();
         
         if ($countHelpNum == $dataRedpacket['num']) {
             return false;
         }
         return [$dataRedpacket,$countHelpNum];
    }
}
