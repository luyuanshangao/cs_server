<?php

namespace app\common\library\redis;

class GoodsInfo extends Redis
{

    /**
     * @name: 设置商品信息 多个字段缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function setGoodsInfoOne($sku_num, $field, $value)
    {
        return self::getInstance()->hSet(CacheKeyMap::goodsInfoHash($sku_num), $field, $value);
    }
    /**
     * @name: 设置商品信息 多个字段缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function setGoodsInfoToo($sku_num, $data)
    {
        return self::getInstance()->hMSet(CacheKeyMap::goodsInfoHash($sku_num), $data);
    }
    
    /**
     * @name: 获取商品信息缓存通过 sku_num
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsInfoBySkuNum($sku_num, $fields)
    {
        return self::getInstance()->hMGet(CacheKeyMap::goodsInfoHash($sku_num), $fields);
    }

    /**
     * @name: 获取所有商品信息缓存Key
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function allGoodsInfoKeys()
    {
        return self::getInstance()->keys(CacheKeyMap::goodsInfoHash('*'));
    }


   /**
     * @name: 获取所有商品信息缓存 通过完整key 获取个别字段
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsInfoByName($name, $fields)
    {
        return self::getInstance()->hMGet($name, $fields);
    }

   /**
     * @name: 获取所有的field-value
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getAllGoodsInfoByName($name)
    {
        return self::getInstance()->hGetall($name);
    }
   /**
     * @name: 获取所有的field-value
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getAllGoodsInfoBySkuNum($sku_num)
    {
        return self::getInstance()->hGetall(CacheKeyMap::goodsInfoHash($sku_num));
    }

    /**
     * @name: 设置商品键名集合
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function setGoodsKeySet($sku_num)
    {
        return self::getInstance()->sAdd(CacheKeyMap::goodsKeySet(), CacheKeyMap::goodsInfoHash($sku_num));
    }
    /**
     * @name: 集合返回所有商品键名
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getAllGoodsKey()
    {
        return self::getInstance()->sMembers(CacheKeyMap::goodsKeySet());
    }
    /**
     * @name: 集合返回所有商品键名数
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getAllGoodsKeyNum()
    {
        return self::getInstance()->scard(CacheKeyMap::goodsKeySet());
    }
    /**
     * @name: 集合随机商品键名
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getGoodsKey($num)
    {
        //随机返回（n）个集合内的元素，由第二个参数决定返回多少个
        //如果 n 大于集合内元素的个数则返回整个集合
        //如果 n 是负数时随机返回 n 的绝对值，数组内的元素会重复出现
        return self::getInstance()->sRandMember(CacheKeyMap::goodsKeySet(), $num);
    }
    /**
     * @name: 集合中删除键名
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function delGoodsKey($sku_num)
    {
     //删除集合中指定的一个元素，元素不存在返回0。删除成功返回1，否则返回0。
        return self::getInstance()->srem(CacheKeyMap::goodsKeySet(), CacheKeyMap::goodsInfoHash($sku_num));
    }
    /**
     * @name: 集合中删除键名
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function delGoodsKeyByName($keyNum)
    {
     //删除集合中指定的一个元素，元素不存在返回0。删除成功返回1，否则返回0。
        return self::getInstance()->srem(CacheKeyMap::goodsKeySet(), $keyNum);
    }
   /**
     * @name: 删除field
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function delFieldsByName($name, $fields)
    {
        return self::getInstance()->hDel($name, $fields);
    }
   /**
     * @name: 删除
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function delKeyByName($sku_num)
    {
        return self::getInstance()->del(CacheKeyMap::goodsInfoHash($sku_num));
    }

   /**
     * @name: 是否存在
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function hasKey($name)
    {
        return self::getInstance()->has($name);
    }
}
