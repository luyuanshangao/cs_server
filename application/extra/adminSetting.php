<?php

/**
 * 后台通用配置
 */

return [
    'db_status' => [ //通用状态 0->无效 1->有效 与数据库相同
        [
            'value' => 0,
            'name' => '无效',
        ],
        [
            'value' => 1,
            'name' => '有效',
        ],

    ],
    'catClassList' => [ //分类状态 0->无效 1->有效 与数据库相同
        [
            'value' => 0,
            'name' => '一级分类',
        ],
        [
            'value' => 1,
            'name' => '二级分类',
        ],
        [
            'value' => 2,
            'name' => '三级分类',
        ],

    ],
    'goods_status' => [ //商品状态 0->未上架 1->已上架 与数据库相同
        [
            'value' => 0,
            'name' => '未上架',
        ],
        [
            'value' => 1,
            'name' => '已上架',
        ],

    ],
    'order_status' => [ //订单状态 -2:未付款的订单 -1：用户取消 0:待发货 1:配送中 2:已完成
        [
            'value' => -2,
            'name' => '未付款',
        ],
        [
            'value' => -1,
            'name' => '已取消',
        ],
        [
            'value' => 0,
            'name' => '待发货',
        ],
        [
            'value' => 1,
            'name' => '待收货',
        ],
        [
            'value' => 2,
            'name' => '已完成',
        ]

    ],
    'services_order_status' => [ //售后单状态 -2取消 -1申请失败 0申请中  1退款中 2退款完成
        [
            'value' => -2,
            'name' => '已取消',
        ],
        [
            'value' => -1,
            'name' => '已拒绝',
        ],
        [
            'value' => 0,
            'name' => '申请中',
        ],
        [
            'value' => 1,
            'name' => '退款中',
        ],
        [
            'value' => 2,
            'name' => '已退款',
        ]

    ],
    'services_customerExpect' => [ //售后类型 退货(10)、换货(20)-暂未开发、维修(30)-暂未开放
        [
            'value' => 10,
            'name' => '退货',
        ],
        [
            'value' => 20,
            'name' => '换货',
        ],
        [
            'value' => 30,
            'name' => '维修',
        ]

    ],
   

];
