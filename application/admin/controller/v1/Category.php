<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Category as CategoryModel;
use app\common\library\storage\Driver;
use app\common\library\redis\GoodsCategory;

class Category extends Base
{
    protected $noAuthArr = ['getStatus'];    //接口白名单    不需要登录的接口方法
    protected $noCheckArr = ['get', 'upload'];



    /**
     * @name: Category列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function list()
    {

        $data = $this->request->get();
        $CategoryModel = new CategoryModel();
        $this->getPageAndSize($data);
        $this->getSort($data);
        $condition = $this->filterParam(['name', 'status', 'catClass'], $data);
        if (!$condition) {
            $condition['parentId'] = 0;
        }
        if (isset($data['parentId'])) {
            $condition['parentId'] = $data['parentId'];
        }
        $total = $CategoryModel->getCount($condition);
        $list = $CategoryModel->getList($condition, $this->from, $this->size, true, $this->sort, ['firBeto', 'secBeto']);
        foreach ($list as &$value) {
            $value['children'] = [];
            $value['catClass'] == 2 ?  $value['hasChildren'] = 0 : $value['hasChildren'] = 1;
        }
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: 单条获取
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function get()
    {
        $data = $this->request->get();
        $data = CategoryModel::get($data['catId']);
        $catIdArray = explode(',', CategoryModel::getParentIds($data['catId']));
        $catArray = CategoryModel::all(['catId' => ['in', $catIdArray]]);
        $catClassData = config('adminSetting.catClassList');

        foreach ($catClassData as $catClass => &$catClassName) {
            foreach ($catArray as $cat) {
                if ($catClassName['value'] == $cat['catClass']) {
                    $catClassName['catName'] = $cat['name'];
                } else {
                    $catClassName['catName'] = '';
                }
            }
        }
        $data['pathData'] = $catClassData;

        return show(1, $data);
    }

    /**
     * @name: Category添加
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function create()
    {

        $data = $this->request->post();
        if ($data['catClass'] > 3) {
            return show(9103);
        }
        $data['createTime'] = time();
        $data['catPath'] = CategoryModel::createPath($data);
        $result = CategoryModel::create($data);
        return show(1);
    }

    /**
     * @name: Category编辑
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function edit()
    {

        $data = $this->request->post();
        $CategoryModel = new CategoryModel();
        $result = $CategoryModel->saveData($data, 'catId');
        return show(1);
    }

    /**
     * @name: Category删除
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function del()
    {

        $data = $this->request->get();
        //查找子分类
        $sonCate = CategoryModel::getSonCatId($data['catId']);
        if (count($sonCate) > 0) {
            return show(9101);
        }

        $data = CategoryModel::get($data['catId']);
        $data->del = 0;
        $data->save();
        return show(1);
    }

    /**
     * @name: Category获取状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getStatus()
    {

        $statusData = config('adminSetting.db_status');
        $catClassData = config('adminSetting.catClassList');
        $returnData = [
            'statusList' => $statusData,
            'catClassList' => $catClassData,

        ];
        return show(1, $returnData);
    }

    /**
     * @name: Category图片上传
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function upload()
    {
        $filedname = 'file';
        $config = config('upload.category');
        // 实例化存储驱动
        $StorageDriver = new Driver($config, $filedname);
        // 上传图片
        if (!$StorageDriver->upload()) {
            return show(0, '', $StorageDriver->getError());
        }

        // 图片信息
        $fileInfo = $StorageDriver->getFileInfo();
        // 图片上传名
        $fileInfo['fileName'] = $StorageDriver->getFileName();
        $fileInfo['catImg'] = $StorageDriver->getUplodDir();

        return show(1, $fileInfo);
    }

    /**
     * @name: 更新分类缓存
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function updateCache()
    {
        $data = GoodsCategory::allGoodsCategoryKeys();
       
        foreach ($data as $key => $value) {
            GoodsCategory::delKey($value);
        }

        $firCategory = CategoryModel::where([
            'parentId' => 0,
            'state' => 1,
        ])->column('catId,parentId,name,catClass,state,catPath,catImg', 'catId');
        GoodsCategory::setGoodsCategory(0, array_values($firCategory));
        
        foreach ($firCategory as $key => $getArr) {
            $returnData = [];
         
            $secCategory = CategoryModel::where(['parentId' => $getArr['catId']])->column('catId,parentId,name,catImg', 'catId');

            $secCatIds = array_keys($secCategory);
            
            //获取三级分类
            $theCategory = CategoryModel::where(['parentId' => ['in', $secCatIds]])->column('catId,parentId,name,catImg', 'catId');

            foreach ($secCategory as $key => &$sec) {
                $secCategory[$key]['childs'] = [];

                foreach ($theCategory as $k => $the) {
                    if ($sec['catId'] == $the['parentId']) {
                        $sec['childs'][] =  $the;
                    }
                }
            }
            
            $returnData = array_values($secCategory);
            
            GoodsCategory::setGoodsCategory($getArr['catId'], $returnData);
        }
        
        return show(1);
    }
}
