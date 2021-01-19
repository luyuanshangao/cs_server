<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\ExtensionAssets;
use app\common\model\ExtensionUser;
use app\common\model\ExtensionUserLog;
use app\common\model\ExtensionInvitation;
use app\common\model\ExtensionDeal;
use app\common\model\ExtensionDealDetail;
use app\common\model\ExtensionAssetsDetails;
use app\common\model\ExtensionGlory;
use app\common\model\Assets;
use app\common\model\AssetsDetails;

class Extension extends Base
{
    protected $noAuthArr = [];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name: 挖矿
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function mining()
    {


        #1 判断是否开通推广
        $isExtension = ExtensionUser::hasExtensionAuth($this->userId);
        $allData = $this->myFansCompute();
        $returnData = [
            'isExtension' => $isExtension,
            'extensionName' => ExtensionUser::extensionName($this->userId),
            'speed' => 1,
            'extensionId' => 1,
            'amount' => 0,
            'gloryAmount' => 0,
            'allInvNum' => $allData['directNum'] + $allData['indirectNum'],

            'challenge' => [
                'senior' => [
                    'speed' => 1,
                    'btnStatus' => 0,
                ],
                'top' => [
                    'speed' => 1,
                    'btnStatus' => 0,
                ],
            ]

        ];



        #2 已开通推广
        if ($isExtension) {
            $gradeAndAuthData = $this->gradeAndAuth()->getdata();
            $speed = $gradeAndAuthData['data']['upgrade'];
            $returnData['speed'] = $speed;
            $amountInfo = ExtensionAssets::getAmountInfo($this->userId);
            $extensionId = ExtensionUserLog::getNowGrade($this->userId);
            $returnData['extensionId'] = $extensionId;

            //显示区分
            switch ($extensionId) {
                case 0:
                case 1:
                case 2:
                case 3:
                case 4:
                    $returnData['amount'] = $amountInfo['amount'];
                    $returnData['gloryAmount'] = 0;
                    break;
                case 5: //荣耀值
                    //原usdt不变 去查询荣耀值
                    $returnData['amount'] = $amountInfo['amount']; //bcmul($amountInfo['amount'], '100', config('app.usdt_float_num'));
                    $returnData['gloryAmount'] = ExtensionGlory::where(['userId' => $this->userId])->value('gloryAmount');
                    break;

                default:
                    break;
            }

            //矿工挑战          //btnStatus 0去完成 1领取奖励 2已完成
            switch ($extensionId) {
                case 3:
                    $returnData['challenge'] = [
                        'senior' => [
                            'speed' => $returnData['speed'],
                            'btnStatus' => 0,
                        ],
                        'top' => [
                            'speed' => 1,
                            'btnStatus' => 0,
                        ],
                    ];

                    break;
                case 4:
                    $rewardAmount = 1; //奖励金额
                    $assetsType = 2;   //奖励金额类型

                    $dataExt = AssetsDetails::get([
                        'userId' => $this->userId,
                        'assetsType' => $assetsType, //eth
                        'detailType' => 1,
                        'amount' => $rewardAmount,
                        'description' => '等级奖励',
                    ]);
                    if (!$dataExt) {
                        $btnStatus = 1;
                    } else {
                        $btnStatus = 0;
                    }
                    $returnData['challenge'] = [
                        'senior' => [
                            'speed' => 0,
                            'btnStatus' => $btnStatus,
                        ],
                        'top' => [
                            'speed' => $returnData['speed'],
                            'btnStatus' => 0,
                        ],
                    ];
                    break;
                case 5:
                    $rewardAmount = 1; //奖励金额
                    $assetsTypeTop = 1;   //奖励金额类型
                    $dataExtTop = AssetsDetails::get([
                        'userId' => $this->userId,
                        'assetsType' => $assetsTypeTop, //btc
                        'detailType' => 1,
                        'amount' => $rewardAmount,
                        'description' => '等级奖励',
                    ]);
                    if (!$dataExtTop) {
                        $btnStatusTop = 1;
                    } else {
                        $btnStatusTop = 2;
                    }
                    

                    $assetsTypeSenior = 2;   //奖励金额类型
                    $dataExtSenior = AssetsDetails::get([
                        'userId' => $this->userId,
                        'assetsType' => $assetsTypeSenior, //eth
                        'detailType' => 1,
                        'amount' => $rewardAmount,
                        'description' => '等级奖励',
                    ]);
                    if (!$dataExtSenior) {
                        $btnStatusSenior = 1;
                    } else {
                        $btnStatusSenior = 0;
                    }
                    $returnData['challenge'] = [
                        'senior' => [
                            'speed' => 0,
                            'btnStatus' => $btnStatusSenior,
                        ],
                        'top' => [
                            'speed' => 0,
                            'btnStatus' => $btnStatusTop,
                        ],
                    ];
                    break;

                default:
                    # code...
                    break;
            }
        }

        return show(1, $returnData);
    }

