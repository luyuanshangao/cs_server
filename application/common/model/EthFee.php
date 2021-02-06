<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

class EthFee extends BaseModel
{
    public static function insertData($transactionHash, $from, $to, $gasPriceToTen, $userId, $assetstype)
    {
        $data = [
            'transactionHash' => $transactionHash,
            'from' => $from,
            'to' => $to,
            'gas_price' => $gasPriceToTen,
            'userId' => $userId,
            'assetstype' => $assetstype,
            'status' => 0,
        ];
        self::create($data);
        return true;
    }

    public static function getPendingTransactions()
    {
        $data = self::where(['status' => 0])->select();
        return $data;
    }
    public static function closeTransactionsByHx($Hx)
    {
        $data = self::get(['transactionHash' => $Hx]);
        if (!$data->status) {
            $data->status = 1;
            $data->save();
        }
    }
}
