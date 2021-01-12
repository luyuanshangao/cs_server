<?php

namespace app\admin\model;

use app\common\model\BaseModel;
use app\admin\common\library\IAuth;
use think\Request;

class AuthAdmin extends BaseModel
{
    
    public function getRoleIdAttr($value, $data)
    {
        return AuthRoleAdmin::where(['adminId' => $data['adminId']])->value('roleId');
    }
    public function getPasswordAttr($value, $data)
    {
        return '';
    }
    /**
     * 登录信息记录
     */
    public function loginInfo($AdminData)
    {

        //更新token
        $AuthAdminTokenModel = new AuthAdminToken();
        $AuthAdminTokenData = $AuthAdminTokenModel::get(['adminId' => $AdminData->adminId]);
        if (!$AuthAdminTokenData) {
            $AuthAdminTokenData = AuthAdminToken::create([
                'token' => Iauth::setLoginToken($AdminData->username),
                'adminId' => $AdminData->adminId,
                'createTime' => time(),
                'updateTime' => time(),
            ]);
            return $AuthAdminTokenData;
        }
        
        $AuthAdminTokenData['token'] = Iauth::setLoginToken($AdminData->username);
        $AuthAdminTokenData['updateTime'] = time();
        $AuthAdminTokenData->save();
        
        //更新登录信息
        $AdminData->lastLoginIp = Request::instance()->ip();
        $AdminData->lastLoginTime = time();
        $AdminData->save();

        return $AuthAdminTokenData;
    }
}
