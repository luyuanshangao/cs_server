<?php

use Swoole\Process;

/**
 * 多进程
 *
 */
class InsertCache
{
    private $workers = [];

    /**
     * 多个进程生产
     */
    public function doJob()
    {

                $process = new Process(function ($worker) {
                    define('IS_CLI', false);
                    define('APP_PATH', __DIR__ . '/../application/');
                    # require __DIR__ . '/../thinkphp/base.php';
                    require __DIR__ . '/../thinkphp/start.php';
                       
                    $output = new think\console\Output();
                    $output->writeln('-------------------------------------------------------------------------');

                    //FLUSHALL
                    ##############################【重新进行商品缓存】#################################
                    try {
                        $GoodsPool = new \app\common\library\redis\GoodsPool;
                        $GoodsSku = new \app\common\library\redis\GoodsSku;
                        $poolArr = \app\api\controller\v1\Vop::getGoodsPool();
                       
                        
                        foreach ($poolArr as $key => $value) {
                            #设置商品池缓存
                            $GoodsPool::setGoodsPool($value['pool_id'], $value);
                            #获取商品池下商品编号
                            $goodsSkuNumArr = \app\api\controller\v1\Vop::getVopGoodsSku($value['pool_id']);
                            $GoodsSku::setGoodsSku($value['pool_id'], $goodsSkuNumArr);
                        }
                        #商品池键名
                        $poolGoodsSkuArr = $GoodsSku::allGoodsSkuKeys();
                        
                        $skuNumArr = [];
                        foreach ($poolGoodsSkuArr as $keyName) {
                            #对应商品池下商品编号数组
                            $resut =  $GoodsSku::getGoodsPoolByName($keyName);
                            #所有商品编号
                            $skuNumArr = array_merge($skuNumArr, array_column($resut, 'sku_num'));
                        }
                        $GoodsCache = new  \app\common\library\redis\GoodsCache;
                        #存入缓存队列
                        foreach ($skuNumArr as  $sku_num) {
                                $output->writeln('存入'.$sku_num);
                                $lPushData['sku_num'] = $sku_num;
                                $GoodsCache::lPushData($lPushData,time(),'all');
                        }
                        $output->writeln('共计：'.count($skuNumArr));
                        
                        
                        
                    } catch (\Exception $th) {
                        $output->writeln('运行错误');
                    }
                
                });
                $pid = $process->start();
                $this->workers[$pid] = $process;

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
$InsertCache = new InsertCache();
$InsertCache->doJob();
$InsertCache->clean();
$etime = microtime(true);

echo 'exec time : ', round(($etime - $stime), 3), PHP_EOL;
