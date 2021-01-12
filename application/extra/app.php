<?php

return [
    'password_pre_halt' => '_#ds',// 密码加密盐
    'aeskey' => 'sgg45747ss223455',//aes 密钥 , 服务端和客户端必须保持一致
    'app_sign_time' => 10,// sign失效时间 秒
    'app_sign_cache_time' => 20,// sign 缓存失效时间 s
    'app_token_time_out' => 86400 * 7,// token 缓存失效时间 登陆过期 s
    'wallet_words_cycle' => 60 * 15,// 多久可以获取助记词一次
    'email' => '329361938@qq.com',// 在第三方创建订单 售后单时所填邮箱
    'float_num' => '2',// 通用保留小数位数
    'usdt_float_num' => '5',// usdt保留小数位数
    'btc_float_num' => '6',// btc保留小数位数
    'eth_float_num' => '5',// eth保留小数位数
    'uni_float_num' => '4',// uni保留小数位数
    'need_access_login' => 1      //账号密码登录
];
