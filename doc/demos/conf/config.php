<?php
return [
    'DB' => array(
        'dh_cms' => array(
            "dbdriver" => 'pdo',    //连接驱动 目前固定值 "pdo"
            "host"     => '192.168.20.156',    //主机地址
            "port"     => '3306',    //端口
            "username" => 'mawang',    //用户名
            "password" => 'mawang123',    //密码
            "dbname"   => 'dh_cms',    //数据库名
            "dbprefix"   => ''     //表前缀
        ),
        'public' => array(
            "pconnect"    => false,  //长连接
            "db_debug"    => true,   //调试模式
            "charset"     => 'utf8'  //编码
        ),
        'slave'  => array(
            array(
                "dbdriver" => 'pdo',    //连接驱动 目前固定值 "pdo"
                "host"     => '192.168.20.156',    //主机地址
                "port"     => '3306',    //端口
                "username" => 'mawang',    //用户名
                "password" => 'mawang123',    //密码
                "dbname"   => 'dh_passport',    //数据库名
                "dbprefix"   => ''     //表前缀
            )
        ),
    ),

    //修改为本地
    "BASE_HOST" => 'http://dev-toutiao-admin.com', //域名
    "SERVER_PASSPORT" => 'http://dev-toutiao-admin.com/', //域名
    "URL_CONTROL_NAME" => 'c', // 默认控制器参数名
    "URL_ACTION_NAME" => 'a', // 默认动作参数名
    "URL_MODE" => 0, // 0 GET  1 PATHINFO

    "REDIS_HOST" => "192.168.20.156", //redis主机地址
    "REDIS_PORT" => "6379", //redis端口
    "REDIS_PWD" => "", //redis密码
    "REDIS_TIMEOUT" => "60", //redis超时时间

    "DB_TYPE" => 'pdo', //连接驱动
    "PASSWORD_KEY"=>"",


    //数据库配置
    "DB_HOST" => '192.168.20.156', //主机地址
    "DB_PORT" => 3306,//端口
    "DB_USERNAME" => 'mawang',//用户名
    "DB_PASSWORD" => 'mawang123',//密码
    "DB_NAME" => 'dh_passport',//数据库名


    "DB_PREFIX" => '',//表前缀
    "DB_PCONNECT" => false, //长连接
    "DB_DEBUG" => true, //调试模式
    "DB_CHARSET" => 'utf8', //编码

    "PAGE_SIZE" => 20, //分页每页数量

    "DEBUG" => true, //日志全开
   
    "DEBUG_SIGNIN" => true, //日志全开

    "MULTIPLE_SIGNIN" => false, //多设备限制登录

    "WITHDRAW_CHECKED" => false, //提现账户余额检查

    "APP_TOKEN_MAXLIFETIME" => 2592000, //app 端用户token最长保留时间30天

    // "COOKIE_DOMAIN" => '.xinyongjinku.com',
    //禁用session
    "DISABLE_SESSION_ROUTE" => '*',
    
    //消息服务器接口
    'MCQUEUE_API_URL' => 'http://test.xinyongjinku.com/messagecenter/services.php',

    //接口访问限制配置
    "ACCESS_LIMIT" => array(
        //登录接口
        "limit_signin" => array(
            "username_limit_num" => 3,
            "ip_limit_num" => 6
        ),
        //登录接口
        "login" => array(
            "username_limit_num" => 2,
            "ip_limit_num" => 6
        ),
    ),
    "APPSTORE_MOBILE"=>array("17801122837"=>"655186",),
    'APP_SESSION_DATA_KEY' => 'app:session:data:%s', # redis data type hash
    'APP_SESSION_ID_KEY' => 'app:session:id:%d', # redis data type string
    "CAPTCHAT" => array(
        'captcha_expire' => 600, //短信、语音验证码，未验证保留有效期
        'frozen_max_num' => 1000, //获取未验证冻结配置
        'send_filter' => array(
            array(
                'interval' => 60, //单位秒
                'limit' => 1,   // 每 interval 分最多发送次数
            ),
            array(
                'interval' => 60*60,
                'limit' => 3,
            ),
            array(
                'interval' => 24*60*60,
                'limit' => 6,
            )
        ),
        'ip_limit' => array(
            'interval' => 60,
            'limit' => 10
        ),
        'ip_limit_num' => 10,
    ),
      
    //账号冻结配置
    "FROZEN_CONFIG" => [
        //交易密码冻结配置
        "trade_pwd" => [
            'cycle_time' => 10800, //冻结时间
            'trigger_num' => 3,     //最多尝试次数
        ],

        //登录密码冻结配置
        "login" => [
            'cycle_time' => 10800, //冻结时间
            'trigger_num' => 6,     //最多尝试次数
        ],

        //实名冻结
        "identify" => [
            'trigger_num' => 3,     //最多尝试次数
        ],
    ],


    //ip 白名单
    "WHITE_IP_LIST" => "127.0.0.1;192.168.20.242;192.168.20.243;192.168.20.245;192.168.10.32;192.168.33.1;192.168.20.229",

    "SERVICE_ACCOUNTS" => array(
        //微信相关
        "WECHAT" => array(
            'appid' => 'wx4dc8114e0fe3f390',
            'secret' => 'ed732d8d5204aa2d3fed97b8589e22e7'
        ),
    ),
    //后台rbac权限设置
    'RBAC_ROLE' => 'admin_role', //用户组数据表名
    'RBAC_NODE' => 'admin_node', //权限规则表
    'RBAC_USER' => 'admin_user',//用户信息表
    //后台rbac Url设置
    "RBAC_URL" => [
        //'1'=>'http://passport.xyjk.com/admin.php',
        '1'=>'http://dev-toutiao-admin.com/admin.php',
    ],

     //新增
    "IMG_CREATE_PATH" =>"D:/phpStudy/WWW/shuzitoutiao/dh_passport/app/web/source/share_images/",

    "H5_URL"   => "https://ready.btctoutiao.info/app/detail/",
];