    /**
     * @name: 统计
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function miningCens()
    {
        $dataArr  = $this->checkdate('Extension', 'get', 'miningCens');
        $returnData = [
            'invitationNum' => 0,
            'payOrderNum' => 0,
            'lockAmount' => 0,
            'alowAmount' => 0,
        ];
        switch ($dataArr['type']) {
            case 1://今日
                    //邀请人数
                    $beginTimeStr = strtotime(date("Y-m-d"), time());
                    $endTimeStr = time();
                break;
            case 2://本周
                    $beginTimeStr = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1, date('y'));
                    $endTimeStr = time();
                break;
            case 3://本月
                    $beginTimeStr = mktime(0, 0, 0, date("m"), 1, date("Y"));
                    $endTimeStr = mktime(23, 59, 59, date("m"), date('t'), date("Y"));
                break;

            
            default:
                return  show(1, $returnData);
                break;
        }
        
        $timeData = $this->myFansCompute(['beginTime' => $beginTimeStr, 'endTime' => $endTimeStr]);
        $invitationNum = $timeData['directNum'] + $timeData['indirectNum'];

        //付款订单
        $extensionId = ExtensionUserLog::getNowGrade($this->userId);
        $extensionId = 4;
        switch ($extensionId) {
            case 1:
                    $payOrderNum = 0;
                break;
            case 2:
            case 3:
                //只算直接的
                    $payOrderNum = ExtensionDeal::getDirectDealNum($this->userId, $beginTimeStr, $endTimeStr);
                    
                break;
            case 4:
                //直接的 + 间接的
                    $directPayOrderNum = ExtensionDeal::getDirectDealNum($this->userId, $beginTimeStr, $endTimeStr);
                    $indirectPayOrderNum = ExtensionDeal::getIndirectDealNum($this->userId, $beginTimeStr, $endTimeStr);
                    $payOrderNum = $directPayOrderNum + $indirectPayOrderNum;
                    
                break;
            case 5:
                //所有关联
                    $payOrderNum =  ExtensionDeal::getAllInvDealNum($this->userId, $beginTimeStr, $endTimeStr);

                break;
            
            default:
                # code...
                break;
        }

        //收益
        $amountInfo = ExtensionAssets::getAmountInfoWithTime($this->userId, $beginTimeStr, $endTimeStr);

        $lockAmount =  $amountInfo['lockAmount'];
        $alowAmount =  $amountInfo['alowAmount'];

        $returnData = [
            'invitationNum' => $invitationNum,
            'payOrderNum' => $payOrderNum,
            'lockAmount' => $lockAmount,
            'alowAmount' => $alowAmount,
        ];
        return show(1, $returnData);
    }
    
    /**
     * @name: 完成奖励
     * @author: gz
     * @description:
     * @param {*}
     * @return {*}
     */
    public function getChallengeReward()
    {
        $dataArr  = $this->checkdate('Extension', 'get', 'getChallengeReward');
        $extensionId = ExtensionUserLog::getNowGrade($this->userId);
        //等级奖励
        if ($dataArr['type'] == 4 && $extensionId == 4) {
            $rewardAmount = 1; //奖励金额
            $assetsType = 2;   //奖励金额类型

            $dataExt = AssetsDetails::get([
                'userId' => $this->userId,
                'assetsType' => $assetsType, //eth
                'detailType' => 1,
                'amount' => $rewardAmount,
                'description' => '等级奖励',
            ]);
            if (!$dataExt) {
                //第一次 给予奖励 1ETH
                $AssetsModel = new  Assets();
                $AssetsModel->addETH($this->userId, $rewardAmount, '等级奖励');
                $returnData['reward']['amount'] = $rewardAmount;
                $returnData['reward']['assetsType'] = $assetsType;
                return show(1);
            }
        }

        if ($dataArr['type'] == 5 && $extensionId == 5) {
            $rewardAmount = 1; //奖励金额
            $assetsType = 1;   //奖励金额类型

            $dataExt = AssetsDetails::get([
                'userId' => $this->userId,
                'assetsType' => $assetsType, //btc
                'detailType' => 1,
                'amount' => $rewardAmount,
                'description' => '等级奖励',
            ]);
            if (!$dataExt) {
                //第一次 给予奖励 1BTC
                $AssetsModel = new  Assets();
                $AssetsModel->addBTC($this->userId, $rewardAmount, '等级奖励');
                $returnData['reward']['amount'] = $rewardAmount;
                $returnData['reward']['assetsType'] = $assetsType;
                return show(1);
            }
        }
        return show(1042);
    }



