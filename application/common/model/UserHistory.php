<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

class UserHistory extends BaseModel
{



    /**
     * @name:        是否存在记录
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function isHistory($skuNum, $userId)
    {

        $condition = [
            'userId' => $userId,
            'skuNum' => $skuNum,
        ];
        return $this::get($condition);
    }

    /**
     * @name:        添加记录
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function add($createArr)
    {
 
            $this::create($createArr);
    }

    /**
     * @name:        删除记录
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function del($skuNumArr = 1, $userId)
    {

        if ($skuNumArr) {
            $condition = [
                'skuNum' => ['in',$skuNumArr],
                'userId' => $userId
            ];
        } else {
            $condition = [
                'userId' => $userId
            ];
        }

        $this->where($condition)->delete();
    }


    /**
     * @name:        返回记录数
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getNum($userId)
    {
        return self::where(['userId' => $userId])->count();
    }
}
