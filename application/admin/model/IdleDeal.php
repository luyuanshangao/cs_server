<?php

namespace app\admin\model;

use app\common\model\BaseModel;
use think\Db;

class IdleDeal extends BaseModel
{

    /**
     * @name: 根据条件来获取列表的数据的总数
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getViewCount($condition = [])
    {
        if (isset($condition['createTime']) && $condition['createTime']) {
            $condition['IdleDeal.createTime'] = $condition['createTime'];
            unset($condition['createTime']);
        }
        
        return Db::view('IdleDeal', 'userId')
        ->view('User', 'userId', 'User.userId=IdleDeal.sellUserId')
        ->where($condition)
        ->count();
    }

 
    /**
     * @name: 根据来获取列表的数据
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getViewList($condition = [], $from = 0, $size = 10, $order = '')
    {
        if (isset($condition['createTime']) && $condition['createTime']) {
            $condition['IdleDeal.createTime'] = $condition['createTime'];
            unset($condition['createTime']);
        }
        $order = 'IdleDeal.createTime desc';
        $result = Db::view('IdleDeal', 'idleDealId,IdleInfoId,dealSn,sellUserId,buyUserId,remark,idleInfoId,price,dealStats,logistics,logisticsNum,paySell,payTime,sendTime,trueTime,createTime,idleDealDisputeId')
                ->view('User', ['userName'=>'suserName','userId'=>'suserId'], 'User.userId=IdleDeal.sellUserId','LEFT')
                ->view('IdleInfo', 'title,infoSn,picPath,description,price as idlePrice,freightFee,condition', 'IdleInfo.IdleInfoId=IdleDeal.IdleInfoId','LEFT')
                ->view('UserAddress', 'userName as buyUserName,userPhone,areaName,addressDetails', 'UserAddress.addressId=IdleDeal.addressId','LEFT')
                ->view('User Buser',['userName'=>'buserName','userId'=>'buserId'], 'Buser.userId=IdleDeal.buyUserId','LEFT')
                ->view('IdleDealDispute',['disputeSn','fromUserId','toUserId','isWho','disputeType','disputeDescribe','disputeResults','createTime'=>'icreateTime'], 'IdleDeal.idleDealDisputeId=IdleDealDispute.idleDealDisputeId','LEFT')
                ->where($condition)
                ->limit($from, $size)
                ->order($order)
                ->select();
                $disputeType = [
                    1=>'物品未收到 ',
                    2=>'物品存在问题 ',
                    3=>'其他 ',
                ];
        foreach ($result as $key => &$value) {
            try {
               
                $pic =  explode(',',$value['picPath']);;
            } catch (\Throwable $th) {
                $pic = [];
            }
            $value['picPath'] = $pic;
            
            if($value['icreateTime']){
                ($value['icreateTime'] + 86400) < time() ? $value['disputeText'] = '上传举证中' : $value['disputeText'] = '裁决阶段';
               
            }

            if($value['isWho']){
                $value['isWho'] == 'sell' ? $value['iswho'] = '卖家' : $value['iswho'] = '买家';
               
                $value['fromUserId'] == $value['buyUserId'] ? $value['fuserName'] =  $value['buserName'] : $value['fuserName'] =  $value['suserName'];
            } 
           
            if($value['disputeType']){
              $value['disputeTypeName'] = $disputeType[$value['disputeType']];
            }
  
        }

        return $result;
    }
}
