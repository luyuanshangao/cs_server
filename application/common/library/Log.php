<?php 
namespace app\common\library;

Class Log{
    /**
     * @param $mark  备注
     * @param $log_content  日志内容
     * @param string $fname  文件名
     */
    public static function mylog($mark, $log_content, $fname = "")
    {
        $max_size = 30000000; //字节
        if ($fname == "") {
            $log_filename = RUNTIME_PATH . '/log/' . date('Ym-d') . ".log";
        } else {
            $log_filename = RUNTIME_PATH . '/log/' . $fname . ".log";
        }

        if (file_exists($log_filename) && abs(filesize($log_filename)) > $max_size) {
            //当日志文件过大，移动文件并重命名
            rename($log_filename, dirname($log_filename) . DS . date('Ym-d-His') . $fname . ".log");
        }

        $t = microtime(true);
        $micro = sprintf("%06d", ($t - floor($t)) * 1000000);
        $d = new \DateTime (date('Y-m-d H:i:s.' . $micro, $t));
        if(is_array($log_content)){
            $log_content =json_encode($log_content,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }

        file_put_contents($log_filename,
            '   ' . $d->format('Y-m-d H:i:s u') . " key：" . $mark . "\r\n" . $log_content ."\r\n",
            FILE_APPEND);
    }

}
    