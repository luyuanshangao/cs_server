<?php 
use Swoole\Process;
/**
 * 多进程
 *
 */
class MsgProcess
{
    private $workers = [];
 
   /**
     * 生产
     */
    public function produNotice()
    {

            $process = new Process(function () {
                define('IS_CLI', false);
                define('APP_PATH', __DIR__ . '/../application/');
                // require __DIR__ . '/../thinkphp/base.php';
                require __DIR__ . '/../thinkphp/start.php';
                
                while(true){
                    \app\queue\common\NoticeLib::getGoodsMessage();
                    sleep(2);
                }
            });
            $process->daemon();
            $pid = $process->start();
            $this->workers[$pid] = $process;

    }
 
    /**
     * 多个进程消费消息
     */
    public function execNotice()
    {
           
            for ($i = 0; $i < 5; $i++) {
                
                $process = new Process(function ()  {
                    define('IS_CLI', false);
                    define('APP_PATH', __DIR__ . '/../application/');
                    // require __DIR__ . '/../thinkphp/base.php';
                    require __DIR__ . '/../thinkphp/start.php';
                    while(true){
                        \app\queue\common\NoticeLib::run();
                        sleep(1);
                    }
                    //echo  '剩余'.$num .'条消息'. PHP_EOL;
                });
                $process->daemon();
                $pid = $process->start();
                $this->workers[$pid] = $process;
            }
    }
    public function clean(){
        $process = new Process(function ()  {
            define('IS_CLI', false);
            define('APP_PATH', __DIR__ . '/../application/');
            // require __DIR__ . '/../thinkphp/base.php';
            require __DIR__ . '/../thinkphp/start.php';
            while (true) {
                if(date('i',time()) == '59'){
                    \app\queue\common\NoticeLib::clearCache();
                }
                sleep(1);
            }
        });
        $process->daemon();
        $pid = $process->start();
        $this->workers[$pid] = $process;
    }
    public function output()
    {
        // 回收子进程
        while ($res = Process::wait()) {
            echo PHP_EOL, 'Worker Exit, PID: ' . $res['pid'] . PHP_EOL;
        }
        
    }

}

// $stime = microtime(true);
$msgProcess = new MsgProcess();
$msgProcess->produNotice();
$msgProcess->execNotice();
$msgProcess->clean();
$msgProcess->output();
// $etime = microtime(true);
 
// echo 'exec time : ', round(($etime - $stime), 3), PHP_EOL;