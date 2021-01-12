<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\api\common\library\IAuth;
use app\common\model\User as  UserModel;
use app\common\model\UserToken;
use app\common\model\WalletWords;
use think\Cache;

class Login extends Base
{
    protected $noAuthArr = ['index','checkWalletWords','loginByAccessAndPwd','makeAccessAndPwd','logip'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name:        登录
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function index()
    {

        $dataArr  = $this->checkdate('User', 'post', 'login');
        $condition = [
            'walletWords' => $dataArr['walletWords'],
            'payPassWord' => setPassword($dataArr['payPassWord']),
        ];
        
        $userObj = UserModel::get($condition);
        if (!$userObj) {
            return show(1011);
        }
        //更新token
        $token =  Iauth::setToken($userObj['userName']);
        $expireTimeStamp = time() + config('app.app_token_time_out');
        $resetArr = [
            'token' => $token,
            'expireTimeStamp' => $expireTimeStamp,
            'lastLoginIp' => $this->request->ip(),
            'updateTime' => time(),
        ];
        $this->resetToken($resetArr, ['userId' => $userObj->userId]);
        


        $rerurnArr = [
            'token' => $token,
            'address' => $userObj['address'],
            'ethAddress' => $userObj['ethAddress'],
        ];
        return show(1, $rerurnArr);
    }


    

    /**
     * @name: 验证助记词
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function checkWalletWords()
    {
        $dataArr  = $this->checkdate('User', 'post', 'checkWalletWords');
        $condition = [
            'walletWords' => $dataArr['walletWords'],
        ];
        $userObj = UserModel::get($condition);
        if (!$userObj) {
            return show(1011);
        }

        return show(1);
    }

    /**
     * @name: 验证支付密码
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function checkPayPassWord()
    {

        $dataArr  = $this->checkdate('User', 'post', 'checkPayPassWord');
        $userObj = $this->clientInfo;
        if (setPassword($dataArr['payPassWord']) !== $userObj->payPassWord) {
            return show(0);
        }
        return show(1);
    }
   /**
     * @name:        修改支付密码
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function updatePayPassWord()
    {

        $dataArr  = $this->checkdate('User', 'post', 'updatePayPassWord');
        $userObj = $this->clientInfo;
        if (setPassword($dataArr['oldPayPassWord']) !== $userObj->payPassWord) {
            return show(1013);
        }
        $userObj->payPassWord = setPassword($dataArr['payPassWord']);
        $userObj->save();
        return show(1);
    }

   /**
     * @name:        修改支付密码(忘记密码)
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function updatePayPassWordAsForget()
    {

        $dataArr  = $this->checkdate('User', 'post', 'updatePayPassWordAsForget');

        $userObj = $this->clientInfo;
        $walletWordsStr = $userObj->walletWords;
        
        $checkResult = $this->checkWalletArr($walletWordsStr, $dataArr['walletWordsArr']);
        if (!$checkResult) {
            return show(0);
        }

        $userObj->payPassWord = setPassword($dataArr['payPassWord']);
        $userObj->save();
        return show(1);
    }

    /**
     * @name: 验证手势密码
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function checkGesturePassword()
    {

        $dataArr  = $this->checkdate('User', 'post', 'checkGesturePassword');
        $userObj = $this->clientInfo;
        if (setPassword($dataArr['gesturePassword']) !== $userObj->gesturePassword) {
            return show(1030);
        }
        return show(1);
    }

    /**
     * @name: 修改手势密码
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function updateGesturePassword()
    {
        $dataArr  = $this->checkdate('User', 'post', 'updateGesturePassword');
        $userObj = $this->clientInfo;

        if (setPassword($dataArr['oldGesturePassword']) !== $userObj->gesturePassword) {
            return show(1034);
        }

        $userObj->gesturePassword = setPassword($dataArr['gesturePassword']);
        $userObj->save();
        return show(1);
    }

    /**
     * @name:        修改手势密码(忘记密码)
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function updateGesturePasswordAsForget()
    {

        $dataArr  = $this->checkdate('User', 'post', 'updateGesturePasswordAsForget');

        $userObj = $this->clientInfo;
        $walletWordsStr = $userObj->walletWords;
        
        $checkResult = $this->checkWalletArr($walletWordsStr, $dataArr['walletWordsArr']);
        if (!$checkResult) {
            return show(0);
        }

        $userObj->gesturePassword = setPassword($dataArr['gesturePassword']);
        $userObj->save();
        return show(1);
    }

    /**
     * @name: 验证两个助记词
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function checkTwoWalletArr()
    {
        $dataArr  = $this->checkdate('User', 'post', 'checkTwoWalletArr');
        $userObj = $this->clientInfo;
        $walletWordsStr = $userObj->walletWords;
        $checkResult = $this->checkWalletArr($walletWordsStr, $dataArr['walletWordsArr']);
        if (!$checkResult) {
            return show(0);
        }
        return show(1);
    }

    /**
     * @name:
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    private function checkWalletArr($walletWordsStr, $walletWordsArr)
    {

        $walletArr  = json_decode($walletWordsArr, true);
        $walletWordsArr = explode(' ', $walletWordsStr);

        foreach ($walletArr as $key => $value) {
            $index = $key - 1;
            if ($walletWordsArr[$index] !== $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * @name: 开启手势密码
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function setGestureOn()
    {
        $userObj = $this->clientInfo;
        $userObj->gestureOnOff = 1;
        $userObj->save();
        return show(1);
    }

   /**
     * @name: 关闭手势密码
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function setGestureOff()
    {

        $dataArr  = $this->checkdate('User', 'post', 'checkGesturePassword');
        $userObj = $this->clientInfo;
        if (setPassword($dataArr['gesturePassword']) !== $userObj->gesturePassword) {
            return show(1030);
        }
        $userObj = $this->clientInfo;
        $userObj->gestureOnOff = 0;
        $userObj->save();
        return show(1);
    }

    /**
     * @name: 获取手势密码开关状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getGestureOnOrOff()
    {

        $userObj = $this->clientInfo;
        $status =  $userObj->gestureOnOff;
        return show(1, ['status' => $status]);
    }

    /**
     * @name:        是否设置了支付密码
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function truePayPassWord()
    {
        $this->clientInfo->payPassWord ? $result = true : $result = false ;
        $result ?  $code = 1 :  $code = 0;
        return show(1, ['result' => $code]);
    }

    /**
     * @name:        设置支付密码
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    public function setPayPassWord()
    {

        $dataArr  = $this->checkdate('User', 'post', 'setPayPassWord');
        $userObj = $this->clientInfo;
        if ($userObj->payPassWord) {
            return show(1015);
        }
        $userObj->payPassWord = setPassword($dataArr['payPassWord']);
        $userObj->save();
        return show(1);
    }

   
    /**
     * @name:        重置token
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    private function resetToken($resetArr, $whereArr)
    {
           
        UserToken::updateToken($resetArr, $whereArr);
    }



    ###############################过审账号密码#####################################

    /**
     * @name: 账号密码登录
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function loginByAccessAndPwd()
    {
        $dataArr  = $this->checkdate('User', 'post', 'loginByAccessAndPwd');
        try {
            $dataUser = db('user_bak')->where([
                'access' => $dataArr['access'],
                'pwd' => $dataArr['pwd'],
            ])->find();
            if (!$dataUser) {
                return show(1054);
            }
            $condition = [
                'userId' => $dataUser['userId'],
            ];
            $userObj = UserModel::get($condition);
    
            //更新token
            $token =  Iauth::setToken($userObj['userName']);
            $expireTimeStamp = time() + config('app.app_token_time_out');
            $resetArr = [
                'token' => $token,
                'expireTimeStamp' => $expireTimeStamp,
                'lastLoginIp' => $this->request->ip(),
                'updateTime' => time(),
            ];
            $this->resetToken($resetArr, ['userId' => $userObj->userId]);
    
            $rerurnArr = [
                'token' => $token,
                'address' => $userObj['address'],
                'ethAddress' => $userObj['ethAddress'],
            ];
            return show(1, $rerurnArr);
        } catch (\Exception $th) {
            return show(1055);
        }
    }

    /**
     * @name: 需要账号密码登录？
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function logip()
    {
        return show(1, config("app.need_access_login"));
    }
}
