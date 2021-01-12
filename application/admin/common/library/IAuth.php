<?php

namespace app\admin\common\library;

use app\admin\model\AuthAdminToken;
use app\admin\model\AuthAdmin;
use app\admin\model\AuthPermission;
use app\admin\model\AuthPermissionRule;
use app\admin\model\AuthRoleAdmin;
use app\common\exception\ApiException;
use think\Loader;
use think\Request;

/**
 * Iauth相关
 * Class IAuth
 */
class IAuth
{

    /**
     * @name:        用户信息
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:      object
     */
    public static function getClient($noAuthBoolean, $noCheckArr = [])
    {
        
        if (!$noAuthBoolean) {
            $token = Request::instance()->header('token'); //获取请求中的header参数

            if (!$token) {
                throw new ApiException(9001);
            }

            $AuthAdminTokenData = AuthAdminToken::get(['token' => $token]);

            if (!$AuthAdminTokenData) {
                throw new ApiException(9006);
            }

            $clientInfo = AuthAdmin::get(['adminId' => $AuthAdminTokenData['adminId']]);

            if (!$clientInfo) {
                throw new ApiException(9001);
            }

            // 排除权限
            $noCheckArr = self::buildNowCheckArray($noCheckArr);

            self::checkAuth($clientInfo, $noCheckArr);

            return $clientInfo;
        }
        return '';
    }

    /**
     * @name:        获取权限列表
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function checkAuth($clientInfo, $noCheckArr)
    {

        $adminId = $clientInfo['adminId'];
        // 获取权限列表
        $authRules = [];
        $roleIds = AuthRoleAdmin::where('adminId', $adminId)->column('roleId');

        if ($roleIds) {
            $permissionRuleIds = AuthPermission::where('roleId', 'in', $roleIds)->column('permissionRuleId');
            $permissionRuleIds = array_unique($permissionRuleIds);

            $rules = AuthPermissionRule::where('permissionRuleId', "in", $permissionRuleIds)->column("name");

            $authRules = ($rules ? $rules : []);
        } else {
            throw new ApiException(9004);
        }

        //检查权限
        $module = Request::instance()->module();
        $controller = loader::parseName(Request::instance()->controller());
        $conArr = explode('.', $controller);
        $action = Request::instance()->action();
        $ruleName = strtolower($module . '/' . $controller . '/' . $action);
        // 不在排除的权限内，并且 用户不为超级管理员
        if (!in_array(strtolower($ruleName), array_map('strtolower', $noCheckArr)) && (empty($clientInfo['username']) || $clientInfo['username'] != 'admin')) {
            if (!self::check($clientInfo, $authRules, [$ruleName], 'and')) {
                throw new ApiException(9004);
            }
        }

        return true;
    }

    /**
     * 检查权限
     *
     * @param  array  $admin     管理员信息
     * @param  array  $authRules 管理员id
     * @param  array  $name      需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param  string $relation  如果为 'or'
     *                           表示满足任一条规则即通过验证;如果为
     *                           'and'则表示需满足所有规则才能通过验证
     * @return bool 通过验证返回true;失败返回false
     */
    public static function check($admin, $authRules = [], $name = [], $relation = 'or')
    {

        if (empty($authRules) || empty($name)) {
            return false;
        }

        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }

        $authPermissionRuleList = AuthPermissionRule::where('name', 'in', $authRules)->column('permissionRuleId,name,condition', 'permissionRuleId');

        $list = [];
        foreach ($authPermissionRuleList as $rule) {
            if (!empty($rule['condition'])) { //根据condition进行验证
                $admin = $admin; // $admin 不能删除，下面正则会用到
                $command = preg_replace('/\{(\w*?)\}/', '$admin[\'\\1\']', $rule['condition']);
                //dump($command);//debug
                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $list[] = strtolower($rule['name']);
                }
            } else {
                $list[] = strtolower($rule['name']);
            }
        }

        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);

        if ($relation == 'and' and empty($diff)) {
            return true;
        }

        return false;
    }

    /**
     * @name:登录白名单
     * @author:      gz
     * @description: 检测当前控制器和方法是否匹配传递的数组
     * @param        $dataArr 需要验证权限的数组
     * @return:      boolean
     */
    public static function match($dataArr)
    {
        $request = Request::instance();
        $dataArr = is_array($dataArr) ? $dataArr : explode(',', $dataArr);
        if (!$dataArr) {
            return false;
        }
        $dataArr = array_map('strtolower', $dataArr);
        // 是否存在
        if (in_array(strtolower($request->action()), $dataArr) || in_array('*', $dataArr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * @name:        拼凑字符串
     * @author:      gz
     * @description:
     * @param        array $dataArr 需要验证权限的数组
     * @return:      array
     */
    public static function buildNowCheckArray($dataArr = [])
    {
        $module = Request::instance()->module();
        $controller = loader::parseName(Request::instance()->controller());
        $buildCheckArray = [];
        foreach ($dataArr as $key => $value) {
            $buildCheckArray[] = strtolower($module . '/' . $controller . '/' . $value);
        }
        return $buildCheckArray;
    }

    /**
     * @name:        设置密码
     * @author:      gz
     * @description:
     * @param        string $data
     * @return:      string
     */
    public static function setPassword($dataStr)
    {
        return md5($dataStr . config('app.password_pre_halt'));
    }

    /**
     * @name:        生成token
     * @author:      gz
     * @description:
     * @param        string $dataStr
     * @return:      string
     */
    public static function setLoginToken($dataStr = '')
    {
        $str = md5(uniqid(md5(microtime(true)), true));

        $str = sha1($str . $dataStr);

        return $str;
    }
}
