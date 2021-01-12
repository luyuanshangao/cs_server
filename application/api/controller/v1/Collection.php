<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\UserCollection;

class Collection extends Base
{
    protected $noAuthArr = [];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name:        收藏列表
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function listCollection()
    {
        $getArr  = $this->checkdate('Collection', 'get', 'list');
        $UserCollectionModel = new UserCollection();
        $this->getPageAndSize($getArr);
        $condition = [
            'userId' => $this->userId
        ];
        isset($getArr['keyword']) && !empty($getArr['keyword']) ? $condition['spuName'] = ['like','%' . $getArr['keyword'] . '%'] : '';
        $total = $UserCollectionModel->getCount($condition);
        $list = $UserCollectionModel->getList($condition, $this->from, $this->size, true, $this->sort);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
    
    /**
     * @name:        添加 取消 收藏
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function addCollection()
    {
        $dataArr  = $this->checkdate('Collection', 'post', 'add');
        $skuNum  = $dataArr['skuNum'];
        $UserCollectionModel = new UserCollection();
        $resultObj = $UserCollectionModel->isCollection($skuNum, $this->userId);
        
        if ($resultObj) {
            $resultObj->delete();
        } else {
            try {
                $price = $this::toUsdt($this::obtainGoodsInfo($dataArr['skuNum'], 'price'));
                $pic = $this::obtainGoodsInfo($dataArr['skuNum'], 'pic');
                $spuName =  $this::obtainGoodsInfo($skuNum, 'spu_name');
                if (!$price || !$pic || !$spuName) {
                    throw new \Exception("Error Processing Request", 1);
                }
                $createArr = [
                    'userId' => $this->userId,
                    'skuNum' => $skuNum,
                    'spuName' =>  $spuName,
                    'priceUsdt' => $price,
                    'pic' => $pic,
                    'createTime' => time(),
                ];
                $UserCollectionModel->add($createArr);
            } catch (\Exception $th) {
                return show(0);
            }
        }
        return show(1);
    }

    /**
     * @name:        删除收藏
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function delCollection()
    {

        $dataArr  = $this->checkdate('Collection', 'post', 'del');
        $UserCollectionModel = new UserCollection();
        $UserCollectionModel->del(json_decode($dataArr['skuNumArr'], true), $this->userId);
        return show(1);
    }

    /**
     * @name:        用户收藏数
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function collectionNum()
    {
        
        $UserCollectionNum = UserCollection::getNum($this->userId);
        return show(1, ['num' => $UserCollectionNum]);
    }
}
