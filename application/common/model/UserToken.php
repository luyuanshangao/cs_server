<?php

namespace app\common\model;

class UserToken extends BaseModel
{
    /**
     * @name:        添加token
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function addToken($dataArr)
    {
        $saveArr  = [
            'token' => $dataArr['token'],
            'expireTimeStamp' => $dataArr['expireTimeStamp'],
            'userId' => $dataArr['userId'],
        ];
        self::create($saveArr);
        return true;
    }
    /**
     * @name:        更新token
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function updateToken($dataArr, $whereArr)
    {
        self::update($dataArr, $whereArr);
        return true;
    }
}
