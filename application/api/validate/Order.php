<?php

namespace app\api\validate;

class Order extends \think\Validate
{
    //规则
    protected $rule = [
        ['page','number', '页数为数字'],
        ['size','number', '页数大小为数字'],
        ['type','number', '订单状态为数字'],
        
        ['skuNum','require|alphaNum', '商品编号不能为空|商品编号错误'],
        ['payType','require|number', '请选择支付方式|请选择支付方式'],
        ['addressId','require|number', '请添加收货地址|请添加收货地址'],
        ['skuNumArr','require|checkSkuNumArr', '商品信息不能为空|商品信息错误'],
        ['reservingDate','number', '配送日期为数字'],
        ['orderRemarks','max:100', '备注超过100字'],
        ['orderSn','require|max:20', '订单号必须|订单号过长'],
        ['goodsScore','require|in:1,2,3,4,5', '商品评价必须|商品评价错误'],
        ['packingScore','require|in:1,2,3,4,5', '快递包装评价必须|快递包装评价错误'],
        ['speedScore','require|in:1,2,3,4,5', '送货速度评价必须|送货速度评价错误'],
        ['serviceScore','require|in:1,2,3,4,5', '配送服务评价必须|配送服务评价错误'],
       
        ['content','max:200', '评价超过200字'],
        ['questionDesc','require|max:500', '请填写描述|描述超过500字'],
        ['skuNumber','require|number', '数量必须|数量错误'],
        ['afsNum','require', '售后单号不能为空'],
        ['orderGoodsId','require|number', '订单商品号不能为空|订单商品号错误'],

        ['servicesId','require|number', '服务单号不能为空|服务单号错误'],
        ['freightMoney','require|number', '运费不能为空|运费错误'],
        ['expressCompany','require', '发运公司不能为空'],
        ['deliverDate','require|checkData', '日期不能为空|日期格式错误'],
        ['expressCode','require|max:50', '货运单号不能为空|货运单号,最大50字符'],
    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'listOrder' => ['page','size','type'],
        'detailOrder' => ['orderSn'],
        'delOrder' => ['orderSn'],
        'confirmReceive' => ['orderSn'],
        'appraisesList' => ['page','size','type'],
        'appraises' => ['orderSn','skuNum','goodsScore','packingScore','speedScore','serviceScore','content'],
        'confirmOrder' => ['payType','addressId','skuNumArr','reservingDate','orderRemarks'],
        'payOrder' => ['orderSn'],
        'cacolOrder' => ['orderSn'],
        'services' => ['orderSn','skuNum','skuNumber','questionDesc'],
        'createServices' => ['orderSn','skuNum','skuNumber','questionDesc'],
        'listServices' => ['page','size'],
        'detailServices' => ['afsNum'],
        'cancelServices' => ['afsNum','orderGoodsId'],
        'sendSkuUpdate' => ['servicesId','afsNum','freightMoney','expressCompany','deliverDate','expressCode'],
    ];

    protected function checkSkuNumArr($value, $rule, $data)
    {

        $data = json_decode($value, true);
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $k => $v) {
            if (!isset($v['skuNum']) && !isset($v['num'])) {
                return false;
            }
            if (!is_numeric($v['num'])) {
                return false;
            }
        }
        
        return true;
    }
    protected function checkData($value, $rule, $data)
    {

        //匹配日期格式
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})$/", $value, $parts)) {
            return true;
        } else {
            return false;
        }
    }
}
