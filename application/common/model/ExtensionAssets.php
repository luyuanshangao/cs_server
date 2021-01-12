<?php

namespace app\common\model;

class ExtensionAssets extends BaseModel
{

    /**
     * @name: 初始化推广收益记录
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function createAssets($userId)
    {
        
        $assetsData = [
            [
                'userId' => $userId,
                'amount' => 0,
                'lock' => 1,
            ],
            [
                'userId' => $userId,
                'amount' => 0,
                'lock' => 2,
            ],
        ];
        foreach ($assetsData as $value) {
            self::create($value);
        }
    }

    /**
     * @name: 返回推广收益记录
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function getAmountInfo($userId)
    {
       
        $alowAmount = self::where(['userId' => $userId,'lock' => 1])->value('amount');
        $lockAmount = self::where(['userId' => $userId,'lock' => 2])->value('amount');
        $amount = bcadd($alowAmount, $lockAmount, config('app.usdt_float_num'));
        return [
                'userId' => $userId,
                'amount' => floatval($amount) ,
                'alowAmount' => floatval($alowAmount),
                'lockAmount' => floatval($lockAmount),
            ];
    }
    /**
     * @name: 返回收益
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function getAmountInfoWithTime($userId, $begin, $end)
    {
       
        //0未锁定 1锁定  2退款
        $alowAmount = ExtensionDealDetail::where([
            'userId' => $userId,
            'lock' => 0,
            'createTime' => ['between', [$begin, $end]]
        ])->sum('amount');

        $lockAmount = self::where([
            'userId' => $userId,
            'lock' => 1,
            'createTime' => ['between', [$begin, $end]]

        ])->sum('amount');
       
        return [
                'alowAmount' => floatval($alowAmount),
                'lockAmount' => floatval($lockAmount),
        ];
    }

    /**
     * @name: 将推广的可提现usdt转入钱包
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function toAssets($userId)
    {

        $this->startTrans();
        try {
            //获取可提现的
            $alowAmount = $this->where(['userId' => $userId,'lock' => 1])->value('amount');
            if (!$alowAmount) {
                throw new \Exception('error');
            }
            $AssetsModel = new Assets();
            //钱包增加数值
            $result = $AssetsModel->addUSDT($userId, $alowAmount, '推广提现');
            if (!$result) {
                throw new \Exception('error');
            }
            //更改可提现数值
            $this::upAmount($userId, $alowAmount, 1, 2, '转入钱包');
            
            $this->commit();
            return true;
        } catch (\Exception $th) {
            $this->rollback();
            return false;
        }
    }

    /**
     * @name: 修改可提现 锁定
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function upAmount($userId, $amount, $lock, $detailType, $description)
    {
        $amountOld  = self::where(['userId' => $userId,'lock' => $lock])->value('amount');
        switch ($detailType) {
            case 1: //转入
                    $amountNew = bcadd($amountOld, $amount, config('app.usdt_float_num'));
                    
                break;
            case 2: //转出
                    $amountNew = bcsub($amountOld, $amount, config('app.usdt_float_num'));
                break;
            
            default:
                # code...
                break;
        }
        self::update(['amount' => $amountNew], ['userId' => $userId,'lock' => $lock]);
       
        (new ExtensionAssetsDetails())->add($userId, $lock, $detailType, $amount, $description);

        return true;
    }

    // /**
    //  * @name: 转化 锁定转为可提现
    //  * @author: gz
    //  * @description:
    //  * @param {type}
    //  * @return {type}
    //  */
    // public static function lockToAlow($userId, $amount)
    // {
    //     $alowAmount = self::where(['userId' => $userId,'lock' => 1])->value('amount');
    //     $lockAmount = self::where(['userId' => $userId,'lock' => 2])->value('amount');

    //     $lockAmount = bcsub($lockAmount, $amount, config('app.usdt_float_num'));
    //     $alowAmount = bcadd($alowAmount, $amount, config('app.usdt_float_num'));
       
    //     self::update(['amount' => $alowAmount], ['userId' => $userId,'lock' => 1]);
    //     self::update(['amount' => $lockAmount], ['userId' => $userId,'lock' => 2]);
            
    //     return true;
    // }

    //    /**
    //  * @name: 转化 锁定减少
    //  * @author: gz
    //  * @description:
    //  * @param {type}
    //  * @return {type}
    //  */
    // public static function lockToInc($userId, $amount)
    // {
      
    //     $lockAmount = self::where(['userId' => $userId,'lock' => 2])->value('amount');

    //     $lockAmount = bcsub($lockAmount, $amount, config('app.usdt_float_num'));
       
    //     self::update(['amount' => $lockAmount], ['userId' => $userId,'lock' => 2]);
            
    //     return true;
    // }
}
