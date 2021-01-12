<?php

namespace app\common\model;

class AssetsWithdraw extends BaseModel
{

    /**
     * @name: 添加提现申请
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function add($userId, $assetsType, $amount, $walletAddr, $commission, $walletImg)
    {
        $data = array();
        $data["withdrawSn"] = $this->makeSn();
        $data["userId"] = $userId;
        $data["assetsType"] = $assetsType;
        $data["amount"] = $amount;
        $data["createTime"] = time();
        $data["withdrawStatus"] = 1;
        $data["finishedTime"] = 0;
        $data["walletAddr"] = $walletAddr;
        $data["receiptId"] = 0;
        $data["receiptTime"] = 0;
        $data["commission"] = $commission;
        $data["walletImg"] = $walletImg;
        $data["withdrawImg"] = "";
        $data["payTime"] = 0;
        $assetsWithdrawStatus = $this->insert($data);
        if ($assetsWithdrawStatus) {
            return $data["withdrawSn"];
        } else {
            return false;
        }
    }

    

    /**
     * @name: 生成提现编号
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    private function makeSn()
    {
        $str = "";
        while (true) {
            $str = date("Ymd", time());
            $str = $str . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);

            $isExist = $this->where(array("withdrawSn" => $str))->find();
            if (!$isExist) {
                break;
            }
        }
        return $str;
    }
}
