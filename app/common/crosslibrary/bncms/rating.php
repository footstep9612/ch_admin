<?php
/**
 * Created by PhpStorm.
 * User: tianxufeng
 * Date: 2018/7/9
 * Time: 19:27
 */
namespace App\Common\CrossLibrary\BnCms;
use Model\AuthUser;
error_reporting(E_ALL^E_NOTICE);
class Rating
{
    private $_model = '';
    private $fields = 'id,title,content,pdf_url,cover_img,create_time';
    public  function __construct($model='')
    {
        $this->_model = new \Model\Model('bn_cms');
    }

    /**
     * 根据条件统计帖子数量
     * @param  $input array  请求参数
     * return  string        统计的数量
     */
    public function getCount($input=array())
    {
        //生成Sql条件语句
        $where  = self::generateSql($input);
        //根据条件查询
        if(checkEmpty($where['query']))
        {
            $query  = 'SELECT COUNT(id) as countNum FROM rating_report '.$where['query'];
            $row    = $this->_model->select($query,$where['bindArr']);
            return $row[0]['countNum'];
        }else
        {
            $query  = 'SELECT COUNT(id) as countNum FROM rating_report '.$where['query'];
            $row    = $this->_model->query($query)->rowArr();
            return $row['countNum'];
        }
    }


    /**
     * 根据条件统计帖子数量
     * @param  $input array  请求参数
     * return  array         列表
     */
    public function getPostList($input=array())
    {
        //生成Sql语句
        $where = self::generateSql($input);
        $query = 'SELECT * FROM post_detail '.$where['query'].' order by id desc limit '.$input['start'].','.$input['pageSize'];

        if(!checkEmpty($where['bindArr']))
        {
            $list = $this->_model->query($query)->resultArr();

        }else
        {
            $list = $this->_model->select($query,$where['bindArr']);
        }

        return $list;
    }
    /**
     * 设置page
     * @param  $input  array  输入的参数
     * @param  $mode   string 查询的方式
     */
    public function setpage($input=array()){
        self::generateSql($input);
    }


    /**
     * 生成Sql语句
     * @param  $input  array  输入的参数
     * @param  $mode   string 查询的方式
     */
    public function generateSql($input=array())
    {
        $session = new \Lib\Session();
        //设置条件数组
        $sqlArr  = array();
        //设置防住入字段列表
        $bindArr = array();
        //判断标题和内容
        if(checkEmpty($input['title']))
        {
            array_push($sqlArr,' (title like :title or content like :title)');
            $bindArr[':title'] = '%'.trim($input['title']).'%';
            $session->set('postList.search.title',trim($input['title']));
        }

        //判断用户手机号
        if(checkEmpty($input['content']))
        {
            $auth_user = new AuthUser();
            //获取用户ID
            $uid =$auth_user->getUserId(array('content'=>trim($input['content'])));
            if(checkEmpty($uid))
            {
                //拼接条件
                array_push($sqlArr,' u_id = :uid');
                $bindArr[':uid'] = $uid;
                $session->set('postList.search.content',trim($input['content']));
            }

        }

        //设置分页
        $session->set('postList.search.page',$input['page']);
        //生成where 条件语句
        $whereQuery = count($sqlArr) > 0 ? ' WHERE '.join(' AND ',$sqlArr) : '';

        //return
        return array('query'=>$whereQuery,'bindArr'=>$bindArr);
    }


    /**
     * 批量删除帖子(逻辑删除)
     * @ param string $idList
     * @ return int  返回的ID
     */
    public function BatchDelPost($idList='')
    {
        //删除
        $sql = "UPDATE post_detail SET status = -1,is_essence_top=0,is_essence=0,is_hot_top=0,is_top=0 WHERE id IN ({$idList}) and status != -1";
        return $this->_model->exec($sql);
    }
    /**
     * 预览
     * @ param string $idList
     * @ return int  返回的ID
     */
    public function getMessageById($id)
    {
        $fields = $this->fields;
        $sql = "SELECT $fields FROM rating_report WHERE id={$id} ";
        $list = $this->_model->query($sql)->rowArr();
        return $list;
    }
}