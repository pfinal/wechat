<?php

$openid = 'oBhV8uMtPrH12dy6czX3LKHu24j4';
$openid2 = 'oBhV8uN54GnGhInEvn2yo8KxhDM8';

//mz
$openid3 = 'o5cI1wr6k6mjcSReR9GNx5xMDfW4';

$imageMediaId = 'ZbXWSFYu_gmXjP9EW5ydPHAgFanMTN0m_IdeYuuqwio';//永久图片素材
$newsMediaId = 'ZbXWSFYu_gmXjP9EW5ydPDqHIZ6hcbfbQI0j0z4AGlA'; //永久文章素材
$voiceMediaId = 'ZbXWSFYu_gmXjP9EW5ydPFFpZ3QbCBr8Xyp0NaPt2Uo';//永久语音素材
$videoMediaId = 'ZbXWSFYu_gmXjP9EW5ydPGhRZb4PLJnxlVdUs9Hyeh8';//永久视频素材

$musicMediaId = 'vChqZugeRqp9HKkMu7E1ARr5-68G4eobNgZoI9J1zws';//永久music素材

$news = [
    [
        "title" => "pFinal",
        "description" => "致力于提供优质PHP中文学习资源",
        "url" => "http://www.pfinal.cn/",
        "picUrl" => "http://www.pfinal.cn/images/pfinal.png"
    ],
    [
        "title" => "迈征",
        "description" => "宇宙中的又一个PHP培学机构",
        "url" => "http://www.myzheng.cn/",
        "picUrl" => "https://mmbiz.qlogo.cn/mmbiz/D7sHwECXBUtWxg2eVOmIsqWOERic2dfBWpjONuWl2prbAI07ZRiasOw9Dt3ibILvs9uIJib7kIiaVbowRWrpnJH1Zew/0?wx_fmt=png"
    ]
];

$menus = [
    [
        "type" => "click",
        "name" => "今日歌曲",
        "key" => "V1001_TODAY_MUSIC"
    ],
    [
        "name" => "菜单",
        "sub_button" => [
            [
                "type" => "view",
                "name" => "搜索",
                "url" => "http://www.soso.com/"
            ],
            [
                "type" => "view",
                "name" => "视频",
                "url" => "http://v.qq.com/"
            ],
            [
                "type" => "click",
                "name" => "赞一下我们",
                "key" => "V1001_GOOD"
            ],
        ],
    ],
];
