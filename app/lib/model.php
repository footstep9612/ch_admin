<?php
/**
 * Created by PhpStorm.
 * Author: Baykier<1035666345@qq.com>
 * Date: 2017/10/10
 * Time: 18:04
 */
namespace Lib;

use Model\Model as BaseModel;

class Model extends BaseModel
{
    /**
     * @返回获取结果集数组的生成器
     * @return \Generator
     */
    public function yieldResultArr()
    {
        while ($row = $this->db->rowArr()) {
            yield $row;
        }
    }

    /**
     * @返回获取结果集对象的生成器
     * @return \Generator
     */
    public function yieldResult()
    {
        while ($object = $this->db->row()) {
            yield $object;
        }
    }
}