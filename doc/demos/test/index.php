<?php
/**
 * @测试方法
 * Created by PhpStorm.
 * Author: Baykier<1035666345@qq.com>
 * Date: 2017/7/16
 * Time: 11:29
 */

/**
 * @pageroute
 */
function index()
{
    $jsonRPCServer = new Lib\jsonRPCServer();
    $jsonRPCServer->addService(loadRpcImpl('TestRpcImpl'));
    $jsonRPCServer->processingRequests();
}