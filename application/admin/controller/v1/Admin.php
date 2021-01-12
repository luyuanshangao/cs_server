<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\AuthRole as RoleModel;
use app\admin\model\AuthAdmin as AdminModel;
use app\admin\model\AuthRoleAdmin;

/**
 * @title Admin
 * @description 接口说明
 * @group 后台接口
 * @header name:token require:1 default: desc:秘钥(区别设置)
 */
class Admin extends Base
{
    protected $noAuthArr = [];
//接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];    //权限白名单
    

    /**
     * @name: Admin列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function list()
    {
        
        $dataArr = $this->request->get();
        $AdminModel = new AdminModel();
        $this->getPageAndSize($dataArr);
        $condition = $this->filterParam(['username','roleId','status'], $dataArr);
        if (isset($condition['roleId'])) {
            $adminId = AuthRoleAdmin::where(['roleId' => $condition['roleId']])->column('adminId');
            unset($condition['roleId']);
            $condition['adminId'] = ['in',$adminId];
        }
        
        $append = ['roleId','password'];
        $total = $AdminModel->getCount($condition);
        $list = $AdminModel->getList($condition, $this->from, $this->size, true, $this->sort, $append);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: Admin添加
     * @author: gz
     * @description:  POST
     * @param {type}
     * @return:
     */
    public function create()
    {
        $dataArr = $this->request->post();
//创建用户
        $createArr = [
            'status' => $dataArr['status'],
            'username' => $dataArr['username'],
            'lastLoginIp' => $this->request->ip(),
            'avatar' => 'https://wpimg.wallstcn.com/f778738c-e4f8-4870-b634-56703b4acafe.gif',
            'createTime' => time(),
            'password' => setPassword($dataArr['password'])
        ];
        $adminObj = AdminModel::create($createArr);
//绑定角色
        $authRoleAdminObj = AuthRoleAdmin::create([
            'roleId' => $dataArr['roleId'],
            'adminId' => $adminObj->adminId,
        ]);
        $adminObj->password = '';
        $adminObj->roleId = $authRoleAdminObj->roleId;
        return show(1, $adminObj);
    }

    /**
     * @name: Admin编辑
     * @author: gz
     * @description: POST
     * @param {type}
     * @return:
     */
    public function edit()
    {
        $dataArr = $this->request->post();
//修改用户
        if ($dataArr['password']) {
            $dataArr['password'] = setPassword($dataArr['password']);
        } else {
            unset($dataArr['password']);
        }
        $roleId = $dataArr['roleId'];
        unset($dataArr['roleId']);
        $adminObj = AdminModel::update($dataArr, ['adminId' => $dataArr['adminId']]);
//绑定角色
        $authRoleAdminObj = AuthRoleAdmin::update([
            'roleId' => $roleId,
        ], [
            'adminId' => $dataArr['adminId'],
        ]);
        $adminObj->roleId = $roleId;
        return show(1, $adminObj);
    }

    /**
     * @name: Admin删除
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function del()
    {
        
        $dataArr = $this->request->get();
        AdminModel::destroy(['adminId' => $dataArr['adminId']]);
        return show(1);
    }

   /**
     * @name: Admin修改状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function editStatus()
    {
        $data = $this->request->post();
        $where = ['adminId' => ['in', $data['checkArray']]];
        unset($data['checkArray']);
        AdminModel::update($data, $where);
        return show(1, $data);
    }
    

    /**
     * @name: Admin获取状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getStatus()
    {
        
        $dataArr = config('adminSetting.db_status');
        return show(1, $dataArr);
    }

    /**
     * @name: Admin获取角色
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getRole()
    {
        
        $dataObj = RoleModel::all();
        $dataArr = [];
        foreach ($dataObj as $value) {
            $dataArr[] = [
               'name' => $value['name'],
               'value' => $value['roleId'],
            ];
        }
        return show(1, $dataArr);
    }
}
