<?php
/**
 * Created by PhpStorm.
 * User: yanghua
 * Date: 2018/7/9
 * Time: 20:04
 */

namespace App\Common\CrossLibrary\BnCms;
class BnCoinInfo
{
    private $_model = '';

    public  function __construct($model='')
    {
        $this->_model = new \Model\Model('bn_cms');
    }

    /**
     * @ 根据条件获取币种ID
     * @ params $where Array  条件
     */
    public  function getCoinId($where=array())
    {

        //转换陈Sql语句
        $query = 'SELECT coin_id from bn_coin_info where coin_name LIKE \'%'.$where['coin_name'].'%\' or coin_symbol like \'%'.$where['coin_name'].'%\'';
        //查询
        $coinInfo = $this->_model->query($query)->resultArr();
        //判断
        if(!checkEmpty($coinInfo))
        {return false;}
        //return
        return array_column($coinInfo,'coin_id');
    }

    /**
     * @ 根据条件生成防注入sql
     * @ params $where Array  条件
     */
    public static function bindWhere($where=array())
    {
        //设置条件数组
        $whereArr = array();
        //设置防住入数组
        $bindArr = array();
        //将数组格式转换
        foreach ($where as $k => $v)
        {
            array_push($whereArr,' '.$k.'=:'.$k.' ');

            $bindArr[':'.$k] = $v;
        }
        //拼接成字符串
        $whereString = join(' and ',$whereArr);
        //return
        return array('where'=>$whereString,'bindVal'=>$bindArr);
    }

    public function getCoinAll($id=array())
    {
        //判断是否有ID列表
        if(checkEmpty($id))
        {
            $sql = 'SELECT id,coin_symbol FROM bn_coin_info WHERE id in('.join(',',$id).')';
        }
        else
        {
            $sql = 'SELECT id,coin_symbol FROM bn_coin_info';
        }

        return $this->_model->query($sql)->resultArr();

    }

    /**
     * 获取可以设置的奖品列表
     */
    public function getExChangeList()
    {
        $sql = 'SELECT coin_symbol,coin_id as id FROM bn_coin_info WHERE coin_symbol in(\'ADA\',\'EOS\',\'BTC\')';
        return $this->_model->query($sql)->resultArr();
    }
    
    /**
     * 根据条件获取币信息
     * @param $where Array  条件
     */
    public function getCoinInfo($id=array())
    {
        if(empty($id)){
            return array();
        }
        $id_str = implode(',',$id);
        $sql = "SELECT id,coin_symbol,coin_name FROM bn_coin_info where id IN ($id_str) ";
        $data = $this->_model->query($sql)->resultArr();
        $return = array();
        if($data){
            foreach($data as $k=>$v){
                $return[$v['id']] = $v;
            }
        }

        return $return;
    }

    /**
     * 获取币信息
     * @param 
     */
    public function getCoinList()
    {
        $sql = "SELECT id,coin_symbol,coin_name FROM bn_coin_info ";
        $data = $this->_model->query($sql)->resultArr();

        return $data;
    }

    /**
     * 修改
     * @param $where  条件
     */
    public function updateCoin($data,$where='1')
    {
        if(!is_array($data) || empty($data)){
            return false;
        }
        $fields = '';
        foreach($data as $k=>$v){
            $fields .= "`$k`='{$v}',";
        }
        $fields = trim($fields,',');
        $sql = "UPDATE bn_coin_info SET $fields WHERE $where ";

        return $this->_model->exec($sql);
    }

    /**
     * 修改帖子数量
     * @param $where  条件
     */
    public function updatePostNum($fields,$where='')
    {
        $sql = "UPDATE bn_coin_info SET $fields WHERE $where ";

        return $this->_model->exec($sql);
    }

    /**
     * 执行自定义sql语句
     * @param 
     */
    public function customStatement($sql,$sign=1)
    {
        if(empty($sql)){
            return array();
        }
        if($sign == 1){
            return $this->_model->query($sql);
        }else{
            return $this->_model->exec($sql);
        }
    }
}