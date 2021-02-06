<?php

namespace app\api\controller\v1;

use app\common\model\Assets;
use app\common\model\AssetsDetails;
use app\common\model\AssetsType;
use app\common\model\AssetsWithdraw;
use app\common\model\Message;
use app\api\controller\Base;

class Wallet extends Base
{


    private $assets;
    private $assetsDetails;
    private $assetsWithdraw;
    private $assetsType;
    private $message;
    private $feeEthAddress = '0x41e76872b4554d4f8a4dbbddf7c7e1cf3adae1c1';
   
    public function __construct()
    {
        parent::__construct();
        $this->assets = new Assets();
        $this->assetsDetails = new AssetsDetails();
        $this->assetsType = new AssetsType();
        $this->assetsWithdraw = new AssetsWithdraw();
        $this->message = new Message();
    }

    /**
     * @name:        资产列表
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function wallet()
    {

        //初始化币安接口
        vendor("Binance");
        $binance = new \Binance();
        //获取资产列表
        $assetsList = $this->assets->assetsTypeList();
        
        //遍历资产并处理
        $assets_data = array();
        $assets_data["USDT"] = 0;
        $assets_data["BTC"] = 0;
        foreach ($assetsList as $k => &$v) {
            switch ($v["assetsName"]) {
                case "BTC":
                    $btc = $this->assets->getBTC($this->userId);
                    $v["amount"] = bcmul('1', $btc, config('app.btc_float_num'));
                    $restUSD = $binance->BTC2USDT($v["amount"]);
                    $v["rateUSD"] =  bcmul('1', $restUSD, config('app.usdt_float_num'));
                    $v['recAddress'] = $this->clientInfo->address;
                    break;
                case "USDT":
                    $usdt = $this->assets->getUSDT($this->userId);
                    $v["amount"] = bcmul('1', $usdt, config('app.usdt_float_num'));
                    $v["rateUSD"] = $v["amount"];
                    $v['recAddress'] =  $this->clientInfo->ethAddress;
                    break;
                case "ETH":
                    $eth = $this->assets->getETH($this->userId);
                    $v["amount"] = bcmul('1', $eth, config('app.eth_float_num'));
                    $rateUSD = $binance->ETH2USDT($v["amount"]);
                    $v["rateUSD"] = bcmul('1', $rateUSD, config('app.usdt_float_num'));
                    $v['recAddress'] = $this->clientInfo->ethAddress;
                    break;
                case "UNI":
                    $uniswap = $this->assets->getUNI($this->userId);
                    $v["amount"] = bcmul('1', $uniswap, config('app.uni_float_num'));
                    $rateUSD = $binance->UNI2USDT($v["amount"]);
                    $v["rateUSD"] = bcmul('1', $rateUSD, config('app.usdt_float_num'));
                    $v['recAddress'] =  $this->clientInfo->ethAddress;
                    break;
                default:
            }
            $v["rateRMB"] =  bcmul(self::$rate, $v["rateUSD"], config('app.float_num'));
            $assets_data["USDT"] += $v["rateUSD"];
        }
        
        $assets_data["RMB"] = bcmul(self::$rate, $assets_data["USDT"], config('app.float_num'));
        $assets_data["BTC"] =  bcmul('1', $binance->USDT2BTC($assets_data["USDT"]), config('app.btc_float_num'));
        $assets_data["assetsList"] = $assetsList;
        return show(1, $assets_data);
    }

    /**
     * @name:        资产明细
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function getAssetsDetails()
    {
        $dataArr  = $this->checkdate('Wallet', 'get', 'getAssetsDetails');
        $AssetsDetailsModel = $this->assetsDetails;
        $this->getPageAndSize($dataArr);
        $condition = [
            'userId' => $this->userId,
        ];
        isset($dataArr['detailType']) ? $condition['detailType'] = $dataArr['detailType'] : '' ;
        isset($dataArr['assetsType']) ? $condition['assetsType'] = $dataArr['assetsType'] : '' ;
        $total = $AssetsDetailsModel->getCount($condition);
        $list = $AssetsDetailsModel->getList($condition, $this->from, $this->size, true, $this->sort);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: 申请提现接口
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function assetsWithdraw()
    {

        $dataArr  = $this->checkdate('Wallet', 'post', 'assetsWithdraw');
        //验证支付密码
        if ($this->clientInfo->payPassWord !== setPassword($dataArr['payPassWord'])) {
            return show(1020);
        }

        $assetsType = $dataArr['assetsType'];
        $amount = $dataArr['amount'];
        $walletAddr = $dataArr['walletAddr'];
        isset($dataArr['walletAddr']) ? $walletImg = $dataArr['walletAddr'] : $walletImg = '' ;

        $assetsTypeExist = $this->assetsType->checkAssetsType($assetsType);
        if (!$assetsTypeExist) {
            return show(1026);
        }

        //检查个人账户金额是否足够提现
        $assetsAmount = 0;
        //计算手续费需收取的btc数量
        //vendor("Binance");
        // $binance = new \Binance();

        // //计算提现手续费抵扣
        // $commissionUSDT = 5;//手续费

      
        // $gas = 0;//旷工费
        // $commission = 0;
        // if ($assetsType == "1") {
        //     $assetsAmount = $this->assets->getBTC($this->userId);
        //     $gas = 0.005;
        //     $commission = $binance->USDT2BTC($commissionUSDT);
        // } elseif ($assetsType == "3") {
        //     $assetsAmount = $this->assets->getETH($this->userId);
        //     $gas = 0.01;
        //     $commission = $binance->USDT2ETH($commissionUSDT);
        // } elseif ($assetsType == "2") {//usdt
        //     $assetsAmount = $this->assets->getUSDT($this->userId);
        //     $gas = 2;
        //     $commission = $commissionUSDT;
        // }
       
        // if ($assetsAmount < ($amount + $commission) && $assetsAmount > $gas) {
        //     return show(1027);
        // }
       
        // if ($$assetsAmount - ($amount + $commission) <= $gas) {
        //     return show(1028);
        // }

        //  //扣除个人资产金额
        // $assetsStatus = false;
        // if ($assetsType == "1") {
        //     $assetsStatus = $this->assets->costBTC($this->userId, $amount, "提现");
        //     $assetsStatus = $this->assets->costBTC($this->userId, $commission, "提现手续费");
        // } elseif ($assetsType == "2") {
        //     $assetsStatus = $this->assets->costETH($this->userId, $amount, "提现");
        //     $assetsStatus = $this->assets->costETH($this->userId, $commission, "提现手续费");
        // } elseif ($assetsType == "3") { //usdt
        //     $assetsStatus = $this->assets->costUSDT($this->userId, $amount, "提现");
        //     $assetsStatus = $this->assets->costUSDT($this->userId, $commission, "提现手续费");
        // } elseif ($assetsType == "4") { //uni
        //     $assetsStatus = $this->assets->costUNI($this->userId, $amount, "提现");
        //     $assetsStatus = $this->assets->costUSDT($this->userId, $commission, "提现手续费");
        // }

        $commission = 0;
        if ($assetsType == "1") {
            $assetsAmount = $this->assets->getBTC($this->userId);
        } elseif ($assetsType == "2") {
            $assetsAmount = $this->assets->getETH($this->userId);
        } elseif ($assetsType == "3") {//usdt
            $assetsAmount = $this->assets->getUSDT($this->userId);
        } elseif ($assetsType == "4") {//uni
            $assetsAmount = $this->assets->getUNI($this->userId);
        }

        if (bccomp($assetsAmount, $amount) < 0) {
            return show(1027);
        }
        //扣除个人资产金额
        $assetsStatus = false;
        if ($assetsType == "1") {
            $assetsStatus = $this->assets->costBTC($this->userId, $amount, "提现");
        } elseif ($assetsType == "2") {
            $assetsStatus = $this->assets->costETH($this->userId, $amount, "提现");
        } elseif ($assetsType == "3") { //usdt
            $assetsStatus = $this->assets->costUSDT($this->userId, $amount, "提现");
        } elseif ($assetsType == "4") { //uni
            $assetsStatus = $this->assets->costUNI($this->userId, $amount, "提现");
        }

        if ($assetsStatus) {
            $withdrawSn = $this->assetsWithdraw->add($this->userId, $assetsType, $amount, $walletAddr, $commission, $walletImg);
            //消息提醒
            $this->message->add($this->userId, "系统通知", "提现申请", $withdrawSn . "订单的提现申请已提交，请查看！");
            return show(1);
        } else {
            return show(0);
        }
    }

    // /**
    //  * @name: eth手续费转入
    //  * @author: gz
    //  * @description:
    //  * @param {*}
    //  * @return {*}
    //  */
    // public function feeTurnForUsdt()
    // {
        
    //     vendor("Eth");
    //     $eth = new \Eth();
    //     //eth地址
    //     $ethAddress = $this->clientInfo['ethAddress'];

    //     //$gasPrice = $eth->bchexdec($eth->eth_gasPrice());
    //     //手续费计算
    //     $weiFeeEth = 21000 * 3 * 1000000000 * 2;
    //     $chargeEth = $eth->wei2eth($weiFeeEth);
    //     //查询ETH余额
    //     $amount = $eth->getBalanceOfAddress($ethAddress);
    //     //转换
    //     $weiBalance = $eth->eth2wei($amount['balance']);

    //     if (intval($weiBalance) < intval($weiFeeEth)) {
    //         //余额小于手续费 转入手续费
    //         try {
    //              $result = $eth->sendETH($this->feeEthAddress, $ethAddress, $chargeEth);
    //         } catch (\Throwable $th) {
    //             return show(1043);
    //         }

    //         if (!$result['result']) {
    //             return show(1044);
    //         }

    //         return show(1, ['address' => $this->clientInfo->ethAddress]);
    //     }
    // }
}
