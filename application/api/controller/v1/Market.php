<?php

namespace app\api\controller\v1;

use app\api\controller\Base;

use app\common\model\Assets;


class Market extends Base
{
  
    
    /**
     * @name: 闪兑
     * @author: gz
     * @description: 
     * @param {*}
     * @return {*}
     */
    public function quickChange() {

        $dataArr  = $this->checkdate('Market', 'post', 'quickChange');
                  //定义接收的
                  $symbol = $dataArr['symbol'];
                  $amount =$dataArr['amount'];
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
                  if(!in_array($symbol,$symbolArr)){
                      return show(0,[],'交易对非法');
                  }
                 
                  #拆分交易对
                
                  list($assetsType_A, $assetsType_B) = explode('|',$symbol);
                 
                  #根据方向判断余额
                  $assetsModel = new \app\common\model\Assets();
                  switch ($side) {
                      case 'SELL':
                          $assetsAmount = $assetsModel->{'get' . $assetsType_A}($userId);
                          if (bccomp($assetsAmount, $amount,18)  !== -1) {
                              return show(0,[],'余额不足');
                          }
  
                          break;
                      case 'BUY':
                          $assetsAmount = $assetsModel->{'get' . $assetsType_B}($userId);
                          if (bccomp($assetsAmount, $amount,18)  !== -1) {
                              return show(0,[],'余额不足');
                          }
                          break;

                      default:
                              return show(0,[],'方向错误');
                              
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
                  $price = $depthList['asks'][1][0]; 
                  #向币安发起订单
                  $result = $binance->order($symbol, $side, bcdiv(floatval(bcmul($amount, 1000000)), 1000000, 8), $price);
                 
                 
                  if (isset($result["code"])) {
                      $newFile = fopen("quickChange_par_error.txt", "a+");
                      fwrite($newFile, '闪兑下单失败'.$result['msg'] . PHP_EOL);
                      fclose($newFile);
                      return show(0,[],'闪兑下单失败');
                     
                  }
                  #手续费设置 0.2%
                  $commissionPercentage = 0.2;
                  $obtainAmount = $binance->getDepth($symbol, $side, bcmul($amount,(100-$commissionPercentage)/100,8));
                  
                  $assetsModel->{'cost'. ($side=='SELL' ? $assetsType_A : $assetsType_B)}($userId, $amount, "闪兑支出");
                  $assetsModel->{'add'. ($side=='SELL' ? $assetsType_B : $assetsType_A)}($userId, $obtainAmount, "闪兑收入");     
                  
                  return show(1);
    }
}