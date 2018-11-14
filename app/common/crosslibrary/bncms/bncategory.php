<?php
/**
 * CMS消息通知表
 */

namespace App\Common\CrossLibrary\BnCms;
class BnCategory
{
    private $_model = '';
    private $fields = 'c_id,category_name,status,is_delete,is_homepage,type,create_time,update_time,sort';

    public  function __construct($model='')
    {
        $this->_model = new \Model\Model('bn_cms');
    }


    /**
     * 根据条件查询总数据量
     * @param       $where            条件
     */
    public  function getTotal($where='1')
    {
        $sql_t = "SELECT count(*) as total_num 
            from bn_category as pd 
            where {$where}";
        $total_arr = $this->_model->query($sql_t)->rowArr();

        return $total_arr['total_num'];
    }

    /**
     * 获取分类列表
     * @param 
     */
    public function getCategoryList()
    {
        $fields = $this->fields;
        $sql = "SELECT $fields from bn_category 
            where is_delete = 0 
            order by sort ";
        $list = $this->_model->query($sql)->resultArr();

        return $list;
    }

     /**
     * @ 获取单条消息信息
     * @param $id  消息ID
     */
    public function getMessageById($id)
    {
        $fields = $this->fields;
        $sql = "SELECT $fields FROM message_notification WHERE id={$id} ";
        $list = $this->_model->query($sql)->rowArr();

        return $list;
    }

    /**
     * 添加分类
     * @param $data  入库数据
     */
    public function addCategory($data)
    {
        if(!is_array($data) || empty($data)){
            return false;
        }
        //获取最新的排序
        $sort_sql = "SELECT sort FROM bn_category ORDER BY sort DESC LIMIT 1 ";
        $max_sort = $this->_model->query($sort_sql)->rowArr();
        $data['sort'] = $max_sort['sort'] + 1;
        $fields = '';
        $fields_val = '(';
        foreach($data as $k=>$v){
            $fields .= "`$k`,";
            $fields_val .= "'{$v}',";
        }
        $fields_val = trim($fields_val,',');
        $fields_val .= ')';
        $fields = trim($fields,',');
        $sql = "INSERT INTO bn_category ($fields) VALUES $fields_val";

        return $this->_model->query($sql);
    }

    /**
     * 修改消息
     * @param $data  修改数据
     */
    public function updMessage($data,$id)
    {
        if(!is_array($data) || empty($data)){
            return false;
        }
        $fields = '';
        foreach($data as $k=>$v){
            $fields .= "`$k`='{$v}',";
        }
        $fields = trim($fields,',');
        $sql = "UPDATE message_notification SET $fields WHERE id={$id} ";

        return $this->_model->query($sql);
    }

    /**
     * 修改排序
     * @param $data  数据
     */
    public function updStatus($data,$id)
    {
        if(!is_array($data) || empty($data)){
            return false;
        }
        $fields = '';
        foreach($data as $k=>$v){
            $fields .= "`$k`='{$v}',";
        }
        $fields = trim($fields,',');
        $sql = "UPDATE bn_category SET $fields WHERE c_id={$id} ";

        return $this->_model->query($sql);
    }

}