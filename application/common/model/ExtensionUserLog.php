<?php

namespace app\common\model;

class ExtensionUserLog extends BaseModel
{
    public static function addLog($userId, $extensionId)
    {
        $data = [
            'userId' => $userId,
            'extensionId' => $extensionId,
            'createTime' => time(),
        ];
        self::create($data);
    }

    public static function getNowGrade($userId)
    {
        return self::where(['userId' => $userId])->order('createTime desc')->value('extensionId');
    }
    public static function getUpGradeTime($userId)
    {
        return self::where(['userId' => $userId])->order('createTime desc')->value('createTime');
    }
}
