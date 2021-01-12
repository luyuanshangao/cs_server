<?php



class Update
{


    const HOST  = "0.0.0.0";
    const PORT = 9595;

    public $ws = null;
    public function __construct()
    {
        //nohup php /www/cs_server/server/Update.php >/dev/null 2>&1 &
        $this->ws =  new Swoole\Http\Server(self::HOST, self::PORT);
        $this->ws->set([
            'worker_num' => 1,
            'max_request' => 0,
            'task_worker_num' => 100,
            'task_max_request' => 2,
            'dispatch_mode' => 3,
            'task_enable_coroutine' => true,   //开启 Task 协程支持
            'max_coroutine' => 200000,
            'daemonize'=>0
        ]);
        $this->ws->on("workerstart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->upPoolGoodsInfo();
        // $this->upCateGoodsInfo();
        $this->noticeQueue();
        $this->ws->start();
    }

    public function upPoolGoodsInfo()
    {
        $poolProcess = new Swoole\Process(function ($process) {
            //加载TP框架
            define('IS_CLI', false);
            define('APP_PATH', __DIR__ . '/../application/');
            // require __DIR__ . '/../thinkphp/base.php';
            require __DIR__ . '/../thinkphp/start.php';
            $this->ws->tick(1000, function () {
                $systemData = db('system')->where('name', '=', 'update_goods_info_cache')->find();
                $systime = $systemData['value'];
                $nowtime = time();
                if ((int)$systime == $nowtime) {
                    try {
                        $GoodsPool = new \app\common\library\redis\GoodsPool;
                        $GoodsSku = new \app\common\library\redis\GoodsSku;
                        $poolArr = \app\api\controller\v1\Vop::getGoodsPool();
                        \app\common\library\Log::mylog('swoole','设置商品池','swoole_pool_cache'); 
                        foreach ($poolArr as $key => $value) {
                            //设置商品池缓存
                            $GoodsPool::setGoodsPool($value['pool_id'], $value);
                            //获取商品池下商品编号
                            $goodsSkuNumArr = \app\api\controller\v1\Vop::getVopGoodsSku($value['pool_id']);
                            $GoodsSku::setGoodsSku($value['pool_id'], $goodsSkuNumArr);
                        }
                        // //商品池键名
                        $poolGoodsSkuArr = $GoodsSku::allGoodsSkuKeys();
                        $skuNumArr = [];
                        foreach ($poolGoodsSkuArr as $keyName) {
                            //对应商品池下商品编号数组
                            $resut =  $GoodsSku::getGoodsPoolByName($keyName);
                            //所有商品编号
                            $skuNumArr = array_merge($skuNumArr, array_column($resut, 'sku_num'));
                        }
                        //所有商品缓存键名
                        $allGoodsInfoKeyArr = \app\common\library\redis\GoodsInfo::allGoodsInfoKeys();
                        $skuNumArrCa = [];
                        foreach ($allGoodsInfoKeyArr as $keyName) {
                            $arrEx = explode(':', $keyName);
                            $skuNumArrCa[] = $arrEx[2];
                        }
    
                        
                        
                        //遍历已存在的缓存 不在新的中 删除掉
                        foreach ($skuNumArrCa as $skuNum) {
                            if (!in_array($skuNum, $skuNumArr)) {
                                \app\common\library\redis\GoodsInfo::delKeyByName($skuNum);
                                \app\common\library\redis\GoodsInfo::delGoodsKeyByName($skuNum);
                            }
                        }
                        \app\common\library\Log::mylog('swoole','已清除无用缓存','swoole_pool_cache'); 
                        
                        $GoodsCache = new  \app\common\library\redis\GoodsCache;
                        // //缓存
                        foreach ($skuNumArr as  $sku_num) {
                            
                            $info = \app\common\library\redis\GoodsInfo::getAllGoodsInfoBySkuNum($sku_num);
                            if($info){
                                continue;
                            }else{
                                $lPushData['sku_num'] = $sku_num;
                                $GoodsCache::lPushData($lPushData,time(),'all');
                            }
                            
                        }

                        \app\common\library\Log::mylog('swoole','已缓存商品池商品','swoole_pool_cache'); 
                         
                        
                    } catch (\Throwable $th) {
                        \app\common\library\Log::mylog('swoole','运行错误','swoole_pool_cache');  
                    }
                    
                }
            });

        }, false, 1, true);
        $poolProcess::daemon();
        $this->ws->addProcess($poolProcess);
    }

    public function noticeQueue()
    {
        //获取更新notice
        
            $messageProcess = new Swoole\Process(function ($process) {
                //加载TP框架
                define('IS_CLI', false);
                define('APP_PATH', __DIR__ . '/../application/');
                // require __DIR__ . '/../thinkphp/base.php';
                require __DIR__ . '/../thinkphp/start.php';
                $this->ws->tick(1000, function () {
                    \app\queue\common\NoticeLib::getGoodsMessage();
                });
    
            }, false, 1, true);
            $this->ws->addProcess($messageProcess);
        

        //  //消费更新缓存
        for ($i=0; $i < 5; $i++) { 
            $runProcess = new Swoole\Process(function ($process) {
                //加载TP框架
                define('IS_CLI', false);
                define('APP_PATH', __DIR__ . '/../application/');
                // require __DIR__ . '/../thinkphp/base.php';
                require __DIR__ . '/../thinkphp/start.php';
                $this->ws->tick(1000, function () {
                    \app\queue\common\NoticeLib::run();
                });
    
            }, false, 1, true);
            $runProcess::daemon();
            $this->ws->addProcess($runProcess);
        }

        //定时器清除不完全缓存
        $clearProcess = new Swoole\Process(function ($process) {
            //加载TP框架
            define('IS_CLI', false);
            define('APP_PATH', __DIR__ . '/../application/');
            // require __DIR__ . '/../thinkphp/base.php';
            require __DIR__ . '/../thinkphp/start.php';
            $this->ws->tick(1000, function () {
                if(date('i',time()) == '59'){
                    \app\queue\common\NoticeLib::clearCache();
                }
            });
        }, false, 1, true);
        $clearProcess::daemon();
        $this->ws->addProcess($clearProcess);
    }



     // public function upCateGoodsInfo()
    // {

    //     $userProcess = new Swoole\Process(function ($process) {
    //         //加载TP框架
    //         define('IS_CLI', false);
    //         define('APP_PATH', __DIR__ . '/../application/');
    //         // require __DIR__ . '/../thinkphp/base.php';
    //         require __DIR__ . '/../thinkphp/start.php';
    //         $this->ws->tick(1000, function () {
    //             $systemData = db('system')->where('name', '=', 'update_cate_goods_info_cache')->find();
    //             $systime = $systemData['value'];
    //             $nowtime = time();
    //             if ((int)$systime == $nowtime) {
    //                 try {
    //                     $oneCategoryIds = db('category')->where(['catClass' => 0])->column('catId');
    //                     $secCategoryIds = db('category')->where(['parentId' => ['in', $oneCategoryIds]])->column('catId');
    //                     $theCategory = db('category')->where(['parentId' => ['in', $secCategoryIds]])->column('catId');
    //                     $queue = new \app\queue\controller\CacheQueue();
    //                     $countCateNum = 0;
    //                     foreach ($theCategory as $catId) {
    //                         try {
                                
    //                             $queue->setCateGoodsSet($catId);
    //                             $countCateNum += 1;
    //                         } catch (\Throwable $th) {
    //                             continue;
    //                         }
                           
    //                     }
    //                     \app\common\library\Log::mylog('swoole','缓存集合添加完成 : '.$countCateNum,'swoole_cate_cache'); 
    //                 } catch (\Throwable $th) {
    //                     \app\common\library\Log::mylog('swoole','运行错误','swoole_cate_cache');  
    //                 }
                    
    //             }
    //         });

    //     }, false, 1, true);
        
    //     $userProcess->name('cateGoodsProcess');
    //     $userProcess::daemon();
    //     $this->ws->addProcess($userProcess);
    // }

    /**
     * @name: onWorkerStart回调
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function onWorkerStart($server, $worker_id)
    {
        //加载TP框架
        define('IS_CLI', false);
        define('APP_PATH', __DIR__ . '/../application/');
        // require __DIR__ . '/../thinkphp/base.php';
        require __DIR__ . '/../thinkphp/start.php';
    }


    public function onRequest($request, $response)
    {
    }

    /**
     * @name: 异步task任务
     * @author: gz
     * @description: GET POST
     * @param {type} 
     * @return: 
     */
    public function onTask($server, $task)
    {

        //工厂
        // $task->worker_id;              //来自哪个`Worker`进程
        // $task->id;                     //任务的编号
        // $task->flags;                  //任务的类型，taskwait, task, taskCo, taskWaitMulti 可能使用不同的 flags
        $data =  $task->data;                   //任务的数据
        $obj = new \app\common\library\task\Task();
        $method = $data['method'];
        $flag = $obj->$method($data['data']);
        // $task->finish($flag); //完成任务，结束并返回数据
    }

    /**
     * @name: task完成回调
     * @author: gz
     * @description: GET POST
     * @param {type} 
     * @return: 
     */
    public function onFinish($server, $task_id, $data)
    {
        print_r($data) . "/n";
    }
}

$obj = new Update();
