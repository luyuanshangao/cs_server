<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;

class Index extends Base
{
    protected $noAuthArr = ['index']; //不登录即可访问的接口
    protected $noCheckArr = [];  //不需要权限即可访问的接口
    public function index()
    {
        echo 'adminId:' . $this->adminId;
    }
}
