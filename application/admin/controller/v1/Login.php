<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\common\library\IAuth;
use app\admin\validate\AdminLogin;
use app\admin\model\AuthAdmin;
use app\admin\model\AuthRoleAdmin;
use app\admin\model\AuthPermission;
use app\admin\model\AuthPermissionRule;
use think\captcha\Captcha;

/**
 * @name: 登录
 * @author: gz
 * @description: GET POST
 * @param {type}
 * @return:
 */
class Login extends Base
{
    protected $noAuthArr = ['index','getCaptcha'];    //接口白名单    不需要登录的接口方法
    protected $noCheckArr = ['getInfo'];    //权限白名单


    /**
     * @name:        后台登录接口
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function index()
    {

        $data = $this->request->post();
        $AdminLoginVilidate = new AdminLogin();
        $result = $AdminLoginVilidate->scene('login')->check($data);
        if (!$result) {
            $msg =  $AdminLoginVilidate->getError();
            return show(0, [], $msg);
        }
        $captcha = new Captcha();
        $resultCaptcha = $captcha->check($data['code'], $data['codeKey']);
        
        // if (!$resultCaptcha) {
        //     return show(9005);
        // }
       
        $pwd = $data['password'];
        $user_name = $data['username'];

        $AuthAdminModel = new AuthAdmin();
        $AdminData = $AuthAdminModel::get(['username' => $user_name]);
        if (!$AdminData) {
            return show(9001);
        }
        if (!$AdminData['status']) {
            return show(9002);
        }
       
        //f4f7d242d42005165423afa9c1fca10f
        if ($AdminData->getData('password') !== setPassword($pwd)) {
            return show(9003);
        }
        // 保存用户信息
        $AuthAdminTokenData = $AuthAdminModel->loginInfo($AdminData);
        //查找对应权限返回
        $returnData = [
            'adminId' => $AuthAdminTokenData['adminId'],
            'username' => $AdminData['username'],
            'token' => $AuthAdminTokenData['token'],
        ];
       
        return show(1, $returnData);
    }

    /**
     * @name: 获取验证码
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getCaptcha()
    {
        $data = $this->request->get();
        $captcha = new Captcha();
        return captcha($data['key']);
        return $captcha->entry($data['key']);
    }
    /**
     * @name:        用户权限信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function getInfo()
    {

        $res = $this->clientInfo;
        
        $authRules = [];
       
        if (!empty($res["username"]) && $res["username"] == "admin") {
            $authRules = ['admin'];
        } else {
            // 获取权限列表
            
            $roleIds = AuthRoleAdmin::where('adminId', $res['adminId'])->column('roleId');
            if ($roleIds) {
                $permissionRuleIds = AuthPermission::where('roleId', 'in', $roleIds)->column('permissionRuleId');
                $permissionRuleIds = array_unique($permissionRuleIds);
            
                $rules = AuthPermissionRule::where('permissionRuleId', "in", $permissionRuleIds)->column("name");
            
                $authRules = $rules ? $rules : [];
            }
        }
        
        $res['authRules'] = $authRules;
        $res['introduction'] = $res['username'];
        $res['name'] = $res['username'];
        return show(1, $res);
    }
}
