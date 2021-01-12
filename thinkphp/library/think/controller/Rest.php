<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\controller;

use think\App;
use think\Request;
use think\Response;

abstract class Rest
{

    protected $method; // 当前请求类型
    protected $type; // 当前资源类型
    // 输出类型
    protected $restMethodList    = 'get|post|put|delete';
    protected $restDefaultMethod = 'get';
    protected $restTypeList      = 'html|xml|json|rss';
    protected $restDefaultType   = 'html';
    protected $restOutputType    = [ // REST允许输出的资源类型列表
        'xml'  => 'application/xml',
        'json' => 'application/json',
        'html' => 'text/html',
    ];

    /**
     * 构造函数 取得模板对象实例
     * @access public
     */
    public function __construct()
    {
        // 资源类型检测
        $request = Request::instance();
        $ext     = $request->ext();
        if ('' == $ext) {
            // 自动检测资源类型
            $this->type = $request->type();
        } elseif (!preg_match('/(' . $this->restTypeList . ')$/i', $ext)) {
            // 资源类型非法 则用默认资源类型访问
            $this->type = $this->restDefaultType;
        } else {
            $this->type = $ext;
        }
        // 请求方式检测
        $method = strtolower($request->method());
        if (!preg_match('/(' . $this->restMethodList . ')$/i', $method)) {
            // 请求方式非法 则用默认请求方法
            $method = $this->restDefaultMethod;
        }
        $this->method = $method;

        //允许跨域
        header("Access-Control-Allow-Origin: *");
        //header('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        header('Access-Control-Allow-Headers:x-requested-with,content-type');
        header('Access-Control-Allow-Methods:POST,GET');


        /*
         * 验证用户身份
         */
        $this->token = input("token");
        $this->uuid = input("uuid");

        $unLoginController = array("User", "About");
        if (!in_array($request->controller(), $unLoginController)) {
            //验证token于uuid是否正确

            if (!$this->token || !$this->uuid) {
                $this->returnJson(0, null, "auth false");
            }

            $condition = array();
            $condition["token"] = $this->token;
            $condition["uuid"] = $this->uuid;
            $tokenInfo = db("member_token")->where($condition)->find();

            if ($tokenInfo) {
                $condition = array();
                $condition["memberId"] = $tokenInfo["memberId"];
                $memberInfo = db("member")->where($condition)->find();
                if ($memberInfo) {
                    $this->memberInfo = array();
                    $this->memberInfo = $memberInfo;
                    $this->tokenInfo = $tokenInfo;
                } else {
                    $this->returnJson(0, null, "auth false");
                }

            } else {
                $this->returnJson(0, null, "auth false");
            }
        }
    }

    protected function returnJson($status, $data, $message) {
        $dataInfo = array(
            'status' => $status,
            'data' => $data,
            'message' => $message
        );
        echo json_encode($dataInfo);die;
    }

    /**
     * REST 调用
     * @access public
     * @param string $method 方法名
     * @return mixed
     * @throws \Exception
     */
    public function _empty($method)
    {
        if (method_exists($this, $method . '_' . $this->method . '_' . $this->type)) {
            // RESTFul方法支持
            $fun = $method . '_' . $this->method . '_' . $this->type;
        } elseif ($this->method == $this->restDefaultMethod && method_exists($this, $method . '_' . $this->type)) {
            $fun = $method . '_' . $this->type;
        } elseif ($this->type == $this->restDefaultType && method_exists($this, $method . '_' . $this->method)) {
            $fun = $method . '_' . $this->method;
        }
        if (isset($fun)) {
            return App::invokeMethod([$this, $fun]);
        } else {
            // 抛出异常
            throw new \Exception('error action :' . $method);
        }
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed     $data 要返回的数据
     * @param String    $type 返回类型 JSON XML
     * @param integer   $code HTTP状态码
     * @return Response
     */
    protected function response($data, $type = 'json', $code = 200)
    {
        return Response::create($data, $type)->code($code);
    }

}
