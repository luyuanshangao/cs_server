<?php

namespace app\admin\controller\v1;

use app\admin\controller\Base;
use app\common\model\RedpacketCash;
use app\common\model\RedpacketHelp as RedpacketHelpModel;
use app\common\model\Redpacket;
use app\common\model\User;

/**
 * @name: 用户管理
 * @author: gz
 * @description: GET POST
 * @param {type}
 * @return:
 */
class Redpackethelp extends Base
{
    protected $noAuthArr = [];
    //接口白名单    不需要登录的接口方法
    protected $noCheckArr = [];
    //权限白名单
    /**
     * @name: User列表
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function list()
    {
        
        $data = $this->request->get();
        $RedpacketCash = new RedpacketCash();
        $this->getPageAndSize($data);
        $condition = $this->filterParam(['state','userName','times','createTime'], $data);
       
        if (isset($condition['userName'])) {
            $userIds = User::where(['userName' => ['like', '%' . $condition['userName'] . ',%']])->column('userId');
            $condition['userId'] = ['in',$userIds];
            unset($condition['userName']);
        }
     
        //用户数据
        $total = $RedpacketCash->getCount($condition);
        $list = $RedpacketCash->getList($condition, $this->from, $this->size, true, $this->sort, ['userName','invaNum','ip']);
        
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
    
    /**
     * @name: 确定提现
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function sure()
    {

        $data = $this->request->get();
        $result = RedpacketCash::trueCash($data['cashId']);
        
        if (!$result) {
            return show(0);
        }
        return show(1);
    }
   /**
     * @name: 拒绝提现
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function false()
    {

        $data = $this->request->get();
        $result = RedpacketCash::falseCash($data['cashId']);
        if (!$result) {
            return show(0);
        }
        return show(1);
    }

   /**
     * @name: 详情查看
     * @author: gz
     * @description:
     * @param {type}
     * @return {type}
     */
    public function listDetails()
    {

        $data = $this->request->get();
        $RedpacketCash = new RedpacketCash();
        $this->getPageAndSize($data);
       
        $yData = $RedpacketCash->where(['cashId' => $data['cashId']])->find();
        $yUserData = User::get(['userId' => $yData['userId']]);
        $redpacketData = Redpacket::get(['userId' => $yData['userId'],'times' => $yData['times']]);
        //userId userName ip duration amount createTime
        $ylist = [
            [
                'userId' => $yData['userId'],
                'userName' => $yUserData['userName'],
                'ip' => $yUserData['registerIp'],
                'duration' => $yUserData['duration'],
                'amount' => floatval($redpacketData['amount']),
                'createTime' => $yData['createTime'],
            ]
        ];
        $RedpacketHelpModel  = new RedpacketHelpModel();
        $condition = [
            'userId' => $yData['userId'],
            'times' => $yData['times'],
        ];
        //用户数据
        $total = $RedpacketHelpModel->getCount($condition);
        $list = $RedpacketHelpModel->getList($condition, $this->from, $this->size, ['userId','amount','helpUserId'], $this->sort, ['userNameNo','ip','amountFloat','duration','createTime']);
        
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'assetsType' => $yData['assetsType'],
            'list' => $list,
            'ylist' => $ylist,
        ];
        return show(1, $returnResult);
    }

    /**
     * @name: 获取红包等级
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getTimes()
    {
        
        $data = [
            [
                'name' => '初级','times' => 1 ,'type' => 'USDT',
            ],
            [
                'name' => '中级','times' => 2 ,'type' => 'ETH',
            ],
            [
                'name' => '高级','times' => 3 ,'type' => 'BTC',
            ],
        ];
        return show(1, $data);
    }
    /**
     * @name: 获取提现状态
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function getState()
    {
        
        $data = [
            [
                'name' => '已拒绝','state' => -1
            ],
            [
                'name' => '申请中','state' => 0
            ],
            [
                'name' => '已通过','state' => 1
            ],
        ];
        return show(1, $data);
    }
}
