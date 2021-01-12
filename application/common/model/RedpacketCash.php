<?php

namespace app\common\model;

class RedpacketCash extends BaseModel
{

    public function addData($userId, $amount, $assetsType, $times)
    {
  
        try {
            $createTime = time();
            $createData = [
                'userId' => $userId,
                'amount' => $amount,
                'assetsType' => $assetsType,
                'times' => $times,
                'state' => 0,
                'cashTime' => $createTime,
                'createTime' => $createTime,
            ];
            self::create($createData);
            return true;
        } catch (\Exception $th) {
            return false;
        }
    }

    public static function trueCash($cashId)
    {
        $data = self::get(['cashId' => $cashId]);
        
        if ($data['state'] !== 0) {
            return false;
        }
        $data->state = 1;
        $data->save();
        
        $AssetsModel = new Assets();
        $assetsName = $AssetsModel->type()->where(['assetsType' => $data['assetsType']])->value("assetsName");
        
        $actionName = 'add' . $assetsName;
        $AssetsModel->{$actionName}($data['userId'], $data['amount'], '红包提现');
       
        return true;
    }

    public static function falseCash($cashId)
    {
        self::update(['state' => -1], ['cashId' => $cashId]);
        return true;
    }

    public function getTimeStrAttr($value, $data)
    {
   
        return date('Y-m-d H:i', $data['createTime']);
    }
    public function getAmountFloatvalAttr($value, $data)
    {
   
        return floatval($data['amount']);
    }
    public function getUserNameAttr($value, $data)
    {
        $userName = User::where(['userId' => $data['userId']])->value('userName');
        return $userName;
    }
    public function getIpAttr($value, $data)
    {
        $ip = User::where(['userId' => $data['userId']])->value('registerIp');
        return $ip;
    }
    public function getInvaNumAttr($value, $data)
    {
        $count = RedpacketHelp::where(['userId' => $data['userId'],'times' => $data['times']])->count();
        return $count;
    }
}
