<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\SearchHistory;
use app\common\model\GoodsAppraises;
use app\common\model\UserCollection;
use app\common\model\ExtensionUser;
use app\common\model\Extension;
use app\common\model\PriceRule;
use app\common\model\Rate;
use app\common\library\redis\GoodsInfo;
use app\common\library\redis\CacheKeyMap;
use app\common\model\UserAddress;

class Goods extends Base
{
    protected $noAuthArr = ['getGoodsInfo','getSkuInfo','search','discover','guess','recommend','getDelivery','getGoodsGift','getGoodsSimilarSku','searchFind','sjSearchR'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name:        搜索
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function search()
    {
        $dataArr  = $this->checkdate('Goods', 'get', 'search');
        $this->getPageAndSize($dataArr);
        isset($dataArr['sort']) ? $sortType = $dataArr['sort'] : $sortType = '';
        isset($dataArr['catId'])  ? $catId = $dataArr['catId'] : $catId = '';
        isset($dataArr['keyword']) ? $keyword = $dataArr['keyword'] : $keyword = '';
        $keyword && $catId ? $keyword = '' : '';
        $data = $this->execSearch($keyword, $this->page, $this->size, $sortType, $catId);
        //存储搜索历史
        if ($this->userId) {
            try {
                if ($dataArr['keyword']) {
                    SearchHistory::create([
                        'userId' => $this->userId,
                        'keyWords' => $dataArr['keyword'],
                        'createTime' => time(),
                    ]);
                }
            } catch (\Exception $th) {
            }
        }
 
        return show(1, $data);
    }

    /**
     * @name: 搜索发现
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function searchFind()
    {
        $sql = 'select keyWords  from ds_search_history group by keyWords order by count(*)  DESC limit 10';
        $data = (new SearchHistory())->query($sql);
        return show(1, array_column($data, 'keyWords'));
    }


    private function execSearch($keyword, $pageIndex, $pageSize, $sortType, $catId)
    {
        $condition =
        [
            "keyword" => $keyword,
            "pageIndex" => $pageIndex,
            "pageSize" => $pageSize,
            "sortType" => $sortType, //sale_desc price_asc price_desc
            "catId" => $catId,
        ];
        $result = Vop::GoodsSearch($condition);
        
        $returnArr = [
            'total' => 0,
            'page_num' => 0,
            'list' => [],
        ];
        if (!$result) {
            return $returnArr;
        }

        $goodsInfoArr = [];
        foreach ($result['hitResult'] as $key => $goods) {
            //商品信息
           
            $info['spu_name'] = $goods['wareName'];
            $info['sku_num'] = $goods['sku_num'];
            $info['pic'] = $goods['imageUrl'];
            //价格
            $cachePrice = GoodsInfo::getGoodsInfoBySkuNum($goods['sku_num'], ['price']); //缓存的价格
            if ($cachePrice['price']) {
                $info['price'] = $this::toUsdt($cachePrice['price']);
                $goodsInfoArr[] = $info;
            } else {
                try {
                        $onprice =  Vop::getPrice($goods['sku_num']);
                    if (!$onprice) {
                        continue;
                    }
                        $price =  $onprice;
             
                    $info['price'] = $this::toUsdt($price);
                    $goodsInfoArr[] = $info;
                } catch (\Exception $th) {
                    continue;
                }
            }
        }
       
        
        $returnArr['total'] = $result['resultCount'];
        $returnArr['page_num'] = $result['pageCount'];
        $returnArr['list'] = $goodsInfoArr;
        return $returnArr;
    }
    
    public function sjSearchR()
    {
        // $dataArr  = $this->checkdate('Goods', 'get', 'searchS');
        $keyword = '苹果';
        $condition =
        [
            "keyword" => $keyword,
            "pageIndex" => 1,
            "pageSize" => 100,
            "sortType" => 'price_asc',
        ];
        $result = Vop::GoodsSearch($condition);
        $count  = count($result['hitResult']);
        $key = mt_rand(0, $count);
        $goods = $result['hitResult'][$key];

        $info['spu_name'] = $goods['wareName'];
        $info['sku_num'] = $goods['sku_num'];
        $info['pic'] = $goods['imageUrl'];
        //价格
        $cachePrice = GoodsInfo::getGoodsInfoBySkuNum($goods['sku_num'], ['price']); //缓存的价格
        if ($cachePrice['price']) {
            $info['price'] = $this::toUsdt($cachePrice['price']);
        } else {
            if ($cachePrice['price']) {
                $price =  $cachePrice['price'];
            } else {
                $onprice =  Vop::getPrice($goods['sku_num']);
                   
                $price =  $onprice;
            }
                $info['price'] = $this::toUsdt($price);
        }
        return show(1, $info);
    }
 
    /**
     * @name:        发现好货
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function discover()
    {
        $dataArr  = $this->checkdate('Goods', 'get', 'discover');
        isset($dataArr['size']) ? $size = $dataArr['size'] : $size = 6;
        $goodsInfoArr = self::randomGoods($size);
        return show(1, $goodsInfoArr);
    }

    /**
     * @name:        猜你喜欢
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function guess()
    {
        $dataArr  = $this->checkdate('Goods', 'get', 'guess');
        isset($dataArr['size']) ? $size = $dataArr['size'] : $size = 6;
      
        if ($this->userId) {
            //用户存在
            $SearchHistory = new SearchHistory();
            $historyObj = $SearchHistory->where(['userId' => $this->userId])->order('searchId desc')->find();
            if (!$historyObj) {
                $goodsInfoArr = self::randomGoods($size);
            } else {
                $keyWords = $historyObj->keyWords;
                $goodsInfoArr  = $this->randomSearch($keyWords, $size);
            }
        } else {
            //用户不存在
            $goodsInfoArr = self::randomGoods($size);
        }
        return show(1, $goodsInfoArr);
    }
    
    /**
     * @name:        为你推荐
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function recommend()
    {
        $dataArr  = $this->checkdate('Goods', 'get', 'recommend');
        isset($dataArr['size']) ? $size = $dataArr['size'] : $size = 6;
        $goodsInfoArr = [];
        if ($this->userId) {
            //用户存在
            $SearchHistory = new SearchHistory();
            $historyObj = $SearchHistory->where(['userId' => $this->userId])->order('searchId desc')->find();
           
            if ($historyObj) {
                $keyWords = $historyObj->keyWords;
                $random = $this->randomSearch($keyWords, $size);
              
                $goodsInfoArr   = $random['list'];
            }
        }
        if (!$goodsInfoArr) {
            //用户不存在
            $goodsInfoArr = self::randomGoods($size);
        }
        return show(1, $goodsInfoArr);
    }
    /**
     * @name: 随机搜索商品
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    private function randomSearch($keyword, $size, $maxPageSize = 100)
    {
        $condition =
        [
            "keyword" => $keyword,
            "pageIndex" => 1,
            "pageSize" => $maxPageSize,
            "sortType" => ''
        ];
        $result = Vop::GoodsSearch($condition);
      
        $returnArr = [
            'total' => 0,
            'page_num' => 0,
            'list' => [],
        ];

        if (!$result) {
            return $returnArr;
        }

       
        if ($result['resultCount'] < $size) {
            $data = $result['hitResult'];
        } else {
            //随机选择出元素
            $temp = array_rand($result['hitResult'], $size);
           
            //重组数组
            foreach ($temp as $key => $val) {
                $data[] = $result['hitResult'][$val];
            }
        }
       
        foreach ($data as $key => $goods) {
            //商品信息
            $info['spu_name'] = $goods['wareName'];
            $info['sku_num'] = $goods['sku_num'];
            $info['pic'] = $goods['imageUrl'];
            //价格
            $cachePrice = GoodsInfo::getGoodsInfoBySkuNum($goods['sku_num'], ['price']);
            $info['price'] = $this::toUsdt($cachePrice['price']);
           
            $goodsInfoArr[] = $info;
        }
        
        $returnArr['total'] = $result['resultCount'];
        $returnArr['page_num'] = $result['pageCount'];
        $returnArr['list'] = $goodsInfoArr;
        return $returnArr;
    }
    /**
     * @name:        随机获取商品
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public static function randomGoods($size)
    {
        $result = [];

    
        $goodsKeyArr = GoodsInfo::getGoodsKey($size);
    
        $goodsInfoArr = [];
        foreach ($goodsKeyArr as $keyName) {
            try {
                $info = GoodsInfo::getGoodsInfoByName($keyName, ['price','spu_name','pics','sku_num','state']);
                $pics = json_decode($info['pics'], true);
                $info['pic'] = $pics[0];
                $info['price'] = self::toUsdt($info['price']);
                $goodsInfoArr[] = $info;
            } catch (\Exception $th) {
                continue;
            }
        }
        return $goodsInfoArr;
    }

    public static function getGoods($size)
    {
       
        $keyNameArr = GoodsInfo::allGoodsInfoKeys();
        
        for ($i = 0; $i < $size; $i++) {
            try {
                $index = random_int(0, count($keyNameArr));
                $info = GoodsInfo::getGoodsInfoByName($keyNameArr[$index], ['price','spu_name','pics','sku_num','state']);
                if (!$info || !isset($info['price']) || !$info['price'] || !isset($info['spu_name']) || !$info['spu_name']  || !isset($info['pics']) ||  !$info['pics'] || !isset($info['state']) ||  !$info['state']) {
                    continue;
                }
                $pics = json_decode($info['pics'], true);
                $info['pic'] = $pics[0];
                $info['price'] = self::toUsdt($info['price']);
                $goodsInfoArr[] = $info;
            } catch (\Throwable $th) {
                continue;
            }
        }
        return $goodsInfoArr;
    }

    /**
     * @name:        商品详情
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function getGoodsInfo()
    {
    
        $dataArr  = $this->checkdate('Goods', 'get', 'getGoodsInfo');

        try {
            $result  =  Vop::getGoodsInfo($dataArr['skuNum']);
            $result['param']  = $this->changeParam($result['param']);
            $result['wxintroduction'] = json_decode($result['wxintroduction'], true);
            
            foreach ($result['wxintroduction'] as &$value) {
                $value = 'https:' . $value;
            }
            
            unset($result['price']);
        } catch (\Exception $th) {
            return show(0);
        }

        return show(1, $result);
    }

    /**
     * @purpose 转换商品详情内规格显示
     * @param $str
     * @return string|string[]|null
     */
    public function changeParam($str)
    {
        if (strstr($str, '</th><tr>') == true) {
            $str = str_replace('</th><tr>', '</th></tr>', $str);
        }
        $p = '/\<table/i';
        $str = preg_replace($p, '<table style="min-width: 100vw;box-sizing: border-box"', $str);
        $p = '/\<p/i';
        $str = preg_replace($p, '<p style="display:none !important"', $str);
        $p = '/\<td/i';
        $str = preg_replace($p, '<td style="min-width: 30%;display: inline-block;border-left:1px solid #F3F3F3;margin-left:-1px;min-height: 20px;padding:5px 10px;box-sizing: border-box"', $str);
        $p = '/\<td style="/';
        $str = preg_replace($p, '<td style="width: 30%;', $str);
        $p = '/\<tr/i';
        $str = preg_replace($p, '<tr style="display: flex;flex-direction: row;justify-content:  flex-start;align-items: center;border:1px solid #F3F3F3;margin-top: -1px;width: 100%;box-sizing: border-box"', $str);
        $p = '/\<th/i';
        $str = preg_replace($p, '<th style="display: flex;min-height: 20px;padding:5px;margin-bottom: -1px;box-sizing: border-box;border: 0"', $str);
        $p = '/\n/i';
        $str = preg_replace($p, '', $str);
        return $str;
    }

    /**
     * @name: 返回价格
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function getSkuPrice()
    {
        $dataArr  = $this->checkdate('Goods', 'get', 'getGoodsInfo');
        try {
            $skuNum = $dataArr['skuNum'];
            $redisKeyName = CacheKeyMap::goodsInfoHash($skuNum);
            $info = GoodsInfo::getAllGoodsInfoByName($redisKeyName);
            
            isset($info['price']) ? $price = $info['price'] : $price = '';
            if (!$price) {
                //直接从三方获取价格
                $price = Vop::getPrice($skuNum);
            }
        } catch (\Exception $th) {
            return show(0);
        }
        
               return show(1, ['price' => $this::toUsdt($price)]);
    }

    /**
     * @name: 返回名称价格图片 评论
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function getSkuInfo()
    {
        $dataArr  = $this->checkdate('Goods', 'get', 'getGoodsInfo');
                
        try {
            //商品信息
            $skuNum = $dataArr['skuNum'];
            # 缓存中取商品信息
            $redisKeyName = CacheKeyMap::goodsInfoHash($skuNum);
                   
            $info = GoodsInfo::getAllGoodsInfoByName($redisKeyName);
            //商品编号
            $info['sku_num'] =  $skuNum;


            //查看缓存价格 没有的走三方
            if ((isset($info['price']) && $info['price'] == '') || !isset($info['price'])) {
                $price = Vop::getPrice($skuNum);
                $info['price'] = $price;
            }
            
            $info['price_r'] = $this::toRmb($info['price']);
            $info['price'] = $this::toUsdt($info['price']);
            
            //查看缓存商品名  没有的走三方
            if ((isset($info['spu_name']) && $info['spu_name'] == '') || !isset($info['spu_name'])) {
                $resultInfo = Vop::getGoodsInfo($skuNum, ['spu_name']);
                $info['spu_name'] = $resultInfo['spu_name'];
            }

            //查看缓存商品图  没有的走三方
           
            if (isset($info['pics']) && $info['pics'] !== '') {
                $pics = json_decode($info['pics'], true);
                $info['pics'] = $pics;
            } else {
                $pics = Vop::getGoodsPic($skuNum);
                $picsArr = array_column($pics[$skuNum], 'pic_path');
                $info['pics'] = $picsArr;
            }
            $info['goodsAppraisesNum'] = GoodsAppraises::getAppraisesNum($dataArr['skuNum']);
            $info['goodsAppraisesData'] = GoodsAppraises::getNewAppraises($dataArr['skuNum']);
            UserCollection::isCollection($dataArr['skuNum'], $this->userId) ? $info['isCollection'] = 1 : $info['isCollection'] = 0;

            #预估挖矿
            #获取当前等级
            $ExtensionUser = ExtensionUser::get(['userId' => $this->userId]);


            switch (true) {
                case !$ExtensionUser:
                        $yAmount = 0;
                    break;
                case $ExtensionUser['extensionId']  < 2:
                        $yAmount = 0;
                    break;
                case $ExtensionUser['extensionId'] == 2 || $ExtensionUser['extensionId'] == 3 || $ExtensionUser['extensionId'] == 4:
                        # 试用初级 初级推广
                        $extensionData = Extension::get(['extensionId' => $ExtensionUser['extensionId']]);
                        $RateModel = new Rate();
                        $rate = $RateModel->getRate();
                        $designData = PriceRule::getIncPrice($price, $rate);
                        $exDataFirIncome = bcdiv($extensionData['firIncome'], '100', config('app.usdt_float_num'));
                        $syUsdt = $designData['usdtPricePercent'] - $designData['oldUsdtPrice'];
                        $yAmount = bcmul($syUsdt, $exDataFirIncome, config('app.usdt_float_num'));
                    break;
                case $ExtensionUser['extensionId'] == 5:
                        # 平台分红
                        $extensionData = Extension::get(['extensionId' => $ExtensionUser['extensionId']]);
                        $RateModel = new Rate();
                        $rate = $RateModel->getRate();
                        $designData = PriceRule::getIncPrice($price, $rate);
                        $exDataFirIncome = bcdiv($extensionData['allIncome'], '100', config('app.usdt_float_num'));
                        $syUsdt = $designData['usdtPricePercent'] - $designData['oldUsdtPrice'];
                        $yAmount = bcmul($syUsdt, $exDataFirIncome, config('app.usdt_float_num'));
                    break;
                
                default:
                        $yAmount = 0;
                    break;
            }
            
            $info['yAmount'] = $yAmount;
        } catch (\Exception $th) {
            return show(0);
        }
                
            return show(1, $info);
    }



    
    /**
     * @name:        商品详情 配送日期
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function getDelivery()
    {
    
        $dataArr  = $this->checkdate('Goods', 'get', 'getDelivery');
        $skuJson = json_encode([ ['skuNum' => $dataArr['skuNum'],'num' => $dataArr['num']]]);

        // //购买限制
        $resultAreaLimit = Vop::getGoodsAreaLimit($dataArr['skuNum'], $dataArr['provinceId'], $dataArr['cityId'], $dataArr['countyId'], $dataArr['townId']);
        if (!$resultAreaLimit || $resultAreaLimit[0]['isAreaRestrict']) {
            return show(1);
        }
 
        //配送到达时间
        $result  =  Vop::getPromiseCalendar($skuJson, $dataArr['provinceId'], $dataArr['cityId'], $dataArr['countyId'], $dataArr['townId']);
        // if($result){
        //     if($result['skuClassifyResult']['resultCode'] == 1){
        //         if($result['skuClassifyResult']['skuClassifyMaps'][$dataArr['skuNum']] === 1){
        //             #取 calendarListResult  中小件日历
        //             $calendarList = $result['calendarListResult'];
        //         }else{
        //             #取 laCalendarListResult 大件日历
        //             $calendarList =  $result['laCalendarListResult'];
        //         }
        //     }else{
        //         $calendarList = [];
        //     }

        // }else{
            
        // }
    
        // return show(1, ['date' => $result]);
        return show(1, ['date' => $result['calendarListResult']['promiseTime'][3]]);
    }

    /**
     * @name:        获取整个送货日历
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function getCalendar()
    {
        $dataArr  = $this->checkdate('Goods', 'post', 'getPromiseCalendar');
        $skuJson = $dataArr['skuNumArrs'];
        $result = Vop::getPromiseCalendar($skuJson, $dataArr['provinceId'], $dataArr['cityId'], $dataArr['countyId'], $dataArr['townId']);
        return show(1, $result);
    }

    /**
     * @name:        获取库存
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function getStock()
    {
        $dataArr  = $this->checkdate('Goods', 'post', 'getStock');
        $skuJson = json_encode([ ['skuNum' => $dataArr['skuNum'],'num' => $dataArr['num']]]);
        $result = Vop::getStock($skuJson, $dataArr['provinceId'] . '_' . $dataArr['cityId'] . '_' . $dataArr['countyId'] . '_' . $dataArr['townId']);
        return show(1, $result[0]);
    }

    /**
     * @name:        获取运费
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function getFreight()
    {

        $dataArr  = $this->checkdate('Goods', 'post', 'getFreight');
        $skuJson = $dataArr['skuNumArrs'];
        $result = Vop::getGoodsFreight($skuJson, $dataArr['provinceId'], $dataArr['cityId'], $dataArr['countyId'], $dataArr['townId']);
        return show(1, ['price' => $result]);
    }
    /**
     * @name:        获取预计挖矿
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function getEstimate()
    {

        $dataArr  = $this->checkdate('Goods', 'post', 'getEstimate');
       
        var_export($dataArr);
        die;
    }



    /**
     * @name:        同类商品查询
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function getGoodsSimilarSku()
    {

        $dataArr  = $this->checkdate('Goods', 'get', 'getGoodsInfo');
        $goodsInfo  =  Vop::getGoodsSimilarSku($dataArr['skuNum']);
        return show(1, $goodsInfo);
    }
    
    /**
     * @name:        查询赠品信息接口
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function getGoodsGift()
    {
        $dataArr  = $this->checkdate('Goods', 'post', 'getGoodsGift');
        list($provinceId, $cityId, $countyId,$townId) = explode("_", $dataArr['areaIdPath']);
        $result = Vop::getGoodsGift($dataArr['skuNum'], $provinceId, $cityId, $countyId, $townId);
      
        $returnData = [];
        if ($result) {
            try {
                if ($result['promoEndTime'] / 1000 > time() && $result['promoStartTime'] / 1000 < time()) {
                    //时间范围内 有赠品
                    foreach ($result['gifts'] as $key => $value) {
                        if ($value['sku_num']) {
                            $info = GoodsInfo::getGoodsInfoBySkuNum($dataArr['skuNum'], ['price','spu_name','pics','sku_num']);
                            if ($info['pics'] && $info['spu_name']) {
                                $pics = json_decode($info['pics'], true);
                                $returnData[$key]['pic'] = $pics[0];
                                $returnData[$key]['spu_name'] = $info['spu_name'];
                            } else {
                                //$resultInfo = Vop::getGoodsInfo($value['sku_num'], ['spu_name']);
                                $pics = Vop::getGoodsPic($dataArr['skuNum']);
                                $picsArr = array_column($pics[$dataArr['skuNum']], 'pic_path');
                                $returnData[$key]['pic'] = $picsArr[0];
                                $returnData[$key]['spu_name'] = $info['spu_name'];
                            }
                           
                            $returnData[$key]['num'] = $value['num'];
                            $returnData[$key]['giftType'] = $value['giftType'];
                            $returnData[$key]['sku_num'] = $value['sku_num'];
                        }
                    }
                }
            } catch (\Exception $th) {
                return show(1, $returnData);
            }
        }
        return show(1, $returnData);
    }


    public function appraisesList()
    {

        $getArr = $this->checkdate('Goods', 'get', 'appraisesList');
        $this->getPageAndSize($getArr);
        $condition = [
            'skuNum' => $getArr['skuNum']
        ];
        $GoodsAppraisesModel = new GoodsAppraises();
        $order = 'createTime desc';
        switch (true) {
            case !isset($getArr['type']) || !$getArr['type']:
                    //全部
                    $order = 'goodsScore desc,createTime desc';
                break;

            case $getArr['type'] == 1:
                    //最新
                break;
            case $getArr['type'] == 2:
                    //好评
                    $condition['goodsScore'] = ['in',[4,5]];
                break;
            case $getArr['type'] == 3:
                    //中评
                    $condition['goodsScore'] = ['in',[2,3]];
                break;
            case $getArr['type'] == 4:
                    //差评
                    $condition['goodsScore'] = ['eq',1];
                break;
            case $getArr['type'] == 5:
                    //有图
                    $condition['imgUrl'] = ['neq',''];
                break;

            default:
                    //默认全部
                    $order = 'goodsScore desc,createTime desc';
                break;
        }
        
        $fields = ['goodsScore','content','imgUrl','userId','spuName'];
        $total = $GoodsAppraisesModel->getCount($condition);
        $list = $GoodsAppraisesModel->getList($condition, $this->from, $this->size, $fields, $order, ['userInfo','imgs']);
        //全部
        $returnResult['totalA'] =  $GoodsAppraisesModel->getCount([
            'skuNum' => $getArr['skuNum']
        ]);
        //好评
        $returnResult['totalH'] =  $GoodsAppraisesModel->getCount([
            'skuNum' => $getArr['skuNum'],
            'goodsScore' => ['in',[4,5]]
        ]);
        //中评
        $returnResult['totalM'] =  $GoodsAppraisesModel->getCount([
            'skuNum' => $getArr['skuNum'],
            'goodsScore' => ['in',[2,3]]
        ]);
        //差评
        $returnResult['totalL'] =  $GoodsAppraisesModel->getCount([
            'skuNum' => $getArr['skuNum'],
            'goodsScore' => ['eq',1]
        ]);
        //有图
        $returnResult['totalI'] =  $GoodsAppraisesModel->getCount([
            'skuNum' => $getArr['skuNum'],
            'imgUrl' => ['neq','']
        ]);

        $returnResult['listResult'] = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        
        return show(1, $returnResult);
    }

    /**
     * @name: 详情 全部返回
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function goodsInfo()
    {

        $dataArr  = $this->checkdate('Goods', 'get', 'getGoodsInfo');
        $skuNum = $dataArr['skuNum'];
        $mis = microtime(true);
        try {
            # vop 详情
            $resultInfo  =  Vop::getGoodsInfo($skuNum);
            if (!$resultInfo) {
                throw new \Exception("no info");
            }
            $resultInfo['param']  = $this->changeParam($resultInfo['param']);
            $resultInfo['wxintroduction'] = json_decode($resultInfo['wxintroduction'], true);
            foreach ($resultInfo['wxintroduction'] as &$value) {
                $value = 'https:' . $value;
            }
        } catch (\Exception $th) {
            $resultInfo = [];
        }
       
        #获取此人当前默认地址
        $addressData = UserAddress::getDefault($this->userId);
        if ($addressData) {
            #解析地址Id
            list($provinceId, $cityId, $countyId,$townId) = explode("_", $addressData['areaIdPath']);

            #购买限制
            try {
                $resultAreaLimit = Vop::getGoodsAreaLimit($skuNum, $provinceId, $cityId, $countyId, $townId);
                $resultAreaLimit = $resultAreaLimit[0]['isAreaRestrict'];
            } catch (\Exception $th) {
                $resultAreaLimit = false;
            }

            #赠品
            try {
                $resultGifts = [];
                $resultGift = Vop::getGoodsGift($skuNum, $provinceId, $cityId, $countyId, $townId);
               
                if ($resultGift['promoEndTime'] / 1000 > time() && $resultGift['promoStartTime'] / 1000 < time()) {
                    //时间范围内 有赠品
                    foreach ($resultGift['gifts'] as $key => $value) {
                            $info = GoodsInfo::getGoodsInfoBySkuNum($skuNum, ['price','spu_name','pics','sku_num']);
                        if ($info['pics'] && $info['spu_name']) {
                            $pics = json_decode($info['pics'], true);
                            $resultGifts[$key]['pic'] = $pics[0];
                            $resultGifts[$key]['spu_name'] = $info['spu_name'];
                        } else {
                            //$resultInfo = Vop::getGoodsInfo($value['sku_num'], ['spu_name']);
                            $pics = Vop::getGoodsPic($skuNum);
                            $picsArr = array_column($pics[$skuNum], 'pic_path');
                            $resultGifts[$key]['pic'] = $picsArr[0];
                            $resultGifts[$key]['spu_name'] = $info['spu_name'];
                        }
                        
                            $resultGifts[$key]['num'] = $value['num'];
                            $resultGifts[$key]['giftType'] = $value['giftType'];
                            $resultGifts[$key]['sku_num'] = $value['sku_num'];
                    }
                }
            } catch (\Exception $th) {
                $resultGifts = [];
            }
                
            #配送到达时间及日历
            try {
                $skuJson = json_encode([ ['skuNum' => $skuNum,'num' => 1]]);
                $resultTimeAndCalendar  =  Vop::getPromiseCalendar($skuJson, $provinceId, $cityId, $countyId, $townId);
            } catch (\Exception $th) {
                $resultTimeAndCalendar = [];
            }
        } else {
            $resultAreaLimit = false;
            $resultGifts = [];
            $resultTimeAndCalendar = [];
        }
        
        #同类商品
        try {
            $resultSimilarSku  =  Vop::getGoodsSimilarSku($skuNum);
        } catch (\Exception $th) {
            $resultSimilarSku = [];
        }
        
        #图片 留言 挖矿
        try {
            //商品信息
            # 缓存中取商品信息
            $redisKeyName = CacheKeyMap::goodsInfoHash($skuNum);
                   
            $info = GoodsInfo::getAllGoodsInfoByName($redisKeyName);
            //商品编号
            $info['sku_num'] =  $skuNum;


            //查看缓存价格 没有的走三方
            if ((isset($info['price']) && $info['price'] == '') || !isset($info['price'])) {
                $price = Vop::getPrice($skuNum);
                $info['price'] = $price;
            }
            
            $info['price_r'] = $this::toRmb($info['price']);
            $info['price'] = $this::toUsdt($info['price']);
            
            //查看缓存商品名  没有的走三方
            if ((isset($info['spu_name']) && $info['spu_name'] == '') || !isset($info['spu_name'])) {
                $resultInfo = Vop::getGoodsInfo($skuNum, ['spu_name']);
                $info['spu_name'] = $resultInfo['spu_name'];
            }

            //查看缓存商品图  没有的走三方
           
            if (isset($info['pics']) && $info['pics'] !== '') {
                $pics = json_decode($info['pics'], true);
                $info['pics'] = $pics;
            } else {
                $pics = Vop::getGoodsPic($skuNum);
                $picsArr = array_column($pics[$skuNum], 'pic_path');
                $info['pics'] = $picsArr;
            }
            $info['goodsAppraisesNum'] = GoodsAppraises::getAppraisesNum($skuNum);
            $info['goodsAppraisesData'] = GoodsAppraises::getNewAppraises($skuNum);
            UserCollection::isCollection($skuNum, $this->userId) ? $info['isCollection'] = 1 : $info['isCollection'] = 0;

            #预估挖矿
            #获取当前等级
            $ExtensionUser = ExtensionUser::get(['userId' => $this->userId]);


            switch (true) {
                case !$ExtensionUser:
                        $yAmount = 0;
                    break;
                case $ExtensionUser['extensionId']  < 2:
                        $yAmount = 0;
                    break;
                case $ExtensionUser['extensionId'] == 2 || $ExtensionUser['extensionId'] == 3 || $ExtensionUser['extensionId'] == 4:
                        # 试用初级 初级推广
                        $extensionData = Extension::get(['extensionId' => $ExtensionUser['extensionId']]);
                        $RateModel = new Rate();
                        $rate = $RateModel->getRate();
                        $designData = PriceRule::getIncPrice($price, $rate);
                        $exDataFirIncome = bcdiv($extensionData['firIncome'], '100', config('app.usdt_float_num'));
                        $syUsdt = $designData['usdtPricePercent'] - $designData['oldUsdtPrice'];
                        $yAmount = bcmul($syUsdt, $exDataFirIncome, config('app.usdt_float_num'));
                    break;
                case $ExtensionUser['extensionId'] == 5:
                        # 平台分红
                        $extensionData = Extension::get(['extensionId' => $ExtensionUser['extensionId']]);
                        $RateModel = new Rate();
                        $rate = $RateModel->getRate();
                        $designData = PriceRule::getIncPrice($price, $rate);
                        $exDataFirIncome = bcdiv($extensionData['allIncome'], '100', config('app.usdt_float_num'));
                        $syUsdt = $designData['usdtPricePercent'] - $designData['oldUsdtPrice'];
                        $yAmount = bcmul($syUsdt, $exDataFirIncome, config('app.usdt_float_num'));
                    break;
                
                default:
                        $yAmount = 0;
                    break;
            }
            
            $info['yAmount'] = $yAmount;
        } catch (\Exception $th) {
            $info = [];
        }

        $returnData = [
            'resultInfo' => $resultInfo,
            'addressInfo' => $addressData,
            'resultAreaLimit' => $resultAreaLimit,
            'resultGifts' => $resultGifts,
            'resultTimeAndCalendar' => $resultTimeAndCalendar,
            'resultSimilarSku' => $resultSimilarSku,
            'resultSkuInfo' => $info,
        ];
       
        return show(1, $returnData);
    }
}
