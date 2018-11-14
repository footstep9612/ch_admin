<?php
/**
 * CMS消息通知表
 */

namespace App\Common\CrossLibrary\BnCms;
class MessageNotification
{
    private $_model = '';
    private $fields = 'id,mes_title,mes_content,url,status,send_time,create_time';

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
            from message_notification as pd 
            where {$where}";
        $total_arr = $this->_model->query($sql_t)->rowArr();

        return $total_arr['total_num'];
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
     * @ 获取消息信息列表
     * @param $page_obj  分页对象
     * @param $where  条件
     */
    public function getMessageList($page_obj,$where='1')
    {
        $fields = $this->fields;
        $sql = "SELECT $fields from message_notification as pd 
            where {$where} 
            order by send_time desc 
            limit {$page_obj->start},{$page_obj->offset}";
        $list = $this->_model->query($sql)->resultArr();

        return $list;
    }

    /**
     * 获取消息详情
     */
    public function getMessageByWhere($where,$field='')
    {
        if(empty($where)){
            return array();
        }
        $field = empty($field) ? $this->field : $field;
        if(is_array($where)){
            $new_where = [];
            foreach($where as $k=>$v){
                $new_where[] = "{$k} =  \"{$v}\"";
            }
            $new_where = implode(' AND ', $new_where);
            $sql = "SELECT $field FROM message_notification WHERE $new_where";
        }else{

            $sql = "SELECT $field FROM message_notification WHERE $where";
        }

        return $this->_model->query($sql)->rowArr();
    }

    /**
     * 添加消息
     * @param $data  入库数据
     */
    public function addMessage($data)
    {
        if(!is_array($data) || empty($data)){
            return false;
        }
        $fields = '';
        $fields_val = '(';
        foreach($data as $k=>$v){
            $fields .= "`$k`,";
            $fields_val .= "'{$v}',";
        }
        $fields_val = trim($fields_val,',');
        $fields_val .= ')';
        $fields = trim($fields,',');
        $sql = "INSERT INTO message_notification ($fields) VALUES $fields_val";

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
     * 修改状态
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
        $sql = "UPDATE message_notification SET $fields WHERE id={$id} ";

        return $this->_model->query($sql);
    }

}