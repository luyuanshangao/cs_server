<?php

namespace app\api\controller;

use app\common\exception\ApiException;
use app\api\common\library\IAuth;
use think\Controller;
use app\common\model\Rate;
use app\common\model\PriceRule;

class Base extends Controller
{
    public $page = 1;
    public $size = 10;
    public $from = 0;
    public $sort = 'createTime desc';
    public static $rate = '';
    protected $noAuthArr = []; //用户登录接口白名单
    protected $noSignArr = []; //接口Sign验证白名单
    protected $clientInfo = null;
    protected $userId = 0;
    
    /**
     * @name:        初始化
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function __construct()
    {
        self::$rate =  Rate::where(1)->value('USDRate');
        parent::__construct();
        $this->init();
    }

    /**
     * @name:        初始化
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function init()
    {

        $noAuthBoolean = Iauth::match($this->noAuthArr);
        $noSignBoolean = false;
        if (!config('sign_debug')) {
            $noSignBoolean = Iauth::match($this->noSignArr);
        }
        $this->clientInfo = Iauth::getClient($noAuthBoolean, $noSignBoolean);
       
        if ($this->clientInfo) {
            $this->userId = $this->clientInfo['userId'];
        }
    }

    /**
     * 获取分页
     *
     * @description
     * @author      gz
     * @param       [type] $data
     * @return      void
     */
    public function getPageAndSize($data)
    {
        $this->page = !empty($data['page']) ? $data['page'] : 1;
        $this->size = !empty($data['size']) ? $data['size'] : 5;
        $this->from = ($this->page - 1) * $this->size;
    }

    /**
     * 获取排序
     *
     * @description
     * @author      gz
     * @param       [type] $data
     * @return      void
     */
    public function getSort($data, $ex)
    {
        if (isset($data['sort']) && !empty($data['sort'])) {
            $this->sort = strtr($data['sort'], $ex, ' ');
        }
    }
    
    /**
     * @name:        接收参数并校验
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function checkdate($validate, $method, $scene)
    {

        if ($method == 'post') {
            $methodArr = $this->request->post();
        } else {
            $methodArr = $this->request->get();
        }
        $vali = '\app\api\validate\\' . $validate;
        $validateModel = new $vali();

        //验证器
        $resultBool =  $validateModel->scene($scene)->check($methodArr);
        if (!$resultBool) {
            $msg =  $validateModel->getError();
            throw new ApiException(0, null, $msg);
        }
        return $methodArr;
    }

    /**
     * 创建参数
     *
     * @description
     * @author      gz
     * @param       [type] $array
     * @return      void
     */
    protected function buildParam($array)
    {
        $data = [];
        if (is_array($array)) {
            foreach ($array as $item => $value) {
                if ($value == 'size' || $value == 'page') {
                    continue;
                }
                $data[$item] = $value;
            }
        }
        return $data;
    }

     /**
      * 过滤参数
      *
      * @description
      * @author      gz
      * @param       [type] $arrays
      * @param       [type] $data
      * @return      void
      */
    protected function filterParam($arrays, $data)
    {
        foreach ($data as $item => &$value) {
            if (!in_array($item, $arrays) || $value == "") {
                unset($data[$item]);
            }

            if ($item == 'page' || $item == 'size' || $item == 'sort') {
                unset($data[$item]);
            }
            if ($item == 'createTime' && $value !== "") {
                foreach ($value as $k => $v) {
                    $value[$k] = $v / 1000;
                }
                $value = ['between', $value];
            }
            if ($item == 'payTime' && $value !== "") {
                foreach ($value as $k => $v) {
                    $value[$k] = $v / 1000;
                }
                $value = ['between', $value];
            }
        }

        return $data;
    }

    public static function obtainGoodsInfo($skuNum, $fields)
    {
        
        $redisKeyName = \app\common\library\redis\CacheKeyMap::goodsInfoHash($skuNum);
        $info = \app\common\library\redis\GoodsInfo::getAllGoodsInfoByName($redisKeyName);
        isset($info['pics']) ? $info['pic'] = $info['pics'] : '';
        //商品编号
        //查看缓存价格 没有的走三方
        if ((isset($info[$fields]) && $info[$fields] == '') || !isset($info[$fields])) {
            switch (true) {
                case $fields == 'price':
                        $price = \app\api\controller\v1\Vop::getPrice($skuNum);
                    if (!$price) {
                        return false;
                    }
                    return $price;
                    break;
                case $fields == 'spu_name':
                        $$resultInfo  = \app\api\controller\v1\Vop::getGoodsInfo($skuNum, ['spu_name']);
                    if (!$info) {
                        return false;
                    }
                    return $resultInfo['spu_name'];
                    break;
                case $fields == 'pics':
                        $pics =  \app\api\controller\v1\Vop::getGoodsPic($skuNum);
                    if (!$pics) {
                        return false;
                    }
                    return array_column(\app\api\controller\v1\Vop::getGoodsPic($skuNum)[$skuNum], 'pic_path');
                    break;
                case $fields = 'pic':
                        $pics =  \app\api\controller\v1\Vop::getGoodsPic($skuNum);
                    if (!$pics) {
                        return false;
                    }
                    return array_column(\app\api\controller\v1\Vop::getGoodsPic($skuNum)[$skuNum], 'pic_path')[0];
                    break;
                
                default:
                    return false;
                    break;
            }
        } else {
            switch (true) {
                case $fields == 'price':
                    return  $info['price'];
                    break;
                case $fields == 'spu_name':
                    return  $info['spu_name'];
                    break;
                case $fields == 'pics':
                        $pics = json_decode($info['pics'], true);
                    return  $pics;
                    break;
                case $fields == 'pic':
                        $pics = json_decode($info['pics'], true);
                    return $pics[0];
                    break;
                    
                default:
                    return false;
                    break;
            }
        }
    }


    public static function toUsdt($price)
    {
            !$price ?  $price = 0 : '';
            $percent = PriceRule::getPricePercent($price);
            $price = $price * (1 + $percent / 100);
            $rate = self::$rate;
            $usdtPrice = ceil($price / $rate * 100) / 100;
            return $usdtPrice;
    }
    public static function toRmb($price)
    {
            $percent = PriceRule::getPricePercent($price);
            $price = ceil($price * (1 + $percent / 100) * 100) / 100;
            return $price;
    }
}
