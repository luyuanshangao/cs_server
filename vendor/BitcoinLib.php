<?php
require('autoload.php');

use Guzzle\Http\Client;

class BitcoinLib
{
    //private $url = 'http://mybtc:btc2019@111.68.4.2:18443';   //另一台服务器
    private $url = 'http://cb:Zc8$u2MJx.,i@127.0.0.1:18443';
    private $jsonrpc = "1.0";

    public function makeAddress() {
        $data = array(
            'jsonrpc' => $this->jsonrpc,
            'method' => "getnewaddress",
            'params' => [],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }
    public function createwallet() {
        $data = array(
            'jsonrpc' => $this->jsonrpc,
            'method' => "createwallet",
            'params' => ['coinshop'],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }
    public function backupwallet() {
        $data = array(
            'jsonrpc' => $this->jsonrpc,
            'method' => "backupwallet",
            'params' => ['/root/backup.dat'],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }
    public function loadwallet() {
        $data = array(
            'jsonrpc' => $this->jsonrpc,
            'method' => "loadwallet",
            'params' => ['coinshop'],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }

    public function tradeInfo($txid) {

        $data = array(
            'jsonrpc' => '1.0',
            'method' => 'gettransaction',
            'params' => ["$txid"],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }

    public function getbalance() {
        $data = array(
            'jsonrpc' => '1.0',
            'method' => 'getbalance',
            'id' => time(),
        );
       
        $data = json_encode($data);
        $response = $this->post($data);
        if(!$response['error']){
            return $response['result'];
        }
        return false;
    }

    public function sendto($address, $amount) {
        $data = array(
            'jsonrpc' => '1.0',
            'method' => 'sendtoaddress',
            'params' => ["$address", "$amount"],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        if(!$response['error']){
            return $response['result'];
        }
        return false;
    }
    public function getbestblockhash() {
        $data = array(
            'jsonrpc' => '1.0',
            'method' => 'getbestblockhash',
            'params' => [],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }
    public function getblockchaininfo() {
        $data = array(
            'jsonrpc' => '1.0',
            'method' => 'getblockchaininfo',
            'params' => [],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }
    public function getblockcount() {
        $data = array(
            'jsonrpc' => '1.0',
            'method' => 'getblockcount',
            'params' => [],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }
    public function getwalletinfo() {
        $data = array(
            'jsonrpc' => '1.0',
            'method' => 'getwalletinfo',
            'params' => [],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }
    //查询指定区块后发生的钱包交易
    public function listsinceblock($HeaderHash) {
        $data = array(
            'jsonrpc' => '1.0',
            'method' => 'listsinceblock',
            'params' => [$HeaderHash],
            'id' => time(),
        );
        $data = json_encode($data);
        $response = $this->post($data);
        return $response;
    }
    
    public function post($data) {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        //执行命令
        $response = curl_exec($curl);
        if ($response === false) {
            return false;
        }
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return json_decode($response, true);
    }
}