<?php

/*
 * @Descripttion:
 * @Author: gz
 */


namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\Category as CategoryModel;
use app\common\library\redis\GoodsCategory;

class Category extends Base
{
    protected $noAuthArr = ['getCategorys','getSonCategorys','hotCategory','getAllCate'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name:        获取商品分类
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function getCategorys()
    {
        $getArr  = $this->checkdate('Goods', 'get', 'getCategory');
        $category = GoodsCategory::getGoodsCategory(0);
        if (!$category) {
            $category = CategoryModel::where(['state' => 1,'parentId' => $getArr['parentId'],'catId' => ['not in',['9259','14379','']]])->select();
        }
        
        return show(1, $category);
    }
    
    /**
     * @name:        获取对应一级商品分类 下的子分类
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function getSonCategorys()
    {
        $getArr  = $this->checkdate('Goods', 'get', 'getCategory');
        
        //缓存
        $categoryCache = GoodsCategory::getGoodsCategory($getArr['parentId']);
        
        if ($categoryCache) {
            $returnData =  $categoryCache;
        } else {
            //获取二级分类
            $secCategory = CategoryModel::where(['parentId' => $getArr['parentId']])->column('catId,parentId,name,catImg', 'catId');
            
            $secCatIds = array_keys($secCategory);
            
            //获取三级分类
            $theCategory = CategoryModel::where(['parentId' => ['in',$secCatIds]])->column('catId,parentId,name,catImg', 'catId');
         
            foreach ($secCategory as $key => &$sec) {
                $secCategory[$key]['childs'] = [];
                  
                foreach ($theCategory as $k => $the) {
                    if ($sec['catId'] == $the['parentId']) {
                        $sec['childs'][] =  $the;
                    }
                }
            }
            
            $returnData = array_values($secCategory);
        }
        return show(1, $returnData);
    }


    /**
     * @name: 缓存中返回所有分类信息
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function getAllCate()
    {
        $category = GoodsCategory::getGoodsCategory(0);
        
        foreach ($category as &$value) {
            $value['childs'] = GoodsCategory::getGoodsCategory($value['catId']);
        }
        return show(1, $category);
    }

    public function hotCategory()
    {
        $hotCategory = [
            '672',
            '2694',
            '696',
            '831',
        ];
        $data = CategoryModel::all(['catId' => ['in',$hotCategory]]);
        return show(1, $data);
    }
}
