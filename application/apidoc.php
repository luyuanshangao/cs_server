<?php

/**
 * @apiDefine Header
 * @apiHeader {String} access-token 用户token
 * @apiHeaderExample {json} 例:
 *     {
 *       "access-token": "3a5904e77aa16b9b8cd78ee47df6ef3a84e43de9",
 *     }
 */

/**
 * @apiDefine Page
 * @apiParam {int} page 页数
 * @apiParam {int} size 每页大小
 */

/**
 * @apiGroup 请求头
 * @apiUse Header
 * @api {get} v1/api/ 请求头
 * @apiVersion 1.0.0
 * @apiSuccessExample 成功返回:
 * {
 * }
 */

/**
 * @apiGroup 商品
 * @apiUse Header
 * @api {get} v1/api/goods/searchFind 搜索发现
 * @apiVersion 1.0.0
 * @apiSuccessExample 成功返回:
 *  {
 *      "code": 1,
 *     "msg": "成功",
 *      "data": [
 *         "零食",
 *         "电脑",
 *         "单反相机",
 *         "雀氏",
 *         "数码相机",
 *         "袜子",
 *         "笔记本",
 *         "手机",
 *         "杰士邦",
 *        "冲饮谷物"
 *      ]
 *  }
 */

/**
 * @apiGroup 商品
 * @apiUse Header
 * @api {get} v1/api/goods/appraisesList 商品评价列表
 * @apiVersion 1.0.0
 * @apiUse Page
 * @apiParam {string} skuNum 商品编号
 * @apiParam {string} type 0全部 1最新 2好评 3中评 4差评 5有图
 * @apiSuccessExample 成功返回:
 * {
 *  "code": 1,
 *  "msg": "成功",
 *  "data": {
 *      "totalA": 1, 全部数量
 *      "totalH": 1, 好评数量
 *      "totalM": 0, 中评数量
 *      "totalL": 0, 差评数量
 *      "totalI": 1, 有图数量
 *      "listResult": { 评论
 *          "total": 1, 总数
 *          "page_num": 1, 页数
 *          "list": [ 列表数据
 *          {
 *                  "goodsScore": 5, 评分
 *                  "content": "很不錯 東西很好吃 物流也很快 支持", 评论内容
 *                  "imgUrl": "/uploads/appraises/20200630/568f8929e1571a438e99a03b657693d9.JPG",  图
 *                  "userId": 10069,
 *                  "spuName": "奥利奥（Oreo） 巧脆卷 巧克力味蛋卷威化饼干 下午茶休闲零食 55g （新老包装随机发货）", 商品名
 *                  "userInfo": {   用户信息
 *                      "userName": "q******8",
 *                      "avatar": ""
 *                  },
 *                  "imgs": [
 *                      "/uploads/appraises/20200630/568f8929e1571a438e99a03b657693d9.JPG"
 *                  ]
 *              }
 *          ]
 *      }
 *  }
 *}
 */
