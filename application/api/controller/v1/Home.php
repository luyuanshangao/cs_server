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
        //  vendor("BitcoinLib");
        // $bitcoin = new \BitcoinLib();
        // $response = $bitcoin->loadwallet();
        // var_export($response);
        // die;
        vendor("Eth");
        $eth = new \Eth();
        $resultReceipt = $eth->eth_getTransactionReceipt('0xb8d5068764abe038260b3affd77b55c02ce5cc7f3641ae4b86c4d9fdd82c104d');
        var_export($resultReceipt);die;
        //'0xb8d5068764abe038260b3affd77b55c02ce5cc7f3641ae4b86c4d9fdd82c104d',
       $eth_gasPrice = $eth->eth_gasPrice();
     var_export($eth->eth_estimateGas('0xd2c18c3d7239e416d6f2725e7df407a41c942941','0x955b9FB52736880592a0C3387cb970a52c7f3230',$eth_gasPrice, "0x" . dechex(0.0012 * 1000000000 * 1000000000) ));die;
     var_export($eth->eth_getTransactionReceipt('0x43fb9e77e93367b306354944393017f53208f40e01a0e5a1da6040d3dbe28603'));die;
        
        //$eth_syncing = $eth->eth_getTransactionByHash('0xb8d5068764abe038260b3affd77b55c02ce5cc7f3641ae4b86c4d9fdd82c104d');
        
        $eth_gasPrice = $eth->eth_gasPrice(); //获取gasprice
        $x_gasPrice = $eth->bchexdec($eth_gasPrice);
        // $eth->unlockAccount('0xd2c18c3d7239e416d6f2725e7df407a41c942941');die();
       // $resposne = $eth->cacalTrans('0xd2c18c3d7239e416d6f2725e7df407a41c942941','0x955b9fb52736880592a0c3387cb970a52c7f3230' ,0,$x_gasPrice);

        $getBalanceOfAddress = $eth->getBalanceOfAddress('0x955b9FB52736880592a0C3387cb970a52c7f3230');
        var_export($getBalanceOfAddress);
        $getBalanceOfAddress = $eth->getBalanceOfAddress('0xd2c18c3d7239e416d6f2725e7df407a41c942941');
        var_export($getBalanceOfAddress);die();
        // $eth_accounts = $eth->eth_accounts();
        // $eth_gasPrice = $eth->eth_gasPrice();
        // $eth_blockNumber = $eth->eth_blockNumber();
        // var_export($eth_accounts);
        // var_export($eth_gasPrice);
        // var_export($eth_blockNumber);
     

        
        $geth = new \EthereumRPC\EthereumRPC('127.0.0.1', 10123);
        $erc20 = new \ERC20\ERC20($geth);
        $token = $erc20->token('0xdac17f958d2ee523a2206206994597c13d831ec7');

        // var_dump($token->decimals());
        // var_dump($token->symbol());
        var_dump($token->balanceOf('0x955b9FB52736880592a0C3387cb970a52c7f3230')); 
        die();
        

    
       
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
