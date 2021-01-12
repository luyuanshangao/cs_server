<?php

/*
 * @Descripttion:
 * @Author: gz
 */
namespace app\common\model;

use think\Loader;
use app\api\controller\v1\Vop;

Loader::import('goods_sdk.SDK.Goods');
Loader::import('goods_sdk.SDK.DataBase');
class Category extends BaseModel
{



    /**
     * @name:        创建phth
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
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
     * @name:        获取当前分类下所有父类ID 返回字串
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function getParentIds($catId)
    {
        return self::__get_ids($catId, '', 'parentId');
    }

    /**
     * @name:        获取类下所有父/子类ID
     * @author:      gz
     * @description:
     * @param        {type} parentId：多个父/子类ID集以,分隔  childids：找到的子/父分类列表 find_column:where查找的字段[id|pid:default]
     * @return:
     */
    public static function __get_ids($parentId, $childids, $find_column = 'catId')
    {
        if (!$parentId || $parentId <= 0 || strlen($parentId) <= 0 || !in_array($find_column, array('catId', 'parentId'))) {
            return 0;
        }

        if (!$childids || strlen($childids) <= 0) {
            $childids = $parentId;
        }

        $column = ($find_column == 'catId' ? "parentId" : "catId"); //id跟pid为互斥
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

    /**
     * @name:        商品分类更新
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function updateGoodsCategory()
    {
        $catClass = [0,1,2];
        
        try {
            foreach ($catClass as $value) {
                $categoryArr =  Vop::getCategorys($value);
                $inserArr = [];
                foreach ($categoryArr as $category) {
                    if (!$category['state']) {
                        continue;
                    }
                    if (!$value) {
                        $category['catPath'] = '-';
                    } else {
                        $category['catPath'] = self::createPath($category);
                    }
                    echo $category['name'] . PHP_EOL;
                    
                    $inserArr[] = $category;
                }
                $this->insertAll($inserArr);
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
