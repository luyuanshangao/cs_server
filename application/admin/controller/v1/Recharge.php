<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\common\model\User;

/**
 * @name: Recharge管理
 * @author: gz
 * @description: GET POST
 * @param {type}
 * @return:
 */
class Recharge extends Base
{
    protected $noAuthArr = ['getStatus'];
    //接口白名单    不需要登录的接口方法
    protected $noCheckArr = ['get'];    //权限白名单

    
    public function list()
    {
        vendor("Eth");
        $eth = new \Eth();

        vendor("ERC");
        $erc = new \ERC();
        $uni = new \UNISWAP();
        $userList = User::userAddre();
        $transEth = $eth->getBalanceOfAddress('0xd2c18c3d7239e416d6f2725e7df407a41c942941');
        foreach ($userList as $userId => &$value) {
            $value['btcBalance'] = 0;
            $value['ethBalance'] = $eth->getBalanceOfAddress($value['ethAddress']);
            $value['usdtBalance'] = $erc->getBalanceOfAddress($value['ethAddress']);
            $value['uniBalance'] = $uni->getBalanceOfAddress($value['ethAddress']);
        }
        return show(1, [
            'transEth',
            'transEth',
        ])
    }
}
