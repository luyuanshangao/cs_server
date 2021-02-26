<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\Rate;

class Market extends Base
{

    protected $noAuthArr = ['exchangeRate'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单
    
    /**
     * @name: 闪兑
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function quickChange()
    {

        $dataArr  = $this->checkdate('Market', 'post', 'quickChange');
        #定义接收的
        $symbol = $dataArr['symbol'];
        $amount = $dataArr['amount'];
        $side = $dataArr['side'];
        $userId = $this->userId;
        #允许交易对
        $symbolArr = [
            'ETH|BTC',
            'UNI|BTC',
            'BTC|USDT',
            'ETH|USDT',
            'UNI|USDT',
        ];
        #判断交易对
        if (!in_array($symbol, $symbolArr)) {
            return show(0, [], '交易对非法');
        }

        #拆分交易对

        list($assetsType_A, $assetsType_B) = explode('|', $symbol);

        #限制
        if($assetsType_B == 'BTC' && bccomp($amount,0.01,8) == -1){
            return show(0,[],'交易数量过小');
        }
        if($assetsType_A == 'ETH' && bccomp($amount,0.2,8) == -1){
            return show(0,[],'交易数量过小');
        }
        if($assetsType_B == 'USDT' && bccomp($amount,50,8) == -1){
            return show(0,[],'交易数量过小');
        }
        if($assetsType_A == 'UNI' && bccomp($amount,1.2,8) == -1){
            return show(0,[],'交易数量过小');
        }
        #根据方向判断余额
        $assetsModel = new \app\common\model\Assets();
        switch ($side) {
            case 'SELL':
                $assetsAmount = $assetsModel->{'get' . $assetsType_A}($userId);
                if (bccomp($assetsAmount, $amount, 18)  !== -1) {
                    return show(0, [], '余额不足');
                }

                break;
            case 'BUY':
                $assetsAmount = $assetsModel->{'get' . $assetsType_B}($userId);
                if (bccomp($assetsAmount, $amount, 18)  !== -1) {
                    return show(0, [], '余额不足');
                }
                break;

            default:
                return show(0, [], '方向错误');

                break;
        }
        #币安下单
        vendor("Binance");
        $binance = new \Binance();
        #拼接交易对
        $symbol = $assetsType_A . $assetsType_B;
        #查询当前订单支付币种与USDT的买卖价格
        $depthList = $binance->depth($symbol);
        #以卖2的价格为币安下单的price
        $side == 'SELL' ? $price = $depthList['asks'][2][0] : $price = $depthList['bids'][2][0] ;
        #向币安发起订单
        $result = $binance->order($symbol, $side, bcdiv(floatval(bcmul($amount, 1000000)), 1000000, 8), $price);


        if (isset($result["code"])) {
            $newFile = fopen("quickChange_par_error.txt", "a+");
            fwrite($newFile, '闪兑下单失败' . $result['msg'] . PHP_EOL);
            fclose($newFile);
            return show(0, [], '闪兑下单失败');
        }
        #手续费设置 0.1%
        $commissionPercentage = 0.1;
        $obtainAmount = $binance->getDepth($symbol, $side, bcmul($amount, (100 - $commissionPercentage) / 100, 8));

        $assetsModel->{'cost' . ($side == 'SELL' ? $assetsType_A : $assetsType_B)}($userId, $amount, "闪兑支出");
        $assetsModel->{'add' . ($side == 'SELL' ? $assetsType_B : $assetsType_A)}($userId, $obtainAmount, "闪兑收入");

        return show(1);
    }


    /**
     * @name: 汇率计算器接口
     * @author: gz
     * @description:  
     * @param {*} type1 待换算币种   type2 换算目标币种   amount 待换算币种数量
     * @return {*}
     */
    public function exchangeRate()
    {

        $dataArr  = $this->checkdate('Market', 'post', 'exchangeRate');
        #定义接收的
        $type1 = $dataArr['type1'];
        $type2 = $dataArr['type2'];
        $amount = $dataArr['amount'];
   
        vendor("Binance");
        $binance = new \Binance();
        $RateModel =  new Rate();
        $rate = $RateModel->getRate();
        $exchangeAmount = 0;

        switch ($type1) {
            case "CNY":
                switch ($type2) {
                    case "CNY":
                        $exchangeAmount = $amount;
                        break;
                    case "BTC":
                        $usd = $amount /$rate;
                        $exchangeAmount = $binance->USDT2BTC($usd);
                        break;
                    case "USDT":
                        $exchangeAmount = $amount /$rate;
                        break;
                    case "ETH":
                        $usd = $amount /$rate;
                        $exchangeAmount = $binance->USDT2ETH($usd);
                        break;
                    case "UNI":
                        $usd = $amount /$rate;
                        $exchangeAmount = $binance->USDT2UNI($usd);
                        break;
                }
                break;
            case "BTC":
                switch ($type2) {
                    case "CNY":
                        $usd = $binance->BTC2USDT($amount);
                        $exchangeAmount =$rate * $usd;
                        break;
                    case "BTC":
                        $exchangeAmount = $amount;
                        break;
                    case "USDT":
                        $exchangeAmount = $binance->BTC2USDT($amount);
                        break;
                    case "ETH":
                        $usd = $binance->BTC2USDT($amount);
                        $exchangeAmount = $binance->USDT2ETH($usd);
                        break;
                    case "UNI":
                        $exchangeAmount = $binance->BTC2UNI($amount);
                        break;
                }
                break;
            case "USDT":
                switch ($type2) {
                    case "CNY":
                        $exchangeAmount =$rate * $amount;
                        break;
                    case "BTC":
                        $exchangeAmount = $binance->USDT2BTC($amount);
                        break;
                    case "USDT":
                        $exchangeAmount = $amount;
                        break;
                    case "ETH":
                        $exchangeAmount = $binance->USDT2ETH($amount);
                        break;
                    case "UNI":
                        $exchangeAmount = $binance->USDT2UNI($amount);
                        break;
                }
                break;
            case "UNI":
                switch ($type2) {
                    case "CNY":
                        $usd = $binance->UNI2USDT($amount);
                        $exchangeAmount =$rate * $usd;
                        break;
                    case "BTC":
                        $usd = $binance->UNI2USDT($amount);
                        $exchangeAmount = $binance->USDT2BTC($amount);
                        break;
                    case "USDT":
                        $exchangeAmount = $binance->UNI2USDT($amount);
                        break;
                    case "ETH":
                        $usd = $binance->UNI2USDT($amount);
                        $exchangeAmount = $binance->USDT2ETH($amount);
                        break;
                    case "UNI":
                        $exchangeAmount =  $amount;
                        break;
                }
                break;
            case "ETH":
                switch ($type2) {
                    case "CNY":
                        $usd = $binance->ETH2USDT($amount);
                        $exchangeAmount =$rate * $usd;
                        break;
                    case "BTC":
                        $usd = $binance->ETH2USDT($amount);
                        $exchangeAmount = $binance->USDT2BTC($usd);
                        break;
                    case "USDT":
                        $exchangeAmount = $binance->ETH2USDT($amount);
                        break;
                    case "ETH":
                        $exchangeAmount = $amount;
                        break;
                    case "UNI":
                        $usd = $binance->ETH2USDT($amount);
                        $exchangeAmount = $binance->USDT2UNI($amount);
                        break;
                }
                break;
        }
        $data = array();
        $data["exchangeAmount"] = $exchangeAmount;
        return show(1,$data);
    }

}