    public function home()
    {

        #1 判断是否开通推广
        $isExtension = ExtensionUser::hasExtensionAuth($this->userId);

        $returnData = [
            'userName' => $this->clientInfo->userName,
            'isExtension' => $isExtension,
            'extensionName' => ExtensionUser::extensionName($this->userId),
            'speed' => 1,
            'diff'=>'',
            'extensionId' => 0,
            'amount' => 0,
            'alowAmount' => 0,
            'lockAmount' => 0,
            'reward' => [
                'amount' => '',
                'assetsType' => '',
            ],

        ];



        #2 已开通推广
        if ($isExtension) {
            $gradeAndAuthData = $this->gradeAndAuth()->getdata();
            $speed = $gradeAndAuthData['data']['upgrade'];
            $returnData['speed'] = $speed;
            $amountInfo = ExtensionAssets::getAmountInfo($this->userId);
            $extensionId = ExtensionUserLog::getNowGrade($this->userId);
            $returnData['extensionId'] = $extensionId;

            //显示区分
            switch ($extensionId) {
                case 0:
                        $returnData['diff'] = '';
                case 1:
                        //查看已邀请人数
                        $conditionDire = [
                            'superiorId' => $this->userId,
                        ];
                        $directCount = ExtensionInvitation::where($conditionDire)->count();
                        $returnData['diff'] = 10 - $directCount;
                    break;
                case 2:
                        //成交人数
                        $conditionDire = [
                            'superiorId' => $this->userId,
                        ];
                        
                        $dealCount = ExtensionDeal::where($conditionDire)->count();
                        $returnData['diff'] = 10 - $dealCount;
                case 3:
                        //10 个初级
                        $conditionDire = [
                            'superiorId' => $this->userId,
                        ];
                        $directUserIds = ExtensionInvitation::where($conditionDire)->column('userId');
                        $conditionPrimary = [
                            'extensionId' => 3,
                            'userId' => ['in', $directUserIds],
                        ];
                        $primaryNum = ExtensionUser::where($conditionPrimary)->count();
                        $returnData['diff'] = 10 - $primaryNum;

                case 4:
                    $returnData['amount'] = $amountInfo['amount'];
                    $returnData['alowAmount'] = $amountInfo['alowAmount'];
                    $returnData['lockAmount'] = $amountInfo['lockAmount'];
                        //10 个高级
                        $conditionDire = [
                            'superiorId' => $this->userId,
                        ];
                        $directUserIds = ExtensionInvitation::where($conditionDire)->column('userId');
                    $conditionSenior = [
                        'extensionId' => 4,
                        'userId' => ['in', $directUserIds],
                    ];
                    $seniorNum = ExtensionUser::where($conditionSenior)->count();
                    $returnData['diff'] = 10 - $seniorNum;
                    break;
                case 5: //荣耀值
                    $returnData['amount'] = $amountInfo['amount']; //bcmul($amountInfo['amount'], '100', config('app.usdt_float_num'));
                    $returnData['alowAmount'] = bcmul($amountInfo['alowAmount'], '100'); //荣耀值
                    $returnData['lockAmount'] = $amountInfo['lockAmount']; // bcmul($amountInfo['lockAmount'], '100', config('app.usdt_float_num'));
                    $returnData['diff'] = '';
                    break;

                default:
                    break;
            }

            $returnData['reward'] = [
                'amount'=>0,
                'assetsType'=>'',
                'alert'=>0
            ];
            //等级奖励
            if ($extensionId == 4) {
                $rewardAmount = 1; //奖励金额
                $assetsType = 2;   //奖励金额类型

                $dataExt = AssetsDetails::get([
                    'userId' => $this->userId,
                    'assetsType' => $assetsType, //eth
                    'detailType' => 1,
                    'amount' => $rewardAmount,
                    'description' => '等级奖励',
                ]);
                if (!$dataExt) {
                    //第一次 给予奖励 1ETH
                    $AssetsModel = new  Assets();
                    $AssetsModel->addETH($this->userId, $rewardAmount, '等级奖励');
                    $returnData['reward']['amount'] = $rewardAmount;
                    $returnData['reward']['assetsType'] = $assetsType;
                    $returnData['reward']['alert'] = 1;
                }
            }elseif ($extensionId == 5) {
                $rewardAmount = 1; //奖励金额
                $assetsType = 1;   //奖励金额类型

                $dataExt = AssetsDetails::get([
                    'userId' => $this->userId,
                    'assetsType' => $assetsType, //btc
                    'detailType' => 1,
                    'amount' => $rewardAmount,
                    'description' => '等级奖励',
                ]);
                $returnData['reward']['amount'] = $rewardAmount;
                $returnData['reward']['assetsType'] = $assetsType;
                if (!$dataExt) {
                    //第一次 给予奖励 1BTC
                    $AssetsModel = new  Assets();
                    $AssetsModel->addBTC($this->userId, $rewardAmount, '等级奖励');
                    $returnData['reward']['amount'] = $rewardAmount;
                    $returnData['reward']['assetsType'] = $assetsType;
                    $returnData['reward']['alert'] = 1;
                }
            }
        }

        return show(1, $returnData);
    }

