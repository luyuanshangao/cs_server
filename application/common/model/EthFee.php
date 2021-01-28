<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

class EthFee extends BaseModel
{
    public static function insertData($transactionHash,$from,$to,$amountFee,$gas_price,$rechargeUserId,$rechargeType,$rechargeAount){
        $data = [
            'transactionHash'=>$transactionHash,
            'from'=>$from,
            'to'=>$to,
            'amountFee'=>$amountFee,
            'gas_price'=>$gas_price,
            'rechargeUserId'=>$rechargeUserId,
            'rechargeType'=>$rechargeType,
            'rechargeAount'=>$rechargeAount,
            'status'=>0,
  
        ];
        self::create($data);
        return true;
    }

    public static function getPendingTransactions($ethAddress){
        $data = self::where(['status'=>0,'to'=>$ethAddress])->find();
        if($data){
            return $data->toArray();
        }
        return $data;
    }
    public static function closeTransactionsByHx($Hx){
        $data = self::get(['transactionHash'=>$Hx]);
        if(!$data->status){
            $data->status = 1;
            $data->save();
        }
        
    }

}
