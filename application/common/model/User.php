<?php

namespace app\common\model;

use app\api\common\library\IAuth;
use think\Request;

class User extends BaseModel
{

    /**
     * @name:        新增用户
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public function addUser($userArr)
    {
        $this->startTrans();
        try {
            $resultUser = $this::get(['walletWords' => $userArr['walletWords']]);
            if ($resultUser) {
                throw new \Exception("Error");
            }

            do {
                $userName = $this->genUserName();
            } while ($this::get(['userName' => $userName]));

            $btcAddress = '';
            $ethAddress = '';
            //生成钱包收款地址
            // vendor("BitcoinLib");
            // $bitcoin = new \BitcoinLib();
            // $response = $bitcoin->makeAddress();
         
            // if ($response === false) {
            //     throw new \Exception("Error");
            // }
            // if (!$response["error"]) {
            //     $btcAddress = $response["result"];
            // } else {
            //     throw new \Exception("Error");
            // }
            // if (!$btcAddress) {
            //     throw new \Exception("Error");
            // }
            // vendor("Eth");
            // $eth = new \Eth();
            // $ethAddress = $eth->genPair();
           
            // if (!$ethAddress) {
            //     throw new \Exception("Error");
            // }
            
            //user表数据
            $ip = Request::instance()->ip();
            $walletWordsCreateTime =  WalletWords::where(['walletWords' => $userArr['walletWords']])->value('createTime');
            $saveArr = [
                'userName' => $userName,
                'walletWords' => $userArr['walletWords'],
                'gesturePassword' => setPassword($userArr['gesturePassword']),
                'payPassWord' => setPassword($userArr['payPassWord']),
                'incode' => $this->makeIncode(6),
                'address' => $btcAddress ,
                'ethAddress' => $ethAddress,
                'createTime' => time(),
                'updateTime' => time(),
                'registerIp' => $ip,
                'duration' => time() - $walletWordsCreateTime,
            ];
            $result = $this::create($saveArr);

            //加入token
            $token =  Iauth::setToken($userName);
            $expireTimeStamp = time() + config('app.app_token_time_out');
            UserToken::addToken(
                [
                'token' => $token,
                'expireTimeStamp' => $expireTimeStamp,
                'userId' => $result->userId,
                'lastLoginIp' => $ip,
                'createTime' => time(),
                'updateTime' => time(),
                ]
            );
             //用户统计信息
             UserCumulative::create(['userId' => $result->userId]);
             //邀请信息
            if (isset($userArr['superiorId'])) {
                //邀请
                ExtensionInvitation::addInvitation($result->userId, $userArr['superiorId']);

                //添加红包助力验证
                
                RedpacketHelp::addHelpVerify($result->userId,$userArr['superiorId']);
               
            }
            (new Assets)->createWallet($result->userId);
             //注册消息
             Message::add($result->userId, '系统通知', '欢迎加入CoinShop', '欢迎加入CoinShop！我们秉承着为您提供最好的购物体验!');
             $this->commit();
             return ['userId' => $result->userId ,'token' => $token , 'address' => $result->address,'ethAddress' => $result->ethAddress,'walletWords' => $userArr['walletWords'],'incode' => $saveArr['incode'],'gesturePassword' => $userArr['gesturePassword']];
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * @name: 随机用户名
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function genUserName($len = 8)
    {
        
            $user = '';
            $lchar = 0;
            $char = 0;
        for ($i = 0; $i < $len; $i++) {
            while ($char == $lchar) {
                $char = rand(48, 109);
                if ($char > 57) {
                    $char += 7;
                }
                if ($char > 90) {
                    $char += 6;
                }
            }
            $user .= chr($char);
            $lchar = $char;
        }
        return $user;
    }
    /**
     * @name:        检查用户名
     * @author:      gz
     * @description:
     * @param        {type}
     * @return:
     */
    public static function checkUserName($userName)
    {
        $reUser  = self::get(['userName' => $userName]);
        if (!$reUser) {
            return false;
        }
        return true;
    }
    /**
     * 生成邀请码
     *
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function makeIncode($num = 10)
    {
        do {
            $incode = "";
            $strList = array("0","1","2","3","4","5","6","7","8","9");
            for ($i = 0; $i < $num; $i++) {
                $incode .= $strList[rand(0, 9)];
            }
        } while ($this->where('incode', $incode)->count() == 1);
        return $incode ;
    }

    public static function getUserForWallet($wallet)
    {
        $data =  self::get(['walletWords' => $wallet]);
        if ($data) {
            return true;
        }
        return false;
    }


    /**
     * @name: 获取全部用户列表
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function userList()
    {
        $list = $this->select();
        return $list;
    }

    /**
     * 通过btc钱包地址获取用户ID
     * @param $address
     * @return bool|mixed
     */
    public function getUserIdByAddress($address)
    {
        $userId = $this->where("address", "=", $address)->value("userId");
        if ($userId) {
            return $userId;
        } else {
            return false;
        }
    }
    /**
     * @name: 通过ETH钱包地址获取用户ID
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function getUserIdByEthAddress($address)
    {
        $userId = $this->where("ethAddress", "=", $address)->value("userId");
        if ($userId) {
            return $userId;
        } else {
            return false;
        }
    }
    

    /**
     * 添加ETH地址
     *
     * @param [type] $userId
     * @param [type] $ethAddress
     * @return void
     */
    public function addEthAddress($userId, $ethAddress)
    {
        $status = $this->where("userId", "=", $userId)->setField("ethAddress", $ethAddress);
        return $status;
    }
}
