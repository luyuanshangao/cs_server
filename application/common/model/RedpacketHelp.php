<?php

namespace app\common\model;

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
}
