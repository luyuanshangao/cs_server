<?php

namespace app\common\model;

class ExtensionGlory extends BaseModel
{
    
    /**
     * @name: 修改数值
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function upAmount($userId, $gloryAmount, $detailType, $description)
    {
        $gloryData = self::get(['userId' => $userId]);
        if (!$gloryData) {
            self::create([
                'userId' => $userId,
                'gloryAmount' => 0,
            ]);
            $amountOld = 0;
        } else {
            $amountOld = $gloryData['gloryAmount'];
        }
         
       
        switch ($detailType) {
            case 1: //转入
                    $amountNew = bcadd($amountOld, $gloryAmount, config('app.usdt_float_num'));
                    
                break;
            case 2: //转出
                    $amountNew = bcsub($amountOld, $gloryAmount, config('app.usdt_float_num'));
                break;
            
            default:
                # code...
                break;
        }
        self::update(['gloryAmount' => $amountNew], ['userId' => $userId]);
        ExtensionGloryDetails::addDetails($userId, $detailType, $gloryAmount, $description);
        return true;
    }
}
