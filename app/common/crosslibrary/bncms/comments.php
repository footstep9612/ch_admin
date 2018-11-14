<?php
/**
 * CMS评论表
 */
namespace App\Common\CrossLibrary\BnCms;
use Model\AuthUser;
use App\Common\CrossLibrary\BnCms\PostDetail;
error_reporting(E_ALL^E_NOTICE);
class Comments
{
    private $_model = '';
    protected $field = "id,a_id,u_id,type,f_id,content,like_nums,create_time,admin_id,status,update_time";

    public function __construct($model='')
    {
        $this->_model = new \Model\Model('bn_cms');
    }

    /**
     * 根据条件查询总数据量
     * @param       $where            条件
     */
    public function getTotal($where='1')
    {
        $sql_t = "SELECT count(*) as total_num 
            from comments 
            where {$where}";
        $total_arr = $this->_model->query($sql_t)->rowArr();

        return $total_arr['total_num'];
    }

    /**
     * 获取评论列表
     * @param $page_obj  分页对象
     * @param $where  条件
     */
    public function getCommentsAll($page_obj,$where='1')
    {
        $fields = $this->field;
        $sql = "SELECT $fields from comments 
            where $where 
            order by id desc 
            limit {$page_obj->start},{$page_obj->offset}";
        $list = $this->_model->query($sql)->resultArr();

        return $list;
    }

    /**
     * 数据处理
     * @param $where  条件
     */
    public function dataProcessing($list)
    {
        if(empty($list)){
            return array();
        }
        $AuthUser = new AuthUser();
        $PostDetail = new PostDetail();
        //获取用户手机号--昵称条件
        $uidList  = array_column($list,'u_id');
        //获取用户手机号列表
        $uidArr   = $AuthUser->fields('id,phone,nickname')->whereIn('id',join(',',$uidList))->get()->resultArr();
        $nameArr  = array_column($uidArr,'nickname','id');
        $uidArr   = array_column($uidArr,'phone','id');
        //获取帖子标题
        $postData = $PostDetail->getPostListOne("id = ".$list[0]['a_id']);
        $commentList = array();
        //整合数据
        foreach($list as $k => $v)
        {
            $commentList[$k] = $v;
            $commentList[$k]['phone']        = $uidArr[$v['u_id']] ? $uidArr[$v['u_id']] : '';
            $commentList[$k]['nickname']     = $nameArr[$v['u_id']] ? $nameArr[$v['u_id']] : '';
            $commentList[$k]['title']     = isset($postData['title']) ? $postData['title'] : '';
        }

        return $commentList;
    }

    /**
     * 修改状态
     * @param $where  条件
     */
    public function updateStatus($data,$where='1')
    {
        if(!is_array($data) || empty($data)){
            return false;
        }
        $fields = '';
        foreach($data as $k=>$v){
            $fields .= "`$k`='{$v}',";
        }
        $fields = trim($fields,',');
        $sql = "UPDATE comments SET $fields WHERE $where ";

        return $this->_model->query($sql);
    }

}