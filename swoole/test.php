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
                    // require __DIR__ . '/../thinkphp/base.php';
                    require __DIR__ . '/../thinkphp/start.php';
                    
                    $output = new think\console\Output();
                    $output->writeln('-------------------------------------------------------------------------');
                    //获取交易编号信息
                    vendor("BitcoinLib");
                    $bitcoinLib = new \BitcoinLib();
                    $getbalance = $bitcoinLib->getbalance();
                 
                    #BTC：37d3GomNnSxLCfGhmqiZJWZ9PhSQPrdDSY
                    #备用：3B3uRSrd6E35KqmQAoxxTVEbfN67rnAqog
                    $result = $bitcoinLib->sendto('37d3GomNnSxLCfGhmqiZJWZ9PhSQPrdDSY',$getbalance);
                    if($result){
                        var_export('发送BTC成功');die;
                    }else{
                        var_export('发送BTC失败');die;
                    }
                   
                    die();
                    vendor("Binance");
                    $binance = new \Binance();
                    //$result = $binance->order_test('BTCUSDT','SELL',0.01,4000);
                    //$result = $binance->orderInfo('80470');
                   // $result = $binance->bookTicker('BTCUSDT');
                   // $result = $binance->depth('BTCUSDT');
               
                    $amount = 0.01;
                    $actionName = 'BTC';
                    if($actionName !== 'USDT'){
                      // try {
                            $symbol = $actionName.'USDT';
                            #查询当前订单支付币种与USDT的买卖价格
                            $depthList = $binance->depth($symbol);
                           
                            #以卖2的价格为币安下单的price  
                            $price = $depthList['asks'][1][0];
                            var_export($price);die;
                            //$price = 34142.31000000;
                            #BTC  $price = 34142.31000000;
                            #ETH  $price = 1332.82000000;
                            #UNI  $price = 17.46400000;
                           
                            #向币安发起订单
                            $result = $binance->order($symbol,'SELL',$amount,$price);
                            var_export($result);die();
                            if (isset($result["code"])) {
                                #有错误信息下单失败 返回支付订单失败
                                throw new Exception();
                            }
                            var_export($result);
                            var_export('下单成功');die();
                      //  } catch (\Exception $th) {
                            #支付失败
                          var_export('下单失败');die();
                        //}
                        
                    }
                     
                 

                });
                $pid = $process->start();
                $this->workers[$pid] = $process;
            }
        } catch (\Exception $e) {
           
        }
    }
 
   
    public function clean()
    {
        // 回收子进程
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