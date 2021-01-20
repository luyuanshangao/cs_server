<?php

namespace app\api\validate;

class User extends \think\Validate
{
    //规则
    protected $rule = [
        ['phone','require|regex:1[34578]\d{9}', '手机号不能为空|请输入正确的手机号码'],
        ['userName','require|chsAlphaNum|length:4,12','昵称不能为空|请输入汉字,字母或数字|长度必须在4-12位之间'],


        ['payPassWord','require|number|length:6', '支付密码不能为空|请输入正确的支付密码(为数字)|密码长度必须为6位'],
        ['payPassWord_c','require|confirm:payPassWord','确认支付密码不能为空|两次密码不一致'],
        ['oldPayPassWord','require|number|length:6', '原支付密码不能为空|请输入正确的原支付密码(为数字)|原支付密码长度必须6位'],

        ['gesturePassword','require|number', '手势不能为空|请输入正确手势'],
        ['gesturePassword_c','require|confirm:gesturePassword','确认手势不能为空|两次手势不一致'],
        ['oldGesturePassword','require|number|checkRe', '手势不能为空|请输入正确手势|新的手势与原手势相同,请重新修改'],

        ['code','require|alphaNum', '验证码不能为空|请输入正确的验证码'],
        
        ['size','number', 'size为数字'],
        ['page','number', 'page为数字'],
        ['messageId','number|require', '参数为数字|参数不能为空'],
        ['messageType','chs', '类型错误'],
        ['type','require|chs', '类型必须|类型错误'],
        ['sex','require|length:1,50', '性别不能为空|性别长度必须在0-50之间'],
        ['birthday','require|length:1,50', '生日不能为空|生日长度必须在0-50之间'],
        ['incode','number|length:6', '邀请码错误|邀请码错误'],
        ['questionId','require|array|checkQuestionId', '问题必须|问题错误|问题错误'],
        ['answer','require|array|checkAnswer', '答案必须填写|答案错误|答案错误'],
        ['checkWalletWords','require|checkWalletWords', '助记词必须填写|助记词错误'],
        ['walletWords','require|checkWalletWords', '助记词必须填写|助记词错误'],
        ['walletWordsArr','require|checkWalletWordsArr', '助记词必须填写|助记词错误'],
        ['pClientId','length:1,50', '客户端参数错误'],




        ['access','require|chsAlphaNum|length:4,12','昵称不能为空|请输入汉字,字母或数字|长度必须在4-12位之间'],
        ['pwd','require|alphaDash|length:4,20', '密码不能为空|请输入正确的密码(为字母和数字)|密码长度必须在4-20位之间'],
        ['pwd_c','require|confirm:pwd','确认密码不能为空|两次密码不一致'],

    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        
        'registe' => ['walletWords','payPassWord','payPassWord_c','gesturePassword','incode'],
        'updateGesturePassword' => ['gesturePassword','gesturePassword_c','oldGesturePassword'],
        'checkPayPassWord' => ['payPassWord'],
        'checkGesturePassword' => ['gesturePassword'],
        'checkTwoWalletArr' => ['walletWordsArr'],
        'updatePayPassWord' => ['payPassWord','payPassWord_c','oldPayPassWord'],
        'updatePayPassWordAsForget' => ['payPassWord','payPassWord_c','walletWordsArr'],
        'updateGesturePasswordAsForget' => ['gesturePassword','gesturePassword_c','walletWordsArr'],
        'setPayPassWord' => ['payPassWord','payPassWord_c'],
        'login' => ['walletWords','payPassWord','pClientId'],
        'checkWalletWords' => ['walletWords'],
        'upUserName' => ['userName'],
        'messageList' => ['type','page','size'],
        'readMessage' => ['messageType'],
        'pClientId' => ['pClientId'],
        'delMessage' => ['messageId'],



        'makeAccessAndPwd' => ['access','pwd','pwd_c','payPassWord','payPassWord_c','gesturePassword','incode'],
        'loginByAccessAndPwd' => ['access','pwd'],
       
    ];
    
    protected function checkQuestionId($value, $rule, $data)
    {

        if (count($value) !== 3) {
            return false;
        }
        
        foreach ($value as $k => $v) {
            if (!is_numeric($v)) {
                return false;
            }
        }
        
        return true;
    }
    protected function checkRe($value, $rule, $data)
    {


        if ($value == $data['gesturePassword']) {
            return false;
        }
       
        return true;
    }
    protected function checkAnswer($value, $rule, $data)
    {
        if (count($value) !== 3) {
            return false;
        }
        foreach ($value as $k => $v) {
            if (mb_strlen($v) > 50) {
                return false;
            }
        }
        return true;
    }

    protected function checkWalletWords($value, $rule, $data)
    {
        $walletArr = explode(' ', $value);
        if (count($walletArr) !== 12) {
            return false;
        }
        return true;
    }
    protected function checkWalletWordsArr($value, $rule, $data)
    {
        $checkData  = json_decode($value, true);
        if (!is_array($checkData)) {
            return false;
        }
        if (count($checkData) !== 2) {
            return false;
        }
        foreach ($checkData as $key => $words) {
            if ($key > 12 || $key < 1) {
                return false;
            }
            if (!$words) {
                return false;
            }
        }
        return true;
    }
}
