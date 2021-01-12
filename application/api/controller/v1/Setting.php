<?php

namespace app\api\controller\v1;

use app\api\controller\Base;

class Setting extends Base
{
    protected $noAuthArr = [];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

   
    /**
     * @name:        设置消息通知
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function setOnOff()
    {
        $this->clientInfo->messageOnOff ? $messageOnOff = 0 : $messageOnOff = 1;
        $this->clientInfo->messageOnOff = $messageOnOff;
        $this->clientInfo->save();
        return show(1);
    }
}
