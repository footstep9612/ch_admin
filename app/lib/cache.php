<?php
/**
 * Created by PhpStorm.
 * Author: Baykier<1035666345@qq.com>
 * Date: 2017/7/4
 * Time: 15:23
 */
namespace Lib;

use \App\common\RedisManage;
use \Lib\Label;

class Cache extends Label{

    const TYPE_STRING = 1;//字符串类型
    const TYPE_LIST = 2;//列表类型
    const TYPE_HASH = 4;//哈希类型
    const TYPE_SET = 8;//集合类型

    //缓存label
    const LABEL_RULE_CONTROLLER_ACTION = RedisManage::USER_RULE_CONTROLLER_ACTION;

    //缓存配置
    protected static $map = array(
        self::LABEL_RULE_CONTROLLER_ACTION => array(
            'name' => '控制器操作节点缓存',
            'type' => self::TYPE_STRING,
            'lifetime' => 3600,
        ),
    );

    /**
     * @设置字符串缓存
     * @param $label
     * @param $key
     * @param $value
     * @param null $lifetime
     * @return bool
     * @throws \Exception
     */
    public static function set($label, $key, $value,$lifetime = null)
    {
        if (!self::label($label) || empty($key) || empty($value))
        {
            return false;
        }
        $key = self::getKey($label,$key);
        $result = getReidsInstance()->set($key,$value);
        $lifetime = $lifetime ?: self::$map[$label]['lifetime'];
        self::setTimeOut($key,$lifetime);
        return $result;
    }

    /**
     * @获取字符串缓存
     * @param $label
     * @param $key
     * @return bool
     * @throws \Exception
     */
    public static function get($label, $key)
    {
        if (!self::label($label) || empty($key))
        {
            return false;
        }
        $key = self::getKey($label,$key);
        return getReidsInstance()->get($key);
    }

    /**
     * @清除缓存
     * @param $label
     * @param null $key 为null时清除所有label前缀的缓存
     * @return bool
     * @throws \Exception
     */
    public static function clear($label,$key = null)
    {
        if (!self::label($label))
        {
            return false;
        }
        //获取所有包含$cacheKey的缓存前缀key
        if (is_null($key)) {
            $cacheKey = getReidsInstance()->keys(sprintf("%s*",$label));
        }else {
            $cacheKey = self::getKey($label,$key);
        }
        return getReidsInstance()->delete($cacheKey);
    }

    /**
     * @生成缓存key
     * @param $label
     * @param $key
     * @return string
     */
    protected static function getKey($label,$key)
    {
        return sprintf("%s-%s",$label,(string) $key);
    }

    /**
     * @缓存hash类型数据
     * @param $key
     * @param $value
     * @param null $lifetime
     */
    public static function setHash($label,$key,array $value,$lifetime = null)
    {
        if (!self::label($label) || empty($value))
        {
            return false;
        }
        $key = self::getKey($label,$key);
        $result = getReidsInstance()->hMSet($key,$value);
        $lifetime = $lifetime ?: self::$map[$label]['lifetime'];
        self::setTimeOut($key,$lifetime);
        return $result;
    }

    /**
     * @获取hash缓存
     * @param $label
     * @param $key
     * @param null $fields
     * @return bool
     * @throws \Exception
     */
    public static function getHash($label,$key,$fields = null)
    {
        if (!self::label($label) || empty($key)) {
            return false;
        }
        $key = self::getKey($label, $key);
        if (! getReidsInstance()->exists($key)) {
            return false;
        }
        if ((is_array($fields) && !empty($fields)) || (is_string($fields) && $fields = explode(',', $fields))) {
            $fields = array_filter($fields);
            $fields = array_unique($fields);
            if ($fields) {
                $data =  getReidsInstance()->hMGet($key, $fields);
                if (!empty(array_values($data))) {
                    return $data;
                }
            }
        }
        //默认获取所有
        return getReidsInstance()->hGetAll($key);
    }

    /**
     * @设置一个集合缓存
     * @param $label
     * @param $key
     * @param $value
     * @param null $lifetime
     * @return bool
     * @throws \Exception
     */
    public static function setArray($label, $key, $value, $lifetime = null)
    {
        if (!self::label($label) || empty($key) || empty($value))
        {
            return false;
        }
        $key = self::getKey($label,$key);
        $result = true;
        foreach ($value as $item) {
            $result &= getReidsInstance()->sAdd($key,$item);
        }
        $lifetime = $lifetime ?: self::$map[$label]['lifetime'];
        self::setTimeOut($key,$lifetime);
        return $result;
    }

    /**
     * @获取集合缓存
     * @param $label
     * @param $key
     * @return bool
     * @throws \Exception
     */
    public static function getArray($label, $key)
    {
        if (!self::label($label) || empty($key)) {
            return false;
        }
        $key = self::getKey($label,$key);
        return getReidsInstance()->sMembers($key);
    }

    /**
     * @设置缓存时间
     * @param $key
     * @param $lifetime
     * @return bool
     */
    protected static function setTimeOut($key,$lifetime)
    {
        return getReidsInstance()->setTimeout($key,(int) $lifetime) !== false;
    }
}