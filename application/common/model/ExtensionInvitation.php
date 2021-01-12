<?php

namespace app\common\model;

class ExtensionInvitation extends BaseModel
{


    public function getuserNameAttr($value, $data)
    {
        $userName = User::where(['userId' => $data['userId']])->value('userName');
        return $userName ? hideStr($userName) : '';
    }

    public function getextensionNameAttr($value, $data)
    {
        $extensionId = ExtensionUser::where(['userId' => $data['userId']])->value('extensionId');
        if (!$extensionId) {
            return '普通会员';
        }
        return Extension::where(['extensionId' => $extensionId])->value('extensionName');
    }

    public function getdateStrAttr($value, $data)
    {
        return date('Y-m-d', $data['createTime']);
    }
    
    /**
     * @name: 添加邀请信息
     * @author: gz
     * @description: $userId用户id $superiorId 邀请人用户id
     * @param {type}
     * @return:
     */
    public static function addInvitation($userId, $superiorId)
    {

        $invitUserData = self::get(['userId' => $superiorId]);
       
        if ($invitUserData) {
            $createData = [
                'userId' => $userId,
                'superiorId' => $superiorId,
                'parentId' => $invitUserData['invitationId'],
                'level' => $invitUserData['level'] + 1,
                'createTime' => time(),
            ];
        } else {
            $createData = [
                'userId' => $userId,
                'parentId' => 0,
                'superiorId' => $superiorId,
                'level' => 0,
                'createTime' => time(),
            ];
        }
        self::create($createData);
        (new ExtensionUser())->changeGrade($superiorId);
        return true;
    }



    public static function getChildIds($parentId)
    {
        return self::__get_ids($parentId, '', 'invitationId');
    }

    
    public static function getParentIds($invitationId)
    {
        return self::__get_ids($invitationId, '', 'parentId');
    }


    public static function __get_ids($parentId, $childids, $find_column = 'invitationId')
    {
        if (!$parentId || $parentId <= 0 || strlen($parentId) <= 0 || !in_array($find_column, array('invitationId', 'parentId'))) {
            return 0;
        }

        if (!$childids || strlen($childids) <= 0) {
            $childids = $parentId;
        }

        $column = ($find_column == 'invitationId' ? "parentId" : "invitationId");
 
        $invitationIds = self::where("$column in($parentId)")->column("$find_column");
        $invitationIds = implode(",", $invitationIds);

        if ($invitationIds <= 0) {
            return $childids;
        }
        
        //添加到集合中
        $childids .= ',' . $invitationIds;
        //递归查找
        return self::__get_ids($invitationIds, $childids, $find_column);
    }
}
