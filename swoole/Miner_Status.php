<?php 
use Swoole\Process;
/**
 * 多进程
 *
 */
class MinerStatus
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
                    vendor("ERC");
                    #数据库中转账手续费
                    $data = \app\common\model\EthFee::getPendingTransactions();
                    if(!$data){
                        $worker->exit(0);
                    }

                    foreach ($data as $value) {
                        $resultReceipt = $eth->eth_getTransactionReceipt($value['transactionHash']);
                        if(!hexdec($resultReceipt['status'])){
                            continue;
                        }
                        try {
                            switch ($value['assetstype']) {
                                //$base = '0x62f422C23565eF5bDbaB6AF88ee9809835dc6AD1';         //总收币地址
                                case 'USDT':
                                        $usdt = new \USDT();    
                                        $usdtAmount = $usdt->getBalance($value['ethAddress']);
                                        $gasPrice =  bcdiv($value['gas_price'], bcpow("10", strval(18), 0), 18);
                                        
                                        try {
                                            $txid = $usdt->sendUSDT($value['ethAddress'], '0x62f422C23565eF5bDbaB6AF88ee9809835dc6AD1', floatval($usdtAmount), $gasPrice);
                                        } catch (\Exception $th) {
                                            $txid = '';
                                        }      
                                    break;
                                case 'UNI':
                                        $uni = new \UNISWAP();    
                                        $uniAmount = $uni->getBalance($ethAddress);
                                        $gasPrice =  bcdiv($gasPriceToTen, bcpow("10", strval(18), 0), 18);
                                        try {
                                            $txid = $uni->sendUNI($ethAddress, '0x62f422C23565eF5bDbaB6AF88ee9809835dc6AD1', floatval($uniAmount), $gasPrice); 
                                        } catch (\Exception $th) {
                                            $txid = '';
                                        }
                                        
                                    break;
                                default:
                                    # code...
                                    break;
                            }
                            if($txid){
                                \app\common\model\EthFee::closeTransactionsByHx($value['transactionHash']);
                            }
                        } catch (\Exception $th) {
                            continue;
                        }
                         
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
$MinerStatus = new MinerStatus();
$MinerStatus->doJob();
$MinerStatus->clean();
$etime = microtime(true);
 
echo 'exec time : ', round(($etime - $stime), 3), PHP_EOL;