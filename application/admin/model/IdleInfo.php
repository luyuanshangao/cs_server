<?php

namespace app\admin\model;

use app\common\model\BaseModel;
use think\Db;

class IdleInfo extends BaseModel
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
            $condition['IdleInfo.createTime'] = $condition['createTime'];
            unset($condition['createTime']);
        }
        
        return Db::view('IdleInfo', 'userId')
        ->view('User', 'userId', 'User.userId=IdleInfo.userId')
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
            $condition['IdleInfo.createTime'] = $condition['createTime'];
            unset($condition['createTime']);
        }
 
        $result = Db::view('IdleInfo', 'idleInfoId,infoSn,userId,title,description,picPath,price,freightFee,verifyFee,condition,isVerify,verifyStatus,groundStatus,createTime')
                ->view('User', 'userName', 'User.userId=IdleInfo.userId')
                ->where($condition)
                ->limit($from, $size)
                ->order($order)
                ->select();
        foreach ($result as $key => &$value) {
            try {
                $pic =  explode(',', $value['picPath']);
                ;
            } catch (\Throwable $th) {
                $pic = [];
            }
            $value['picPath'] = $pic;
        }

        return $result;
    }
}
