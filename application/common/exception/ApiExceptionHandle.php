<?php

namespace app\common\exception;

use think\exception\Handle;

class ApiExceptionHandle extends Handle
{


    public $httpCode = 200;
    public function render(\Exception $e)
    {
       
        if (config('app_debug') == true) {
            return parent::render($e);
        }
        if ($e instanceof ApiException) {
            $this->httpCode = $e->httpCode;
        }
        
        $datas = empty($e->data) ? [] : $e->data;
        $code = empty($e->code) ? 0 : $e->code;
        $msg = empty($e->msg) ? $e->getMessage() : $e->msg;
        return  show($code, $datas, $msg, $this->httpCode);
    }
}
