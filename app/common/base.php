<?php
/**
 * Description: RPC基类，
 * 检查用户是否登录
 */

namespace App\common;

use Lib\Session;
use Lib\JsonRpcService;
use Model\AuthAccountRatethrottle;
use Model\AuthIpRatethrottle;
use App\user\exception\AllErrorException;


class Base extends JsonRpcService
{

    protected $userId;

    /**
     * @var Session
     */
    protected $sessionHandle;

    public function __construct()
    {
        $this->sessionHandle = new Session();
    }

    /**
     * 检查登录状态
     *
     * @return mixed
     * @throws AllErrorException
     */
    protected function checkLoginStatus()
    {
        $userData = $this->sessionHandle->get(USER_DATA_SKEY);

        if (empty($userData) || empty($userData['user_id'])) {
            return false;
        }

        //记录user_agent 信息
        // $this->logUserAgent('user_agent');
        //设备检查
        // config("MULTIPLE_SIGNIN") && $this->deviceProtect($userData['user_id']);

        return $userData['user_id'];
    }
    /**
     * 记录当前登录设备session id
     *
     * @param $userId
     */
    protected function recordSid($userId)
    {
        $redis = getReidsInstance();
        $device = getAgent('platform');
        if ($this->checkedDevice($device)) {
            $redis->hset(DEVICE_SID_CONTAINER, $userId, session_id());
        }
    }
    //清除限制记录数据
    protected function resetRatethrottle($userId, $handles, $resetIp = false)
    {
        //清除用户记录
        $accountThrottle = new AuthAccountRatethrottle();
        $accountThrottle->resetFailedCounter($userId, $handles);

        //是否同时清除ip
        if ($resetIp) {
            $ipThrottle = new AuthIpRatethrottle();
            $ipThrottle->reset($handles);
        }
    }
    //检查是否在受限制列表
    protected function checkedDevice($device)
    {
        $device = strtoupper($device);
        $protectedDevice = empty(config('PROTECTED_DEVICES')) ? array('IOS', 'ANDROID') : config('PROTECTED_DEVICES');

        return in_array($device, $protectedDevice);
    }



}