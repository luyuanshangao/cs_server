<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

use app\common\library\redis\OrderDelayed;

class IdleDealDisputeEvidence extends BaseModel
{
    public function getContentAttr($value, $data)
    {
        $userInfo = User::get(['userId' => $data['userId']]);
        $idleInfoId = IdleDealDispute::where(['idleDealDisputeId' => $data['idleDealDisputeId']])->value('idleInfoId');
        $idleInfo = IdleInfo::get(['idleInfoId' => $idleInfoId]);
        if ($idleInfo) {
            try {
                $pic = explode(',', $idleInfo['picPath']);
            } catch (\Exception $th) {
                $pic = '';
            }
          
            $returnData = [
                'userName' => $userInfo['userName'],
                'picArr' => $pic,
                'content' => $data['content'],
                'createTime' => date('Y-m-d H:i:s', $data['createTime']),

            ];
            return $returnData;
        }
    }
}
