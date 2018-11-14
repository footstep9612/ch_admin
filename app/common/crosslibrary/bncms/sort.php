<?php
/**
 * CMS消息通知表
 */

namespace App\Common\CrossLibrary\BnCms;
class Sort
{
    private $_model = '';
    private $fields = 'c_id,category_name,status,is_delete,is_homepage,type,create_time,update_time,sort';
    public  function __construct($model='')
    {
        $this->_model = new \Model\Model('bn_cms');
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
        $sql = "UPDATE search_circle_recommend SET $fields WHERE id={$id} ";
        return $this->_model->query($sql);
    }

}