<?php

class Binance
{
    private $url;
    private $api_key;
    private $secret_key;

    function __construct()
    {
        $this->url = "https://api.binance.com";
        $this->api_key = "h2BB7ze8XByNYM8mIK7ojdwfQ8fYt7BTUvUUFToxaXmlAeNPfTDE7ze7MFPLSPbf";
        $this->secret_key = "XTKqdJE2ywcOhFxV8T3l4Mvriqs9bnwtCJSUffD78BkHXk1t1rbkuDYmHseaFXn5";
    }

    public function ETH2USDT($amount) {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol"=>"ETHUSDT", "limit"=>5));
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $usdt = $amount * $rate;
            return number_format($usdt, 4, '.', '');
        }
    }
    public function UNI2USDT($amount) {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol"=>"UNIUSDT", "limit"=>5));
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $usdt = $amount * $rate;
            return number_format($usdt, 4, '.', '');
        }
    }
    public function USDT2ETH($amount) {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol"=>"ETHUSDT", "limit"=>5));
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $eth = $amount / $rate;
            return number_format($eth, 8, '.', '');
        }
    }
    public function BTC2USDT($amount) {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol"=>"BTCUSDT", "limit"=>5));
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $usdt = $amount * $rate;
            return number_format($usdt, 4, '.', '');
        }
    }
    public function USDT2BTC($amount) {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol"=>"BTCUSDT", "limit"=>5));
       
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $btc = $amount / $rate;
            return number_format($btc, 8, '.', '');
        }
    }
    public function USDT2USDT($amount) {
         return $amount;
    }

    public function getDepth($symbol, $side, $amount) {
        $dataInfo = $this->sendCurl("/api/v3/ticker/bookTicker", array("symbol"=>$symbol));
        if (!$dataInfo) {
            return false;
        }
        if (isset($dataInfo["code"])) {
            return false;
        }
        if ($side == "SELL") {
            return $amount * $dataInfo["bidPrice"];
        } else {
            return $amount / $dataInfo["bidPrice"];
        }
    }

    public function makeOrder($symbol, $side, $amount) {
        $dataInfo = $this->sendCurl("/api/v3/ticker/bookTicker", array("symbol"=>$symbol));
        if (!$dataInfo) {
            return false;
        }
        if (isset($dataInfo["code"])) {
            return false;
        }

        $params = array();
        $params["symbol"] = $symbol;
        $params["side"] = $side;
        $params["type"] = "LIMIT";
        $params["timeInForce"] = "GTC";
        $params["quantity"] = $amount;
        $params["price"] = $dataInfo["bidPrice"];
        $params["recvWindow"] = 60000;
        $params["timestamp"] = getMillisSecond();
        $sign = $this->makeSignature($params, $this->secret_key);
        $params["signature"] = $sign;

        $dataInfo = $this->sendCurl("/api/v3/order", $params, "post");

        if (isset($dataInfo["code"])) {
            return false;
        } else {
            return true;
        }
    }

    public function orderInfo($orderId) {
        $params = array();
        $params["symbol"] = "BTCUSDT";
        $params["orderId"] = $orderId;
        $params["recvWindow"] = 60000;
        $params["timestamp"] = getMillisSecond();
        $sign = $this->makeSignature($params, $this->secret_key);
        $params["signature"] = $sign;

        $dataInfo = $this->sendCurl("/api/v3/order", $params, "get");
        return $dataInfo;
    }

    public function orderList() {
        $params = array();
        $params["symbol"] = "BTCUSDT";
        $params["recvWindow"] = 60000;
        $params["timestamp"] = getMillisSecond();
        $sign = $this->makeSignature($params, $this->secret_key);
        $params["signature"] = $sign;

        $dataInfo = $this->sendCurl("/api/v3/openOrders", $params, "get");
        return $dataInfo;
    }


    private function sendCurl($api, $data, $method = "get") {
        //初始化
        $curl = curl_init();
        $headers = array(
            'X-MBX-APIKEY: ' . $this->api_key,
        );
        //设置抓取的url
        if ($method == "get") {
            if (empty($data)) {
                curl_setopt($curl, CURLOPT_URL, $this->url.$api);
            } else {
                curl_setopt($curl, CURLOPT_URL, $this->url.$api."?".http_build_query($data));
            }
        } else {
            curl_setopt($curl, CURLOPT_URL, $this->url.$api);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        //设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

        // curl_setopt($curl, CURLOPT_PROXY, "127.0.0.1"); //代理服务器地址  
        // curl_setopt($curl, CURLOPT_PROXYPORT, 1080); //代理服务器端口 
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return json_decode($data, true);
    }

    private function makeSignature($args, $key)
    {
        $requestString = http_build_query($args);
        $newSign = hash_hmac("sha256", $requestString, $key);
        return $newSign;
    }
}