    /**
     * @name: 等级与权限
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function gradeAndAuth()
    {

        #1 判断用户级别
        $extensionId = ExtensionUser::where([
            'userId' => $this->userId,
        ])->value('extensionId');
        $upTimeStr = ExtensionUserLog::getUpGradeTime($this->userId);
        #2 直接邀请的数量
        $conditionDire = [
            'superiorId' => $this->userId,
        ];
        $directUserIds = ExtensionInvitation::where($conditionDire)->column('userId');
        $directNum = count($directUserIds);

        #3 成交人数
        $dealNum = ExtensionDeal::where($conditionDire)->count();

        #4 初级人数
        $conditionPrimary = [
            'extensionId' => 3,
            'userId' => ['in', $directUserIds],
        ];
        $primaryNum = ExtensionUser::where($conditionPrimary)->count();

        #5 高级人数
        $conditionSenior = [
            'extensionId' => 4,
            'userId' => ['in', $directUserIds],
        ];
        $seniorNum = ExtensionUser::where($conditionSenior)->count();

        #4 计算下一级百分比
        !$extensionId ? $extensionId = 1 : '';

        $upgrade = 0;
        switch ($extensionId) {
            case 1:
                # 普通用户 1    下一级需邀请10人

                //邀请
                $upgrade = $this->cumputeUpgrade($directNum);
                break;
            case 2:
                # 试用初级 2    下一级需邀请10人 成交10人
                
                //成交
                $upgrade = $this->cumputeUpgrade($dealNum);

                break;
            case 3:
                # 初级推广 3   下一级需邀请10初级

                $upgrade = $this->cumputeUpgrade($primaryNum);

                break;
            case 4:
                # 高级推广 4

                $upgrade = $this->cumputeUpgrade($seniorNum);

                break;
            case 5:
                # 平台分红 5

                $upgrade = 0;
                $amountInfo = ExtensionAssets::getAmountInfo($this->userId);
                $gradeNum = bcdiv($amountInfo['alowAmount'] * 100, '100000', 2);
                if ($gradeNum < 1) {
                    $upgrade = $gradeNum;
                } else {
                    $upgrade = 1;
                }
                break;
            default:
                break;
        }

        $returnData = [
            'extensionId' => $extensionId,  //等级
            'upgrade' => bcsub('1', $upgrade, 2),
            'directNum' => $directNum,    //邀请人数
            'dealNum' => $dealNum,        //成交人数
            'primaryNum' => $primaryNum,  //初级人数
            'seniorNum' => $seniorNum,    //高级人数
        ];

        return show(1, $returnData);
    }

    /**
     * @name: 开通推广资格
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function open()
    {
        ExtensionUser::createData($this->userId);

        return show(1);
    }

    /**
     * @name: 我的粉丝
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function myFans()
    {

        $allData = $this->myFansCompute();

        $todayTimeBeginStr = strtotime(date("Y-m-d"), time());
        $todayTimeEndStr = $todayTimeBeginStr + 86400;

        $todayData = $this->myFansCompute(['beginTime' => $todayTimeBeginStr, 'endTime' => $todayTimeEndStr]);
        $num = 6;
        //曲线图 按日算
        $dayCategories = getEveryday($num - 1);

        $thenDayDirectNum = [];
        $thenDayIndirectNum = [];

        foreach ($dayCategories as $key => $date) {
            $dateStr = strtotime(date("Y-", time()) . $date);
            $endStr =  $dateStr + 86400;

            $thenData = $this->myFansCompute(['beginTime' => $dateStr, 'endTime' => $endStr]);
            array_push($thenDayDirectNum, $thenData['directNum']);
            array_push($thenDayIndirectNum, $thenData['indirectNum']);
            if ($key == ($num - 1)) {
                $k = $num - 2;
                $dayThanDirectNum = $thenData['directNum'] - $thenDayDirectNum[$k];
                $dayThanIndirectNum = $thenData['indirectNum'] - $thenDayIndirectNum[$k];
            }
        }

        $dayDataArr['thanDirectNum'] = $dayThanDirectNum;
        $dayDataArr['thanIndirectNum'] = $dayThanIndirectNum;
        $dayDataArr['categories'] = $dayCategories;
        $dayDataArr['series'] = [
            [
                'name' => '直接',
                'data' => $thenDayDirectNum,
            ],
            [
                'name' => '间接',
                'data' => $thenDayIndirectNum,
            ],
        ];

        //曲线图 按周算
        $weekData  = getEveryweek($num);

        $thenWeekDirectNum = [];
        $thenWeekIndirectNum = [];
        $weekCategories = [];
        foreach (array_reverse($weekData) as $key => $date) {
            $key ? array_push($weekCategories, '前' . $key . '周') : array_push($weekCategories, '本周');
            $begStr = strtotime($date[0]);
            $endStr = strtotime($date[1]) + 86400;

            $thenData = $this->myFansCompute(['beginTime' => $begStr, 'endTime' => $endStr]);
            array_push($thenWeekDirectNum, $thenData['directNum']);
            array_push($thenWeekIndirectNum, $thenData['indirectNum']);
            if ($key == ($num - 1)) {
                $k = $num - 2;
                $weekThanDirectNum = $thenData['directNum'] - $thenWeekDirectNum[$k];
                $weekThanIndirectNum = $thenData['indirectNum'] - $thenWeekIndirectNum[$k];
            }
        }
        $weekDataArr['thanDirectNum'] = $weekThanDirectNum;
        $weekDataArr['thanIndirectNum'] = $weekThanIndirectNum;
        $weekDataArr['categories'] = array_reverse($weekCategories);
        $weekDataArr['series'] = [
            [
                'name' => '直接',
                'data' => $thenWeekDirectNum,
            ],
            [
                'name' => '间接',
                'data' => $thenWeekIndirectNum,
            ],
        ];

        //曲线图 按月算
        $monthData  = getEverymonth(6);

        $thenMonthDirectNum = [];
        $thenMonthIndirectNum = [];
        $monthCategories = [];
        foreach (array_reverse($monthData) as $key => $date) {
            $key ? array_push($monthCategories, '前' . $key . '月') : array_push($monthCategories, '本月');
            $begStr = strtotime($date[0]);
            $endStr = strtotime($date[1]) + 86400;

            $thenData = $this->myFansCompute(['beginTime' => $begStr, 'endTime' => $endStr]);
            array_push($thenMonthDirectNum, $thenData['directNum']);
            array_push($thenMonthIndirectNum, $thenData['indirectNum']);
            if ($key == ($num - 1)) {
                $k = $num - 2;
                $monthThanDirectNum = $thenData['directNum'] - $thenMonthDirectNum[$k];
                $monthThanIndirectNum = $thenData['indirectNum'] - $thenMonthIndirectNum[$k];
            }
        }
        $monthDataArr['thanDirectNum'] = $monthThanDirectNum;
        $monthDataArr['thanIndirectNum'] = $monthThanIndirectNum;
        $monthDataArr['categories'] = array_reverse($monthCategories);
        $monthDataArr['series'] = [
            [
                'name' => '直接',
                'data' => $thenMonthDirectNum,
            ],
            [
                'name' => '间接',
                'data' => $thenMonthIndirectNum,
            ],
        ];


        $returnData = [
            'extensionName' => ExtensionUser::extensionName($this->userId),
            'all_directNum' => $allData['directNum'], //直接
            'all_indirectNum' => $allData['indirectNum'], //间接
            'today_directNum' => $todayData['directNum'], //今日直接
            'today_indirectNum' => $todayData['indirectNum'], //今日间接
            'curveView' => [
                'day' => $dayDataArr,
                'week' => $weekDataArr,
                'month' => $monthDataArr,
            ]
        ];
        return show(1, $returnData);
    }
    /**
     * @name: 计算我的粉丝
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function myFansCompute($dataArr = [])
    {

        #判断用户级别
        $extensionId = ExtensionUser::where([
            'userId' => $this->userId,
        ])->value('extensionId');

        //直接邀请的数量
        $conditionDire = [
            'superiorId' => $this->userId,
        ];
        if (isset($dataArr['beginTime']) && isset($dataArr['endTime'])) {
            $beginTimeSt = $dataArr['beginTime'];
            $endTimeSt = $dataArr['endTime'];

            $conditionDire['createTime'] = ['between', [$beginTimeSt, $endTimeSt]];
        }

        $directUserIds = ExtensionInvitation::where($conditionDire)->column('userId');

        $directNum = count($directUserIds);


        switch ($extensionId) {
            case 5:
                # 平台分红 5
                //直接邀请的id
                $invitationIds = ExtensionInvitation::where(['superiorId' => $this->userId])->column('invitationId');
                $invitIdArr = [];
                foreach ($invitationIds as $invitationId) {
                    $getChildIdsStr = ExtensionInvitation::getChildIds($invitationId);
                    $childIdsArr = explode(',', $getChildIdsStr);
                    $keyIn = array_search($invitationId, $childIdsArr);
                    array_splice($childIdsArr, $keyIn, 1);
                    $invitIdArr = array_merge($invitIdArr, $childIdsArr);
                }

                $conditionIndir = [
                    'invitationId' => ['in', $invitIdArr],
                ];
                if (isset($dataArr['beginTime']) && isset($dataArr['endTime'])) {
                    $conditionIndir['createTime'] = ['between', [$beginTimeSt, $endTimeSt]];
                }
                break;

            default:
                # 普通用户 1
                # 试用初级 2
                # 初级推广 3
                # 高级推广 4
                $directNoTimeUserIds = ExtensionInvitation::where(['superiorId' => $this->userId])->column('userId');


                $conditionIndir = [
                    'level' => 1,
                    'superiorId' => ['in', $directNoTimeUserIds],
                ];

                if (isset($dataArr['beginTime']) && isset($dataArr['endTime'])) {
                    $conditionIndir['createTime'] = ['between', [$beginTimeSt, $endTimeSt]];
                }



                break;
        }

        $indirectNum = ExtensionInvitation::where($conditionIndir)->count();


        $returnData = [
            'directNum' => $directNum, //直接
            'indirectNum' => $indirectNum, //间接
        ];
        return $returnData;
    }

    /**
     * @name: 我的粉丝-全部粉丝
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function allFans()
    {

        $dataArr  = $this->checkdate('Extension', 'get', 'allFans');

        $returnData = [
            'total' => 0,
            'page_num' => 0,
            'list' => [],
        ];
        $ExtensionInvitationModel = new ExtensionInvitation();
        switch ($dataArr['type']) {
            case 1:
                //直接

                $condition = [
                    'superiorId' => $this->userId,
                ];

                break;
            case 2:
                //间接
                //判断用户等级
                $extensionId = ExtensionUser::where([
                    'userId' => $this->userId,
                ])->value('extensionId');

                switch ($extensionId) {
                    case 5:
                        # 平台分红
                        //直接邀请的id
                        $invitationIds = ExtensionInvitation::where(['superiorId' => $this->userId])->column('invitationId');
                        $invitIdArr = [];
                        foreach ($invitationIds as $invitationId) {
                            $getChildIdsStr = ExtensionInvitation::getChildIds($invitationId);
                            $childIdsArr = explode(',', $getChildIdsStr);
                            $keyIn = array_search($invitationId, $childIdsArr);
                            array_splice($childIdsArr, $keyIn, 1);

                            $invitIdArr = array_merge($invitIdArr, $childIdsArr);
                        }

                        $condition = [
                            'invitationId' => ['in', $invitIdArr],
                        ];

                        break;

                    default:
                        # 普通用户 1
                        # 试用初级 2
                        # 初级推广 3
                        # 高级推广 4

                        //直接邀请的id
                        $invitationIds = ExtensionInvitation::where(['superiorId' => $this->userId])->column('invitationId');

                        $condition = [
                            'parentId' => ['in', $invitationIds],
                        ];

                        break;
                }



                break;

            default:
                break;
        }

        $this->getPageAndSize($dataArr);
        $returnData['total'] = $ExtensionInvitationModel->getCount($condition);
        $returnData['page_num'] = ceil($returnData['total'] / $this->size);
        $returnData['list'] = $ExtensionInvitationModel->getList($condition, $this->from, $this->size, ['userId', 'createTime'], $this->sort, ['userName', 'extensionName', 'dateStr']);



        return show(1, $returnData);
    }

    /**
     * @name: 邀请好友赚收益
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function profit()
    {

        $myFansData = $this->myFansCompute();

        $returnData = [
            'directNum' => $myFansData['directNum'], //直接
            'indirectNum' => $myFansData['indirectNum'], //间接
            'alreadyCash' => ExtensionAssetsDetails::where(['lock' => 1, 'detailType' => 2, 'userId' => $this->userId])->sum('amount'), //已提现
            'incode' => $this->clientInfo->incode, //邀请码
        ];
        return show(1, $returnData);
    }
    /**
     * @name: 邀请好友赚收益-直接用户列表
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function profitUserList()
    {

        $dataArr  = $this->checkdate('Extension', 'get', 'profitUserList');

        $returnData = [
            'total' => 0,
            'page_num' => 0,
            'list' => [],
        ];
        $ExtensionInvitationModel = new ExtensionInvitation();
        $invitationIds = ExtensionInvitation::where(['superiorId' => $this->userId])->column('invitationId');
        $condition = [
            'invitationId' => ['in', $invitationIds],
        ];
        $this->getPageAndSize($dataArr);
        $returnData['total'] = $ExtensionInvitationModel->getCount($condition);
        $returnData['page_num'] = ceil($returnData['total'] / $this->size);
        $returnData['list'] = $ExtensionInvitationModel->getList($condition, $this->from, $this->size, ['userId,createTime'], $this->sort, ['userName', 'dateStr']);
        return show(1, $returnData);
    }

    /**
     * @name: 邀请好友保存
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function inviView()
    {
        $returnData = [
            'userName' => $this->clientInfo->userName,
            'incode' => $this->clientInfo->incode, //邀请码
        ];

        return show(1, $returnData);
    }

    /**
     * @name: 我的资产
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function myAssets()
    {
        $amountInfo = ExtensionAssets::getAmountInfo($this->userId);

        $returnData = [
            'amount' => $amountInfo['amount'],
            'lockAmount' => $amountInfo['lockAmount'],
            'alowAmount' => $amountInfo['alowAmount'],
        ];

        //判断用户等级
        $extensionId = ExtensionUser::where([
            'userId' => $this->userId,
        ])->value('extensionId');

        //获得当日凌晨的时间戳
        $todayTimeStr = strtotime(date("Y-m-d"), time());

        switch ($extensionId) {
            case 1: //普通
                $returnData['todayInc'] = '0.00';
                $returnData['todayDec'] = '0.00';

                break;
            case 2: //试用初级
            case 3: //初级
            case 4:
            case 5:
                //今日收益锁定的
                $amounInc = ExtensionDealDetail::where([
                    'userId' => $this->userId,
                    'createTime' => ['gt', $todayTimeStr],
                    'lock' => 1,
                ])->sum('amount');
                $returnData['todayInc'] = bcmul('1', $amounInc, config('app.usdt_float_num'));

                //今日被退款的
                $amounDec = ExtensionDealDetail::where([
                    'userId' => $this->userId,
                    'updateTime' => ['gt', $todayTimeStr],
                    'lock' => 2,
                ])->sum('amount');
                $returnData['todayDec'] = bcmul('1', $amounDec, config('app.usdt_float_num'));

                break;

            default:
                $returnData['todayInc'] = '0.00';
                $returnData['todayDec'] = '0.00';
                break;
        }


        return show(1, $returnData);
    }




    /**
     * @name: 我的资产-收益记录
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function myAssetsList()
    {
        $dataArr  = $this->checkdate('Extension', 'get', 'myAssetsList');

        $returnData = [
            'total' => 0,
            'page_num' => 0,
            'list' => [],
        ];
        $condition = [
            'userId' => $this->userId,
        ];
        $ExtensionDealDetailModel = new ExtensionDealDetail();
        $this->getPageAndSize($dataArr);
        $returnData['total'] = $ExtensionDealDetailModel->getCount($condition);
        $returnData['page_num'] = ceil($returnData['total'] / $this->size);
        //平台分红 显示不同

        $returnData['list'] = $ExtensionDealDetailModel->getList($condition, $this->from, $this->size, ['userId', 'amount', 'lock', 'createTime', 'updateTime'], $this->sort, ['detailsDate']);
        return show(1, $returnData);
    }

    /**
     * @name: 我的资产-转入钱包
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function toAssets()
    {
        $ExtensionAssetsModel = new ExtensionAssets();
        $result = $ExtensionAssetsModel->toAssets($this->userId);
        if ($result) {
            return show(1);
        }
        return show(0);
    }

    /**
     * @name: 提现明细
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function assetsDetails()
    {
        $alreadyCash = ExtensionAssetsDetails::where(['lock' => 1, 'detailType' => 2, 'userId' => $this->userId])->sum('amount'); //已提现
        $returnData = [
            'extensionName' => ExtensionUser::extensionName($this->userId),
            'alreadyCash' => $alreadyCash
        ];

        return show(1, $returnData);
    }
    /**
     * @name: 提现明细-列表
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function assetsDetailsLog()
    {

        $dataArr  = $this->checkdate('Extension', 'get', 'profitUserList');

        $returnData = [
            'total' => 0,
            'page_num' => 0,
            'list' => [],
        ];
        $ExtensionAssetsDetailsModel = new ExtensionAssetsDetails();
        $condition = [
            'lock' => 1,
            'detailType' => 2,
            'userId' => $this->userId
        ];
        $this->getPageAndSize($dataArr);
        $returnData['total'] = $ExtensionAssetsDetailsModel->getCount($condition);
        $returnData['page_num'] = ceil($returnData['total'] / $this->size);
        $returnData['list'] = $ExtensionAssetsDetailsModel->getList($condition, $this->from, $this->size, ['createTime', 'detailType', 'amount', 'description'], $this->sort, ['dateStr']);
        return show(1, $returnData);
    }
    /**
     * @name: 计算完成
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    private function cumputeUpgrade($num)
    {
        $upgrade = 0;
        $gradeNum = bcdiv($num, '10', 2);
        if ($gradeNum < 1) {
            $upgrade = $gradeNum;
        } else {
            $upgrade = 1;
        }
        return $upgrade;
    }
}
