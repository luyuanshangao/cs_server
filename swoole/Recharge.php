<?php 
use Swoole\Process;
/**
 * 多进程
 *
 */
class Recharge
{
    private $workers = [];
 
   /**
     * 多个进程生产
     */
    public function doJob()
    {
        try {
           
            for ($i = 0; $i < 10; $i++) {
                sleep(6);
                $process = new Process(function ($worker) {
                    define('IS_CLI', false);
                    define('APP_PATH', __DIR__ . '/../application/');
                    // require __DIR__ . '/../thinkphp/base.php';
                    require __DIR__ . '/../thinkphp/start.php';
                    
                    $output = new think\console\Output();
                    $output->writeln('-------------------------------------------------------------------------');
                    #获取数据库中执行到的块
                    $blockNumber = \app\common\model\BlockNumber::block();
                    ##########################################
                    //$blockNumber  = 11761671;
                    $output->writeln('数据库中执行到的块：'.$blockNumber);
                    #获取最新块
                    vendor("Eth");
                    $eth = new \Eth();
                    $latest = hexdec($eth->eth_blockNumber());
                    $output->writeln('获取最新块：'.$latest);
                    #最新块之前才执行
                    if(($latest-1) <= $blockNumber){
                        $output->writeln('最新块之前已执行完毕');
                        $worker->exit(0);
                    }
                        #更新块记录
                        ##########################################
                        \app\common\model\BlockNumber::blockInc();
                        #获取用户数据
                        $userList = \app\common\model\User::userEthAddre();
                        #当前执行查询的块
                        $selectBlockNum  = '0x'.dechex($blockNumber);
                        #此块的交易数量
                        $blockTranNum = hexdec($eth->eth_getBlockTransactionCountByNumber($selectBlockNum));
                        $output->writeln('当前查询块的交易数量：'.$blockTranNum);
                        
                        #交易数据解析
                        for ($i=0; $i < $blockTranNum; $i++) { 
                            $output->writeln('处理第 '.($i+1).' 笔交易中');
                            try {
                                #根据index查询对应交易数据
                                $data = $eth->eth_getTransactionByBlockNumberAndIndex($selectBlockNum,'0x'.dechex($i));
                                // if($i !== 30){
                                //     continue;
                                // }
                                #判断是否直接是eth交易
                                if($data['input'] !== '0x'){
                                    
                                    //代币交易
                                    if($data['to'] == '0xdac17f958d2ee523a2206206994597c13d831ec7'){
                                        #usdt交易
                                        $output->writeln('  交易类型【 USDT 】');
                                        $assetsType = 'USDT';
                                        $assetsTypeFunc = 'addUSDT';
                                        $multiple = 1000000;
                                    }elseif($data['to'] == '0x1f9840a85d5af5bf1d1762f925bdaddc4201f984'){
                                        #uni交易
                                        $output->writeln('  交易类型【 UNI 】');
                                        $assetsType = 'UNI';
                                        $assetsTypeFunc = 'addUNI';
                                        $multiple = 1000000000000000000;
                                    }else{
                                        $output->writeln('  交易类型不明已跳过...');
                                        continue;
                                    }
                                    
                                    $resultReceipt = $eth->eth_getTransactionReceipt($data['hash']);
                                    $tranData = end($resultReceipt['logs']);
                                    $toAddress = '0x'.substr($tranData['topics'][2],26,46);
                                    $output->writeln('  交易至地址：'.$toAddress);
                                    #用户地址中查找对应的  充值地址等于用户地址 并且状态为1
                                    $userId = array_search($toAddress,$userList);
                                    
                                    if($userId && hexdec($resultReceipt['status'])){
                                            #充值的数量
                                           
                                            $balance = floatval(bcdiv(hexdec($tranData['data']),$multiple,8));
                                            $output->writeln('地址：'.$toAddress.' 充值'.$assetsType.'：'.$balance.'用户Id：'.$userId);
                                           
                                            #为用户添加对应数量
                                            $AssetsMpdel = new \app\common\model\Assets();
                                            $AssetsMpdel->$assetsTypeFunc(18480,$balance,$assetsType."充值");     
                                            \app\common\model\Message::add($userId, '资产变动通知', '充值成功', '您充值的'.$assetsType.'已到账，请查看！');       
                                    }else{
                                        $output->writeln('  此交易目的地址不属于用户跳过处理');
                                    }
                                    
                                }else{
                                    //eth充值
                                    $output->writeln('  交易类型【 ETH 】');
                                    $toAddress = $data['to'];
                                    $output->writeln('  交易至地址：'.$toAddress);
                                    $userId = array_search($toAddress,$userList);
                                    $resultReceipt = $eth->eth_getTransactionReceipt($data['hash']);
                                    if($userId && hexdec($resultReceipt['status'])){
                                        #充值的数量
                                        $balance = floatval(bcdiv(hexdec($data['value']),1000000000000000000,6));
                                        $output->writeln('地址：'.$toAddress.' 充值ETH：'.$balance.'用户Id：'.$userId);
                                        #为用户添加对应数量
                                        $AssetsMpdel = new \app\common\model\Assets();
                                        $AssetsMpdel->addETH(18480, $balance, "ETH充值");
                                        \app\common\model\Message::add($userId, '资产变动通知', '充值成功', '您充值的ETH已到账，请查看！');      
                                    }else{
                                        $output->writeln('  此交易目的地址不属于用户跳过处理');
                                    }
                                    
                                
                                }
                            } catch (\Exception $th) {
                                $output->writeln('【执行错误，已跳过此笔：'.$blockNumber.'】');
                                continue;
                                
                            }
                        
                            
                        }
                        

                        $output->writeln('完成块交易查询：'.$blockNumber);
                        


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
$Recharge = new Recharge();
$Recharge->doJob();
$Recharge->clean();
$etime = microtime(true);
 
echo 'exec time : ', round(($etime - $stime), 3), PHP_EOL;