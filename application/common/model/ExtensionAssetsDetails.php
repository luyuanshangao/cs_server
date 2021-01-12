<?php

namespace app\common\model;

class ExtensionAssetsDetails extends BaseModel
{
    public function getdateStrAttr($value, $data)
    {
        return date('Y-m-d H:i:s', $data['createTime']);
    }
 /**
     * @name:        添加钱包记录
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function add($userId, $lock, $detailType, $amount, $description)
    {
        $data = array();
        $data["userId"] = $userId;
        $data["lock"] = $lock;
        $data["detailType"] = $detailType;
        $data["amount"] = $amount;
        $data["createTime"] = time();
        $data["description"] = $description;
        $detailStatus = $this->insert($data);
        return $detailStatus;
    }
}
