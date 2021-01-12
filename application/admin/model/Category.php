<?php

namespace app\admin\model;

use app\common\model\BaseModel;

class Category extends BaseModel
{

    /**
     * 根据条件来获取列表的数据的总数
     * @param array $param
     */
    public function getGoodsCategoryCountByCondition($condition = [])
    {

        return $this->where($condition)
            ->count();
//echo $this->getLastSql();
    }

    /**
     * 根据来获取列表的数据
     * @param array $param
     */
    public function getGoodsCategoryListByCondition($condition = [], $from = 0, $size = 10, $field = true, $order = '')
    {

        $result = $this->where($condition)
            ->field($field)
            ->limit($from, $size)
            ->order($order)
            ->select();
//echo $this->getLastSql();exit;
        return $result;
    }
    /**
     * 创建phth
     */
    public static function createPath($data)
    {
        $str = self::getParentIds($data['parentId']);
        if (!$str) {
            return '-';
        }
        $str_a  = explode(',', $str);
        $str_b  = array_reverse($str_a);
        $str  = implode(',', $str_b);
        return $str . ',';
    }
    /**
     * 获取当前分类下所有子类ID 返回数组
     */
    public static function getSonCatId($parentId = 0)
    {
        $data = self::where(['del' => 1, 'catPath' => ['like', '%' . $parentId . ',%']])->column('catId');
        return $data;
    }
    /**
     * 获取当前分类下所有子类ID 返回字串
     * @parentId：父类ID
     */
    public static function getChildIds($parentId)
    {
        return self::__get_ids($parentId, '', 'catId');
    }
    /**
     * 获取当前分类下所有父类ID 返回字串
     * @catId：子类ID
     */
    public static function getParentIds($catId)
    {
        return self::__get_ids($catId, '', 'parentId');
    }
    /**
     * 获取类下所有父/子类ID
     * @parentId：多个父/子类ID集以,分隔
     * @childids：找到的子/父分类列表
     * @find_column:where查找的字段[id|pid:default]
     */
    public static function __get_ids($parentId, $childids, $find_column = 'catId')
    {
        if (!$parentId || $parentId <= 0 || strlen($parentId) <= 0 || !in_array($find_column, array('catId', 'parentId'))) {
            return 0;
        }

        if (!$childids || strlen($childids) <= 0) {
            $childids = $parentId;
        }

        $column = ($find_column == 'catId' ? "parentId" : "catId");
//id跟pid为互斥
        $catIds = self::where("$column in($parentId)")->column("$find_column");
        $catIds = implode(",", $catIds);
//未找到,返回已经找到的
        if ($catIds <= 0) {
            return $childids;
        }
        
        //添加到集合中
        $childids .= ',' . $catIds;
//递归查找
        return self::__get_ids($catIds, $childids, $find_column);
    }

    public function getFirBetoAttr($value, $data)
    {
        $parentPath = explode(',', $data['catPath']);
        $parentCat = $this::get(['catId' => $parentPath[0]]);
        return $parentCat ? $parentCat['name'] : '-';
    }
    public function getsecBetoAttr($value, $data)
    {
        $parentPath = explode(',', $data['catPath']);
        if (isset($parentPath[1])) {
            $parentCat = $this::get(['catId' => $parentPath[1]]);
            return $parentCat ? $parentCat['name'] : '-';
        } else {
            return '-';
        }
    }
}
