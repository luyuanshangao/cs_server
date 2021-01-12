<?php

namespace app\common\exception;

use think\Exception;

class ApiException extends Exception
{

    public $code = 1 , $datas = [] , $msg = '' , $httpCode = 200;
    public function __construct($code = 1, $datas = [], $msg = '', $httpCode = 200)
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->data = $datas;
        $this->httpCode = $httpCode;
    }
}
