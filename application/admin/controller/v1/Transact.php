<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\admin\model\Order;

class Transact extends Base
{
    protected $noAuthArr = ['month'];//接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];    //权限白名单

    /**
     * @name: Transact列表
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function list()
    {
        
        $dataArr = $this->request->get();
    
        if (isset($dataArr['year']) && $dataArr['year']) {
            $beginYear = date('Y', $dataArr['year'] / 1000);
            $nowYear = date('Y', $dataArr['year'] / 1000);
        } else {
            $beginYear = 2020;
            $nowYear  = date('Y', time());
        }
        
        //生成年份数组
        $yearArr = range($beginYear, $nowYear);
        
        $list = [];
        $condition = ['isPay' => 1];
        foreach ($yearArr as $key => $year) {
            //年份时间戳
            $beginTimeSt = strtotime($year . "-1" . "-1");
            //年份结束时间戳
            $endTimeSt =  strtotime(($year + 1) . "-1" . "-1");

            $condition['payTime'] = ['between',[$beginTimeSt,$endTimeSt]];
           
            $usdt = Order::where($condition)->sum('totalMoney');
            $list[$key]['year'] = $year;
            $list[$key]['usdt'] = $usdt;
        }

        $returnResult = [
            'list' => $list,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: 月份数据
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function month()
    {
        
        $beginYear = 2020;
        $nowYear  = date('Y', time());
        //年份数组
        $yearArr = range($beginYear, $nowYear);
        //月份数组
        $monthArr = range(1, 12);
        array_push($monthArr, '总交易金额');

        $condition = ['isPay' => 1];
        foreach ($yearArr as $keyY => $year) {
            $yearList[$keyY]['name'] = $year . '年';

            $yearUsdt = 0 ;
            foreach ($monthArr as $keyM => $month) {
                if ($keyM < 12) {
                    //月份时间戳
                    $beginThismonth = mktime(0, 0, 0, $month, 1, $year);
                    //月份结束时间戳
                    $endThismonth = mktime(23, 59, 59, $month, date('t', strtotime($year . '-' . $month . '-1')), $year);

                    $condition['payTime'] = ['between',[$beginThismonth,$endThismonth]];
                    $monthUsdt = Order::where($condition)->sum('totalMoney');
                    $yearList[$keyY]['list'][$keyM]['usdt'] = $monthUsdt;
                    $yearUsdt += $monthUsdt;
                } else {
                    $yearList[$keyY]['list'][$keyM]['usdt'] = $yearUsdt;
                }
            }
            
            $list['dateList']['list'] = $year;
        }


        
        $returnResult = [
            'yearList' => $yearList,
            'dateList' => $monthArr
        ];
        return show(1, $returnResult);
    }
}
