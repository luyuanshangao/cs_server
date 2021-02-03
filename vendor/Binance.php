<?php

class Binance
{
    private $url;
    private $api_key;
    private $secret_key;

    function __construct()
    {
        #测试网络
        // $this->url = "https://testnet.binance.vision";
        // $this->api_key = "BLIS6EW79TIIbCi5qqVwHEL4B0HTZcS80XToq95mObRpaf6T54hKH4JDklpW2juy";
        // $this->secret_key = "IlHLu5vIRdpNvkQsDN5PHgG0WC4czGHqXv82tRg3ya7WTTVP6dSQR1DUXyz10J9q";
        #正式网络
        $this->url = "https://api.binance.com";
        $this->api_key = "8VqgSX1bZ4qc3eYyUoAbXKjxgRFBlTR0HhxbQ9jQQtC2rC1fQewtZ0WPLWK6u2hT";
        $this->secret_key = "BDmGGKaZYdvyGRKYO2IdkNNysWWmA1o4fj8wbL9vuB1Yrv4qtVLCcn89sCquVN6o";
    }

    public function ETH2USDT($amount)
    {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol" => "ETHUSDT", "limit" => 5));
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $usdt = $amount * $rate;
            return number_format($usdt, 4, '.', '');
        }
    }
    public function UNI2USDT($amount)
    {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol" => "UNIUSDT", "limit" => 5));
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $usdt = $amount * $rate;
            return number_format($usdt, 4, '.', '');
        }
    }
    public function USDT2ETH($amount)
    {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol" => "ETHUSDT", "limit" => 5));
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $eth = $amount / $rate;
            return number_format($eth, 8, '.', '');
        }
    }
    public function BTC2USDT($amount)
    {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol" => "BTCUSDT", "limit" => 5));
        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $usdt = $amount * $rate;
            return number_format($usdt, 4, '.', '');
        }
    }
    public function USDT2BTC($amount)
    {
        $dataInfo = $this->sendCurl("/api/v3/depth", array("symbol" => "BTCUSDT", "limit" => 5));

        if (isset($dataInfo["code"])) {
            return false;
        } else {
            $rate = $dataInfo["bids"][1][0];
            $btc = $amount / $rate;
            return number_format($btc, 8, '.', '');
        }
    }
    public function USDT2USDT($amount)
    {
        return $amount;
    }

    public function getDepth($symbol, $side, $amount)
    {
        $dataInfo = $this->sendCurl("/api/v3/ticker/bookTicker", array("symbol" => $symbol));
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

    public function makeOrder($symbol, $side, $amount)
    {
        $dataInfo = $this->sendCurl("/api/v3/ticker/bookTicker", array("symbol" => $symbol));
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
        $params["timestamp"] = $this->getTimes();
        $sign = $this->makeSignature($params, $this->secret_key);
        $params["signature"] = $sign;

        $dataInfo = $this->sendCurl("/api/v3/order", $params, "post");

        if (isset($dataInfo["code"])) {
            return false;
        } else {
            return true;
        }
    }

    //测试订单
    public function order_test($symbol, $side, $amount, $price)
    {

        $params = array();
        $params["symbol"] = $symbol;
        $params["side"] = $side;
        $params["type"] = "LIMIT";
        $params["timeInForce"] = "GTC";
        $params["quantity"] = $amount;
        $params["price"] = $price;
        $params["recvWindow"] = 60000;
        $params["timestamp"] = $this->getTimes();
        $sign = $this->makeSignature($params, $this->secret_key);
        $params["signature"] = $sign;

        $dataInfo = $this->sendCurl("/api/v3/order/test", $params, "post");
        return $dataInfo;
    }

    //下订单
    public function order($symbol, $side, $amount, $price)
    {

        $params = array();
        $params["symbol"] = $symbol;
        $params["side"] = $side;
        $params["type"] = "LIMIT";
        $params["timeInForce"] = "GTC"; //直到取消为止
        $params["quantity"] = $amount;
        $params["price"] = $price;    //以多少美元买卖出 
        $params["recvWindow"] = 60000;
        $params["timestamp"] = $this->getTimes();
        $sign = $this->makeSignature($params, $this->secret_key);
        $params["signature"] = $sign;

        $dataInfo = $this->sendCurl("/api/v3/order", $params, "post");
        // array (
        //     'symbol' => 'BTCUSDT',
        //     'orderId' => 80470,
        //     'orderListId' => -1,
        //     'clientOrderId' => '1cf615ni4Oa4QKiSNDjznD',
        //     'transactTime' => 1612153991061,
        //     'price' => '40000.00000000',
        //     'origQty' => '0.01000000',
        //     'executedQty' => '0.00000000',
        //     'cummulativeQuoteQty' => '0.00000000',
        //     'status' => 'NEW',
        //     'timeInForce' => 'GTC',
        //     'type' => 'LIMIT',
        //     'side' => 'SELL',
        //     'fills' => 
        //     array (
        //     ),
        //   )
        return $dataInfo;
    }

    //订单信息
    public function orderInfo($orderId, $symbol = "BTCUSDT")
    {
        $params = array();
        $params["symbol"] = $symbol;
        $params["orderId"] = $orderId;
        $params["recvWindow"] = 60000;
        $params["timestamp"] = $this->getTimes();
        $sign = $this->makeSignature($params, $this->secret_key);
        $params["signature"] = $sign;

        $dataInfo = $this->sendCurl("/api/v3/order", $params, "get");
        // array (
        //     'symbol' => 'BTCUSDT',
        //     'orderId' => 80470,
        //     'orderListId' => -1,
        //     'clientOrderId' => '1cf615ni4Oa4QKiSNDjznD',
        //     'price' => '40000.00000000',
        //     'origQty' => '0.01000000',
        //     'executedQty' => '0.00000000',
        //     'cummulativeQuoteQty' => '0.00000000',
        //     'status' => 'NEW',
        //     'timeInForce' => 'GTC',
        //     'type' => 'LIMIT',
        //     'side' => 'SELL',
        //     'stopPrice' => '0.00000000',
        //     'icebergQty' => '0.00000000',
        //     'time' => 1612153991061,
        //     'updateTime' => 1612153991061,
        //     'isWorking' => true,
        //     'origQuoteOrderQty' => '0.00000000',
        //   )

        return $dataInfo;
    }


    //当前最优挂单 汇率
    public function bookTicker($symbol)
    {
        $params = array();
        $params["symbol"] = $symbol;
        $dataInfo = $this->sendCurl("/api/v3/ticker/bookTicker", $params, "get");
        // array (
        //     'symbol' => 'BTCUSDT',
        //     'bidPrice' => '33547.68000000',
        //     'bidQty' => '0.01490100',
        //     'askPrice' => '33547.69000000',
        //     'askQty' => '2.61993800',
        //   )

        return $dataInfo;
    }

    //深度信息 
    public function depth($symbol)
    {
        $params = array();
        $params["symbol"] = $symbol;
        $dataInfo = $this->sendCurl("/api/v3/depth", $params, "get");
        // array (
        //     'lastUpdateId' => 8383968590,
        //     'bids' => 
        //     array (
        //       0 => 
        //       array (
        //         0 => '33383.79000000',
        //         1 => '0.03673600',
        //       ),
        //       1 => 
        //       array (
        //         0 => '33383.57000000',
        //         1 => '0.00598900',
        //       ),
        //       2 => 
        //       array (
        //         0 => '33381.94000000',
        //         1 => '0.48299900',
        //       ),
        //       3 => 
        //       array (
        //         0 => '33381.93000000',
        //         1 => '2.00000000',
        //       ),
        //       4 => 
        //       array (
        //         0 => '33381.01000000',
        //         1 => '0.06237100',
        //       ),
        //     ),
        //     'asks' => 
        //     array (
        //       0 => 
        //       array (
        //         0 => '33391.02000000',
        //         1 => '0.06580500',
        //       ),
        //       1 => 
        //       array (
        //         0 => '33391.90000000',
        //         1 => '0.02538900',
        //       ),
        //       2 => 
        //       array (
        //         0 => '33391.91000000',
        //         1 => '0.20000000',
        //       ),
        //       3 => 
        //       array (
        //         0 => '33393.56000000',
        //         1 => '0.00599000',
        //       ),
        //       4 => 
        //       array (
        //         0 => '33393.76000000',
        //         1 => '0.05659500',
        //       ),
        //     ),
        //   )
        return $dataInfo;
    }

    //交易规范信息
    public function exchangeInfo()
    {
        $dataInfo = $this->sendCurl("/api/v3/exchangeInfo", [], "get");

        return $dataInfo;
    }


    private function sendCurl($api, $data, $method = "get")
    {
        //初始化
        $curl = curl_init();
        $headers = array(
            'X-MBX-APIKEY: ' . $this->api_key,
        );
        //设置抓取的url
        if ($method == "get") {
            if (empty($data)) {
                curl_setopt($curl, CURLOPT_URL, $this->url . $api);
            } else {
                curl_setopt($curl, CURLOPT_URL, $this->url . $api . "?" . http_build_query($data));
            }
        } else {
            curl_setopt($curl, CURLOPT_URL, $this->url . $api);
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
    public function getTimes(){
        $dataInfo = $this->sendCurl("/api/v3/time", [], "get");
        return $dataInfo['serverTime'];
    }
    private function makeSignature($args, $key)
    {
        $requestString = http_build_query($args);
        $newSign = hash_hmac("sha256", $requestString, $key);
        return $newSign;
    }
}
