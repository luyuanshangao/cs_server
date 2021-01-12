<?php

namespace app\common\model;

class ExtensionDealDetail extends BaseModel
{
    //创建数据
    public static function profitAdd($data)
    {
        self::create($data);
        return true;
    }
   /**
     * @name: 获取器将一笔收益分为4份
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function getdetailsDateAttr($value, $data)
    {
        //等级
        
        $extensionId = ExtensionUserLog::getNowGrade($data['userId']);
       
        $detailsData = [];
        $rules = [
            ['name' => '商品推广金','weight' => '0.5'],
            ['name' => '级别奖励','weight' => '0.3'],
            ['name' => '浏览商品','weight' => '0.1'],
            ['name' => '分享奖励','weight' => '0.1'],
        ];
        $createTime = date('Y-m-d H:i:s', $data['createTime']);
       
        switch ($data['lock']) {
            case 0:
                break;
            case 1:
                foreach ($rules as $key => $value) {
                    $detailsData[] = [
                        'name' => $value['name'],
                        'amount' => (string)bcmul($data['amount'], $value['weight']),
                        'createTime' => $createTime,
                    ];
                }

                break;
            case 2:
                $detailsData[] = [
                    'name' => '购物退款',
                    'amount' =>(string)$data['amount'],
                    'createTime' => $createTime,
                ];
                break;
            default:
                break;
        }
     
        return $detailsData;
    }

    //退款添加 收益记录
    public static function refundAdd($dealId)
    {
        $list = self::where(['dealId' => $dealId])->column('amount', 'userId');
      
        foreach ($list as $userId => $amount) {
            self::profitAdd([
                'dealId' => $dealId,
                'userId' => $userId,
                'amount' => $amount,
                'lock' => 2,
                'updateTime' => time(),
                'createTime' => time(),
            ]);
           
            //账户数值变化
            ExtensionAssets::upAmount($userId, $amount, 2, 2, '购物退款');
        }
    }
}
