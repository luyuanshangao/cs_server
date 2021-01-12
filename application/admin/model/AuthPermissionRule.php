<?php

namespace app\admin\model;

use app\common\model\BaseModel;

class AuthPermissionRule extends BaseModel
{

    /**
     * @name:        获取当前分类下所有父类ID 返回字串
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public static function getparentIds($permissionRuleId)
    {
        return self::__get_ids($permissionRuleId, '', 'pid');
    }

    /**
     * @name:        获取类下所有父/子类ID
     * @author:      gz
     * @description: GET POST
     * @param        {type} pid：多个父/子类ID集以,分隔  childids：找到的子/父分类列表 find_column:where查找的字段[id|pid:default]
     * @return:
     */
    public static function __get_ids($pid, $childids, $find_column = 'permissionRuleId')
    {
        if (!$pid || $pid <= 0 || strlen($pid) <= 0 || !in_array($find_column, array('permissionRuleId', 'pid'))) {
            return 0;
        }

        if (!$childids || strlen($childids) <= 0) {
            $childids = $pid;
        }

        $column = ($find_column == 'permissionRuleId' ? "pid" : "permissionRuleId"); //id跟pid为互斥
        $permissionRuleIds = self::where("$column in($pid)")->column("$find_column");
        $permissionRuleIds = implode(",", $permissionRuleIds);
        
        //未找到,返回已经找到的
        if ($permissionRuleIds <= 0) {
            return $childids;
        }
        
        //添加到集合中
        $childids .= ',' . $permissionRuleIds;
        //递归查找
        return self::__get_ids($permissionRuleIds, $childids, $find_column);
    }
}
