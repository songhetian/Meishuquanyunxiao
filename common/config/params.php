<?php
return [
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'metis' => [
        'Url' => [
            'category' => 'http://web.meishuquan.net/rest/category-v2/get-category-list',
            'keyword' => 'http://web.meishuquan.net/rest/keyword-v2/get-keyword-list',
            'publishing' => 'http://web.meishuquan.net/rest/commodity/get-publishing-company-list',
            'commodity' => 'http://web.meishuquan.net/rest/commodity/get-pic-one-to-yun',
            'commoditys' => 'http://web.meishuquan.net/rest/commodity/get-pic-to-yun',

            'course' => 'http://web.meishuquan.net/rest/course-v2/get-course-to-yun',
            'ebook' => 'http://web.meishuquan.net/rest/ebook/get-ebook-to-yun',
            'ebook_banners' => 'http://web.meishuquan.net/rest/ebook/get-ebook-banner',
            'picture_banners' => 'http://web.meishuquan.net/rest/ebook/get-pic-banner',
            'course-chapter' => 'http://web.meishuquan.net/rest/course-chapter/get-course-chapter-one',
            'course-chapters' => 'http://web.meishuquan.net/rest/course-chapter/get-course-chapter',
            'metis-video'     => 'http://web.meishuquan.net/rest/course-v2/get-course-filter-to-yun',
            'metis-banner'    => 'http://web.meishuquan.net/rest/course-v3/get-banner-list',
           
            'picture_search' => 'http://web.meishuquan.net/rest/commodity/get-pic-search-to-yun',
            'course_search' => 'http://web.meishuquan.net/rest/course-v2/get-course-search-to-yun',

            'commodity_num' => 'http://web.meishuquan.net/rest/commodity/get-pic-num-to-yun',
            'course_num' => 'http://web.meishuquan.net/rest/course-v2/get-course-num-to-yun',
            'ebook_num' => 'http://web.meishuquan.net/rest/ebook/get-ebook-num-to-yun',
            'picture_search_num' => 'http://web.meishuquan.net/rest/commodity/get-pic-search-to-yun-num',
            'course_search_num' => 'http://web.meishuquan.net/rest/course-v2/get-course-search-to-yun-num',

            'register' => 'http://web.meishuquan.net/rest/user/jbj-register',
            'upload' => 'http://web.meishuquan.net/rest/user/upload-image?type=jpg&source=national',
        ],
        'pay_desc'=>[
            '0'=>'',
            '5'=>'送7天会员',
            '1'=>'送1个月会员价值168元',
            '2'=>'送3个月会员价值448元',
            '3'=>'送6个月会员价值798元',
            '4'=>'送12个月会员价值1298元',
        ]
    ],
    'cc' => [
        //创建CC直播间
        'createlive' => 'http://api.csslcloud.net/api/room/create',
        //编辑CC直播间
        'updatelive' => 'http://api.csslcloud.net/api/room/update',
        //关闭CC直播间
        'closelive' => 'http://api.csslcloud.net/api/room/close',
        //查询直播间列表
        'liveinfo' => 'http://api.csslcloud.net/api/room/info',
        //按直播id获取回放信息
        'record' => 'http://api.csslcloud.net/api/v2/record/info',
        'recordsearch' => 'http://api.csslcloud.net/api/v2/record/search',
        //查询CC直播间信息
        'searchlive' => 'http://api.csslcloud.net/api/room/search',
        //获取直播间连接数
        'connections' => 'http://api.csslcloud.net/api/statis/connections',
        //获取直播间地址
        'livecode' => 'http://api.csslcloud.net/api/room/code',
        //获取直播间用户出入数据
        'userinfo' => 'http://api.csslcloud.net/api/statis/useraction',

        'userview' =>'http://api.csslcloud.net/api/statis/userview',
        //获取正在直播的直播间
        'broadcasting' => 'http://api.csslcloud.net/api/rooms/broadcasting',
        //CC美术圈userid,apikey
        'userid' => '8FF243A3955D1B18',
        'apikey' => '2pCjBtvSs7kno4jSNwrr95mXR0scCBkv',
        'templatetype'=>[
            '1' => '视频直播',
            '2' => '视频直播+聊天互动+直播问答',
            '3' => '视频直播+聊天互动',
            '4' => '视频直播+聊天互动+直播文档',
            '5' => '视频直播+聊天互动+直播文档+直播问答',
            '6' => '视频直播+直播问答',
        ],
        'authtype'=>[
            '0' => '接口验证',
            '1' => '密码验证',
            '2' => '免密码验证'
        ],
        'barrage'=>[
            '0' => '不开启',
            '1' => '开启'
        ],
        'foreignpublish'=>[
            '0' => '不开启',
            '1' => '开启'
        ],
        'openlowdelaymode'=>[
            '0' => '不开启',
            '1' => '开启'
        ],
        'showusercount'=>[
            '0' => '不开启',
            '1' => '开启'
        ],
    ],
    'oss' => [
        //服务器外网地址，深圳为 http://oss-cn-shenzhen.aliyuncs.com
        'ossServer' => 'http://img-cn-beijing.aliyuncs.com',
        //服务器内网地址，深圳为 http://oss-cn-shenzhen-internal.aliyuncs.com
        'ossServerInternal' => 'http://oss-cn-beijing-internal.aliyuncs.com',
        //阿里云给的AccessKeyId
        'AccessKeyId' => 'G8pSSKbmB4zZ0a4g',
        //阿里云给的AccessKeySecret
        'AccessKeySecret' => 'za8SORmnZthijt2JupuPXUEgwkUzWN',
        //创建的空间名
        'Bucket' => 'meishuquanyunxiao',
        'Size' => [
            '250x250' => '?x-oss-process=style/250x250',
            '375x250' => '?x-oss-process=style/375x250',
            '320x320' => '?x-oss-process=style/320x320',
            '350x350' => '?x-oss-process=style/350x350',
            '475x270' => '?x-oss-process=style/475x270',
            '500x500' => '?x-oss-process=style/500x500',
            '750x500' => '?x-oss-process=style/750x500',
            '1000x1000' => '?x-oss-process=style/1000x1000',
            'original' => '?x-oss-process=style/ef92587c6ac8577915de51f9fa6cae2c',
            'thumb' => '?x-oss-process=style/thumb',
            'general' => '?x-oss-process=style/general',
            'info' => '?x-oss-process=image/info',
            '950x540' => '?x-oss-process=style/950x540',
            '512x512' => '?x-oss-process=style/512x512',
            '57x57' => '?x-oss-process=style/57x57',
            'fix_width' => '?x-oss-process=style/fix_width'
        ]
    ],
    'alidayu' => [
        'appkey' => '23339662',
        'secretKey' => 'c14330ff4060f164cf6e356042496e84',
        'smsFreeSignName' => '云校美术',
        'smsTemplateCode' => 'SMS_40280003',
    ],
    'spark' => [
        'normal' => [
            'userid' => '8FF243A3955D1B18',
            'key' => 'cwBqz2OBnd8gPlOvBaI1NIQcfkYjQpU4',
        ],
        'encrypt' => [
            'userid' => '6E7D1C1E1C09DB4D',
            'key' => 'iKardUvkyz2uSNkXoNo6l4pGJKPmIER8',
        ],
        'url' => [
            'main' => 'http://spark.bokecc.com/api',
            'video' => '/video',
            'playcode' => '/playcode'
        ]
    ],
    'ueditor' => [
        'toolbars' => [
            [
                'undo', //撤销
                'redo', //重做
                'formatmatch', //格式刷
                'removeformat', //清除格式
                'fontfamily', //字体
                'fontsize', //字号
                'fullscreen', //全屏
            ],
            [
                'bold', //加粗
                'italic', //斜体
                'underline', //下划线
                'strikethrough', //删除线
                'forecolor', //字体颜色
                'backcolor', //背景色
                'justifyjustify', //两端对齐
                'justifyleft', //居左对齐
                'justifycenter', //居中对齐
                'justifyright', //居右对齐
                'insertorderedlist', //有序列表
                'insertunorderedlist', //无序列表
                'lineheight', //行间距
                //'horizontal', //分隔线
            ],
            [
                'insertimage', //多图上传
                'insertvideo', //视频
                'link', //超链接
            ]
        ]
    ],
    'select' => [
        'toggleAllSettings' => [
            'selectLabel' => '<i class="glyphicon glyphicon-unchecked"></i> 全部选择',
            'unselectLabel' => '<i class="glyphicon glyphicon-checked"></i> 全部取消选择'
        ]
    ],
    'manage' => [
        'teacher' => [
            [
                'id' => 1,
                'color' => 'rgba(172,196,110,1)',
                'title' => '班级管理',
                'type' => 'bjgl'
            ],
            [
                'id' => 2,
                'color' => 'rgba(144,163,209,1)',
                'title' => '教师管理',
                'type' => 'jsgl'
            ],
            /*
            [
                'id' => 3,
                'color' => 'rgba(182,187,192,1)',
                'title' => '课件管理(调试中)',
                'type' => 'kjgl'
            ],
            [
                'id' => 4,
                'color' => 'rgba(142,127,118,1)',
                'title' => '宿舍管理(调试中)',
                'type' => 'ssgl'
            ],
            */
            [
                'id' => 5,
                'color' => 'rgba(119,155,167,1)',
                'title' => '教学时间',
                'type' => 'jxsj'
            ],
            [
                'id' => 6,
                'color' => 'rgba(119,155,167,1)',
                'title' => '激活码',
                'type' => 'jhm'
            ],
            [
                'id' => 7,
                'color' => 'rgba(171,156,144,1)',
                'title' => '请假审批',
                'type' => 'qjsp'
            ],
            [
                'id' => 8,
                'color' => 'rgba(156,143,212,1)',
                'title' => '考试成绩',
                'type' => 'kscj'
            ],
            [
                'id' => 9,
                'color' => 'rgba(108,195,144,1)',
                'title' => '学生管理',
                'type' => 'xsgl',
                'newAdd' => 16,
                'height' => true
            ],
            /*
            [
                'id' => 10,
                'color' => 'rgba(109,166,206,1)',
                'title' => '学生作业(调试中)',
                'type' => 'xszy',
                'height' => true
            ],
            */
            [
                'id' => 12,
                'color' => 'rgba(142,127,118,1)',
                'title' => '权限管理',
                'type' => 'qxgl'
            ],
        ],
        'teacherSpecial' => [
            [
                'id' => 1,
                'color' => 'rgba(171,156,144,1)',
                'title' => '请假审批',
                'type' => 'qjsp'
            ],
            [
                'id' => 8,
                'color' => 'rgba(156,143,212,1)',
                'title' => '考试成绩',
                'type' => 'kscj'
            ]
        ],
        'student' => [
            [
                'id' => 1,
                'color' => 'rgba(171,156,144,1)',
                'title' => '请假审批',
                'type' => 'qjsp'
            ],
            [
                'id' => 2,
                'color' => 'rgba(156,143,212,1)',
                'title' => '考试成绩',
                'type' => 'kscj'
            ],
            [
                'id' => 3,
                'color' => 'rgba(182,187,192,1)',
                'title' => '查看学分',
                'type' => 'ckxf'
            ],

        ],
        'family' => [
            [
                'id' => 1,
                'color' => 'rgba(171,156,144,1)',
                'title' => '请假审批',
                'type' => 'qjsp'
            ],
            [
                'id' => 2,
                'color' => 'rgba(156,143,212,1)',
                'title' => '考试成绩',
                'type' => 'kscj'
            ],
            [
                'id' => 3,
                'color' => 'rgba(182,187,192,1)',
                'title' => '查看学分',
                'type' => 'ckxf'
            ],
        ],
    ],
    'leave' => [
        'student' => [
            [
                'title' => '我要请假',
                'type' => 2,
                'icon' => 'https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/leave/create.png?x-oss-process=style/250x250'
            ],
            [
                'title' => '我发起的',
                'type' => 3,
                'icon' => 'https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/leave/initiate.png?x-oss-process=style/250x250'
            ],
        ],
        'family' => [
            [
                'title' => '我审批的',
                'type' => 1,
                'icon' => 'https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/leave/audit.png?x-oss-process=style/250x250'
            ],
        ],
        'teacher' => [
            [
                'title' => '我审批的',
                'type' => 1,
                'icon' => 'https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/leave/audit.png?x-oss-process=style/250x250'
            ],
            [
                'title' => '我要请假',
                'type' => 2,
                'icon' => 'https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/leave/create.png?x-oss-process=style/250x250'
            ],
            [
                'title' => '我发起的',
                'type' => 3,
                'icon' => 'https://meishuquanyunxiao.oss-cn-beijing.aliyuncs.com/icon/leave/initiate.png?x-oss-process=style/250x250'
            ],
        ]
    ],
    'alipay' => [
        'gatewayUrl' =>'',
        'appId' =>'2019032563745029',
        'rsaPrivateKey' =>'MIIEpQIBAAKCAQEAur4gpVjYk0hIozvkbfHTz2+YzCEa0oWCC72d7ZeICu4yqaE7zIUXPYh9UFhuJ9W3U1+dBOrMpTLQHV8tkkouzYXhfccbTbjb+C5c9ZA96fOW7EEUEZHP/OqDr0MRCoXkRSUrCcYJou8JxlUmsjb6QPUPTLb5Qk88QKJwfxrLsEbAUNGsdRgYerRacyzicN/xqzBCwrC1jEM+zROUCXeP5JukcT5gnWm1N44YZSG5iQ+KJp2xoLJJdMBwUkyAja8jN07rsfwRq7BDH2OJ8Qt+g6uUtKTlEj9+VxvUipevaLkyEn1lCNPKLn7rnLFkCIxr0gAAAALEeRc/vWXz6j5ADQIDAQABAoIBAASufY0HpC5VEpUdDEYBWQkLSC5d6hk7BZ6bu1jYgq2beSOtih0/fOyq/lFEVkajYfwyGnKkHEtL8dG6sB7Jw1CduaB4nGOfbcxUBTqWyiaSV8dGfmVOXYN+sZx0Nvonjyh4nRKap2UxTvJs8hJntHBqdF68+5TA/ca1C9Lz7gVWRcP39QvG2GwJ7Nd5ijgZDjJ8qkoJidW6e0ojwqNR2MdqmtUXlZGYfO7DX8TmBoLeHhBOmrULWGoWT4Qrw+TABthTc4GqJVIO8h9cJi/UV1dpvX48BjOb0NWfu0F1YxM6FMw30xTp0c5Tzv2FG+odcnu5eUUMMnOYyG1yq/KSmVUCgYEA5dp1kYIQeqtrxbkH86zxeXiecXytfWPE2RrAemLJ8BB0sadWp5F141RSbxkMn40DBggth5vUxid+GYJz5UdzBVI9++nyT7Mt3US6AbNfXiKc/Qi98rFLOxtkm8OOSTwrtoent5ojZ47euUPw8yAsZ1N5TnIhGggji2f+jrl5rxsCgYEAz/w+909JpmW+jLfnR1LE0lQXOlZmVghY1/dXf2JbVwXzzZjCYXO2+H1wjfzOTHwHYo2VjpKlgTV2cTmZVitKq6EV8uEtmM3uWQemVKuYAAIniX8ac+qIrtTKj7D3jaeexiFArInQ1oZxtww7dxr1MVdshJlWwlAJ+1kgwCWet/cCgYEAn/NkOTvPh+3ec5uNYw8Ig0VizQq8GDPjeklZhFFWwY5SBoaykK1y4h19t/4wgJ86aWordOEGMlatM8dKK1WLPzq6E6H7k2bXFdGLtz9BsFpZ8OOyez7RYXJwksyFKYWLzduc5PGIjbooV7hl5mqPO+Ak3GgjjN/5DDv0MxHzEd0CgYEAmiO9npeaY8Gf2LYqp4dF6wL7O/bwXO5Oua/LntKMExCMQWDnHkYd4kdE9VXYpoJ9DqMTpdg05G902jDv3Ra0fkIh/CC6JDbqX/z1XmbVfZwbJSGXvzSgG8IEZT2oGcmSOBBI2BZDOdnlyN097OWDtg+ukw75Z4TeAPNq/DxlRr0CgYEAxZfYtebzxYAk6g3mf8LAlmmC99zKPhkZvC7sxei2lkmHwdBb/PAXFyk2kv/4/DMpe6bsdGVEVHIYSF2StJ0sdALIxIE765MKX78qKpub5nMqJMV3B1aqCnOC2GjZdjccZY/cuRlQ6YVWerySOBPdw8dYRiRLHzMGYAiQECvwKDU=',
        'format' =>'',
        'charset' =>'',
        'signType' =>'',
        'alipayrsaPublicKey' =>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAur4gpVjYk0hIozvkbfHTz2+YzCEa0oWCC72d7ZeICu4yqaE7zIUXPYh9UFhuJ9W3U1+dBOrMpTLQHV8tkkouzYXhfccbTbjb+C5c9ZA96fOW7EEUEZHP/OqDr0MRCoXkRSUrCcYJou8JxlUmsjb6QPUPTLb5Qk88QKJwfxrLsEbAUNGsdRgYerRacyzicN/xqzBCwrC1jEM+zROUCXeP5JukcT5gnWm1N44YZSG5iQ+KJp2xoLJJdMBwUkyAja8jN07rsfwRq7BDH2OJ8Qt+g6uUtKTlEj9+VxvUipevaLkyEn1lCNPKLn7rnLFkCIxr0gAAAALEeRc/vWXz6j5ADQIDAQAB',
    ],
    'weixin' => [
        'appid' => 'wxcd0cfe2909f76506',
        'mch_id' => '1533498881',//商户id
        'key' => '17b1326a109fb105fdd2061e0f3b6525',
    ],
    'Push' =>  [

        //测试环境
        'Android-appkey'    => '2d53ea1b1081a',
        'Android-appsecret' => 'cbd3d60b24bbd4670779bc9d698128e7',

        'Formal-appkey'        => '2a95ad1e3ed9a',
        'Formal-appsecret'     => 'fe31c91f023f17cfd0ea960f887fdbe1',

    ]
];
