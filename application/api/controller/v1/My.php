<?php

namespace app\api\controller\v1;

use app\api\controller\Base;
use app\common\model\UserCollection;
use app\common\model\User;
use app\common\model\UserHistory;
use app\common\model\Order;
use app\common\model\OrderGoods;
use app\common\model\Message;
use app\common\model\AssetsType;

class My extends Base
{
    protected $noAuthArr = ['setPclientId'];  //用户登录接口白名单
    protected $noSignArr = [];  //接口Sign验证白名单

    /**
     * @name: 用户信息
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function home()
    {
        
        //用户信息
        $toBePay = Order::where(['orderStatus' => '-2','userId' => $this->userId,'del' => 1])->count(); //待支付数
        $toBeSend = Order::where(['orderStatus' => '0','userId' => $this->userId,'del' => 1])->count(); //待发货数
        $toBeTake = Order::where(['orderStatus' => '1','userId' => $this->userId,'del' => 1])->count(); //待收货数
        //待收货数
        //已支付订单
        $orderIds = Order::where(['orderStatus' => 2,'userId' => $this->userId])->column('orderId');
        $toBeTalk = OrderGoods::where(['isAppraise' => '0','isServices' => 0,'orderId' => ['in',$orderIds]])->count(); //待评价数
        $toBeBack = OrderGoods::where(['isServices' => 1,'orderId' => ['in',$orderIds]])->count(); //售后数
        $returnArr = [
            'userName' => $this->clientInfo->userName,
            'userId' => $this->clientInfo->userId,
            'avatar' => $this->clientInfo->avatar,
            'collectionNum' => UserCollection::getNum($this->userId),
            'historyNum' => UserHistory::getNum($this->userId),
            'toBePay' => $toBePay,
            'toBeSend' => $toBeSend,   //待发货
            'toBeTake' => $toBeTake,   //待收货
            'toBeTalk' => $toBeTalk,   //待评价
            'toBeBack' => $toBeBack,   //退款/售后数
        ];
        return show(1, $returnArr);
    }

    /**
     * @name: 设置推送的客户端id
     * @author: gz
     * @description:
     * @param {type}
     * @return:
     */
    public function setPclientId()
    {
        $dataArr  = $this->checkdate('User', 'get', 'pClientId');
        //设置推送的客户端id
        if ($this->userId && isset($dataArr['pClientId']) && $dataArr['pClientId']) {
            $this->clientInfo->pClientId = $dataArr['pClientId'];
            $this->clientInfo->save();
        }
        return show(1);
    }
    /**
     * @name: 修改昵称
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function upUserName()
    {
        $dataArr  = $this->checkdate('User', 'get', 'upUserName');
        if ($dataArr['userName'] == $this->clientInfo['userName']) {
            return show(1);
        }
        $data = User::get([
            'userName' => $dataArr['userName']
        ]);

        if ($data) {
            return show(1025);
        }
        $this->clientInfo->userName = $dataArr['userName'];
        $this->clientInfo->save();
        return show(1);
    }

    /**
     * @name: 修改默认支付
     * @author: gz
     * @description: GET POST
     * @param {type}
     * @return:
     */
    public function upDefaultAssetsType()
    {
        $data = $this->request->get();
        $AssetsTypeData = AssetsType::get([
            'assetsType' => $data['defaultAssetsType']
        ]);

        if (!$AssetsTypeData) {
            return show(0);
        }
        $this->clientInfo->defaultAssetsType = $data['defaultAssetsType'];
        $this->clientInfo->save();
        return show(1);
    }



    /**
     * @name: 获取消息列表接口
     * @author: gz
     * @description: GET type
     * @param {type}
     * @return:
     */
    public function messageList()
    {

        $dataArr  = $this->checkdate('User', 'get', 'messageList');
        $MessageModel = new Message();
        $this->getPageAndSize($dataArr);
        $condition = [
            'type' => $dataArr['type'],
            'userId' => $this->userId
        ];
        $total = $MessageModel->getCount($condition);
        $list = $MessageModel->getList($condition, $this->from, $this->size, true, 'time desc', ['createTime']);
        $returnResult = [
            'total' => $total,
            'page_num' => ceil($total / $this->size),
            'list' => $list,
        ];
        return show(1, $returnResult);
    }
    /**
     * @name: 删除消息
     * @author: gz
     * @description: GET type
     * @param {type}
     * @return:
     */
    public function delMessage()
    {

        $dataArr  = $this->checkdate('User', 'get', 'delMessage');
        Message::destroy([
            'messageId' => $dataArr['messageId'],
            'userId' => $this->userId,
        ]);
        return show(1);
    }

    /**
     * @name: 获取未读消息数量
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function messageCount()
    {
        $MessageModel = new Message();
        $data = array();
        $data["systemCount"] = $MessageModel->getUnreadMessageCount("系统通知", $this->userId);
        $data["assetsCount"] = $MessageModel->getUnreadMessageCount("资产变动通知", $this->userId);
        $data["serviceCount"] = $MessageModel->getUnreadMessageCount("服务通知", $this->userId);
        $data["orderCount"] = $MessageModel->getUnreadMessageCount("订单通知", $this->userId);
        $data["expressCount"] = $MessageModel->getUnreadMessageCount("物流通知", $this->userId);
        return show(1, $data);
    }


    /**
     * @name: 标记消息已读接口
     * @author: gz
     * @description: GET
     * @param {type}
     * @return:
     */
    public function readMessage()
    {
        $dataArr  = $this->checkdate('User', 'get', 'readMessage');
      
        if (!$dataArr) {
            Message::update(['read' => 1], ['read' => 0,'userId' => $this->userId]);
        } elseif (isset($dataArr['messageId'])) {
            $messageIdArr = json_decode($dataArr['messageId'], true);
            Message::update(['read' => 1], ['read' => 0,'messageId' => ['in',$messageIdArr],'userId' => $this->userId]);
        }
        return show(1);
    }
}
