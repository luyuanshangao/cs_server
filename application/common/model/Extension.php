<?php

namespace app\common\model;

class Extension extends BaseModel
{
    
    public static function getExtension()
    {
        return self::column('extensionName', 'extensionId');
    }
}
