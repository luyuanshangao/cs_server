<?php

namespace app\common\model;

class ExtensionUser extends BaseModel
{
    
    /**
     * @name: 初始化
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function createData($userId)
    {
        try {
            $dataUser = [
                'userId' => $userId,
                'lose' => 0,
                'extensionId' => 2,    //直接开通就是试用初级
                'createTime' => time(),
            ];
            //创建用户
            self::create($dataUser);
            //等级日志
            ExtensionUserLog::addLog($userId, 1);
            //创建钱包信息
            ExtensionAssets::createAssets($userId);
        } catch (\Exception $th) {
            return true;
        }
    }

    /**
     * @name: 是否开通推广
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function hasExtensionAuth($userId)
    {
        $data = self::get(['userId' => $userId]);
        if ($data) {
            $state = 1;
            if ($state['lose']) {
                $state = 2;
            }
        } else {
            $state = 0;
        }
        return $state;
    }

    /**
     * @name: 用户级别
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public static function extensionName($userId)
    {
        $extension = Extension::getExtension();
        if (!$extension) {
            return '普通用户';
        }
        $data = self::get(['userId' => $userId]);
        if ($data) {
            $name = $extension[$data['extensionId']];
        } else {
            $name = '普通用户';
        }
        return $name;
    }

    /**
     * @name: 等级变动
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function changeGrade($userId)
    {

        #邀请人等级变动
        //邀请人当前等级
        $superiorUserData = self::get(['userId' => $userId]);
        $superiorId = ExtensionInvitation::where(['userId' => $userId])->value('superiorId'); //邀请人id
        $upTimeStr = ExtensionUserLog::getUpGradeTime($userId);
        if (!$superiorUserData) {
            return ;
        }
        if ($superiorUserData['lose']) {
            return ;
        }
        
        switch ($superiorUserData['extensionId']) {
            case 1:
                    //普通用户 邀请10人升级
                    $count = ExtensionInvitation::where(['superiorId' => $userId])->count(); //已邀请数量
                   
                if ($count >= 10) {
                    //升级
                    $superiorUserData->extensionId = 2;
                    $superiorUserData->save();
                    ExtensionUserLog::addLog($userId, 2);
                   
                    if ($superiorId) {
                        $this->changeGrade($superiorId);
                    }
                }
                
                
                break;
            case 2:
                    //试用初级  10人购买升级
                    $count = ExtensionDeal::where(['superiorId' => $userId])->count();  //成交记录

                if ($count >= 10) {
                    //升级
                    $superiorUserData->extensionId = 3;
                    $superiorUserData->save();
                    ExtensionUserLog::addLog($userId, 3);
                    $this->changeGrade($userId);
                    if ($superiorId) {
                        $this->changeGrade($superiorId);
                    }
                }

                break;
            case 3:
                    //初级  邀请了10个初级

                    $childIdsUserIds = ExtensionInvitation::where(['superiorId' => $userId])->column('userId');   //成交记录
                    $count  = $this->where([
                        'userId' => ['in',$childIdsUserIds],
                        'extensionId' => 3,
                    ])->count();
                if ($count >= 10) {
                    //升级
                    $superiorUserData->extensionId = 4;
                    $superiorUserData->save();
                    ExtensionUserLog::addLog($userId, 4);
                    $this->changeGrade($userId);
                    if ($superiorId) {
                        $this->changeGrade($superiorId);
                    }
                }

                break;
            case 4:
                    //高级  邀请了10个高级

                    $childIdsUserIds = ExtensionInvitation::where(['superiorId' => $userId])->column('userId');   //成交记录
                    $count  = $this->where([
                        'userId' => ['in',$childIdsUserIds],
                        'extensionId' => 4,
                    ])->count();
                if ($count >= 10) {
                    //升级
                    $superiorUserData->extensionId = 5;
                    $superiorUserData->save();
                    ExtensionUserLog::addLog($userId, 5);
                    if ($superiorId) {
                        $this->changeGrade($superiorId);
                    }
                }

                break;
            
            default:
                # code...
                break;
        }
        return ;
    }

    /**
     * @name: 取消 试用 7天内未有10人下单的  推广权限
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public static function checkPromotionAuth()
    {

        $timeStr = time() - 86400 * 7;
        
        $userIdList = self::where([
            'extensionId' => 2,
            'createTime' => ['lt',$timeStr],
            'lose' => 0,
        ])->column('userId');
       
        foreach ($userIdList as $userId) {
            $count = ExtensionDeal::where(['superiorId' => $userId])->count();
            
            if ($count < 10) {
                self::update(['lose' => 1], ['userId' => $userId]);
            }
        }
    }
}
