<?php

namespace app\common\model;

use think\Model;

class Bitcoin extends Model
{
    /**
     * 获取未确认订单列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUnConfirmList()
    {
        $bitcoinList = $this->where("checkTime", "<", (time() - 300))->where("confirmations", "<", "6")->select();
        return $bitcoinList;
    }

    /**
     * 通过txid获取订单信息
     * @param $txid
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfoByTxid($txid)
    {
        $info = $this->where("txid", "=", $txid)->find();
        return $info;
    }

    /**
     * 通过txid更改订单确认次数
     * @param $txid
     * @param $confirmations
     * @return bool
     */
    public function changeConfirmationsByTxid($txid, $confirmations)
    {
        db("bitcoin")->where("txid", "=", $txid)->setField("confirmations", $confirmations);
        return true;
    }

    public function changeStatusByTxid($txid, $status)
    {
        $this->where("txid", "=", $txid)->setField("status", $status);
        return true;
    }

    public function getStatusByTxid($txid)
    {
        $status = $this->where("txid", "=", $txid)->value("status");
        return $status;
    }

    /**
     * 添加订单信息
     * @param $txid
     * @param $address
     * @param $amount
     * @param $confirmations
     * @return bool
     */
    public function add($txid, $address, $amount, $confirmations)
    {
        $data = array();
        $data["txid"] = $txid;
        $data["time"] = time();
        $data["address"] = $address;
        $data["amount"] = $amount;
        $data["confirmations"] = $confirmations;
        $data["checkTime"] = time();
        $data["status"] = 0;
        $this->insert($data);
        return true;
    }
}
