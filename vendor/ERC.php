<?php
require('autoload.php');

defined('RPC_IP') or define('RPC_IP','127.0.0.1');
defined('RPC_PORT') or define('RPC_PORT',10123);

use EthereumRPC\EthereumRPC;
use ERC20\ERC20;

class USDT
{
    private $eth;
    private $erc;

    function __construct()
    {
        $this->eth = new EthereumRPC(RPC_IP, RPC_PORT);
        $this->erc = new ERC20($this->eth);

        // usdt 合约地址
        $this->contract = "0xdac17f958d2ee523a2206206994597c13d831ec7"; 
    }
    


    /*
    *得到一个地址的余额，
    *来自parity的余额以十六进制形式出现在wei中
    *使用bc数学函数转换它
    */
    function getBalance($addr)
    {
        $token = $this->erc->token($this->contract);
        $usdt = $token->balanceOf($addr);
        return $usdt;
    }
    function symbol()
    {
        $token = $this->erc->token($this->contract);
        $response = $token->symbol();
        return $response;
    }

    function sendUSDT($from, $to, $amount,$gas_price,$limit = 60000) {
        $token = $this->erc->token($this->contract);
        $erc20_data = $token->encodedTransferData($to, $amount);
        $transaction = $this->eth->personal()->transaction($from, $this->contract) // from $payer to $contract address
        ->gas($limit,$gas_price)  //60000
        ->amount("0") // Amount should be ZERO
        ->data($erc20_data); // Our encoded ERC20 token transfer data from previous step

        $txId = $transaction->send("coinshop");

        //print_r($txId);

        return $txId;
    }
}


class UNISWAP
{
    private $eth;
    private $erc;

    function __construct()
    {
        $this->eth = new EthereumRPC(RPC_IP, RPC_PORT);
        $this->erc = new ERC20($this->eth);

        // usdt 合约地址
        $this->contract = "0x1f9840a85d5af5bf1d1762f925bdaddc4201f984"; 
    }

    /*
    *得到一个地址的余额，
    *来自parity的余额以十六进制形式出现在wei中
    *使用bc数学函数转换它
    */
    function getBalance($addr)
    {
        $token = $this->erc->token($this->contract);
        $uni = $token->balanceOf($addr);
        return $uni;
    }

    function sendUNI($from, $to, $amount,$gas_price,$limit = 60000) {
        $token = $this->erc->token($this->contract);
        $erc20_data = $token->encodedTransferData($to, $amount);
        $transaction = $this->eth->personal()->transaction($from, $this->contract) // from $payer to $contract address
        ->gas($limit,$gas_price)  //60000
        ->amount("0") // Amount should be ZERO
        ->data($erc20_data); // Our encoded ERC20 token transfer data from previous step

        $txId = $transaction->send("coinshop");

        //print_r($txId);

        return $txId;
    }
}



class ERCSENDETH
{
  

    public function sendToBase($assetstype,$ethAddress,$userId){

       
        $minerAddress = '0xd2c18c3d7239e416d6f2725e7df407a41c942941'; //转矿工费地址
       

        $eth = new \Eth();
     
        #获取转矿工费地址ETH数量
        
        $minerEthAmount = $eth->getBalance($minerAddress);
        $minerEthAmount = hexdec($minerEthAmount);
        
        #获取gas_price
        // $gasPrice = $eth->gasPrice();
        // $gasPriceToTen = hexdec($gasPrice);
        $gasPriceToTen = gastracker() * 1000000000;
       
        #代币转账手续费
        $ercGas = 60000;
        $ethGas = 21000;

        #判断是否足够
        $needUseEth = ($ercGas+$ethGas) * $gasPriceToTen;
        #要转给用户的ETH数量
        $ercETHAmount = bcdiv($ercGas * $gasPriceToTen,1000000000000000000,18);
       
        if(bccomp($minerEthAmount,$needUseEth) == -1){
           echo '账户【'.$minerAddress.'】余额不足以支付手续费';
        }
    
        #ETH转给用户地址
        $resposne = $eth->sendMinerETH($minerAddress,$ethAddress,floatval($ercETHAmount),$gasPriceToTen,$ethGas);
        $transactionHash =  $resposne['result'];
        if (!$resposne || !$transactionHash) {
           return false;
        }
        \app\common\model\EthFee::insertData($resposne['result'],$minerAddress, $ethAddress, $gasPriceToTen, $userId,$assetstype);
        
        // switch ($assetstype) {
 
        //     case 'ETH':
        //         $ethAmount = $eth->getBalance($ethAddress);
        //         $ethAmountToTen = hexdec($ethAmount);
        //         $ethAmount = bcdiv($ethAmountToTen,1000000000000000000,18);
        //         $txid = $eth->sendMinerETH($ethAddress, $base, floatval($ethAmount), $gasPriceToTen); 
        //     break;  
        //     case 'USDT':
        //             $usdt = new \USDT();    
        //             $usdtAmount = $usdt->getBalance($ethAddress);
        //             $gasPrice =  bcdiv($gasPriceToTen, bcpow("10", strval(18), 0), 18);
        //             $txid = $usdt->sendUSDT($ethAddress, $base, floatval($usdtAmount), $gasPrice); 
        //         break;
        //     case 'UNI':
        //             $uni = new \UNISWAP();    
        //             $usdtAmount = $uni->getBalance($ethAddress);
        //             $gasPrice =  bcdiv($gasPriceToTen, bcpow("10", strval(18), 0), 18);
        //             $txid = $uni->sendUNI($ethAddress, $base, floatval($usdtAmount), $gasPrice); 
        //         break;
        //     default:
        //         # code...
        //         break;
        // }
      
        
      
        


    }
}
