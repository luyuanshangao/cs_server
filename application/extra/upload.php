<?php

/**
 * 上传默认配置文件
 */

return [
    //banner
    'banner' => [             //banner上传配置
        'default' => 'Local',
        'engine' => [
            'foldername' => 'banner',   //文件夹名
            'size' => 2,              //大小 M
            'ext' => 'jpg,jpeg,png,gif'
        ]
    ],
    //category
    'category' => [             //category上传配置
        'default' => 'Local',
        'engine' => [
            'foldername' => 'category',   //文件夹名
            'size' => 2,              //大小 M
            'ext' => 'jpg,jpeg,png,gif'
        ]
    ],
    //version
    'version' => [             //app更新上传配置
        'default' => 'Local',
        'engine' => [
            'foldername' => 'appversion',   //文件夹名
            'size' => 100,              //大小 M
            'ext' => 'apk,wgt'
        ]
    ]


];
