<?php

namespace app\queue\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\model\User;
use app\common\model\EthFee;

class ConsoleUsdt extends Command
{

    private $baseEthAddress = "0x62f422C23565eF5bDbaB6AF88ee9809835dc6AD1";
    private $transferAccountsEthAddress = "0xd2c18c3d7239e416d6f2725e7df407a41c942941";

    protected function configure()
    {
        $this->setName('usdt')->setDescription('Usdt充值监控开始');
    }

    protected function execute(Input $input, Output $output)
    {
        vendor("Eth");
        $eth = new \Eth();

        vendor("ERC");
        $erc = new \ERC();

        $userList = User::userEthAddre();
        $processData = [];
        foreach ($userList as $userId => $ethAddress) {
            //判断用户是否已经生成ETH地址
            if (!$ethAddress) {
                $ethAddress = $eth->genPair();
                (new User())->addEthAddress($userId, $ethAddress);
            }
            #if($userId == 18373){ continue; } //这个账户中 有0.4usdt

            //查询用户eth钱包 usdt金额
            $amount = $erc->getBalanceOfAddress($ethAddress);
            $balance = $amount["balance"];
            $output->writeln("地址：" . $ethAddress . '  余额：' . $balance);
            //查询是否有正在转出操作 等待中的
            switch (true) {
                case $balance > 0:
                        $FastGasPrice =   gastracker();
                        $x_gasPrice  = bcmul($FastGasPrice, 1000000000);
                        $amountFee =  $eth->wei2eth(60000 * $x_gasPrice);

                        #解锁账户
                        $eth->unlockAccount($this->transferAccountsEthAddress);
                        $resposne = $eth->sendMinerETH($this->transferAccountsEthAddress, $ethAddress, $amountFee, $x_gasPrice);
                        $transactionHash =  $resposne['result'];
                    if (!$resposne || !$transactionHash) {
                        $output->writeln('转入旷工费交易发起失败[ form：' . $this->transferAccountsEthAddress . ' ]');
                        continue;
                    }

                        $output->writeln('转入旷工费交易发起成功[ form：' . $this->transferAccountsEthAddress . ' ]' . '  amount:' . $amountFee . '  gasPrice:' . $x_gasPrice);
        
                        #转账旷工费结果存入数据库
                        EthFee::insertData($resposne['result'], $this->transferAccountsEthAddress, $ethAddress, $amountFee, $x_gasPrice, $userId, 3, $balance);
           
                        $data = [
                            'transactionHash' => $transactionHash,
                            'from' => $ethAddress,
                            'to' => $this->baseEthAddress,
                            'balance' => $balance,
                            'x_gasPrice' => $x_gasPrice,
                        ];
                        $processData[$userId] = $data;

                    break;

                default:
                    continue;
                    break;
            }
        }

        #多进程处理充值
        foreach ($processData as $userId => $value) {
            foreach ($value as $key => $field) {
                $$key = $field;
            }



            $process = new \Swoole\Process(function ($worker) use ($transactionHash, $from, $to, $balance, $x_gasPrice, $userId, $output) {
                vendor("Eth");
                $eth = new \Eth();
                vendor("ERC");
                $erc = new \ERC();
                $output->writeln('[ transactionHash：' . $transactionHash . ' ]');
                $output->writeln('[ from：' . $from . ' ]');
                $output->writeln('[ to：' . $to . ' ]');
                $output->writeln('[ balance：' . $balance . ' ]');
                $output->writeln('[ gasPrice：' . $x_gasPrice . ' ]');
   
               

                    #确认矿工费到账
                    $resultReceipt = $eth->eth_getTransactionReceipt($transactionHash);
                    ######################################
                    #$resultReceipt = 0;
                    ######################################
                    $output->writeln('[ 矿工费交易结果：' . $resultReceipt . ' ]');

                if ($resultReceipt) {
                    #查询数据
                    $dataTran = \app\common\model\EthFee::get(['transactionHash' => $transactionHash]);
                        
                    try {
                        if (!$dataTran['erTransactionHash']) {
                            #解锁账户
                            $eth->unlockAccount($from);
                            #向总账户转账
                            //var_export(bcdiv($x_gasPrice,pow(10,18)));die;
                            $price =  bcdiv($x_gasPrice, bcpow("10", strval(18), 0), 18);
                            $erTransactionHash = $erc->sendUSDT($from, $to, $balance, $price);
                            if ($erTransactionHash) {
                                \app\common\model\EthFee::update(['erTransactionHash' => $erTransactionHash], ['transactionHash' => $transactionHash]);
                            }
                        } else {
                            $erTransactionHash = $dataTran['erTransactionHash'];
                        }


                        $resultErReceipt = $eth->eth_getTransactionReceipt($dataTran['erTransactionHash']);
                        if ($resultErReceipt) {
                        }
                            
                        if ($dataTran['erTransactionHash'] && !$dataTran['erstatus']) {
                            #确认到总账户
                                
                            if ($resultErReceipt) {
                                #充值
                                $AssetsMpdel = new \app\common\model\Assets();
                                $AssetsMpdel->addUSDT($userId, $balance, "USDT充值");
                                \app\common\model\EthFee::update(['erstatus' => 1], ['transactionHash' => $transactionHash]);
                                #完成充值
                                \app\common\model\EthFee::closeTransactionsByHx($transactionHash);
                                $output->writeln('[ USDT充值成功：[ userId：' . $userId . ' amount：' . $balance . ' ]');
                            }
                        }
                    } catch (\Exception $th) {
                    }
                };

                    sleep(1);
            });
            $pid = $process->start();
            $output->writeln("pid ：" . $pid);
            $this->workers[$pid] = $process;
        }

        $this->output();
    }

    public function output()
    {
        // 回收子进程
        while ($res = \Swoole\Process::wait()) {
            echo PHP_EOL, 'Worker Exit, PID: ' . $res['pid'] . PHP_EOL;
        }
    }
}
