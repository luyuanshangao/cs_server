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
                    vendor("BitcoinLib");
                    $bitcoin = new \BitcoinLib();
                    $result  = $bitcoin->getbestblockhash();
                    $result  = $bitcoin->getblockchaininfo();
                    $result  = $bitcoin->getblockcount();

                    var_export($result);die();
                    $tradeInfo = $bitcoin->tradeInfo('ca53d3482c4b381619d6bb7a6d9b5ed3a0629305124eabf827108ea2a82f79c8');	
      
                    var_export($tradeInfo);
                        


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