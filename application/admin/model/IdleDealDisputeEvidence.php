<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\admin\model;
use app\common\model\BaseModel;
use think\Db;
class IdleDealDisputeEvidence extends BaseModel
{
    public function getViewList($condition = [])
    {

        $order = 'IdleDealDisputeEvidence.createTime desc';
        $result = Db::view('IdleDealDisputeEvidence', true)
                ->view('User', ['userName'], 'User.userId=IdleDealDisputeEvidence.userId','LEFT')
                ->where($condition)
                ->order($order)
                ->select();
                
        foreach ($result as $key => &$value) {
            try {
               
                $pic =  explode(',',$value['picPath']);;
            } catch (\Throwable $th) {
                $pic = [];
            }
            $value['picPath'] = $pic;
            $value['createTime'] = date("Y-m-d H:i:s",$value['createTime'] );
            $value['isWho'] == 'sell' ? $value['isWho'] = '卖家' : $value['isWho'] = '买家';
         
        }

        return $result;
    }
}
