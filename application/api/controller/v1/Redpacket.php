<?php

namespace app\api\controller\v1;

use app\admin\model\User;
use app\api\controller\Base;
use app\common\model\Assets;
use app\common\model\Redpacket as RedpacketModel;
use app\common\model\RedpacketHelp;
use app\common\model\RedpacketCash;
use think\Cache;

class Redpacket extends Base
{
    protected $noAuthArr = ['index'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

   
    public function index(){
       
        // $helpVerifyData = Cache::get('helpVerify_'.'18378');
        // var_export($helpVerifyData);die;
    }
    /**
     * @name: 红包信息
     * @author: gz
     * @description: 
     * @param {*}
     * @return {*}
     */
    public function info(){
        $helpVerifyData = Cache::get('helpVerify_'.$this->userId);
        if(!$helpVerifyData){
            $isHelp = 0;
            $userName = '';
        }else{
            $isHelp = 1;
            $toHelpUserId = $helpVerifyData['tohelp'];
            $userName = User::where(['userId'=>$toHelpUserId])->value('userName');
        }
        return show(1,[
            'isHelp'=>$isHelp,
            'userName'=>$userName,
        ]);
    }     

    /**
     * @name: 开红包
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function open()
    {
        
       
        //助力是否完成
        $resultOpenAuth = RedpacketModel::openAuth($this->userId);
        if (is_bool($resultOpenAuth)) {
            return show(1038);
        }
        
        //已开次数
        $alreadytimes = $resultOpenAuth['times'];
        if ($alreadytimes) {
            //是否已提现
            $cashData = RedpacketCash::get([
                'userId' => $this->userId,
                'times' => $alreadytimes,
            ]);
           
            if (!$cashData) {
                return show(1039);
            }
        } else {
            $alreadytimes = 0;
        }
       
       
        
        switch ($alreadytimes) {
            case 0:
                    //币类型
                    $assetsType = 3;
                    //有效期
                    $expireTime = 86400;
                    //红包数组放大倍数 小数2位 乘100
                    $boost = 100;
                    //初始红包数值区间
                    $start = 7 * $boost;
                    $end = 8.1 * $boost;
                    //红包总数值
                    $totalNum = 10;
                    $startBoost = $totalNum * $boost;
                    //红包数量
                    $randomDiv = random_int(20, 30);
                    //初始红包数
                    $firstBoostNum = random_int($start, $end);
                    //初始红包数去倍数
                    $firstMoney  = $firstBoostNum / $boost;
                    //红包数组和
                    $endNum =  bcsub($startBoost, $firstBoostNum);
                     
                break;
            case 1:
                    //币类型
                    $assetsType = 2;
                    //有效期
                    $expireTime = 86400;
                    //红包数组放大倍数 小数4位 乘10000
                    $boost = 10000;
                    //初始红包数值区间
                    $start = 0.068 * $boost;
                    $end = 0.076 * $boost;
                    //红包总数值
                    $totalNum = 0.1;
                    $startBoost = $totalNum * $boost;
                    //红包数量
                    $randomDiv = random_int(80, 120);
                    //初始红包数
                    $firstBoostNum = random_int($start, $end);
                    //初始红包数去倍数
                    $firstMoney  = $firstBoostNum / $boost;
                    //红包数组和
                    $endNum =  bcsub($startBoost, $firstBoostNum);
                break;
            case 2:
                    //币类型
                    $assetsType = 1;
                    //有效期
                    $expireTime = 86400;
                    //红包数组放大倍数 小数6位 乘10000000
                    $boost = 10000000;
                    //初始红包数值区间
                    $start = 0.051 * $boost;
                    $end = 0.063 * $boost;
                    //红包总数值
                    $totalNum = 0.1;
                    $startBoost = $totalNum * $boost;
                    //红包数量
                    $randomDiv = random_int(900, 1500);
                    //初始红包数
                    $firstBoostNum = random_int($start, $end);
                    //初始红包数去倍数
                    $firstMoney  = $firstBoostNum / $boost;
                    //红包数组和
                    $endNum =  bcsub($startBoost, $firstBoostNum);
                    
                    
                break;
            
            default:
                return show(1041);
                break;
        }

        do {
            $collectionArr = $this->randomDivInt($endNum, $randomDiv);
            $collectionSum  = array_sum($collectionArr);
        } while ($collectionSum !==  (int)$endNum);
        
        //红包入库
        $resultAdd  = RedpacketModel::addData($this->userId, $assetsType, $totalNum, $firstMoney, $randomDiv, $collectionArr, $boost, $alreadytimes + 1, $expireTime);
        if (!$resultAdd) {
            return show(0);
        }
        

        #助力验证
        $helpVerifyData = Cache::get('helpVerify_'.$this->userId);
        return show(1, [
            'assetsType' => $assetsType,
            'amount' => $firstMoney,
            'speed' => $firstBoostNum / $startBoost,
            'helpVerifyData' => $helpVerifyData? $helpVerifyData : [],
        ]);
    }

    /**
     * @name: 验证助记词
     * @author: gz
     * @description: 
     * @param {*}
     * @return {*}
     */
    public function verifyWaWord(){
        
       $waWord = $this->request->post('waWord');
      
        try {
           $waWord = json_decode($waWord,true);
           //$waWord =  ['oyster','exile'] ;
            if(!$waWord || !is_array($waWord) || count($waWord) !== 2){
                throw new \Exception("Error");
                
            }
        } catch (\Exception $th) {
            return show(0000);
        }
 
        #验证
        $helpVerifyData = Cache::get('helpVerify_'.$this->userId);
 
        if(!$helpVerifyData){
            return show(1070);
        }
        if($helpVerifyData['verifyWordFreq'] == 0){
            return show(1071);
        }
        
        #判断被助力人红包是否有效
        $dataRedpacket = RedpacketModel::where(['userId'=>$helpVerifyData['tohelp']])->order('createTime desc')->find();
        
        if (!$dataRedpacket) {
            return show(1072);
        }
        
       
     
        list($first,$secend) = $helpVerifyData['verifyWord'];
       
        $walletWordsArr = json_encode([$first=>$waWord[0],$secend=>$waWord[1]]);
        $userObj = $this->clientInfo;
        $walletWordsStr = $userObj->walletWords; 
       
        $resultCheck = $this->checkWalletArr($walletWordsStr,$walletWordsArr);
      
        
        if($resultCheck){

            #验证结果保存
            $helpVerifyData['verifyWordResult'] = 1;
         
            #通过被助力userId 查找其是否含有助力验证 
            $topHelpVerifyData = Cache::get('helpVerify_'.$helpVerifyData['tohelp']);
            
            if(!$topHelpVerifyData || $topHelpVerifyData['verifyType'] == 1){
                
                if($helpVerifyData['verifyType'] == 1){
                    RedpacketModel::addHelp($this->userId, $helpVerifyData['tohelp']);
                }
                
            }
            
            #助力
            RedpacketHelp::isAgainHelp($topHelpVerifyData,$helpVerifyData);
            
           
        }else{
            $helpVerifyData['verifyWordFreq'] -= 1;
        }
        $expire = $dataRedpacket['expireTime'] - time();
        if($expire > 0){
            Cache::set('helpVerify_'.$this->userId,$helpVerifyData,$expire);
        }

        return show(1,[
            'resultCheck'=>$resultCheck,
            'verifyWordFreq'=> $helpVerifyData['verifyWordFreq'],
        ]);
        
    }

    /**
     * @name: 红包页面
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function home()
    {

        $returnData = [
            'times' => 0,
            'assetsType' => 0,
            'amount' => 0,
            'incode' => $this->clientInfo['incode'],
            'expireTime' => 0,
            'expireStatus' => 1,
            'cashBtnState' => 0,
            'moreBtnState' => 0,
            'totalNum' => 0,
            'helpList' => [],
        ];
        
        //红包数据
        $data = RedpacketModel::where(['userId' => $this->userId])->order('createTime desc')->find();
       
        if ($data) {
            $RedpacketHelp = new RedpacketHelp();
            //已助力数量
            $alreadyHelpAmount = $RedpacketHelp->alreadyHelpAmount($this->userId, $data['times']);
            
            //已助力人数
            $countHelpNum  = $RedpacketHelp->alreadyHelpNum($this->userId, $data['times']);
      
            if ($data['num'] == $countHelpNum) {
                //此次红包提现数据
                $cashData = RedpacketCash::get(['userId' => $this->userId,'times' => $data['times']]);
               
                if ($cashData) {
                    $cashBtnState = 0;
                    if ($data['expireTime'] > time()) {
                        //红包有效期内
                        if ($data['times'] == 3) {
                            $moreBtnState = 0;
                        } else {
                            $moreBtnState = 1;
                        }
                    } else {
                        $moreBtnState = 0;
                    }
                } else {
                    $moreBtnState = 0;
                    if ($data['expireTime'] > time()) {
                        //红包有效期内
                        $cashBtnState = 1;
                    } else {
                        $cashBtnState = 0;
                    }
                }
            } else {
                $cashBtnState = 0;
                $moreBtnState = 0;
            }
            
            if ($data['expireTime'] > time()) {
                $expireStatus = 0;
            } else {
                $expireStatus = 1;
            }

            $returnData = [
                'times' => $data['times'],
                'assetsType' => $data['assetsType'],
                'amount' => ($data['amount'] * 10000000 + $alreadyHelpAmount * 10000000) / 10000000,
                'incode' => $this->clientInfo['incode'],
                'expireTime' => $data['expireTime'],
                'expireStatus' => $expireStatus,
                'cashBtnState' => $cashBtnState,
                'moreBtnState' => $moreBtnState,
                'totalNum' => $data['totalNum'],
            ];
        }
        return show(1, $returnData);
    }

    /**
     * @name: 提现
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function cash()
    {
        #判断钱包是否有充值
        $assetsDatas = Assets::where(['amount'=>['gt',0]])->find();
        $assetsDatas = 1;   //暂时修改 没有激活直接可以提现
        if($assetsDatas){
            RedpacketModel::cashAmount($this->userId);
            return show(1);
        }
        return show(1073);
    }

    /**
     * @name: 提现记录
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function cashList()
    {
        
        $getArr  = $this->checkdate('Collection', 'get', 'list');
        $data = RedpacketModel::where(['userId' => $this->userId])->order('createTime desc')->find();
        $RedpacketCash = new RedpacketCash();
        $this->getPageAndSize($getArr);
        $condition = [
            'userId' => $this->userId
        ];
        $total = $RedpacketCash->getCount($condition);
        $list = $RedpacketCash->getList($condition, $this->from, $this->size, true, $this->sort, ['timeStr','amountFloatval']);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
    /**
     * @name: 助力记录
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function helpList()
    {
        
        $getArr  = $this->checkdate('Collection', 'get', 'list');
        $data = RedpacketModel::where(['userId' => $this->userId])->order('createTime desc')->find();
        $returnResult = [
            'total' => 0,
            'page_num' => 0,
            'list' => [],
        ];
        
        if ($data) {
            $RedpacketHelp = new RedpacketHelp();
            $this->getPageAndSize($getArr);
            $condition = [
                'userId' => $this->userId,
                'times' => $data['times'],
            ];
            $total = $RedpacketHelp->getCount($condition);
            $list = $RedpacketHelp->getList($condition, $this->from, $this->size, true, $this->sort, ['userName','timeStr','amountFloatval']);
            $returnResult = [
                'total' => $total,
                'page_num' => ceil($total / $this->size),
                'list' => $list,
            ];
        }
        return show(1, $returnResult);
    }
    /**
     * @name: 随机红包
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    private function randomDivInt($total = 0, $num = 0)
    {
        $bag = [];
        $max = 0;
        for ($i = 0; $i < $num - 1; $i++) {
            $_bag = $this->is_repeat($bag, $total);
            if ($_bag > $max) {
                $max = $_bag;
            }
            array_push($bag, $_bag);
        }
        sort($bag);
        $money = [];
        for ($i = 0; $i < count($bag); $i++) {
            if ($i == 0) {
                $_money = $bag[$i];
            } else {
                $_money = $bag[$i] - $bag[$i - 1];
            }
            array_push($money, $_money);
        }
       # 最后一个值(max可以不进行比较，在数组排序后 选择$bag[$num-2])
        $_quantity = (int)($total - $max);
        array_push($money, $_quantity);
        rsort($money);
        return array_values($money);
    }

    private function is_repeat($array = [], $max = 0)
    {
        $_bag = rand(1, $max - 1);
        if (in_array($_bag, $array)) {
            $_bag = $this->is_repeat($array, $max);
        }
        return $_bag;
    }
}
