<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\Banner as BannerModel;
use think\cache\driver\Redis;

class Banner extends Base
{
    protected $noAuthArr = ['list'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name:        轮播图列表
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function list()
    {
        $BannerModel = new BannerModel();
        $condition = [
            'status' => 1,
        ];
        $total = $BannerModel->getCount($condition);
        $fields = [
            'appPath',
            'fileDir',
        ];
        $list = $BannerModel->getList($condition, $this->from, 5, $fields, $this->sort);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
}
