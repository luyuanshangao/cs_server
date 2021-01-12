<?php

namespace app\api\validate;

class Goods extends \think\Validate
{
    //规则
    protected $rule = [
        ['page','number', '页数为数字'],
        ['size','number', '页数大小为数字'],
        ['skuNum','require|alphaNum', '商品编号不能为空|商品编号错误'],
        ['sku','require|alphaNum', '商品编号不能为空|商品编号错误'],
        ['add_id','require|number', '地址不能为空|地址错误'],
        ['parentId','require|number', '商品分类不能为空|商品分类错误'],
        ['provinceId','require|number', '省份不能为空|省份错误'],
        ['cityId','require|number', '市不能为空|市错误'],
        ['countyId','require|number', '区不能为空|区错误'],
        ['townId','number', '镇不能为空|镇错误'],
        ['num','number', '数量为数字'],
        ['areaIdPath','require', '所在地区必须'],
        ['keyword','max:20', '请输入搜索内容|搜索内容过长'],
        ['skuNumArr','require|checkSkuNumArr', '商品参数必需|商品参数错误'],
        ['skuNumArrs','require|checkJson', '商品参数必需|商品参数错误'],
        ['type','number', '类型错误'],
        ['catId','number', '类型错误'],
    ];

    //场景不同场景验证不同的字段
    protected $scene = [

        'search' => ['keyword','page','size','catId'],
        'searchS' => ['keyword'],
        'discover' => ['size'],
        'guess' => ['size'],
        'recommend' => ['size'],
        'getGoodsInfo' => ['skuNum'],
        'getGoodsGift' => ['skuNum','areaIdPath'],
        'address' => ['add_id'],
        'checkAddress' => ['provinceId','cityId','countyId','townId'],
        'getCategory' => ['parentId'],
        'getDelivery' => ['skuNum','provinceId','cityId','countyId','townId'],
        'cart' => ['skuNum','num'],
        'delCart' => ['skuNumArrs'],
        'getStock' => ['skuNum','num','provinceId','cityId','countyId','townId'],
        'getFreight' => ['skuNumArrs','provinceId','cityId','countyId','townId'],
        'getPromiseCalendar' => ['skuNumArrs','provinceId','cityId','countyId','townId'],
        'appraisesList' => ['page','size','skuNum','type'],
        'getEstimate' => ['skuNum'],
        
    ];
    protected function checkJson($value, $rule, $data)
    {
        $checkData  = json_decode($value, true);
        if (!is_array($checkData)) {
            return false;
        }
        return true;
    }
    protected function checkSkuNumArr($value, $rule, $data)
    {

        if (!is_array($value)) {
            return false;
        }
        
        foreach ($value as $k => $v) {
            if (!isset($v['skuNum']) && !isset($v['num'])) {
                return false;
            }
            if (!is_numeric($v['num'])) {
                return false;
            }
        }
        
        return true;
    }
}
