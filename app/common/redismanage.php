<?php

namespace App\common;


class RedisManage
{



    const USER_RULE_CONTROLLER_ACTION = 'dh:user_rule_controller_action:cache';//用户权限节点缓存



    public static function setHash($key,$val,$lifetime=3600){

        try {
            $redis = getReidsInstance();
            $redis->hMSet($key,$val);
            $redis->setTimeout($key,$lifetime);
            return ;
        } catch (\Exception $e) {
            echo $e->getMessage();
            logs('setHash',json_encode($e->getMessage()));
            exit();
        }


    }

    public static function getHash($key,$fields=''){
        $redis = getReidsInstance();
        if (! $redis->exists($key)) {
            return false;
        }
        if (!empty($fields)){
            if(is_string($fields)){
                $fields=explode(',',$fields);
            }
            $fields = array_filter($fields);
            $fields = array_unique($fields);
            $data=$redis->hMGet($key,$fields);
        }else{
            $data=$redis->hGetAll($key);
        }
        return $data;
    }
}