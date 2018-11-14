<?php

namespace App\common;


/**
 * redis cache 命名规则说明：
 * get* 打头的函数为获取redis数据，数据不存在时，从数据库获取并存入redis
 * modify* 打头的函数为更新redis数据
 * save* 打头的函数为 更新redis数据并且更新到数据库。
 */
class BaseCache
{
    public $redis;

    public function __construct()
    {
        $this->redis  = getReidsInstance();
        $this->redis->select(1);
    }

    /**
     * 设置redis某一列
     */
    public function setRedisData($key,$id, $array)
    {
        if(empty($array)){
            return false;
        }
        $ret = $this->redis->ZADD($key, $id, json_encode($array,JSON_UNESCAPED_UNICODE) );
        if(!$ret){
            logs('设置redis缓存失败('.$key.')', 'setRedis');
        }
        return $ret;
    }


    /**
     * 修改redis某一列
     */
    public function saveRedisData($key1,$key2, $id, $array,$oldArray)
    {
        if(empty($array)){
            return false;
        }
        $this->delRedisData($key2,$oldArray);
        $ret = $this->redis->ZADD($key1, $id, json_encode($array,JSON_UNESCAPED_UNICODE) );
        if(!$ret){
            logs('设置redis缓存失败('.$key.')', 'setRedis');
        }
        return $ret;
    }

    /**
     * 删除redis
     */
    public function delRedisData($key, $array)
    {
        if(empty($array)){
            return false;
        }
        $ret = $this->redis->ZREM($key, json_encode($array,JSON_UNESCAPED_UNICODE) );
        if(!$ret){
            logs('删除redis缓存失败('.$key.')', 'setRedis');
        }
        return $ret;
    }

    /**
     * 更新redis
     */
    public function updRedisData($key,$count)
    {
        if(empty($key)){
            return false;
        }
        $sum = $this->redis->ZCARD   ($key);
        $start=0;
        if($sum>$count){
            $stop = $sum-$count-1;
            $ret = $this->redis->ZREMRANGEBYRANK ($key, $start, $stop );
            if(!$ret){
                logs('更新redis缓存失败('.$key.')', 'setRedis');
            }
            return $ret;
        }
        return true;

    }

    /**
     * 取redis某一列
     */
    public function getRedisData($key,$page = 1, $page_size = 20)
    {
        if(empty($key)){
            return false;
        }
        //获取集合开始位置结束位置
        $start = $page_size*($page-1);
        $stop  = $page_size*$page-1;

        $ret = $this->redis->ZREVRANGE ($key, $start, $stop);
        if($ret){
            return json_decode($ret, true);
        }
        return false;
    }

    /**
     * 删除redis键
     */
    public function delRedisKey($key)
    {
        if(empty($key)){
            return false;
        }
        $ret = $this->redis->DEL($key);

        if(!$ret){
            logs('删除redis缓存失败('.$key.')', 'setRedis');
        }
        return true;
    }


    /**
     * 获取所有key
     *
     */
    public function getKeys($key)
    {   
        return $this->redis->keys($key);
    }

    /**
     * 获取hase Key下的所有字段
     */
    public function getFileds($key='')
    {
        if(empty($key))
            return false;
        return $this->redis->HKEYS($key);
    }


    /**
     * 设置hash字段
     */
    public function setHashData($key, $array, $expiration = 864000)
    {
        if(empty($array)){
            return false;
        }
        $hash = $this->null2str($array);
        $ret  = $this->redis->hmset($key, $hash);
        if(!$ret){
            logs('设置redis缓存失败('.$key.')', 'setRedis');
        }else{
            $this->redis->expire($key, $expiration);
        }
        return $ret;
    }


    /**
     * null转空字符串
     * @param  array $array hase数组
     * @return array        把数组中值为null的值改为''
     */
    public function null2str($array)
    {
        $arr = array();
        foreach ($array as $key => $value) {
            if(is_null($value)){
                $arr[$key] = '';
            }else{
                $arr[$key] = $value;
            }
        }
        return $arr;
    }


    /**
     * 获取hash数据     
     *
     */
    public function getHashData($key,$fields = array())
    { 

        if(empty($fields)){
            $fields = $this->getFileds($key);
        }

        $ret = $this->redis->hMget($key, $fields);
        return $ret;
    }

    /**
     * 入队列
     */
    public function getLpush($key,$fields)
    {

        if(empty($key)){
           return false;
        }
        $ret = $this->redis->lpush($key, $fields);
        return $ret;
    }

    /**
     * 出队列
     */
    public function getLrange($key)
    {
        if(empty($key)){
            return false;
        }
        $ret = $this->redis->LRANGE ($key, 0,50);
        return $ret;
    }


    /**
     * 删除 队列中得元素
     */
    public function getLrem($key,$fields)
    {

        if(empty($key)){
            return false;
        }
        $ret = $this->redis->Lrem($key, $fields);
        return $ret;
    }
}
