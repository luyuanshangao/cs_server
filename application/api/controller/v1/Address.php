<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use think\Loader;
use app\common\model\UserAddress;
use app\common\model\Area;
use think\Cache;

Loader::import('goods_sdk.SDK.Address');
Loader::import('goods_sdk.SDK.DataBase');
class Address extends Base
{
    protected $noAuthArr = ['getDefaultAddress','getIp'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name:        收货地址列表
     * @author:      gz
     * @description: GET
     * @param        {type}
     * @return:
     */
    public function listAddress()
    {

        $dataArr  = $this->checkdate('Address', 'get', 'list');
        $UserAddressModel = new UserAddress();
        $this->getPageAndSize($dataArr);
        $condition = [
            'userId' => $this->userId,
            'del' => 0
        ];
        $total = $UserAddressModel->getCount($condition);
        $list = $UserAddressModel->getList($condition, $this->from, $this->size, true, 'isDefault desc,createTime desc');
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name:        添加收货地址
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function addAddress()
    {
        $dataArr  = $this->checkdate('Address', 'post', 'add');
        list($provinceId, $cityId, $countyId,$townId) = explode("_", $dataArr['areaIdPath']);
        
        //验证收货地址
        $result = Vop::checkAddress($provinceId, $cityId, $countyId, $townId);
        if (!$result) {
            return show(1040);
        }
        
        $UserAddressModel = new UserAddress();
        $data = $UserAddressModel->where(['userId' => $this->userId,'del' => 0])->find();
        if (!$data) {
            $dataArr['isDefault'] = 1;
        }
        $result = $UserAddressModel->add($this->userId, $dataArr);
        return show(1, ['addressId' => $result]);
    }

    /**
     * @name:        获取收货地址信息
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function getAddress()
    {

        $dataArr  = $this->checkdate('Address', 'post', 'get');
        $UserAddressModel = new UserAddress();
        $condition = [
            'userId' => $this->userId,
            'addressId' => $dataArr['addressId'],
            'del' => 0
        ];
        $obj = $UserAddressModel::get($condition);
        return show(1, $obj);
    }

    /**
     * @name:        获取用户默认收货地址信息
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function getDefaultAddress()
    {
        if (!$this->userId) {
            return show(1);
        }
        $UserAddressModel = new UserAddress();
        
        $condition = [
            'userId' => $this->userId,
            'isDefault' => 1,
            'del' => 0
        ];
        $obj = $UserAddressModel::get($condition);
        return show(1, $obj);
    }

    /**
     * @name:        修改收货地址
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function updateAddress()
    {
        $dataArr  = $this->checkdate('Address', 'post', 'update');
        list($provinceId, $cityId, $countyId,$townId) = explode("_", $dataArr['areaIdPath']);
        //验证收货地址
        $result = Vop::checkAddress($provinceId, $cityId, $countyId, $townId);
        if (!$result) {
            return show(1040);
        }
        $UserAddressModel = new UserAddress();
        $UserAddressModel->edit($this->userId, $dataArr);
        return show(1);
    }


    /**
     * @name: 修改收货地址
     * @author: gz
     * @description: POST
     * @param {type}
     * @return:
     */
    public function delAddress()
    {
        $dataArr  = $this->checkdate('Address', 'post', 'del');
        $UserAddressModel = new UserAddress();
        $UserAddressModel->del($this->userId, $dataArr);
        return show(1);
    }


    /**
     * @name: 获取一级地址
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getProvinces()
    {
        $arr = Area::where(['level' => 1])->select();
        return show(1, $arr);
    }
     /**
      * @name:        获取二级地址
      * @author:      gz
      * @description: GET
      * @param        {type}
      * @return:
      */
    public function getCitys()
    {
        
        $postArr  = $this->checkdate('Goods', 'get', 'address');
        $arr = Area::where(['parent_id' => $postArr['add_id']])->select();
        return show(1, $arr);
    }
     /**
      * @name:        获取三级地址
      * @author:      gz
      * @description: GET
      * @param        {type}
      * @return:
      */
    public function getCountys()
    {
        $postArr  = $this->checkdate('Goods', 'get', 'address');
        $arr = Area::where(['parent_id' => $postArr['add_id']])->select();
        return show(1, $arr);
    }
     /**
      * @name:        获取四级地址
      * @author:      gz
      * @description: GET
      * @param        {type}
      * @return:
      */
    public function getTowns()
    {
        $postArr  = $this->checkdate('Goods', 'get', 'address');
        $arr = Area::where(['parent_id' => $postArr['add_id']])->select();
        return show(1, $arr);
    }
     
     /**
      * @name:        验证收货地址
      * @author:      gz
      * @description: GET
      * @param        {type}
      * @return:
      */
    public function checkAddress()
    {
        $postArr  = $this->checkdate('Goods', 'get', 'checkAddress');
        $arr = Vop::checkAddress($postArr['provinceId'], $postArr['cityId'], $postArr['countyId'], $postArr['townId']);
        if (!$arr) {
            return show(0);
        }
        return show(1, $arr);
    }

    /**
     * @name: 获取地址
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function getPcct()
    {
        $postArr  = $this->checkdate('Address', 'post', 'getPcct');
        require_once __DIR__ . '/../../../../vendor/autoload.php';
        $bs = new \ipip\datx\City(__DIR__ . '/../../../../mydata4vipday2.datx');
        $result = $bs->find($postArr['ip']);
        try {
            return show(1, [
                'p' => $result[1],
                'c' => $result[2],
            ]);
        } catch (\Exception $th) {
            return show(1);
        }
    }
}
