<?php

/*
 * @Descripttion:
 * @Author: gz
 */


namespace app\common\model;

class GoodsAppraises extends BaseModel
{

    /**
     * @name: 获取器
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function getUserInfoAttr($value, $data)
    {
     
      //,'userName'
        $dataR = User::where(['userId' => $data['userId']])->field('userName,avatar')->find();
        $strLen = hideStr($dataR->userName);
        $dataR->userName  = $strLen;
        return $dataR;
    }
    /**
     * @name: 获取器
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function getImgsAttr($value, $data)
    {
     
        if ($data['imgUrl']) {
            return explode(',', $data['imgUrl']);
        }
        return [];
    }
    /**
     * @name:        添加商品评论
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function addAppraises($userId, $data)
    {
        $orderGoodsObj = OrderGoods::get(['orderGoodsId' => $data['orderGoodsId']]);
        $saveData = [
            'userId' => $userId,
            'orderGoodsId' => $data['orderGoodsId'],
            'orderSn' => $data['orderSn'],
            'skuNum' => $data['skuNum'],
            'spuName' => $orderGoodsObj['spuName'],
            'goodsScore' => $data['goodsScore'],
            'packingScore' => $data['packingScore'],
            'speedScore' => $data['speedScore'],
            'serviceScore' => $data['serviceScore'],
            'content' => $data['content'] ? $data['content'] : '此用户没有填写评论',
            'imgUrl' => isset($data['imgUrl']) && !empty($data['imgUrl'])  ? implode(',', json_decode($data['imgUrl'], true)) : '',
            'createTime' => time()
        ];
       
        self::create($saveData);
        return true;
    }
    /**
     * @name: 评价数
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getAppraisesNum($skuNum)
    {
        $condition = [
            'isShow' => 1,
            'skuNum' => $skuNum,
        ];
        return self::where($condition)->count();
    }
    /**
     * @name: 获取商品详情页最新评价
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function getNewAppraises($skuNum)
    {
        $condition = [
            'isShow' => 1,
            'skuNum' => $skuNum,
        ];

        $dataObj = self::get($condition);
        if (!$dataObj) {
            return null;
        }
        $userObj = User::get(['userId' => $dataObj->userId]);
      
        $returnData = [
            'userName' => $userObj->userName,
            'avatar' => $userObj->avatar,
            'content' => $dataObj->content,
            'imgUrl' => explode(',', $dataObj->imgUrl),
        ];
        return $returnData;
    }
}
