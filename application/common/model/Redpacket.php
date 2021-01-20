<?php

namespace app\common\model;

class Redpacket extends BaseModel
{


   


    /**
     * @name: 添加红包
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function addData($userId, $assetsType, $totalNum, $amount, $num, $collection, $boost, $times, $expireTime)
    {
        try {
            $createTime = time();
            $createData = [
                'userId' => $userId,
                'assetsType' => $assetsType,
                'totalNum' => $totalNum,
                'amount' => $amount,
                'num' => $num,
                'collection' => json_encode($collection),
                'boost' => $boost,
                'times' => $times,
                'expireTime' => $createTime + $expireTime,
                'createTime' => $createTime,
            ];
            $data = self::create($createData);
        } catch (\Exception $th) {
            return false;
        }
        
        return true;
    }
  
    /**
     * @name: 红包助力是否已完成
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function openAuth($userId, $times = '')
    {
       
        $times ? $condition['times'] = $times : null;
        $condition['userId'] = $userId;
        $data = self::where($condition)->order('createTime desc')->find();
        if (!$data) { //未开红包
            return $data;
        }
        $countHelpNum  = RedpacketHelp::where([
            'userId' => $userId,
            'times' => $data['times'],
        ])->count();
        if ($countHelpNum == $data['num']) { //助力数等于红包数组数
            return $data;
        }
        return false;
    }


    /**
     * @name: 提现操作
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function cashAmount($userId)
    {

        $result = self::openAuth($userId);
        if (is_bool($result) || !$result) {
            return;
        }
        $RedpacketCash = new RedpacketCash();
        $data = $RedpacketCash->where(['userId' => $userId,'times' => $result['times']])->find();
        if (!$data) {
            $RedpacketCash->addData($userId, $result['totalNum'], $result['assetsType'], $result['times']);
        }
        return;
    }


    /**
     * @name: 添加助力红包
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function addHelp($helpUserId, $userId)
    {

        try {
            //红包数据
            $data = self::where(['userId' => $userId])->order('createTime desc')->find();

            if (!$data) {
                return;
            }
            //红包有效期内
            if ($data['expireTime'] < time()) {
                return;
            }

            $collectionArr = json_decode($data['collection']);
            $countHelpNum  = RedpacketHelp::where([
                'userId' => $userId,
                'times' => $data['times'],
            ])->count();
           
            if (!isset($collectionArr[$countHelpNum])) {
                return;
            }
            
            
            //查找助力
            $RedpacketHelpData = RedpacketHelp::get([ 'userId' => $userId,
            'helpUserId' => $helpUserId]);
            if($RedpacketHelpData){
                return;
            }

            $createData = [
                'userId' => $userId,
                'helpUserId' => $helpUserId,
                'amount' => $collectionArr[$countHelpNum] / $data['boost'],
                'times' => $data['times'],
                'createTime' => time(),
            ];
           
            RedpacketHelp::create($createData);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
