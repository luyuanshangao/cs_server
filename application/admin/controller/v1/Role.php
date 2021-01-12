<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\AuthRole as RoleModel;
use app\admin\model\AuthPermission as AuthPermissionModel;
use app\admin\model\AuthPermissionRule as AuthPermissionRuleModel;

class Role extends Base
{
    protected $noAuthArr = ['getStatus'];//接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];    //权限白名单

    /**
     * @name: Role列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function list()
    {
        
        $dataArr = $this->request->get();
        $RoleModel = new RoleModel();
        $this->getPageAndSize($dataArr);
        $this->getSort($dataArr);
        $condition = $this->filterParam(['name','status'], $dataArr);
        $total = $RoleModel->getCount($condition);
        $list = $RoleModel->getList($condition, $this->from, $this->size, true, $this->sort);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }



    /**
     * @name: Role删除
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function del()
    {
        
        $dataArr = $this->request->get();
        $dataArr = RoleModel::destroy(['roleId' => $dataArr['id']]);
        return show(1);
    }
    

    /**
     * @name: Role获取状态
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
     * @name: Role获取权限列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function authList()
    {
        $dataArr = $this->request->get();
        $checked_keys = AuthPermissionModel::where('roleId', $dataArr['roleId'])->column('permissionRuleId');
        $rule_list = AuthPermissionRuleModel::where(['status' => 1])->order('listorder desc')->select();
        $returnResult['auth_list'] = $this->authTree($rule_list);
        $returnResult['checked_keys'] = $checked_keys;
        return show(1, $returnResult);
    }
    /**
     * @name: 修改授权
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function editAuthPermission()
    {
        $dataArr = $this->request->post();
        list($checkArr,$roleId) = $dataArr;
        AuthPermissionModel::where(['roleId' => $roleId])->delete();
        $idStr = '';
        foreach ($checkArr as $key => $value) {
            //查找父亲
            $idstr = AuthPermissionRuleModel::getparentIds($value);
            $arr = explode(',', $idstr);
            if (!$key) {
                $idStr .= $idstr;
            } else {
                $idStr .= ',' . $idstr;
            }
        }
        $idArr = explode(',', $idStr);
        foreach ($checkArr as $key => $value) {
            $saveData = [
                'roleId' => $roleId,
                'permissionRuleId' => $value,
                'type' => 'admin',
             ];
             AuthPermissionModel::create($saveData);
        }
        
        return show(1);
    }
    /**
     * @name: 修改角色信息
     * @author: gz
     * @description: POST
     * @param {type}
     * @return:
     */
    public function editAuthRole()
    {
        $dataArr = $this->request->post();
        $dataArr['updateTime'] = time();
        $obj = RoleModel::update($dataArr, ['roleId' => $dataArr['roleId']]);
        return show(1, $obj);
    }
    /**
     * @name: 添加角色信息
     * @author: gz
     * @description: POST
     * @param {type}
     * @return:
     */
    public function addAuthRole()
    {
        $dataArr = $this->request->post();
        $dataArr['createTime'] = time();
        $dataArr['updateTime'] = time();
        $obj = RoleModel::create($dataArr);
        return show(1, $obj);
    }

    /**
     * @name: 修改状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function editStatus()
    {
        $data = $this->request->post();
        $where = ['roleId' => ['in', $data['checkArray']]];
        unset($data['checkArray']);
        RoleModel::update($data, $where);
        return show(1, $data);
    }

    /**
     * @name: 权限管理页面数据
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function allPermissionRule()
    {
        
        $rule_list = AuthPermissionRuleModel::where(['status' => 1])->order('listorder desc')->select();
        $dataArr['rule_list'] = $this->authTree($rule_list);
        $dataArr['tree_list'] = cateTree($rule_list, 'permissionRuleId', 'pid', 0);
        return show(1, $dataArr);
    }

   /**
    * @name: 添加权限管理页面数据
    * @author: gz
    * @description: GET POST
    * @param {type}
    * @return:
    */
    public function addPermissionRule()
    {
        $dataArr = $this->request->post();
        $resut =  AuthPermissionRuleModel::create($dataArr);
        return show(1, $resut);
    }

   /**
    * @name: 修改权限管理页面数据
    * @author: gz
    * @description: GET POST
    * @param {type}
    * @return:
    */
    public function editPermissionRule()
    {
        $dataArr = $this->request->post();
        $resut =  AuthPermissionRuleModel::update($dataArr, ['permissionRuleId' => $dataArr['permissionRuleId']]);
        return show(1, $resut);
    }

   /**
    * @name: 删除权限管理页面数据
    * @author: gz
    * @description: GET POST
    * @param {type}
    * @return:
    */
    public function delPermissionRule()
    {
        $dataArr = $this->request->get();
        $resut =  AuthPermissionRuleModel::destroy($dataArr['permissionRuleId']);
        return show(1, $resut);
    }

    /**
     * @name: 引用生成权限树
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    private function authTree($array)
    {
    
        //第一步 构造数据
        $items = array();
        foreach ($array as $value) {
            if (is_object($value)) {
                $items[$value['permissionRuleId']] = $value->toArray();
            } else {
                $items[$value['permissionRuleId']] = $value;
            }
            //$items[$value['permissionRuleId']]['disabled'] = true;
        }
    
        //第二部 遍历数据 生成树状结构
        $tree = array();
        //遍历构造的数据
        foreach ($items as $key => $value) {
            //如果parentId这个节点存在
    
            if (isset($items[$value['pid']])) {
                //把当前的$value放到parentId节点的children中 注意 这里传递的是引用 为什么呢？
    
                $items[$value['pid']]['children'][] = &$items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }
}
