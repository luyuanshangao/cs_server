<?php

namespace app\queue\controller;

use app\common\model\Assets;
use app\common\model\User;
use think\Controller;
use think\Exception;
use think\Request;

class UsdtQueue extends Controller
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

    public function test()
    {
        vendor("ERC");
        $erc = new \ERC();
        $erc->test();
    }
    
    public function listener()
    {
        vendor("Eth");
        $eth = new \Eth();

        vendor("ERC");
        $erc = new \ERC();

        $userList = $this->user->userList();
       
        for ($i = 0; $i < count($userList); $i++) {
            $userInfo = $userList[$i];
            //判断用户是否已经生成ETH地址
            if (!$userInfo["ethAddress"]) {
                $ethAddress = $eth->genPair();
                $this->user->addEthAddress($userInfo["userId"], $ethAddress);
            }
            
            //查询用户eth钱包金额变动
            $amount = $erc->getBalanceOfAddress($userInfo["ethAddress"]);
            
            if ($amount["balance"] > 0) {
                //准备将金额转出
                $re = $erc->sendUSDT($userInfo["ethAddress"], $this->baseEthAddress, $amount["balance"]);
                if ($re) {
                    //将金额存入账户
                    $this->assets->addUSDT($userInfo["userId"], $amount["balance"], "USDT充值");
                }
            }
        }
    }
}
