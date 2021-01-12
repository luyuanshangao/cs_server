<?php

namespace app\admin\model;

use app\common\model\BaseModel;
use think\Db;

class AssetsWithdraw extends BaseModel
{

    /**
     * @name: 根据条件来获取列表的数据的总数
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getViewCount($condition = [])
    {
        if (isset($condition['createTime']) && $condition['createTime']) {
            $condition['AssetsWithdraw.createTime'] = $condition['createTime'];
            unset($condition['createTime']);
        }
        return Db::view('AssetsWithdraw', 'userId')
        ->view('User', 'userId', 'User.userId=AssetsWithdraw.userId')
        ->where($condition)
        ->count();
    }

 
    /**
     * @name: 根据来获取列表的数据
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getViewList($condition = [], $from = 0, $size = 10, $order = '')
    {
        if (isset($condition['createTime']) && $condition['createTime']) {
            $condition['AssetsWithdraw.createTime'] = $condition['createTime'];
            unset($condition['createTime']);
        }
        $result = Db::view('AssetsWithdraw', 'withdrawId,userId,assetsType,amount,createTime,withdrawStatus,walletAddr,walletImg,walletAddr')
                ->view('User', 'userName', 'User.userId=AssetsWithdraw.userId')
                ->where($condition)
                ->limit($from, $size)
                ->order($order)
                ->select();

        return $result;
    }
}
