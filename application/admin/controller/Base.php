<?php

namespace app\admin\controller;

use app\admin\common\library\IAuth;
use think\Controller;

class Base extends Controller
{
    public $page = 1;
    public $size = 10;
    public $from = 0;
    public $sort = '';
    protected $noAuthArr = []; //用户登录接口白名单
    protected $noCheckArr = []; //用户接口权限白名单
    protected $clientInfo = null;
    protected $adminId = null;

    /**
     * 初始化
     *
     * @description
     * @author      gz
     */
    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    /**
     * 初始化方法
     *
     * @description
     * @author      gz
     * @return      void
     */
    public function init()
    {
        $noAuthBoolean = Iauth::match($this->noAuthArr);
        //true 传用户usertoken 获取到用户  不传返回null   传错返回null
        $this->clientInfo = Iauth::getClient($noAuthBoolean, $this->noCheckArr);
       
        if ($this->clientInfo) {
            $this->adminId = $this->clientInfo['adminId'];
        }
    }


    /**
     * 获取分页
     *
     * @description
     * @author      gz
     * @param       [type] $data
     * @return      void
     */
    public function getPageAndSize($data)
    {
        $this->page = !empty($data['page']) ? $data['page'] : 1;
        $this->size = !empty($data['size']) ? $data['size'] : config('paginate.list_rows');
        $this->from = ($this->page - 1) * $this->size;
    }

    /**
     * 获取排序
     *
     * @description
     * @author      gz
     * @param       [type] $data
     * @return      void
     */
    public function getSort($data)
    {
        if (isset($data['sort']) && !empty($data['sort'])) {
            $this->sort = strtr($data['sort'], '-', ' ');
        }
    }

    /**
     * 创建参数
     *
     * @description
     * @author      gz
     * @param       [type] $array
     * @return      void
     */
    protected function buildParam($array)
    {
        $data = [];
        if (is_array($array)) {
            foreach ($array as $item => $value) {
                if ($value == 'size' || $value == 'page') {
                    continue;
                }
                $data[$item] = $value;
            }
        }
        return $data;
    }

    /**
     * 过滤参数
     *
     * @description
     * @author      gz
     * @param       [type] $arrays
     * @param       [type] $data
     * @return      void
     */
    protected function filterParam($params, $data)
    {
        foreach ($data as $item => &$value) {
            if (!in_array($item, $params) || $value == "") {
                unset($data[$item]);
            }

            if ($item == 'userName' && $value !== "") {
                $value = ['like', '%' . $value . '%'];
            }

            if ($item == 'page' || $item == 'size' || $item == 'sort') {
                unset($data[$item]);
            }
            if ($item == 'createTime' && $value !== "") {
                foreach ($value as $k => $v) {
                    $value[$k] = $v / 1000;
                }
                $value = ['between', $value];
            }
            if ($item == 'payTime' && $value !== "") {
                foreach ($value as $k => $v) {
                    $value[$k] = $v / 1000;
                }
                $value = ['between', $value];
            }
        }

        return $data;
    }
}
