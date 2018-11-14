<?php
/**
 * Created by PhpStorm.
 * Author: Baykier<1035666345@qq.com>
 * Date: 2017/7/16
 * Time: 13:51
 */
namespace App\test\rpcserverimpl;

use App\common\ApiBag;
use \Lib\JsonRpcService as BaseService;

class TestRpcImpl extends BaseService
{
    /**
     * @测试发送短信验证码接口
     * @JsonRpcMethod
     * @param $params
     * @return array
     */
    public function sendCode($params)
    {
        logs((array)$params,'test_api_' . __FUNCTION__);
        return array(
            'code' => 200,
            'message' => 'success',
            'data' => array(
                'send_status' => true,
            ),
        );
    }

    /**
     * @JsonRpcMethod
     * @param $params
     */
    public function bindBankCard($params)
    {
        logs((array)$params,'test_api_' . __FUNCTION__);
        return array(
            'code' => 200,
            'message' => 'success',
            'data' => array(
                'send_status' => true,
            ),
        );
    }

    /**
     * @测试查询订单状态接口
     * @JsonRpcMethod
     * @param $params
     */
    public function pushUserOrder($params)
    {
        $apiBag = new ApiBag();
        $check = $apiBag->parseParams($params);
        if (!$check) {
            return array(
                'code' => 400,
                'message' => '验证签名失败',
                'data' => array(),
            );
        }
        logs($check,'test_api_check_sign' . __FUNCTION__);
        return array(
            'code' => 200,
            'message' => 'success',
            'data' => array(
                'send_status' => true,
            ),
        );
    }
}