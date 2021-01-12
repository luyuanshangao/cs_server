<?php
require('autoload.php');

use Guzzle\Http\Client;

class BitcoinLib
{
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
        return $response;
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