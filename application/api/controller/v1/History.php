<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\UserHistory;

class History extends Base
{
    protected $noAuthArr = [];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name:        浏览记录列表
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function listHistory()
    {
       
        $getArr  = $this->checkdate('Collection', 'get', 'list');
        $UserHistoryModel = new UserHistory();
        $this->getPageAndSize($getArr);
        $condition = [
            'userId' => $this->userId
        ];
        
        isset($getArr['keyword']) && !empty($getArr['keyword']) ? $condition['spuName'] = ['like','%' . $getArr['keyword'] . '%'] : '';
        $total = $UserHistoryModel->getCount($condition);
        $list = $UserHistoryModel->getList($condition, $this->from, $this->size, true, 'createTime desc');
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        
        return show(1, $returnResult);
    }
    
    /**
     * @name:        添加浏览记录
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function addHistory()
    {

        $dataArr  = $this->checkdate('Collection', 'post', 'add');
        $skuNum  = $dataArr['skuNum'];
        $UserHistoryModel = new UserHistory();
        $resultObj = $UserHistoryModel->isHistory($skuNum, $this->userId);
      
        if ($resultObj) {
            //更新时间
            $resultObj->createTime = time();
            $resultObj->save();
        } else {
            try {
                $price = $this::toUsdt($this::obtainGoodsInfo($dataArr['skuNum'], 'price'));
                $pic = $this::obtainGoodsInfo($dataArr['skuNum'], 'pic');
                $spuName =  $this::obtainGoodsInfo($skuNum, 'spu_name');
                if (!$price || !$pic  || !$spuName) {
                    throw new \Exception("Error Processing Request", 1);
                }
                $createArr = [
                    'userId' => $this->userId,
                    'skuNum' => $skuNum,
                    'spuName' => $spuName,
                    'priceUsdt' =>  $price,
                    'pic' => $pic,
                    'createTime' => time(),
                ];
                 $UserHistoryModel->add($createArr);
            } catch (\Exception $th) {
            }
        }
        return show(1);
    }

    /**
     * @name:        删除浏览记录
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function delHistory()
    {

        $dataArr  = $this->checkdate('Collection', 'post', 'del');
        $UserHistoryModel = new UserHistory();
        $UserHistoryModel->del(json_decode($dataArr['skuNumArr'], true), $this->userId);
        return show(1);
    }

    /**
     * @name:        清空浏览记录
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function delAllHistory()
    {

        $UserHistoryModel = new UserHistory();
        $UserHistoryModel->where(['userId' => $this->userId])->delete();
        return show(1);
    }

    /**
     * @name:        用户记录数
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function historyNum()
    {
        $UserCollectionNum = UserHistory::getNum($this->userId);
        return show(1, ['num' => $UserCollectionNum]);
    }
}
