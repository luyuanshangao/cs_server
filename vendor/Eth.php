<?php
require('autoload.php');

define('RPC_IP','localhost');
define('RPC_PORT',10123);

require 'ethereum-php/ethereum.php';

class Eth
{
    private $eth;

    function __construct()
    {
        $this->eth = new Ethereum(RPC_IP, RPC_PORT);
        if(!$this->eth->net_version()) die('RPC ERROR');
    }

    /*
    *得到一个地址的余额，
    *来自parity的余额以十六进制形式出现在wei中
    *使用bc数学函数转换它
    */
    function getBalanceOfAddress($addr)
    {
        $eth_hex = $this->eth->eth_getBalance($addr, 'latest');
        $eth = $this->wei2eth($this->bchexdec($eth_hex));
        return array('balance'=>$eth);
    }

    function eth_syncing(){
        $eth_hex = $this->eth->eth_syncing();
        
        return $eth_hex;
    }

    function eth_accounts()
    {
        $eth_hex = $this->eth->eth_accounts();
        
        return $eth_hex;
    }
    function eth_gasPrice()
    {
        $eth_hex = $this->eth->eth_gasPrice();
        
        return $eth_hex;
    }
    function eth_blockNumber()
    {
        $eth_hex = $this->eth->eth_blockNumber();
        
        return $eth_hex;
    }
    function parity_devLogs()
    {
        $eth_hex = $this->eth->request("parity_devLogs", array());
       
        return $eth_hex;
    }


    function getCurrentPrice($currency='USD')
    {
        $data = json_decode(file_get_contents('https://api.coinbase.com/v2/prices/ETH-'.$currency.'/spot'),true);
        return $data['data']['amount'];
    }

    function sendETH($from, $to, $amount) {
        $data = array();
        $data["from"] = $from;
        $data["to"] = $to;
        $data["value"] = "0x" . dechex($amount * 1000000000 * 1000000000 - 21000 * 3 * 1000000000);
        $data["gas"] = "0x" . dechex(21000);
        $data["gasPrice"] = "0x". dechex(3 * 1000000000);

        $resposne = $this->eth->request("personal_sendTransaction", array($data, "coinshop"));

        // $resposne = $this->eth->eth_sendTransaction($data);
        return $resposne;
    }

    function test() {
    }

    /*
    *我们将使用vanityeth生成私钥对
    * npm install -g vanity-eth
    *我们必须重新格式化输出字符串以用作JSON
    */
    function genPair()
    {
        $response = $this->eth->request("personal_newAccount", array("coinshop"));
        $ethAddress = json_decode(json_encode($response),true)["result"];
        return $ethAddress;
    }
    //以下功能用于转换和处理大数字
    function wei2eth($wei)
    {
        return bcdiv($wei,1000000000000000000,18);
    }
    function eth2wei($eth)
    {
        return bcmul($eth,1000000000000000000,18);
    }
    function bchexdec($hex) {
        if(strlen($hex) == 1) {
            return hexdec($hex);
        } else {
            $remain = substr($hex, 0, -1);
            $last = substr($hex, -1);
            return bcadd(bcmul(16, $this->bchexdec($remain)), hexdec($last));
        }
    }

}