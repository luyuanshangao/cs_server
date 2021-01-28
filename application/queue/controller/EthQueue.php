<?php

namespace app\queue\controller;

use app\common\model\Assets;
use app\common\model\User;
use think\Controller;
use think\Request;

class EthQueue extends Controller
{
     /** @var Assets */
     private $assets;
     /** @var User */
     private $user;
 
    private $baseEthAddress = "0x62f422C23565eF5bDbaB6AF88ee9809835dc6AD1";
    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->assets = new Assets();
        $this->user = new User();
    }
 
    public function getAddress()
    {
        $userList = $this->user->userList();
        $address = array();
        for ($i = 0; $i < count($userList); $i++) {
            $userInfo = $userList[$i];
            
            array_push($address, $userInfo["ethAddress"]);
        }
        echo json_encode($address);
        die;
    }
 
    public function sendETH()
    {
        vendor("Eth");
        $eth = new \Eth();
 
        $address = input('address');
        $amount = input('amount');
        $ProposeGasPrice =   gastracker();
        $x_gasPrice  = bcmul($ProposeGasPrice, 1000000000);
        #解锁账户
        $eth->unlockAccount($address);
        $resposne = $eth->sendMinerETH($address, $this->baseEthAddress, $amount,$x_gasPrice, false);
        //$response = $eth->sendETH($address, $this->baseEthAddress, $amount);
 
        print_r($resposne);
    }
 
    public function addETH()
    {
        $address = input('address');
        $amount = input('amount');
        $pass = input('pass');
 
        if ($pass != 'pFFXjqUvcvNde2To') {
            $data = array();
            $data["status"] = "error";
            echo json_encode($data);
            die;
        }
 
        $userId = $this->user->getUserIdByEthAddress($address);
 
        //将金额存入账户
        $this->assets->addETH($userId, $amount, "ETH充值");
 
        $data = array();
        $data["status"] = "success";
        echo json_encode($data);
        die;
    }

    public function listener()
    {
        vendor("Eth");
        $eth = new \Eth();
 
        $userList = $this->user->userList();
        for ($i = 0; $i < count($userList); $i++) {
            $userInfo = $userList[$i];
            //判断用户是否已经生成ETH地址
            if (!$userInfo["ethAddress"]) {
                $ethAddress = $eth->genPair();
                $this->user->addEthAddress($userInfo["userId"], $ethAddress);
            }
             
 
            //查询用户eth钱包金额变动
            $amount = $eth->getBalanceOfAddress($userInfo["ethAddress"]);
            if ($amount["balance"] > 0) {
                print_r($amount);
                print_r($userInfo);
                //准备将金额转出
                $re = $eth->sendETH($userInfo["ethAddress"], $this->baseEthAddress, $amount["balance"]);
                print_r($re);
                die;
                //将金额存入账户
                $this->assets->addETH($userInfo["userId"], $amount["balance"], "ETH充值");
            }
        }
    }
}
