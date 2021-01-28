<?php

namespace app\api\controller\v1;

use app\common\model\User as  UserModel;
use app\common\model\WalletWords;
use think\Controller;
use app\common\exception\ApiException;

class Register extends Controller
{
    protected $noAuthArr = ['index','getWalletWords','indexsss_sss'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

     /**
     * @name:        注册
     * @author:      gz
     * @description: POST
     * @param        {type}
     * @return:
     */
    public function index()
    {

        $dataArr  = $this->checkdate('User', 'post', 'registe');
        $dataArr = $this->request->post();
        $UserModel = new UserModel();
        $resultCheck = $this->searchWalletWords($dataArr['walletWords']);
        
        if (!$resultCheck) {
            return show(1009);
        }
        if (isset($dataArr['incode']) && $dataArr['incode']) {
            $superiorUser = $UserModel->where(['incode' => $dataArr['incode']])->find();
            if ($superiorUser) {
                $dataArr['superiorId'] = $superiorUser->userId;
            }
        }
        $resuleAddUser = $UserModel->addUser($dataArr);

        if (!$resuleAddUser) {
            return show(1010);
        }
        //返回token
           
        return show(1, $resuleAddUser);
    }

    /**
     * @name: 获取助记词
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getWalletWords()
    {
      
        do {
            $walletWords = make_walletWords();
        } while (check_array_unique(explode(' ', $walletWords)) || UserModel::getUserForWallet($walletWords));

        //记录已生成的助记词
        WalletWords::create([
            'walletWords' => $walletWords,
            'createTime' => time(),
        ]);

        return show(1, ['words' => $walletWords]);
    }

    /**
     * @name: 注册-账号
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function makeAccessAndPwd()
    {

        $dataArr  = $this->checkdate('User', 'post', 'makeAccessAndPwd');
         $UserModel = new UserModel();
         $apiData = $this->getWalletWords()->getdata();
         $dataArr['walletWords'] = $apiData['data']['words'];
          
        if (isset($dataArr['incode']) && $dataArr['incode']) {
            $superiorUser = $UserModel->where(['incode' => $dataArr['incode']])->find();
            if (!$superiorUser) {
                return show(1036);
            }
            $dataArr['superiorId'] = $superiorUser->userId;
        }
         $resuleAddUser = $UserModel->addUser($dataArr);
         
        if (!$resuleAddUser) {
            return show(1010);
        }
         db('user_bak')->insert([
            'userId' => $resuleAddUser['userId'],
            'access' => $dataArr['access'],
            'pwd' => $dataArr['pwd'],
         ]);
         //返回token
            
         return show(1, $resuleAddUser);
    }
    /**
     * @name: 验证助记词
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    private function searchWalletWords($walletWords)
    {
        $data = \app\common\model\WalletWords::get([
            'walletWords' => $walletWords
        ]);
        if (!$data) {
            return false;
        }
        return true;
    }

    
    public function indexsss_sss($dataArr)
    {

        //$dataArr  = $this->checkdate('User', 'post', 'registe');
        $UserModel = new UserModel();
       
        if (isset($dataArr['incode']) && $dataArr['incode']) {
            $superiorUser = $UserModel->where(['incode' => $dataArr['incode']])->find();
            if (!$superiorUser) {
                return show(1036);
            }
            $dataArr['superiorId'] = $superiorUser->userId;
        }
        $resuleAddUser = $UserModel->addUser($dataArr);

        if (!$resuleAddUser) {
            return show(1010);
        }
        //返回token
           
        return show(1, $resuleAddUser);
    }

    /**
     * @name:        接收参数并校验
     * @author:      gz
     * @description: GET POST
     * @param        {type}
     * @return:
     */
    private function checkdate($validate, $method, $scene)
    {

        if ($method == 'post') {
            $methodArr = $this->request->post();
        } else {
            $methodArr = $this->request->get();
        }
        $vali = '\app\api\validate\\' . $validate;
        $validateModel = new $vali();

        //验证器
        $resultBool =  $validateModel->scene($scene)->check($methodArr);
        if (!$resultBool) {
            $msg =  $validateModel->getError();
            throw new ApiException(0, null, $msg);
        }
        return $methodArr;
    }
}
