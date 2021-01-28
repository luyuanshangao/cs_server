<?php
require('autoload.php');

defined('RPC_IP') or define('RPC_IP','127.0.0.1');
defined('RPC_PORT') or define('RPC_PORT',10123);

use EthereumRPC\EthereumRPC;
use ERC20\ERC20;

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
    function getBalanceOfAddress($addr)
    {
        $token = $this->erc->token($this->contract);
        $uni = $token->balanceOf($addr);
        return array('balance'=>$uni);
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