<?php

namespace app\common\model;

class AssetsDetails extends BaseModel
{


    /**
     * @name:        添加钱包记录
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function add($memberId, $assetsType, $detailType, $amount, $description)
    {
        $data = array();
        $data["userId"] = $memberId;
        $data["assetsType"] = $assetsType;
        $data["detailType"] = $detailType;
        $data["amount"] = $amount;
        $data["createTime"] = time();
        $data["description"] = $description;
        $detailStatus = $this->insert($data);
        return $detailStatus;
    }
}
