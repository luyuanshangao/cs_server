<?php

namespace app\admin\model;

use app\common\model\BaseModel;
use app\common\model\ExtensionDealDetail;

class User extends BaseModel
{



    public function getInvitationNumAttr($value, $data)
    {
        $count = ExtensionInvitation::where(['superiorId' => $data['userId']])->count();
        return $count;
    }
    public function getCommissionMoneyAttr($value, $data)
    {
        $sum = ExtensionDealDetail::where(['userId' => $data['userId']])->sum('amount');
        return $sum;
    }
    public function getDealOrderNumAttr($value, $data)
    {
        $sum = Order::where(['userId' => $data['userId'],'orderStatus' => 2])->count();
        return $sum;
    }
    public function getDealOrderMoneyAttr($value, $data)
    {
        $sum = Order::where(['userId' => $data['userId'],'orderStatus' => 2])->sum('totalMoney');
        return $sum;
    }
    public function getUserLvAttr($value, $data)
    {
        $extensionId = ExtensionUser::where(['userId' => $data['userId']])->order('createTime desc')->value('extensionId');
        return $extensionId ? $extensionId : 0;
    }
}
