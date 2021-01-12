<?php

namespace app\common\library\redis;

use think\cache\driver\Redis as tpRedis;

/**
 * 定制化的redis
 */
class Redis extends tpRedis
{

    protected static $_instance = null;

    /**
     * @name: 获取单例redis对象，一般用此方法实例化
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getInstance()
    {
        if (!is_null(self::$_instance)) {
            return self::$_instance;
        }
        self::$_instance = new self();
        return self::$_instance;
    }
    
    /**
     * @name: 删除
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function delKey($name)
    {
        return self::getInstance()->del($name);
    }

    /**
     * @name: 架构函数
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function __construct()
    {
        $options = [
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'password'   => '123456',
            'select'     => 0,
        ];
        $options['prefix'] = CacheKeyMap::$prefix;
        parent::__construct($options);
    }


    /**
     * @name: 覆写，实际的缓存标识以CacheKeyMap来管理
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    protected function getCacheKey($name)
    {
        return $name;
    }


    /**
     * @name: redis排重锁
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function redisLock($key, $expires, $value = 1)
    {
        //在key不存在时,添加key并$expires秒过期
        return $this->handler()->set($key, $value, ['nx', 'ex' => $expires]);
    }


    /**
     * @name: 调用缓存类型自己的高级方法
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function __call($method, $args)
    {
        if (method_exists($this->handler, $method)) {
            return call_user_func_array(array($this->handler,$method), $args);
        } else {
            exception(__CLASS__ . ':' . $method . '不存在');
            return;
        }
    }
}
