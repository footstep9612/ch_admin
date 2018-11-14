<?php
namespace App\common;

use Lib\JsonRpcBasicErrorException as BasicException;

class AllErrorException extends BasicException
{
    /**
     * 错误分类
     * 10xx 接口调用错误
     * 11xx 参数验证错误
     * 12xx 输入错误
     * 13xx 逻辑错误
     * 15xx 服务器错误
     * 16XX
     * 17xx  调用成功
     */

    //接口调用错误
    const API_MIS_PARAMS = 1000;  //缺少必要参数
    const API_ILLEGAL = 1001;
    const API_BUSY = 1002;
    const API_FAILED = 1003;
    const API_EXPIRE = 1004;
    const API_SMS = 1005;
    const API_ERROR_PARAMS = 1006;
    const API_ERROR_CREATE = 1007;  //创建任务失败

     //调用成功提示
    const API_SUCCESS_PHONE = 1700;

    //参数验证错误
    const VALID_CAPTCHA_FAIL = 1100;
    const VALID_SMS_FAIL = 1101;
    const VALID_PWD_FAIL = 1102;
    const VALID_PHONE_FAIL = 1103;
    const VALID_PHONE_USED = 1104;
    const VALID_BANKCARD_FAIL = 1105;
    const VALID_TOKEN_FAIL = 10006;


    //输入错误
    const INPUT_CAPTCHA_MIS = 1200;
    const INPUT_SMSCODE_MIS = 1201;
    const INPUT_VERIFY_FAIL = 1202;


    //逻辑错误
    const NOT_SET_TRADE_PWD = 1300;
    const NOT_IDENTIFY = 1301;
    const HAD_IDENTIFIED = 1302;
    const NOT_BIND_BANKCARD = 1303;
    const HAD_BIND_BANKCARD = 1304;
    const HAD_SET_TRADE_PWD = 1305;
    const USERNAME_NOT_EXIST = 1306;
    const BIND_BANKCARD_AGAIN = 1307;
    const REPEAT_IDENTIFY = 1308;
    const OTHER_BIND_CARD = 1309;
    const HAD_CHECKED_IN = 1310;
    const BIND_CARD_NO_ERROR = 1311;


    //服务器错误
    const SAVE_USER_FAIL = 1400;
    const SAVE_ADDRESS_FAIL = 1401;
    const SAVE_TRADE_PWD_FAIL = 1402;
    const SAVE_BIND_CARD_FAIL = 1403;
    const MODIFY_BIND_PHONE_FAIL = 1404;

    //第三方合作账号相关错误


    //账号异常
    const ACCOUNT_TRADE_FROZEN = 1500;
    const ACCOUNT_SIGNIN_FROZEN = 1501;
    const ACCOUNT_IDENTIFY_FROZEN = 1502;
    const ACCOUNT_BLANK_MEMBER = 1505;

    const REMOTE_SIGNIN = 1510;

    //服务器异常
    const SERVER_ERROR = 1999;


    protected static $errorArray = array(
        self::API_MIS_PARAMS   => "缺少必要参数",
        self::API_ILLEGAL      => "非法请求", //无效的绑卡请求
        self::API_BUSY         => "操作太频繁啦，休息一下",
        self::API_FAILED       => "接口调用失败",
        self::API_EXPIRE       => "接口请求过期",
        self::API_SMS          => "短信发送失败",
        self::API_ERROR_PARAMS => "参数个数或值错误",

        self::VALID_TOKEN_FAIL => "用户未登录",



    );

    public function __construct($code, $data = array(), $message = "")
    {
        if (empty($message)) {
            $message = isset(self::$errorArray[$code]) ? self::$errorArray[$code] : 'error';
        }
        //异常记录
        $this->debugTrace($code, $message, $data);

        parent::__construct($code, $message, $data);
    }


    static public function dumpAllError()
    {
        var_export(self::$errorArray);
    }

    /**
     * 错误跟踪
     *
     * @param $code
     * @param $message
     * @param array $data
     */
    private function debugTrace($code, $message, $data = array())
    {
        $errormsg = "接口调用失败：错误码：" . $code;
        $errormsg .= "；错误信息：" . $message . PHP_EOL;
        if (!empty($data)) {
            $errormsg .= "额外数据：" . json_encode($data) . PHP_EOL;
        }
        $errormsg .= "追踪信息：" . PHP_EOL . self::getTraceAsString();

        logs($errormsg, 'apicall_debug');
    }
}