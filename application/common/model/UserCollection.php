<?php

/*
 * @Descripttion:
 * @Author: gz
 */


namespace app\common\model;

class UserCollection extends BaseModel
{


    /**
     * @name:        用户是否收藏
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function isCollection($skuNum, $userId)
    {

        $condition = [
            'userId' => $userId,
            'skuNum' => $skuNum,
        ];
        return self::get($condition);
    }
   
    /**
     * @name:        添加收藏
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function add($data)
    {
        return $this::create($data);
    }

    /**
     * @name:        删除收藏
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
     * @name:        返回收藏数
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
