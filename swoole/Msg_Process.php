<?php 
use Swoole\Process;
/**
 * 多进程
 *
 */
class Msg_Process
{
    private $workers = [];
 
   /**
     * 多个进程生产
     */
    public function produNotice()
    {
        try {
           
            for ($i = 0; $i < 3; $i++) {
             
                $process = new Process(function () {
                    define('IS_CLI', false);
                    define('APP_PATH', __DIR__ . '/../application/');
                    // require __DIR__ . '/../thinkphp/base.php';
                    require __DIR__ . '/../thinkphp/start.php';
                    $messageNum = \app\queue\common\NoticeLib::getGoodsMessage();
                    echo  '获取'.$messageNum .'条消息'. PHP_EOL;
                });
                $pid = $process->start();
                $this->workers[$pid] = $process;
            }
        } catch (\Exception $e) {
            var_dump($e);
        }
    }
 
    /**
     * 多个进程消费消息
     */
    public function execNotice()
    {
        try {
           
            for ($i = 0; $i < 5; $i++) {
             
                $process = new Process(function ()  {
                    define('IS_CLI', false);
                    define('APP_PATH', __DIR__ . '/../application/');
                    // require __DIR__ . '/../thinkphp/base.php';
                    require __DIR__ . '/../thinkphp/start.php';
                    for ($i=0; $i < 60; $i++) { 
                        $num = \app\queue\common\NoticeLib::run();
                        sleep(1);
                    }
                    echo  '剩余'.$num .'条消息'. PHP_EOL;
                });
                $pid = $process->start();
                $this->workers[$pid] = $process;
            }
        } catch (\Exception $e) {
            var_dump($e);
        }
    }
    public function clean(){
        $process = new Process(function ()  {
            define('IS_CLI', false);
            define('APP_PATH', __DIR__ . '/../application/');
            // require __DIR__ . '/../thinkphp/base.php';
            require __DIR__ . '/../thinkphp/start.php';
            if(date('i',time()) == '59'){
                \app\queue\common\NoticeLib::clearCache();
            }
        });
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
 
$stime = microtime(true);
$msgProcess = new Msg_Process();
$msgProcess->produNotice();
$msgProcess->execNotice();
$msgProcess->clean();
$msgProcess->output();
$etime = microtime(true);
 
echo 'exec time : ', round(($etime - $stime), 3), PHP_EOL;