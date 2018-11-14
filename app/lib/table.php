<?php
/**
 * Created by PhpStorm.
 * User: wlb
 * Date: 2017/10/23
 * Time: 18:15
 */

namespace Lib;


class Table
{
    private $_start = 2000001;
    private $_num = 2000000;

    /**
     * 根据总数返回表名
     * @param string $total
     * @param string $table_name
     * @return string
     */
    private function _getTargeTable($total,$table_name)
    {
        if(( $total - $this->_start ) < 0){
            return $table_name;
        }
        $tableId = floor(($total-$this->_start)/$this->_num);
//        print_r($tableId);
        return $table_name.'_'.$tableId;
    }

    /**
     * 根据总数定位表
     * @params string $total
     * @params string $table_name
     * @return string
     */
    public function locateTableName($total,$table_name)
    {
        return $this->_getTargeTable($total,$table_name);
    }

    /**
     * @获取表总数
     * @param $total
     * @return float
     */
    public function getTableNum($total) {
        return  empty($total) ? 1 : ceil($total/$this->_num);
    }

    /**
     * @获取单表最大数
     * @return int
     */
    public function getMaxNum()
    {
        return $this->_num;
    }


    /**
     * 创建表
     * @params string $table
     * @return bool
     */
    public function createTable($table){
        $is_table = $this->query("CREATE TABLE IF NOT EXISTS `".$table."` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '商品ID',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '点击时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;");
        return $is_table;
    }
}