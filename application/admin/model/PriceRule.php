<?php

namespace app\admin\model;

use app\common\model\BaseModel;

class PriceRule extends BaseModel
{

    public function getAdminAttr($value, $data)
    {
        $adminObj = AuthAdmin::get(['adminId' => $data['adminId']]);
        
        return $adminObj ? $adminObj->username : '';
    }
}
