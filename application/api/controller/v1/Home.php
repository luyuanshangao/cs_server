<?php

namespace app\api\controller\v1;

use app\common\model\Category;
use think\Controller;
use app\common\model\Category as CategoryModel;
use think\Log;

class Home extends Controller
{

    public function index()
    {
  
        $geth = new \EthereumRPC\EthereumRPC('127.0.0.1', 10123);
        $erc20 = new \ERC20\ERC20($geth);
        $token = $erc20->token('0xdac17f958d2ee523a2206206994597c13d831ec7');
      
        var_dump($token->name());
        var_dump($token->decimals());
        var_dump($token->symbol());
        var_dump($token->balanceOf('0xc121162bcca875b95f0398d14fd61166f1688f52')); 
        die();
        
        // vendor("Eth");
        // $eth = new \Eth();

        // var_export($eth->eth_syncing());
        //vendor("ERC");
        // $erc = new \ERC();
        // //查询用户eth钱包金额变动
        // $amount1 = $erc->getBalanceOfAddress('0x62f422C23565eF5bDbaB6AF88ee9809835dc6AD1');
        // $amount = $erc->getBalanceOfAddress('0xc121162bcca875b95f0398d14fd61166f1688f52');
        // var_export($amount1);
        // var_export($amount);
  
        // $eth = new \Eth();
        // $amount1 = $eth->getBalanceOfAddress('0x62f422C23565eF5bDbaB6AF88ee9809835dc6AD1');
        // $amount = $eth->getBalanceOfAddress('0xc121162bcca875b95f0398d14fd61166f1688f52');
        // var_export($amount1);
        // var_export($amount);
        // die;
    
        // vendor("BitcoinLib");
        // $bitcoin = new \BitcoinLib();
        // $response = $bitcoin->loadwallet();
        // var_export($response);
        // die;
    }

  /**
   * @name: 批量注册
   * @author: gz
   * @description:
   * @param {type}
   * @return {type}
   */
    public function batchRegList()
    {
        $numRe =  $this->request->get('numRe');
        $incode =  $this->request->get('incode');
        $skuNum =  $this->request->get('skuNum');
        $aa = $this->request->isOptions();
        if ($aa) {
            return show(1);
        }
        return show(1, self::batchReg($numRe, $incode, $skuNum));
    }


  /**
   * @name: 批量注册
   * @author: gz
   * @description:
   * @param {type}
   * @return {type}
   */
    public static function batchReg($numRe, $incode, $skuNum)
    {
      // //注册人数

        $registerModel = new \app\api\controller\v1\Register();
        $list = [];

        for ($i = 0; $i < $numRe; $i++) {
            $apiData = $registerModel->getWalletWords()->getdata();
            $words = $apiData['data']['words'];
            $dataArr = [
            'walletWords' => $words,
            'incode' => $incode,
            'gesturePassword' => '03678',
            'payPassWord' => '000000',
            ];

            $result = $registerModel->indexsss_sss($dataArr);

            $dada = $result->getdata();

            if ($dada['code'] == 1) {
                $list[] = [
                'walletWords' => $dada['data']['walletWords'],
                'incode' => $dada['data']['incode'],
                'result' => '注册成功',
                ];
            }
        }

        return $list;
    }
}
