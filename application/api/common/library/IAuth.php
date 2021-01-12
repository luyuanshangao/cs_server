<?php

namespace app\api\common\library;

use app\common\exception\ApiException;
use app\api\common\library\Aes;
use app\common\model\UserToken;
use app\common\model\User;
use think\Request;

/**
 * Iauth相关
 * Class IAuth
 */
class IAuth
{

    public static $signAttr = '';
    /**
     * 获取用户信息   需要登录用户header中必须传accesstoken accesstoken加密方式
     *
     * @param  Request $request
     * @return $this
     * @throws UnauthorizedException
     */
    public static function getClient($noAuthBoolean, $noSignBoolean)
    {

     
        if (isset($_POST['http_server'])) {
            $headersArr['access-token'] = $_SERVER['ACCESS-TOKEN'];
        } else {
            $headersArr = Request::instance()->header();   //获取请求中的header参数
        }
        
   
        //验证sign
        // if (!$noSignBoolean) {
        //     self::checkSignPass($headersArr);
        // }
        //验证登录的token
        // if (!$noAuthBoolean) {
            return self::checkAccessTokenPass($headersArr, $noAuthBoolean);
        //  }
    }

    /**
     * @name:登录白名单
     * @author:      gz
     * @description: 检测当前控制器和方法是否匹配传递的数组
     * @param        $dataArr 需要验证权限的数组
     * @return:      boolean
     */
    public static function match($dataArr)
    {
        $request = Request::instance();
        $dataArr = is_array($dataArr) ? $dataArr : explode(',', $dataArr);
        if (!$dataArr) {
            return false;
        }
        $dataArr = array_map('strtolower', $dataArr);
        // 是否存在
        if (in_array(strtolower($request->action()), $dataArr) || in_array('*', $dataArr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }




    /**
     * @name:        登录后生成token
     * @author:      gz
     * @description:
     * @param
     * @return       string
     */
    public static function setToken($password)
    {

        $str = md5(uniqid(md5(microtime(true)), true));
        $str = sha1($str . $password);
        return $str;
    }

    /**
     * @name:        模拟客户端生成每次请求的sign
     * @author:      gz
     * @description:
     * @param        array $data
     * @return       string
     */
    public static function setSign($dataArr = [])
    {
        return self::dataAesEncrypt($dataArr);
    }

    /**
     * @name:        模拟客户端生成accessToken
     * @author:      gz
     * @description:
     * @param
     * @return       string
     */
    public static function setAccessToken($token, $userId)
    {
        
        //token 与 userId 使用 || 相连
        $string  = $token . '||' . $userId;
        // 通过aes来加密
        $string = Aes::encrypt($string);
        return $string;
    }

    /**
     * @name:        数据Aes加密
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function dataAesEncrypt($dataArr)
    {
        // 1 按字段排序
        ksort($dataArr);
       
        // 2拼接字符串数据  &
        $string = http_build_query($dataArr);
        
        // 3通过aes来加密
        $string = Aes::encrypt($string);
       
        return $string;
    }

    /**
     * @name:        检查每次请求参数及sign是否正常
     * @author:      gz
     * @description:
     * @param        array $data
     * @param        $data
     * @return       boolen
     */
    public static function checkSignPass($headerArr)
    {
       
        //模拟生成sign
        $data = [
            'apptype' => 'ios',
            'uuid' => '6836FE46-04F4-4F23-A96D-987536AAA613',
            'time' => get13TimeStamp(),
            'version' => 1,
        ];
        
        $aa = self::dataAesEncrypt($data);
        
        $headerValidate =  new \app\api\validate\Header();
        $checkBoolean  =  $headerValidate->check($headerArr);
        
        if (!$checkBoolean) {
            throw new ApiException(1006);
        }

        $decryptStr = Aes::decrypt($headerArr['sign']);
       
        
        if (!$decryptStr) {
            throw new ApiException(1007, []);
        }
        
        parse_str($decryptStr, $decryptArr);
        
        if (!is_array($decryptArr) || empty($decryptArr['apptype']) || empty($decryptArr['version']) || empty($decryptArr['time']) || empty($decryptArr['uuid'])) {
            throw new ApiException(1007, []);
        }
        
        //时间判断
        // if ((time() - ceil($decryptArr['time']/1000)) > config('app.app_sign_time')) {
        //     throw new ApiException(1008,[]);
        // }
        
        // 唯一性判定
        // if (Cache::get($headerArr['sign'])) {
        //     throw new ApiException(1008,[]);
        // }
       
        //缓存令牌
        //Cache::set($headerArr['sign'], time(), config('app.app_sign_cache_time'));

        self::$signAttr =  $decryptArr;

        return true;
    }

    public static function checkAccessTokenPass($headersArr, $noAuthBoolean)
    {
        
        if (!$noAuthBoolean) {
            $accessToken =  $headersArr['access-token'];   //获取请求中的header参数
           
            if (!$accessToken) {
                throw new ApiException(1001);
            }
            
            $userTokenObj = UserToken::get(['token' => $accessToken]);
            if (!$userTokenObj) {
                throw new ApiException(1000);
            }
            
            
            $userObj = User::get($userTokenObj->userId);
            
            if (!$userObj) {
                throw new ApiException(1005);
            }
            
            return $userObj;
        } else {
            isset($headersArr['access-token']) ? $accessToken = $headersArr['access-token'] : $accessToken = ''   ;
           
            if ($accessToken) {
                $userTokenObj = UserToken::get(['token' => $accessToken]);
                
                if ($userTokenObj) {
                        $userObj = User::get($userTokenObj->userId);
                    if ($userObj) {
                        return $userObj;
                    }
                }
            }
        }
    }
}
