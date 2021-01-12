<?php

/*
 * @Descripttion:
 * @Author: gz
 */


namespace app\common\model;

class UserAddress extends BaseModel
{

    /**
     * @name:        设置默认
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function setDefault($userId, $dataArr)
    {
    }

    /**
     * @name:        获取默认
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getDefault($userId)
    {
        return self::get([
            'userId'=>$userId,
            'isDefault'=>1,
        ]);
    }

    /**
     * @name:        添加
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function add($userId, $dataArr)
    {
         //修改之前的默认地址
        if ($dataArr['isDefault']) {
            $condition = [
                'userId' => $userId,
                'isDefault' => 1,
            ];
            $obj = $this->get($condition);
            if ($obj) {
                $obj->isDefault = 0;
                $obj->save();
            }
        }
       
        $createArr = [
            'userId' => $userId,
            'userName' => $dataArr['userName'],
            'userPhone' => $dataArr['userPhone'],
            'areaIdPath' => $dataArr['areaIdPath'],
            'areaName' =>  $dataArr['areaName'] ,
            'addressDetails' => $dataArr['addressDetails'],
            'isDefault' => $dataArr['isDefault'],
            'createTime' => time(),
            'updateTime' => time(),
        ];
        $result = $this::create($createArr);
        return $result->addressId;
    }

    /**
     * @name:        修改
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function edit($userId, $dataArr)
    {
        //修改之前的默认地址
        if ($dataArr['isDefault']) {
            $condition = [
                'userId' => $userId,
                'isDefault' => 1,
            ];
            $obj = $this->get($condition);
            if ($obj) {
                $obj->isDefault = 0;
                $obj->save();
            }
        }
        $condition  = [
            'addressId' => $dataArr['addressId'],
            'userId' => $userId
        ];
        $createArr = [
            'userId' => $userId,
            'userName' => $dataArr['userName'],
            'userPhone' => $dataArr['userPhone'],
            'areaIdPath' => $dataArr['areaIdPath'],
            'areaName' => $dataArr['areaName'],
            'addressDetails' => $dataArr['addressDetails'],
            'isDefault' => $dataArr['isDefault'],
            'updateTime' => time(),
        ];
        $this::update($createArr, $condition);
    }

    /**
     * @name:        删除
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function del($userId, $dataArr)
    {


        $condition = [
            'userId' => $userId,
            'addressId' => $dataArr['addressId']
        ];

        $this::update(['del' => 1], $condition);
    }
}
