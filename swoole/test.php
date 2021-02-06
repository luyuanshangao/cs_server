<?php

use Swoole\Process;

/**
 * 多进程
 *
 */
class Test
{
    private $workers = [];

    /**
     * 多个进程生产
     */
    public function doJob()
    {
        try {

            for ($i = 0; $i < 1; $i++) {

                $process = new Process(function ($worker) {
                    define('IS_CLI', false);
                    define('APP_PATH', __DIR__ . '/../application/');
                    # require __DIR__ . '/../thinkphp/base.php';
                    require __DIR__ . '/../thinkphp/start.php';

                    $output = new think\console\Output();
                    $output->writeln('-------------------------------------------------------------------------');
                    //定义接收的
                    $symbol = 'ETH|BTC';
                    $amount = 1;
                    $side = 'BUY';
                    $userId = 18332;
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
                        $output->writeln('交易对非法');
                        die();
                    }
                   
                    #拆分交易对
                  
                    list($assetsType_A, $assetsType_B) = explode('|',$symbol);
                   
                    #根据方向判断余额
                    $assetsModel = new \app\common\model\Assets();
                    switch ($side) {
                        case 'SELL':
                            $assetsAmount = $assetsModel->{'get' . $assetsType_A}($userId);
                            if (bccomp($assetsAmount, $amount,18)  !== -1) {
                                $output->writeln('余额不足');
                                die();
                            }
    
                            break;
                        case 'BUY':
                            $assetsAmount = $assetsModel->{'get' . $assetsType_B}($userId);
                            if (bccomp($assetsAmount, $amount,18)  !== -1) {
                                $output->writeln('余额不足');
                                die();
                            }
                            break;

                        default:
                                $output->writeln('方向错误');
                                die();
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
                    //$result = $binance->order($symbol, $side, bcdiv(floatval(bcmul($amount, 1000000)), 1000000, 8), $price);
                   
                   
                    // if (isset($result["code"])) {
                    //     $newFile = fopen("quickChange_par_error.txt", "a+");
                    //     fwrite($newFile, '闪兑下单失败'.$result['msg'] . PHP_EOL);
                    //     fclose($newFile);
                    //     $output->writeln('闪兑下单失败');
                    //     die();
                    // }
                    #手续费设置 0.2%
                    $commissionPercentage = 0.2;
                    $obtainAmount = $binance->getDepth($symbol, $side, bcmul($amount,(100-$commissionPercentage)/100,8));
                    
                    $assetsModel->{'cost'. ($side=='SELL' ? $assetsType_A : $assetsType_B)}($userId, $amount, "闪兑支出");
                    $assetsModel->{'add'. ($side=='SELL' ? $assetsType_B : $assetsType_A)}($userId, $obtainAmount, "闪兑收入");     
                    die();
                    ##############################【清除无用分类】#################################
                    // $category = new \app\common\model\Category();
                    // $i = 0;
                    // #处理3级
                    // $cla2Class = $category::all(['catClass'=>2]);
                    // foreach ($cla2Class as $key => $value) {
                    //     #删除查不到的
                    //     // $condition =
                    //     // [
                    //     //     "keyword" => '',
                    //     //     "pageIndex" =>1,
                    //     //     "pageSize" => 1,
                    //     //     "sortType" => '', //sale_desc price_asc price_desc
                    //     //     "catId" => $value['catId'],
                    //     // ];
                    //     // $result = \app\api\controller\v1\Vop::GoodsSearch($condition);

                    //     // if(!$result || !$result['hitResult']){
                    //     //     $i +=1;
                    //     //    $value->delete();
                    //     //    $output->writeln('删除：'.$value['name']);
                    //     // }
                    //     #删除没图的
                    //     if(!$value['catImg']){
                    //              $i +=1;
                    //        $value->delete();
                    //        $output->writeln('删除：'.$value['name']);
                    //     }

                    // }
                    // #处理2级
                    // $cla2Class = $category::all(['catClass'=>1]);
                    // foreach ($cla2Class as $key => $value) {
                    //     $data = $category::get(['parentId'=>$value['catId']]);
                    //     if(!$data){
                    //               $i +=1;
                    //        $value->delete();
                    //        $output->writeln('删除：'.$value['name']);
                    //     }
                    // }
                    // $output->writeln('删除：'.$i.'个');
                    //  #处理1级
                    //  $cla2Class = $category::all(['catClass'=>0]);
                    //  foreach ($cla2Class as $key => $value) {
                    //      $data = $category::get(['parentId'=>$value['catId']]);
                    //      if(!$data){
                    //                $i +=1;
                    //         $value->delete();
                    //         $output->writeln('删除：'.$value['name']);
                    //      }
                    //  }
                    //  $output->writeln('删除：'.$i.'个');
                    ##############################【清除无用分类】#################################
                    // ##############################【ETH及其代币转账测试】#################################
                    // vendor("Eth");
                    // $eth = new \ETH();
                    // $ethAddress = '0x84c340509f5d17fda479533e4bff36209670c406';   //用户地址
                    // $minerAddress = '0xd2c18c3d7239e416d6f2725e7df407a41c942941'; //转矿工费地址
                    // $base = '0x62f422C23565eF5bDbaB6AF88ee9809835dc6AD1';         //总收币地址
                    // #获取转矿工费地址ETH数量
                    // $ethAmount = $eth->getBalance($ethAddress);
                    // $ethAmountToTen = hexdec($ethAmount);
                    // var_export($ethAmountToTen);die;
                    // var_export($ethAmount);
                    // $ethAmount = '0x'.dechex($amount*100000000000000000);
                    // $ethAmountToTen = hexdec($ethAmount);
                    // var_export($ethAmount);die();
                    // #获取gas
                    // $estimateGas = $eth->estimateGas($ethAddress,$base,$ethAmount);
                    // $estimateGasToTen =  hexdec($estimateGas); //21000

                    // #获取gas_price
                    // // $gasPrice = $eth->gasPrice();
                    // // $gasPriceToTen = hexdec($gasPrice);
                    // $gasPriceToTen = gastracker() * 1000000000;


                    // #代币转账手续费
                    // $ercGas = 60000;

                    // #判断是否足够
                    // $needUseEth = ($ercGas+$estimateGasToTen) * $gasPriceToTen;

                    // $output->writeln('账户余额ETH：'.bcdiv($ethAmountToTen,1000000000000000000,18));
                    // $output->writeln('转移与耗费共计ETH：'.bcdiv($needUseEth,1000000000000000000,18));

                    // if(bccomp($ethAmountToTen,$needUseEth) == -1){
                    //     $output->writeln('账户【0xd2c18c3d7239e416d6f2725e7df407a41c942941】余额不足以支付手续费');
                    // }

                    // #要转给用户的ETH数量
                    // $ercETHAmount = bcdiv($ercGas * $gasPriceToTen,1000000000000000000,18);
                    // $eth->sendMinerETH($minerAddress,$ethAddress,$ercETHAmount,$gasPriceToTen,$estimateGasToTen);

                    // #ETH转给用户地址

                    // vendor("ERC");
                    // $usdt = new \USDT();    
                    // $usdtAmiunt = $usdt->getBalance($ethAddress);
                    // $gasPrice =  bcdiv($gasPriceToTen, bcpow("10", strval(18), 0), 18);
                    // $txid = $usdt->sendUSDT($ethAddress, $base, floatval($usdtAmiunt), $gasPrice);  




                    // ##############################【ETH及其代币转账测试】#################################


                    // ##################################【BTC转账测试】#####################################
                    // #获取交易编号信息
                    // vendor("BitcoinLib");
                    // $bitcoinLib = new \BitcoinLib();
                    // $getbalance = $bitcoinLib->getbalance();

                    // #BTC：37d3GomNnSxLCfGhmqiZJWZ9PhSQPrdDSY
                    // #备用：3B3uRSrd6E35KqmQAoxxTVEbfN67rnAqog
                    // $result = $bitcoinLib->sendto('37d3GomNnSxLCfGhmqiZJWZ9PhSQPrdDSY', $getbalance);
                    // if ($result) {
                    //     var_export('发送成功');
                    //     die;
                    // } else {
                    //     var_export('发送成功');
                    //     die;
                    // }
                    // ##################################【BTC转账测试】######################################



                    // ##################################【币安测试】######################################
                    // vendor("Binance");
                    // $binance = new \Binance();
                    // #$result = $binance->order_test('BTCUSDT','SELL',0.01,4000);
                    // #$result = $binance->orderInfo('80470');
                    // #$result = $binance->bookTicker('BTCUSDT');
                    // # $result = $binance->depth('BTCUSDT');

                    // $amount = 0.01;
                    // $actionName = 'BTC';
                    // if ($actionName !== 'USDT') {
                    //     # try {
                    //     $symbol = $actionName . 'USDT';
                    //     #查询当前订单支付币种与USDT的买卖价格
                    //     $depthList = $binance->depth($symbol);

                    //     #以卖2的价格为币安下单的price  
                    //     $price = $depthList['asks'][1][0];
                    //     var_export($price);
                    //     die;
                    //     #$price = 34142.31000000;
                    //     #BTC  $price = 34142.31000000;
                    //     #ETH  $price = 1332.82000000;
                    //     #UNI  $price = 17.46400000;

                    //     #向币安发起订单
                    //     $result = $binance->order($symbol, 'SELL', $amount, $price);
                    //     var_export($result);
                    //     die();
                    //     if (isset($result["code"])) {
                    //         #有错误信息下单失败 返回支付订单失败
                    //         throw new Exception();
                    //     }
                    //     var_export($result);
                    //     var_export('下单成功');
                    //     die();
                    //     #  } catch (\Exception $th) {
                    //     #支付失败
                    //     var_export('下单失败');
                    //     die();
                    //     #}

                    // }
                    // ##################################【币安测试】######################################

                });
                $pid = $process->start();
                $this->workers[$pid] = $process;
            }
        } catch (\Exception $e) {
        }
    }


    public function clean()
    {
        # 回收子进程
        while ($res = Process::wait()) {
            echo PHP_EOL, 'Worker Exit, PID: ' . $res['pid'] . PHP_EOL;
        }
    }
}

$stime = microtime(true);
$Test = new Test();
$Test->doJob();
$Test->clean();
$etime = microtime(true);

echo 'exec time : ', round(($etime - $stime), 3), PHP_EOL;
