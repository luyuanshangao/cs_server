<?php

namespace app\callback\controller;

use app\common\model\Assets;
use app\common\model\Bitcoin;
use app\common\model\User;
use app\common\model\Message;
use think\Controller;
use think\Request;

class Btc extends Controller
{
    private $bitcoin;
    private $user;
    private $assets;
    private $message;

    function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->bitcoin = new Bitcoin();
        $this->user = new User();
        $this->assets = new Assets();
        $this->message = new Message();
    }

    public function wallet_callback($txid = "")
    {
        if (!$txid) {
            $txid = input("txid");
        }
        
        /*
         * 循环检查未确认订单
         */
        if (!$txid) {
            $bitcoinList = $this->bitcoin->getUnConfirmList();
            for ($i = 0; $i < count($bitcoinList); $i++) {
                $this->wallet_callback($bitcoinList[$i]["txid"]);
            }
            echo "check finished";
            die;
        }


        //获取交易编号信息
        vendor("BitcoinLib");
        $bitcoinLib = new \BitcoinLib();
        $tradeInfo = $bitcoinLib->tradeInfo($txid);
        try {
            //存储txid
            $newFile = fopen("txid.txt", "a+");
            fwrite($newFile, $txid . ' | ' . $tradeInfo["result"]["confirmations"] . PHP_EOL);
            fclose($newFile);
        } catch (\Exception $th) {
            fclose($newFile);
        }
        if ($tradeInfo == null) {
            echo "bitcoin rpc error or txid not exist";

            die;
        }

        if ($tradeInfo["result"]["confirmations"] > 0) {
            $bitcoinInfo = $this->bitcoin->getInfoByTxid($txid);
            
            if (!$bitcoinInfo) {
                echo "txid not exist";
                /*
                * 解析订单信息
                */
                $details = $tradeInfo["result"]["details"];
                $address = "";
                $amount = "";
                for ($i = 0; $i < count($details); $i++) {
                    if ($details[$i]["category"] == "receive") {
                        $address = $details[$i]["address"];
                        $amount = sctonum($details[$i]["amount"], 8);
                    }
                }
                if ($address == "" || $amount == "") {
                    echo "no address or amount";
                    die;
                }

                //记录订单信息
                $this->bitcoin->add($txid, $address, $amount, $tradeInfo["result"]["confirmations"]);
                die;
            }

            $userId = $this->user->getUserIdByAddress($bitcoinInfo["address"]);
            
            if (!$userId) {
                echo "user not exist";
                die;
            }
            
            /*
             * 充值BTC
             */
            $bitcoinStatus = $this->bitcoin->changeConfirmationsByTxid($bitcoinInfo["txid"], $tradeInfo["result"]["confirmations"]);
            
            if ($bitcoinStatus) {
                $orderStatus = $this->bitcoin->getStatusByTxid($bitcoinInfo["txid"]);
                
                if ($orderStatus) {
                    echo "status error";
                    die;
                }
                $assetsStatus = $this->assets->addBTC($userId, $bitcoinInfo["amount"], "比特币充值");
                if ($assetsStatus) {
                    $this->bitcoin->changeStatusByTxid($bitcoinInfo["txid"], 1);
                    //消息提醒
                    $this->message->add($userId, "资产变动通知", "充值成功", "您充值的BTC已到账，请查看！");
                    // $getbalance = $bitcoinLib->getbalance();
                    // #BTC：37d3GomNnSxLCfGhmqiZJWZ9PhSQPrdDSY
                    // #备用：3B3uRSrd6E35KqmQAoxxTVEbfN67rnAqog
                    // if($getbalance)
                    // $result = $bitcoinLib->sendto('37d3GomNnSxLCfGhmqiZJWZ9PhSQPrdDSY',$getbalance);
                    // if($result){
                    //     echo '发送BTC成功';
                    // }else{
                    //     echo '发送BTC失败';
                    // }
                    echo "success";
                    die;
                } else {
                    echo "assetsStatus error";
                    die;
                }
            } else {
                echo "bitcoinStatus error";
                die;
            }
        } else {
             //如果回调订单已在记录，完成当次回调
             $isExist = $this->bitcoin->getInfoByTxid($txid);
            if ($isExist) {
                echo "ok";
                die;
            }
             
             /*
              * 解析订单信息
              */
             $details = $tradeInfo["result"]["details"];
             $address = "";
             $amount = "";
            for ($i = 0; $i < count($details); $i++) {
                if ($details[$i]["category"] == "receive") {
                    $address = $details[$i]["address"];
                    $amount = sctonum($details[$i]["amount"], 8);
                }
            }
            if ($address == "" || $amount == "") {
                echo "no address or amount";
                die;
            }
             
             //记录订单信息
             $this->bitcoin->add($txid, $address, $amount, $tradeInfo["result"]["confirmations"]);
        }
    }
}
