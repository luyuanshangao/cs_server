<?php

namespace app\common\model;

use think\Model;

class AssetsType extends BaseModel
{
    /**
     * 获取资产类型名称
     *
     * @param  $assetsType
     * @return mixed
     */
    public function getAssetsName($assetsType)
    {
        return $this->where("assetsType", "=", $assetsType)->value("assetsName");
    }

    /**
     * 验证资产类型
     *
     * @param  $assetsType
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkAssetsType($assetsType)
    {
        $assetsTypeExist = $this->where(array("assetsType" => $assetsType,"canWithdraw" => 1))->find();
        if ($assetsTypeExist) {
            return true;
        } else {
            return false;
        }
    }
}
