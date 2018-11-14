<?php
/**
 * Created by PhpStorm.
 * Author: Baykier<1035666345@qq.com>
 * Date: 2017/8/23
 * Time: 16:33
 */
namespace Lib;

class Label {
    protected static $map = array();

    public static function label($key,$default = null) {
        return isset(static::$map[$key]) ? static::$map[$key] : null;
    }
    public static function labels() {
        return static::$map;
    }
}