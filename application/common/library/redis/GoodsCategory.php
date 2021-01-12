<?php

namespace app\common\library\redis;

class GoodsCategory extends Redis
{

    /**
     * @name: 设置分类缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function setGoodsCategory($catId, $data)
    {
       
        return self::getInstance()->set(CacheKeyMap::categoryString($catId), $data);
    }
    
    /**
     * @name: 获取分类缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsCategory($catId)
    {
        return self::getInstance()->get(CacheKeyMap::categoryString($catId));
    }

    /**
     * @name: 获取分类缓存Key
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function allGoodsCategoryKeys()
    {
        return self::getInstance()->keys(CacheKeyMap::categoryString('*'));
    }

   /**
     * @name: 获取分类缓存 通过完整key
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsCategoryByName($name)
    {
        return self::getInstance()->get($name);
    }



    /**
     * @name: 设置三级集合
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function setTheCaKeySet($catid)
    {
        return self::getInstance()->sAdd(CacheKeyMap::theCateSet(), $catid);
    }

   /**
     * @name: 三级集合中删除
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function delTheCaKeySet($catid)
    {
     //删除集合中指定的一个元素，元素不存在返回0。删除成功返回1，否则返回0。
        return self::getInstance()->srem(CacheKeyMap::theCateSet(), $catid);
    }

    /**
     * @name: 三级返回所有
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getTheCaKey()
    {
        return self::getInstance()->sMembers(CacheKeyMap::theCateSet());
    }

        /**
     * @name: 集合返回所有商品键名数
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getTheCaKeyNum()
    {
        return self::getInstance()->scard(CacheKeyMap::theCateSet());
    }
}
