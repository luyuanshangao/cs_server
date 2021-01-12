<?php

namespace app\common\model;

class ExtensionGloryDetails extends BaseModel
{
    
    public static function addDetails($userId, $detailType, $gloryAmount, $description)
    {

        self::create([
            'userId' => $userId,
            'detailType' => $detailType,
            'gloryAmount' => $gloryAmount,
            'description' => $description,
        ]);
    }
}
