<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

class BlockNumber extends BaseModel
{
    public static function block(){
        return self::get(['id'=>1])['blockNumber'];
    }
    public static function blockInc(){
        $data = self::get(['id'=>1]);
        $data->blockNumber+=1;
        $data->save();
    }

}
