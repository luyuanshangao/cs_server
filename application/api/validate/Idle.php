<?php

namespace app\api\validate;

class Idle extends \think\Validate
{
    //规则
    protected $rule = [
        ['page','number', '页数为数字'],
        ['size','number', '页数大小为数字'],
        ['keyword','max:20', '搜索内容过长'],

        ['skuData','require', '规格必须'],
        ['type','require|in:1,2,3,4', '类型必须|类型错误'],
        ['title','require|max:60|min:5', '标题必须|标题过长|标题过短'],
        ['description','require|max:300|min:5', '标题必须|标题过长|标题过短'],
        ['price','require|number', '价格必须|价格错误'],
        ['condition','require|in:1,2,3,4,5', '成色必须|成色错误'],
        ['idleInfoId','require|number', '信息错误|信息错误'],
        ['idleDealId','require|number', '信息错误|信息错误'],
        ['sellUserId','require|number', '信息错误|信息错误'],
        ['idleDealDisputeId','require|number', '信息错误|信息错误'],
        ['idleDealRefundId','require|number', '信息错误|信息错误'],
        ['addressId','require|number', '地址错误|地址错误'],
        ['remark','min:4|max:100', '备注过短|备注过长'],
        ['payPassWord','require|length:6', '支付密码错误|支付密码错误'],
        ['logistics','require|max:50', '物流错误|物流错误'],
        ['logisticsNum','require|max:100', '物流单号错误|物流单号错误'],
        ['reson','require|max:50', '申请原因错误|申请原因错误'],
        ['resonDetail','require|min:4|max:100', '申请描述错误|申请描述过短|申请描述过长'],
        ['picPath','require|max:1500', '图片必须|图片过长'],
        ['disputeType','require|in:1,2,3', '描述错误|描述错误'],
        ['disputeDescribe','require|min:4|max:100', '描述错误|申请描述过短|申请描述过长'],
        ['content','require|min:4|max:100', '描述错误|描述过短|描述过长'],
        ['infoSn','require|max:50', '号错误|号错误'],
        ['dealSn','require|max:50', '号错误|号错误'],
        ['idleInfoIdArr','require', '参数错误'],


        

    ];

    //场景不同场景验证不同的字段
    protected $scene = [
        'list' => ['page','size','search'],
        'createIdle' => ['title','description','skuData','picPath'],
        'cacalIdle' => ['idleInfoId'],
        'idleListsInfo' => ['page','size','type'],
        'editIdleInfo' => ['title','description','skuData','picPath','idleInfoId'],
        'downIdleInfo' => ['idleInfoId'],
        'payVerifyFee' => ['idleInfoId','payPassWord'],
        'aginIdleInfo' => ['idleInfoId'],
        'deleteIdleInfo' => ['idleInfoId'],
        'idleDetail' => ['idleInfoId'],
        'idleColleDelete' => ['idleInfoIdArr'],
        'listMessage' => ['page','size','idleInfoId'],
        'createDeal' => ['idleInfoId','addressId','remark'],
        'payDeal' => ['idleDealId','payPassWord'],
        'cacolDeal' => ['idleDealId'],
        'sendDeal' => ['idleDealId','logistics','logisticsNum'],
        'trueDeal' => ['idleDealId'],
        'closeDeal' => ['idleInfoId'],
        'refundDeal' => ['idleDealId','idleInfoId','reson','resonDetail'],
        'lookRefundDeal' => ['idleDealId','idleDealRefundId'],
        'cocalRefundDeal' => ['idleDealId','idleDealRefundId'],
        'agreeRefundDeal' => ['idleDealId','idleDealRefundId'],
        'voteRefundDeal' => ['idleDealId','idleDealRefundId'],
        'createDispute' => ['idleDealId','idleInfoId','disputeType','disputeDescribe'],
        'upEvidence' => ['idleDealDisputeId','picPath','content'],
        'evidenceDetail' => ['page','size','idleDealDisputeId'],
        'deleteDeal' => ['idleDealId'],
        'sellUserInfo' => ['userId'],
        'onlineSellDeal' => ['userId'],
        'dealDetail' => ['idleDealId'],
    ];
}
