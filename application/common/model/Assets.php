<?php

namespace app\common\model;

use think\Model;

class Assets extends Model
{
    private $BTC_TYPE;
    private $ETH_TYPE;
    private $USDT_TYPE;
    private $UNI_TYPE;
    private $assetsDetails;

    protected function initialize()
    {
        parent::initialize();
        $this->BTC_TYPE = $this->type()->where("assetsName", "=", "BTC")->value("assetsType");
        $this->ETH_TYPE = $this->type()->where("assetsName", "=", "ETH")->value("assetsType");
        $this->USDT_TYPE = $this->type()->where("assetsName", "=", "USDT")->value("assetsType");
        $this->UNI_TYPE = $this->type()->where("assetsName", "=", "UNI")->value("assetsType");
        $this->assetsDetails = new AssetsDetails();
    }

    public function type()
    {
        return $this->belongsTo("assets_type", "assetsType", "assetsType");
    }


    /**
     * 初始化用户钱包
     *
     * @param  $userId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createWallet($userId)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->BTC_TYPE;
        $isExist = $this->where($condition)->find();
        if (!$isExist) {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->BTC_TYPE;
            $data["amount"] = 0;
            $this->insert($data);
        }
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->ETH_TYPE;
        $isExist = $this->where($condition)->find();
        if (!$isExist) {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->ETH_TYPE;
            $data["amount"] = 0;
            $this->insert($data);
        }
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->USDT_TYPE;
        $isExist = $this->where($condition)->find();
        if (!$isExist) {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->USDT_TYPE;
            $data["amount"] = 0;
            $this->insert($data);
        }
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->UNI_TYPE;
        $isExist = $this->where($condition)->find();
        if (!$isExist) {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->UNI_TYPE;
            $data["amount"] = 0;
            $this->insert($data);
        }
        return true;
    }

    /**
     * 获取资产类型列表
     *
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function assetsTypeList()
    {
        return $this->type()->select();
    }

    /**
     * 添加BTC数量
     *
     * @param  $userId
     * @param  $amount
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addBTC($userId, $amount, $description)
    {
        $this->startTrans();
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->BTC_TYPE;
        $isExist = $this->where($condition)->find();
        if ($isExist) {
            $status = $this->where($condition)->setInc("amount", $amount);
        } else {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->BTC_TYPE;
            $data["amount"] = $amount;
            $status = $this->where($condition)->insert($data);
        }
        if (!$status) {
            $this->rollback();
            return false;
        }

        $detailStatus = $this->assetsDetails->add($userId, $this->BTC_TYPE, 1, $amount, $description);
        if (!$detailStatus) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 添加ETH数量
     *
     * @param  $userId
     * @param  $amount
     * @param  $description
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addETH($userId, $amount, $description)
    {
        $this->startTrans();
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->ETH_TYPE;
        $isExist = $this->where($condition)->find();
        if ($isExist) {
            $status = $this->where($condition)->setInc("amount", $amount);
        } else {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->ETH_TYPE;
            $data["amount"] = $amount;
            $status = $this->where($condition)->insert($data);
        }
        if (!$status) {
            $this->rollback();
            return false;
        }
        $data = array();
        $data["userId"] = $userId;
        $data["assetsType"] = $this->ETH_TYPE;
        $data["detailType"] = 1;
        $data["amount"] = $amount;
        $data["createTime"] = time();
        $data["description"] = $description;
        $detailStatus = $this->assetsDetails->insert($data);
        if (!$detailStatus) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 添加USDT数量
     *
     * @param  $userId
     * @param  $amount
     * @param  $description
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function addUSDT($userId, $amount, $description)
    {
        $this->startTrans();
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->USDT_TYPE;
        $isExist = $this->where($condition)->find();
        if ($isExist) {
            $status = $this->where($condition)->setInc("amount", $amount);
        } else {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->USDT_TYPE;
            $data["amount"] = $amount;
            $status = $this->where($condition)->insert($data);
        }
        if (!$status) {
            $this->rollback();
            return false;
        }
        $data = array();
        $data["userId"] = $userId;
        $data["assetsType"] = $this->USDT_TYPE;
        $data["detailType"] = 1;
        $data["amount"] = $amount;
        $data["createTime"] = time();
        $data["description"] = $description;
        $detailStatus = $this->assetsDetails->insert($data);
        if (!$detailStatus) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }
    /**
     * 添加UNI数量
     *
     * @param  $userId
     * @param  $amount
     * @param  $description
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function addUNI($userId, $amount, $description)
    {
        $this->startTrans();
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->UNI_TYPE;
        $isExist = $this->where($condition)->find();
        if ($isExist) {
            $status = $this->where($condition)->setInc("amount", $amount);
        } else {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->UNI_TYPE;
            $data["amount"] = $amount;
            $status = $this->where($condition)->insert($data);
        }
        if (!$status) {
            $this->rollback();
            return false;
        }
        $data = array();
        $data["userId"] = $userId;
        $data["assetsType"] = $this->UNI_TYPE;
        $data["detailType"] = 1;
        $data["amount"] = $amount;
        $data["createTime"] = time();
        $data["description"] = $description;
        $detailStatus = $this->assetsDetails->insert($data);
        if (!$detailStatus) {
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    
    /**
     * 获取BTC数量
     *
     * @param  $userId
     * @return mixed
     */
    public function getBTC($userId)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->BTC_TYPE;
        $amount = $this->where($condition)->value("amount");
        return $amount;
    }

    /**
     * 获取USDT数量
     *
     * @param  $userId
     * @return mixed
     */
    public function getUSDT($userId)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->USDT_TYPE;
        $amount = $this->where($condition)->value("amount");
        return $amount;
    }

    /**
     * 获取ETH数量
     *
     * @param  $userId
     * @return mixed
     */
    public function getETH($userId)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->ETH_TYPE;
        $amount = $this->where($condition)->value("amount");
        return $amount;
    }
    /**
     * 获取UNI数量
     *
     * @param  $userId
     * @return mixed
     */
    public function getUNI($userId)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->UNI_TYPE;
        $amount = $this->where($condition)->value("amount");
        return $amount;
    }

    /**
     * 消费BTC
     *
     * @param  $userId
     * @param  $amount
     * @param  $description
     * @return bool
     * @throws \think\Exception
     */
    public function costBTC($userId, $amount, $description)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->BTC_TYPE;
        $status = $this->where($condition)->setDec("amount", $amount);
        if ($status) {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->BTC_TYPE;
            $data["detailType"] = 2;
            $data["amount"] = $amount;
            $data["createTime"] = time();
            $data["description"] = $description;
            $this->assetsDetails->insert($data);
        }
        return $status;
    }

    /**
     * 消费ETH
     *
     * @param  $userId
     * @param  $amount
     * @param  $description
     * @return bool
     * @throws \think\Exception
     */
    public function costETH($userId, $amount, $description)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->ETH_TYPE;
        $this->where($condition)->setDec("amount", $amount);
        $data = array();
        $data["userId"] = $userId;
        $data["assetsType"] = $this->ETH_TYPE;
        $data["detailType"] = 2;
        $data["amount"] = $amount;
        $data["createTime"] = time();
        $data["description"] = $description;
        $this->assetsDetails->insert($data);
        return true;
    }

    /**
     * 消费USDT
     *
     * @param  $userId
     * @param  $amount
     * @param  $description
     * @return bool
     * @throws \think\Exception
     */
    public function costUSDT($userId, $amount, $description)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->USDT_TYPE;
        $status = $this->where($condition)->setDec("amount", $amount);
        if ($status) {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->USDT_TYPE;
            $data["detailType"] = 2;
            $data["amount"] = $amount;
            $data["createTime"] = time();
            $data["description"] = $description;
            $this->assetsDetails->insert($data);
        }
        return $status;
    }

    /**
     * 消费UNI
     *
     * @param  $userId
     * @param  $amount
     * @param  $description
     * @return bool
     * @throws \think\Exception
     */
    public function costUNI($userId, $amount, $description)
    {
        $condition = array();
        $condition["userId"] = $userId;
        $condition["assetsType"] = $this->UNI_TYPE;
        $status = $this->where($condition)->setDec("amount", $amount);
        if ($status) {
            $data = array();
            $data["userId"] = $userId;
            $data["assetsType"] = $this->UNI_TYPE;
            $data["detailType"] = 2;
            $data["amount"] = $amount;
            $data["createTime"] = time();
            $data["description"] = $description;
            $this->assetsDetails->insert($data);
        }
        return $status;
    }
}
