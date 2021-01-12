<?php

namespace app\common\library\redis;

class OrderDelayed extends Redis
{

  /**
   * @name: 删除任务
   * @author: gz
   * @description:
   * @param {type}
   * @return:
   */
    public static function delOrderDelayedTask($value)
    {
        return self::getInstance()->zRem(CacheKeyMap::orderDelayedSet(), $value);
    }

  /**
   * @name: 获取任务
   * @author: gz
   * @description:
   * @param {type}
   * @return:
   */
    public static function getOrderDelayedTask()
    {
      //获取任务，以0和当前时间为区间，返回一条记录
        return self::getInstance()->zRangeByScore(CacheKeyMap::orderDelayedSet(), 0, time(), ['limit' => [0, 1]]);
    }

  /**
   * @name: 添加任务
   * @author: gz
   * @description:
   * @param {type}
   * @return:
   */
    public static function addOrderDelayedTask($data, $time, $action)
    {
      //添加任务，以时间作为score，对任务队列按时间从小到大排序
        return self::getInstance()->zAdd(
            CacheKeyMap::orderDelayedSet(),
            $time,
            json_encode([
            'action' => $action,
            'time' => $time,
            'data' => $data,
            ])
        );
    }

  /**
   * @name: 消费
   * @author: gz
   * @description:
   * @param {type}
   * @return:
   */
    public static function run($task)
    {
   
      //每次只取一条任务
        //$task = self::getOrderDelayedTask();
        if (!empty($task)) {
            $task = $task[0];
    
            //有并发的可能，这里通过zrem返回值判断谁抢到该任务
            
            if (self::delOrderDelayedTask($task)) {
                $task = json_decode($task, true);
                
                //处理任务
                switch ($task['action']) {
                    case 'del':
                          $aa = \app\common\model\Order::update(['orderStatus' => -1], [
                              'orderSn' => $task['data']['orderSn'],
                              'orderStatus' => '-2',
                              'del' => 1,
                          ]);
                          \app\common\model\Message::add($task['data']['userId'], '订单通知', '订单已取消', $task['data']['orderSn'] . '订单，已经支付超时，系统已自动为您取消！');
                          
                          //echo '订单：' . $task['data']['orderSn']  . '已取消 运行时间：' . date('Y-m-d H:i:s') . PHP_EOL;
                        break;
                    case 'send':
                          //消息
                          \app\common\model\Message::add($task['data']['userId'], '订单通知', '订单即将取消', $task['data']['orderSn'] . '订单，即将自动取消，请您尽快支付！');
                          //echo '订单取消通知：' . $task['data']['orderSn']   . date('Y-m-d H:i:s') . PHP_EOL;
                        break;
                    case 'delDeal':
                          #恢复idleInfo状态
                          $confition = [
                            'idleDealId' => $task['data']['idleDealId'],
                            'dealStats' => 1,
                          ];
                        
                          $dealInfo = \app\common\model\IdleDeal::get($confition);
                          $dealInfo->dealStats = 6;
                          $dealInfo->save();
                          $idleInfo = \app\common\model\IdleInfo::getDeail($dealInfo['idleInfoId']);
                          $idleInfo->sellStatus = 0;
                          $idleInfo->save();
                          \app\common\model\Message::add($task['data']['userId'], '订单通知', '闲置订单已取消', '闲置订单，已经支付超时，系统已自动为您取消！');
                          
                          //echo '订单：' . $task['data']['orderSn']  . '已取消 运行时间：' . date('Y-m-d H:i:s') . PHP_EOL;
                        break;
                    case 'sendDeal':
                          //消息
                          \app\common\model\Message::add($task['data']['userId'], '订单通知', '闲置订单即将取消', '闲置订单，即将自动取消，请您尽快支付！');
                          //echo '订单取消通知：' . $task['data']['orderSn']   . date('Y-m-d H:i:s') . PHP_EOL;
                        break;
                    
                    default:
                        break;
                }
                
                //$userObj = \app\common\model\User::get(['userId' => $task['data']['userId']]);
                //推送消息
                // if ($userObj && $userObj->pClientId) {
                //   try {
                //     \app\common\library\push\ServerPush::send($userObj->pClientId, '通知', '您有一条订单消息，请您及时查看！', ['page' => 1]);
                //   } catch (\Exception $th) {
                    
                //   }
                    
                // }
            }
        }
        
        return;
    }
}
