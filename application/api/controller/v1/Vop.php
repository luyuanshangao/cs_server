<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Loader;
use app\common\model\Rate;
use app\common\model\PriceRule;

Loader::import('goods_sdk.SDK.Goods');
Loader::import('goods_sdk.SDK.Address');
Loader::import('goods_sdk.SDK.DataBase');
Loader::import('goods_sdk.SDK.Order');
Loader::import('goods_sdk.SDK.AfterSale');
Loader::import('goods_sdk.SDK.Message');
class Vop extends Controller
{
    protected $noAuthArr = [];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单


    /**
     * @name:        获取商品池信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsPool()
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsPool');
        $ParameterInfo->SetVersion('1.0');
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return [];
        }
    }
    /**
     * @name:        获取随机商品池
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getRandoomGoodsPool()
    {

        $poolArr = self::getGoodsPool();
        $keys  =  array_rand($poolArr, 1);
        return  $poolArr[$keys];
    }
    /**
     * @name:        获取商品池商品编号
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getVopGoodsSku($poolId)
    {
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsSku');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["poolId" => $poolId];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return [];
        }
    }
    
    /**
     * @name:        查询商品池内分类信息接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getCategorys($catClass)
    {
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getCategorys');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["catClass" => $catClass];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }


    /**
     * @name:        查询商品详细信息接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsInfo($skuNum, $fields = [])
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsInfo');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["skuNum" => $skuNum];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            if ($fields) {
                foreach ($fields as $key => $field) {
                    $data[$field] = $resultArr['result'][$field];
                }
            } else {
                $data = $resultArr['result'];
            }
          
            return $data;
        } else {
            return false;
        }
    }

    /**
     * @name:        同类商品查询
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsSimilarSku($skuNum)
    {
       
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsSimilarSku');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["skuNum" => $skuNum];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }
    
    /**
     * @name:        获取图片
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsPic($skuStr)
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsPic');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["sku" => $skuStr];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        查询商品
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function GoodsSearch($condition)
    {
       
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/GoodsSearch');
        $ParameterInfo->SetVersion('1.0');
        
        $businessParamsJson = json_encode($condition);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }
    
    /**
     * @name:        通过商品编号获取商品信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function getVopGoodsInfo($skuNum)
    {
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsInfo');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["skuNum" => $skuNum];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return [];
        }
    }
    /**
     * @name:        查询赠品信息接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsGift($sku, $province, $city, $county, $town = 0)
    {
       
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsGift');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["sku" => $sku,'province' => $province,'city' => $city,'county' => $county,'town' => $town];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } elseif ($resultArr && $resultArr['resultCode'] == '0010' && $resultArr['success'] == 'false') {
            return [];
        } else {
            return false;
        }
    }

    /**
     * @name:        获取一级地址
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getProvinces()
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Address/getProvinces');
        $ParameterInfo->SetVersion('1.0');
        $result = \Address::getAddressUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }
    
    /**
     * @name:        查询二级地址
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getCitys($addId)
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Address/getCitys');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["addId" => $addId];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Address::getAddressUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        查询三级地址
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getCountys($addId)
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Address/getCountys');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["addId" => $addId];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Address::getAddressUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }
    
    /**
     * @name:        查询四级地址
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getTowns($addId)
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Address/getTowns');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["addId" => $addId];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Address::getAddressUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        验证地址是否正确
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function checkAddress($provinceId, $cityId, $countyId, $townId = 0)
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Address/checkAddress');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["provinceId" => $provinceId,"cityId" => $cityId,"countyId" => $countyId,"townId" => $townId];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Address::getAddressUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        查询商品Usdt价格
     * @author:      gz
     * @description:
     * @param        {type} $skuNum array
     * @return:
     */
    public static function getUsdtPrice($skuNum)
    {
        
        is_array($skuNum) ? $skuNumStr = implode(',', $skuNum) : $skuNumStr = $skuNum;
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getPrice');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ['sku' => $skuNumStr];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            if (is_string($skuNum) || count($skuNum) == 1) {
                $price = $resultArr['result'][0]['price'];
                $percent = PriceRule::getPricePercent($price);
                $price = $price * (1 + $percent / 100);
                $rate =  Rate::where(1)->value('USDRate');
                $usdtPrice = round($price / $rate, config('app.float_num'));
                return $usdtPrice;
            }
            return $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        查询商品价格并转为usdt
     * @author:      gz
     * @description:
     * @param        {type} $skuNum array
     * @return:
     */
    public static function getPrice($skuNum)
    {
        
        is_array($skuNum) ? $skuNumStr = implode(',', $skuNum) : $skuNumStr = $skuNum;
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getPrice');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ['sku' => $skuNumStr];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            if (is_string($skuNum) || count($skuNum) == 1) {
                $price = $resultArr['result'][0]['price'];
                return $price;
            }
            return $resultArr['result'];
        } else {
            return false;
        }
    }
    /**
     * @name:        查询商品价格并转为usdt
     * @author:      gz
     * @description:
     * @param        {type} $skuNum array
     * @return:
     */
    public static function upGetPrice($skuNum)
    {
        
        is_array($skuNum) ? $skuNumStr = implode(',', $skuNum) : $skuNumStr = $skuNum;
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getPrice');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ['sku' => $skuNumStr];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            if (is_string($skuNum) || count($skuNum) == 1) {
                $priceArr = $resultArr['result'][0];
                return $priceArr;
            }
            return $resultArr['result'];
        } else {
            return false;
        }
    }
    /**
     * @name:        查询商品库存
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getStock($skuJson, $areaStr = '0_0_0')
    {
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getStock');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["sku" => $skuJson,"area" => $areaStr];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
       
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }
    /**
     * @name:        地区购买限制
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsAreaLimit($sku = '', $province, $city, $county, $town = 0)
    {
        //echo $province.'_'.$city.'_'.$county.'_'.$town;
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsAreaLimit');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["sku" => $sku,'province' => $province,'city' => $city,'county' => $county,'town' => $town];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }

        /**
     * @name:        查询运费rmb
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsRmbFreight($skuJson, $province, $city, $county, $town = 0)
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsFreight');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = [
            "sku" => $skuJson,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'town' => $town,
            "paymentType" => 4
        ];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            $price = $resultArr['result']['freight'];
            if ($price) {
                return $price;
            } else {
                return 0;
            }
        } else {
            return false;
        }
    }
    /**
     * @name:        查询运费并转为usdt
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsFreight($skuJson, $province, $city, $county, $town = 0)
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getGoodsFreight');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = [
            "sku" => $skuJson,
            'province' => $province,
            'city' => $city,
            'county' => $county,
            'town' => $town,
            "paymentType" => 4
        ];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            $price = $resultArr['result']['freight'];
            
            if ($price) {
                $rate =  Rate::where(1)->value('USDRate');
                $usdtPrice = ceil($price * 100 / $rate) / 100;
                return $usdtPrice;
            } else {
                return 0;
            }
           
            return $price;
        } else {
            return false;
        }
    }

    
    /**
     * @name:        配送日历
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getPromiseCalendar($skuJson, $province, $city, $county, $town = 0)
    {
       
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Goods/getPromiseCalendar');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["sku" => $skuJson,'province' => $province,'city' => $city,'county' => $county,'town' => $town,"paymentType" => 4];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson);
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        统一下单
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function createOrder($orderInfo)
    {
        list($orderObj,$addressObj,$goodsJson) = $orderInfo;
        $input = new \OrderInfo();
        //设置参数
        $input->SetMethod('Order/createOrder'); //提交方法
        $input->SetVersion('1.0');
        
        $input->SetthirdOrder($orderObj->orderSn);//必填 第三方的订单单号（客户根据自己规则定义，不超20位）
        $input->Setsku($goodsJson);//必填 [{"skuNum":商品编号, "num":商品数量,"bNeedAnnex":true, "bNeedGift":false }](最高支持一次对50个不同sku下单)bNeedAnnex：表示是否需要附件，默认每个订单都给附件，默认值为：true，如果客户实在不需要附件bNeedAnnex可以给false，该参数配置为false时请谨慎，真的不会给客户发附件的;bNeedGift：表示是否需要赠品，如赠品无货，可以给false，不影响主商品下单
        $input->Setname($addressObj->userName);//必填 收货人（少于10字）
        $input->Setprovince($addressObj->procince);//必填 一级地址id
        $input->Setcity($addressObj->city);//必填 二级地址id
        $input->Setcounty($addressObj->county);//必填 三级地址id
        $input->Settown($addressObj->towwn);//必填 四级地址id
        $input->Setaddress($addressObj->addressDetails);//必填 详细地址（少于30字）
        $input->Setzip("");//非必填 邮编
        $input->Setphone("");//非必填 座机号
        $input->Setmobile($addressObj->userPhone);//必填 手机号
        $input->Setemail(config('app.email'));//必填 邮箱（接收订单邮件，买断模式可传B端商家邮箱地址）
        $input->Setremark($orderObj->orderRemarks);//非必填 备注（少于100字）
        $input->SetinvoiceState("2");//必填 2为集中开票(买断模式固定传2)
        $input->SetinvoiceType("2");//必填 2增值税发票
        $input->SetselectedInvoiceTitle("5");//必填 5单位（买断模式下仅传5）
        $input->SetcompanyName("XX有限公司");//非必填 发票抬头  (如果selectedInvoiceTitle=5则此字段必传)
        $input->SetinvoiceContent("1");//必填 备注:若增值发票则只能选 1明细
        $input->SetbookInvoiceContent("");//非必填 bookInvoiceContent=4(图书普票随货开必传，其他不传)
        $input->SetpaymentType("4");//必填 4：在线支付（余额支付）买断业务预存款客户下单传4
        $input->SetisUseBalance("1");//必填 买断业务预存款下单固定传1，使用余额
        $input->SetsubmitState("0");//必填 是否预占库存：0是预占库存（后续需要调用确认预占库存订单接口）;
        $input->SetreservingDate($orderObj->reservingDate);//非必填 大家电配送日期 默认值为-1，0表示当天，1表示明天，2：表示后天; 如果为-1表示不使用大家电预约日历
        $input->SetinstallDate("-1");//非必填 大家电安装日期 不支持默认按-1处理，0表示当天，1表示明天，2：表示后天
        $input->SetneedInstall("false");//非必填 大家电是否选择了安装 是否选择了安装，默认为true，选择了“暂缓安装”，此为必填项，必填值为false。
        $input->SetpromiseDate($orderObj->promiseDate);//非必填 中小件配送预约日期  格式：yyyy-MM-dd
        $input->SetpromiseTimeRange($orderObj->promiseTimeRange);//非必填 中小件配送预约时间段 时间段如： 9:00-15:00
        $input->SetpromiseTimeRangeCode($orderObj->timeRangeCode);//非必填 中小件预约时间段的标记
        $input->SetreservedDateStr($orderObj->reservedDateStr);//非必填 家电配送预约日期，格式：yyyy-MM-dd
        $input->SetreservedTimeRange($orderObj->reservedTimeRange);//非必填 大家电配送预约时间段，如果：9:00-15:00
        $input->SetdoOrderPriceMode("0");//必填 下单价格模式 0:客户端订单价格快照不做验证对比，还是以对接端价格正常下单;1:必需验证客户端订单价格快照，如果快照与对接端价格不一致返回下单失败，需要更新商品价格后，重新下单;
        $input->SetorderPriceSnap('');//非必填 客户端订单价格快照（C端用户在B端平台下单时的价格）Json格式的数据，'[{"price":330,"skuNum":sc0000000011}]'
        try {
            $result = \Order::createOrder($input);
            $resultArr = json_decode($result, true);

            if ($resultArr && $resultArr['resultCode'] == '0000') {
                return $resultArr['result'];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            print_r('Message: ' . $e->getMessage());
        }
    }

    /**
     * @name:        确认预占库存订单接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function occupyStockConfirm($ylOrderId)
    {

        $input = new \OrderInfo();
        $input->SetMethod('Order/occupyStockConfirm'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单号
        $result = \Order::occupyStockConfirm($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return true;
        } else {
            return false;
        }
    }
    /**
     * @name:        支付订单
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function orderDoPay($ylOrderId)
    {

        $input = new \OrderInfo();
        $input->SetMethod('Order/occupyStockConfirm'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单号
        $result = \Order::occupyStockConfirm($input);
     
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @name:        取消未确认订单接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function cancelOrder($ylOrderId)
    {

        $input = new \OrderInfo();
        //设置参数
        $input->SetMethod('Order/cancelOrder'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单号
        $result = \Order::cancelOrder($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @name:        厂家直送订单确认
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function openConfirmreceivedQuery($ylOrderId)
    {
        $input = new \OrderInfo();
        //设置参数
        $input->SetMethod('Order/openConfirmreceivedQuery'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单号
        $result = \Order::openConfirmreceivedQuery($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }
    /**
     * @name:        查询源链订单信息接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function selectylOrderQuery($ylOrderId)
    {
 
        $input = new \OrderInfo();
        $input->SetMethod('Order/selectylOrderQuery'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单号
        $input->SetqueryExts("orderType,ylOrderState");//非必填 扩展参数，支持单个状态，多个状态查询[逗号间隔]orderType及ylOrderState
        $result = \Order::selectylOrderQuery($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }
    /**
     * @name:        查询源链订单配送信息接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function orderTrackQuery($ylOrderId)
    {
 
        $input = new \OrderInfo();
        //设置参数
        $input->SetMethod('Order/orderTrackQuery'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单号
        $result = \Order::orderTrackQuery($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        校验某订单中某商品是否可以提交售后服务
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function availableNumberCompQuery($ylOrderId, $skuNum)
    {
        $input = new \AfterSaleInfo();
        //设置参数
        $input->SetMethod('AfterSale/availableNumberCompQuery'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单号
        $input->SetskuNum($skuNum);//必填 源链商品编号
        $result = \AfterSale::availableNumberCompQuery($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        查询支持的售后服务类型
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function customerExpectCompQuery($ylOrderId, $skuNum)
    {
        $input = new \AfterSaleInfo();

        $input->SetMethod('AfterSale/customerExpectCompQuery'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单号
        $input->SetskuNum($skuNum);//必填 源链商品编号
        $result = \AfterSale::customerExpectCompQuery($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        服务单创建申请
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function afsApplyCreate($orderServicesOjb)
    {
        $asCustomerDtoJson = json_encode(
            [
            'customerContactName' => $orderServicesOjb->customerContactName,
            'customerTel' => $orderServicesOjb->customerTel,
            'customerMobilePhone' => $orderServicesOjb->customerMobilePhone,
            'customerEmail' => $orderServicesOjb->customerEmail,
            'customerPostcode' => $orderServicesOjb->customerPostcode,
            ],
            JSON_UNESCAPED_UNICODE
        );
       
        $asPickwareDtoJson = json_encode(
            [
            'pickwareType' => $orderServicesOjb->pickwareType,
            'pickwareProvince' => $orderServicesOjb->pickwareProvince,
            'pickwareCity' => $orderServicesOjb->pickwareCity,
            'pickwareCounty' => $orderServicesOjb->pickwareCounty,
            'pickwareVillage' => $orderServicesOjb->pickwareVillage,
            'pickwareAddress' => $orderServicesOjb->pickwareAddress,
            ],
            JSON_UNESCAPED_UNICODE
        );
        $asReturnwareDtoJson = json_encode(
            [
            'returnwareType' => $orderServicesOjb->returnwareType,
            'returnwareProvince' => $orderServicesOjb->returnwareProvince,
            'returnwareCity' => $orderServicesOjb->returnwareCity,
            'returnwareCounty' => $orderServicesOjb->returnwareCounty,
            'returnwareVillage' => $orderServicesOjb->pickwareVillage,
            'returnwareAddress' => $orderServicesOjb->returnwareAddress,
            ],
            JSON_UNESCAPED_UNICODE
        );
        $asDetailDtoJson = json_encode(
            [
            'skuNum' => $orderServicesOjb->skuNum,
            'skuNumber' => $orderServicesOjb->skuNumber,
            ],
            JSON_UNESCAPED_UNICODE
        );

        $input = new \AfterSaleInfo();
        //设置参数
        $input->SetMethod('AfterSale/afsApplyCreate'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($orderServicesOjb->ylOrderId);//必填 源链订单号
        $input->SetcustomerExpect($orderServicesOjb->customerExpect);//非必填 客户预期（退货(10)、换货(20)、维修(30)）,该数值取于3.5查询支持的售后服务类型
        $input->SetquestionDesc($orderServicesOjb->questionDesc);//非必填 产品问题描述，最多1000字符
        $input->SetisNeedDetectionReport(false);//非必填 是否需要检测报告
        $input->SetquestionPic($orderServicesOjb->questionPic);//非必填 问题描述图片最多2000字符，上传图片访问地址：不同图片英文逗号分割
        $input->SetisHasPackage("");//非必填 是否有包装
        $input->SetpackageDesc("");//非必填 包装描述 0 无包装 10 包装完整 20 包装破损
        $input->SetasCustomerDto($asCustomerDtoJson);//必填 客户信息实体
        $input->SetasPickwareDto($asPickwareDtoJson);//必填 取件信息实体
        $input->SetasReturnwareDto($asReturnwareDtoJson);//必填 返件信息实体
        $input->SetasDetailDto($asDetailDtoJson);//必填 申请单明细
        $result = \AfterSale::afsApplyCreate($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }
    /**
     * @name:        查询服务单概要信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function serviceListPageQuery($ylOrderId)
    {
        $input = new \AfterSaleInfo();
        //设置参数
        $input->SetMethod('AfterSale/serviceListPageQuery'); //提交方法
        $input->SetVersion('1.0');
        $input->SetylOrderId($ylOrderId);//必填 源链订单单号 //SPS0409759893
        $input->SetpageSize("100");//必填 每页记录数
        $input->SetpageIndex("1");//必填 页码
        $result = \AfterSale::serviceListPageQuery($input);
        
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }


    /**
     * @name:        查询服务单明细信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function serviceDetailInfo($afsNum, $steps = "[1,2,4,5]")
    {
        $input = new \AfterSaleInfo();
        $input->SetMethod('AfterSale/serviceDetailInfo'); //提交方法
        $input->SetVersion('1.0');
        $input->SetAfsNum($afsNum);//必填 服务单号
        $input->SetappendInfoSteps($steps);//非必填 获取信息模块 不设置数据表示只获取服务单主信息、商品明细以及客户信息；1、代表增加获取售后地址信息，即客户发运时填写的地址 2、代表增加获取客户发货信息 4、增加获取服务单处理跟踪信息 5、获取允许的操作信息
        $result = \AfterSale::serviceDetailInfo($input);
        $resultArr = json_decode($result, true);
        
        if ($resultArr && $resultArr['resultCode'] === '0') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }


    /**
     * @name:        填写客户发运信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function sendSkuUpdate($AfsNum, $freightMoney, $expressCompany, $deliverDate, $expressCode)
    {
        $input = new \AfterSaleInfo();
        //设置参数
        $input->SetMethod('AfterSale/sendSkuUpdate'); //提交方法
        $input->SetVersion('1.0');
        $input->SetAfsNum($AfsNum);//必填 服务单号
        $input->SetfreightMoney($freightMoney);//必填 运费
        $input->SetexpressCompany($expressCompany);//必填 发运公司：圆通快递、申通快递、韵达快递、中通快递、宅急送、EMS、顺丰快递
        $input->SetdeliverDate($deliverDate);//2020-04-08 10:50:21必填 发货日期，格式为yyyy-MM-dd HH:mm:ss
        $input->SetexpressCode($expressCode);//必填 货运单号，最大50字符
        $result = \AfterSale::sendSkuUpdate($input);
        $resultArr = json_decode($result, true);
       
        if ($resultArr && $resultArr['resultCode'] === '0000') {
            return  true;
        } else {
            return false;
        }
    }


    /**
     * @name: 退款信息查询
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function aftersaleOrderInfo($afsNum, $ylOrderId)
    {
        $input = new \AfterSaleInfo();
        //设置参数
        $input->SetMethod('AfterSale/aftersaleOrderInfo'); //提交方法
        $input->SetVersion('1.0');
        $input->SetAfsNum($afsNum);//非必填 服务单号
        $input->SetylOrderId($ylOrderId);//必填 订单号
        $result = \AfterSale::aftersaleOrderInfo($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] === '0000') {
            return $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:  取消服务单/客户放弃
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function auditCancelQuery($afsNumList)
    {
        $input = new \AfterSaleInfo();
        //设置参数
        $input->SetMethod('AfterSale/auditCancelQuery'); //提交方法
        $input->SetVersion('1.0');
        $input->SetAfsNumList($afsNumList);//必填 服务单号集合
        $input->SetapproveNotes("通过");//必填 审核意见
        $result = \AfterSale::auditCancelQuery($input);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @name:        信息推送接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getMessage($type, $messageType)
    {

        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Message/getMessage');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["type" => $type,'messageType' => $messageType];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Message::getMessageUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            return  $resultArr['result'];
        } else {
            return false;
        }
    }

    /**
     * @name:        信息删除接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function delMessage($idString)
    {
    
        $ParameterInfo = new \ParameterInfo();
        $ParameterInfo->SetMethod('Message/delMessage');
        $ParameterInfo->SetVersion('1.0');
        $businessParams = ["msg_id" => $idString];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Message::getMessageUnify($ParameterInfo);
        return $result;
    }
    /**
     * @name:        是否下架
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getGoodsState($skuNum)
    {
        is_array($skuNum) ? $skuNumStr = implode(',', $skuNum) : $skuNumStr = $skuNum;
        $ParameterInfo = new \ParameterInfo();
        //设置参数
        $ParameterInfo->SetMethod('Goods/getGoodsState');
        $ParameterInfo->SetVersion('1.0');
        //业务参数示例
        $businessParams = ["sku" => $skuNumStr];
        $businessParamsJson = json_encode($businessParams);
        $ParameterInfo->SetBusinessParams($businessParamsJson); //
        $result = \Goods::getGoodsUnify($ParameterInfo);
        $resultArr = json_decode($result, true);
        if ($resultArr && $resultArr['resultCode'] == '0000') {
            if (is_string($skuNum) || count($skuNum) == 1) {
                $state = $resultArr['result'][0]['state'];
                return $state;
            }
            return  $resultArr['result'];
        } else {
            return false;
        }
    }
}
