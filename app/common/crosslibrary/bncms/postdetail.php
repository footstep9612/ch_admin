<?php
/**
 * Created by PhpStorm.
 * User: tianxufeng
 * Date: 2018/7/9
 * Time: 19:27
 */
namespace App\Common\CrossLibrary\BnCms;
use Lib\Model;
use Model\AuthUser;
error_reporting(E_ALL^E_NOTICE);
class PostDetail
{
    private $_model = '';
    protected $field = "id,c_id,u_id,title,content,img_urls,like_nums,create_time,admin_id,status,is_top,is_hot_top,is_essence,top_sort,hot_top_sort";

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
            $query  = 'SELECT COUNT(id) as countNum FROM post_detail '.$where['query'];
            $row    = $this->_model->select($query,$where['bindArr']);
            return $row[0]['countNum'];
        }else
        {
            if($input['source']==NULL){
                $query  = 'SELECT COUNT(id) as countNum FROM post_detail '.$where['query'];
            }else{
                $query  = 'SELECT COUNT(id) as countNum FROM post_detail where status=0 AND source='.$input['source'];
            }
            $row    = $this->_model->query($query)->rowArr();
            return $row['countNum'];
        }
    }

    /**
     * 根据条件统计帖子数量
     * @param  $input array  请求参数
     * return  string        统计的数量
     */
    public function getDeletedCount($input=array())
    {
        //生成Sql条件语句
        $where  = self::coinDeleteDSql($input);
        //根据条件查询
        if(checkEmpty($where['query']))
        {
            $query  = 'SELECT COUNT(id) as countNum FROM post_detail '.$where['query'];
            $row    = $this->_model->select($query,$where['bindArr']);
            return $row[0]['countNum'];
        }else
        {
            $query  = 'SELECT COUNT(id) as countNum FROM post_detail where status=-1';
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
        if(!checkEmpty($where['bindArr']))
        {
            if($input['source']==NULL){
                $query = 'SELECT * FROM post_detail '.$where['query'].' where `status`=0 order by id desc limit '.$input['start'].','.$input['pageSize'];
                $list = $this->_model->query($query)->resultArr();
                //$list = $this->_model->select($query,$where['bindArr']);
            }else{
                $query = 'SELECT * FROM post_detail '.$where['query'].' where `status`=0 and `source`='.$input['source'].' order by id desc limit '.$input['start'].','.$input['pageSize'];
                $list = $this->_model->query($query)->resultArr();
            }
        }else{
            $query = 'SELECT * FROM post_detail '.$where['query'].' order by id desc limit '.$input['start'].','.$input['pageSize'];
            $list = $this->_model->select($query,$where['bindArr']);
        }
        //判断
        if(count($list) <= 0)
        {
            return array();
        }
        //统计用户手机号--昵称条件
        $uidList  = array_column($list,'u_id');
        //统计币种
        $coinList = array_column($list,'c_id');
        //统计评论数
        $comList  = array_column($list,'id');
        //获取用户列表实例
        $AuthUser = new AuthUser();
        //获取用户手机号列表
        $uidArr   = $AuthUser->fields('id,phone,nickname')->whereIn('id',join(',',$uidList))->get()->resultArr();
        $nameArr  = array_column($uidArr,'nickname','id');
        $uidArr   = array_column($uidArr,'phone','id');
        //获取用户币种列表
        $coinSql  = 'SELECT coin_id,coin_name FROM bn_coin_info where coin_id in ('.join(',',$coinList).')';
        $coinArr  =  $this->_model->query($coinSql)->resultArr();
        //获取币种列表
        $coinArr  = array_column($coinArr,'coin_name','coin_id');
        //获取用户评论列表
        $common   = 'SELECT a_id,count(a_id) as commonNum FROM comments where a_id in('.join(',',$comList).') group by a_id';
        $commonArr= $this->_model->query($common)->resultArr();
        $commonArr= array_column($commonArr,'commonNum','a_id');
        //管理员信息
        $admin_ids = array_filter(array_unique(array_column($list, 'del_admin_id')));
        $adminModel = new \Model\AdminUser();
        $admin_list = $adminModel->getAdminList($admin_ids);
        $postList = array();
        //整合数据
        foreach($list as $k => $v)
        {
            $postList[$k] = $v;
            $postList[$k]['phone']        = $uidArr[$v['u_id']] ? $uidArr[$v['u_id']] : '';
            $postList[$k]['coin_name']    = $coinArr[$v['c_id']] ? $coinArr[$v['c_id']] : '';
            $postList[$k]['com_num']      = $commonArr[$v['id']] ? $commonArr[$v['id']] : 0;
            $postList[$k]['nickname']     = $nameArr[$v['u_id']] ? $nameArr[$v['u_id']] : '';
            $postList[$k]['img_urls']     = checkEmpty($v['img_urls']) ? explode(',',$v['img_urls']):false;
            $contentLen = mb_strlen(strip_tags($v['content']),'utf-8');
            //判断
            $postList[$k]['show_detail']  =  $contentLen > 80 ? true : false;
            $postList[$k]['content']      =  $contentLen > 80 ? mb_substr($v['content'],0,80,'utf-8') : $v['content'];
            //返回隐藏数据
            $postList[$k]['hide_content'] = mb_substr($v['content'],80,-1);
            $postList[$k]['admin_name'] = isset($admin_list[$v['del_admin_id']]) ? $admin_list[$v['del_admin_id']]['name'] : '';
        }
        return $postList;
    }
    /**
     * 根据条件统计帖子数量
     * @param  $input array  请求参数
     * return  array         已删帖子列表
     */
    public function deletedList($input=array())
    {
        //生成Sql语句
        $where = self::coinDeleteDSql($input);
        //$query = 'SELECT * FROM post_detail '.$where['query'].'where status=-1 order by id desc limit '.$input['start'].','.$input['pageSize'];
        if(!checkEmpty($where['bindArr']))
        {
            $query = 'SELECT * FROM post_detail '.$where['query'].'where status=-1 order by id desc limit '.$input['start'].','.$input['pageSize'];

            $list = $this->_model->query($query)->resultArr();
        }else
        {
            $query = 'SELECT * FROM post_detail '.$where['query'].' order by id desc limit '.$input['start'].','.$input['pageSize'];
            $list = $this->_model->select($query,$where['bindArr']);
        }
        //echo $query;die;
        //判断
        if(count($list) <= 0)
        {
            return array();
        }
        //统计用户手机号--昵称条件
        $uidList  = array_column($list,'u_id');
        //统计币种
        $coinList = array_column($list,'c_id');
        //统计评论数
        $comList  = array_column($list,'id');
        //获取用户列表实例
        $AuthUser = new AuthUser();
        //获取用户手机号列表
        $uidArr   = $AuthUser->fields('id,phone,nickname')->whereIn('id',join(',',$uidList))->get()->resultArr();
        $nameArr  = array_column($uidArr,'nickname','id');
        $uidArr   = array_column($uidArr,'phone','id');
        //获取用户币种列表
        $coinSql  = 'SELECT coin_id,coin_name FROM bn_coin_info where coin_id in ('.join(',',$coinList).')';
        $coinArr  =  $this->_model->query($coinSql)->resultArr();
        //获取币种列表
        $coinArr  = array_column($coinArr,'coin_name','coin_id');
        //获取用户评论列表
        $common   = 'SELECT a_id,count(a_id) as commonNum FROM comments where a_id in('.join(',',$comList).') group by a_id';
        $commonArr= $this->_model->query($common)->resultArr();
        $commonArr= array_column($commonArr,'commonNum','a_id');
        //管理员信息
        $admin_ids = array_filter(array_unique(array_column($list, 'del_admin_id')));
        $adminModel = new \Model\AdminUser();
        $admin_list = $adminModel->getAdminList($admin_ids);
        $postList = array();
        //整合数据
        foreach($list as $k => $v)
        {
            $postList[$k] = $v;
            $postList[$k]['phone']        = $uidArr[$v['u_id']] ? $uidArr[$v['u_id']] : '';
            $postList[$k]['coin_name']    = $coinArr[$v['c_id']] ? $coinArr[$v['c_id']] : '';
            $postList[$k]['com_num']      = $commonArr[$v['id']] ? $commonArr[$v['id']] : 0;
            $postList[$k]['nickname']     = $nameArr[$v['u_id']] ? $nameArr[$v['u_id']] : '';
            $postList[$k]['img_urls']     = checkEmpty($v['img_urls']) ? explode(',',$v['img_urls']):false;
            $contentLen = mb_strlen(strip_tags($v['content']),'utf-8');
            //判断
            $postList[$k]['show_detail']  =  $contentLen > 80 ? true : false;
            $postList[$k]['content']      =  $contentLen > 80 ? mb_substr($v['content'],0,80,'utf-8') : $v['content'];
            //返回隐藏数据
            $postList[$k]['hide_content'] = mb_substr($v['content'],80,-1);
            $postList[$k]['admin_name'] = isset($admin_list[$v['del_admin_id']]) ? $admin_list[$v['del_admin_id']]['name'] : '';
        }
        return $postList;
    }
    /**
     * 生成已删除帖子列表Sql语句
     * @param  $input  array  输入的参数
     * @param  $mode   string 查询的方式
     */
    public function coinDeleteDSql($input=array())
    {
        $session = new \Lib\Session();
        //设置条件数组
        $sqlArr  = array();
        //设置防住入字段列表
        $bindArr = array();
        //删除Session
        $session->del('coin_deleted.search');
        //判断标题和内容
        if(checkEmpty($input['title']))
        {
            array_push($sqlArr,' (title like :title or content like :title) and status=-1');
            $bindArr[':title'] = '%'.trim($input['title']).'%';
            $session->set('coin_deleted.search.title',trim($input['title']));
        }

        //判断用户手机号
        if(checkEmpty($input['phone']))
        {
            $auth_user = new AuthUser();
            //获取用户ID
            $uid =$auth_user->getUserId(array('phone'=>trim($input['phone'])));
            if(checkEmpty($uid))
            {
                //拼接条件
                array_push($sqlArr,' u_id = :uid and status=-1');
                $bindArr[':uid'] = $uid;
                $session->set('coin_deleted.search.phone',trim($input['phone']));
            }

        }

        //判断用户币种
        if(checkEmpty($input['money']))
        {

            $bn_coin = new BnCoinInfo();
            //获取币种Id
            $coin_id = $bn_coin->getCoinId(array('coin_name'=>trim($input['money'])));
            if(checkEmpty($coin_id))
            {
                //写入数组
                array_push($sqlArr,' c_id in (:coin_id) and status=-1');
                $bindArr[':coin_id'] = join(',',$coin_id);
                $session->set('coin_deleted.search.money',trim($input['money']));
            }
        }

        //判断发帖时间
        if(checkEmpty($input['starttime']) && checkEmpty($input['endtime']))
        {
            array_push($sqlArr,' create_time between :start and :end and status=-1');
            $bindArr[':start'] = trim($input['starttime']);
            $bindArr[':end']   = trim($input['endtime']);
            $session->set('coin_deleted.search.starttime',trim($input['starttime']));
            $session->set('coin_deleted.search.endtime',trim($input['endtime']));
        }

        //设置分页
        $session->set('coin_deleted.search.page',$input['page']);

        //生成where 条件语句
        $whereQuery = count($sqlArr) > 0 ? ' WHERE '.join(' AND ',$sqlArr) : '';
        //return
        return array('query'=>$whereQuery,'bindArr'=>$bindArr);
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
        //删除Session
        $session->del('postList.search');
        //判断标题和内容
        if(checkEmpty($input['title']))
        {
            array_push($sqlArr,' (title like :title or content like :title) and status=0');
            $bindArr[':title'] = '%'.trim($input['title']).'%';
            $session->set('postList.search.title',trim($input['title']));
        }
        //判断数据来源

        if(checkEmpty($input['source'])||$input['source']!=NULL)
        {
            array_push($sqlArr,' source = :source and status=0');
            $bindArr[':source'] = trim($input['source']);
            $session->set('postList.search.source',trim($input['source']));
        }
        //判断用户手机号
        if(checkEmpty($input['phone']))
        {
            $auth_user = new AuthUser();
            //获取用户ID
            $uid =$auth_user->getUserId(array('phone'=>trim($input['phone'])));
            if(checkEmpty($uid))
            {
                //拼接条件
                array_push($sqlArr,' u_id = :uid and status=0');
                $bindArr[':uid'] = $uid;
                $session->set('postList.search.phone',trim($input['phone']));
            }

        }

        //判断用户币种
        if(checkEmpty($input['money']))
        {

            $bn_coin = new BnCoinInfo();
            //获取币种Id
            $coin_id = $bn_coin->getCoinId(array('coin_name'=>trim($input['money'])));
            if(checkEmpty($coin_id))
            {
                //写入数组
                array_push($sqlArr,' c_id in (:coin_id) and status=0');
                $bindArr[':coin_id'] = join(',',$coin_id);
                $session->set('postList.search.money',trim($input['money']));
            }
        }

        //判断发帖时间
        if(checkEmpty($input['starttime']) && checkEmpty($input['endtime']))
        {
            array_push($sqlArr,' create_time between :start and :end and status=0');
            $bindArr[':start'] = trim($input['starttime']);
            $bindArr[':end']   = trim($input['endtime']);
            $session->set('coin_deleted.search.starttime',trim($input['starttime']));
            $session->set('postList.search.endtime',trim($input['endtime']));
        }

        //设置分页
        $session->set('postList.search.page',$input['page']);

        //生成where 条件语句
        $whereQuery = count($sqlArr) > 0 ? ' WHERE '.join(' AND ',$sqlArr) : '';
        //return
        return array('query'=>$whereQuery,'bindArr'=>$bindArr);
    }

    /**
     * 根据条件查询总数据量
     * @param       $where            条件
     */
    public  function getTotal($where='1')
    {
        $sql_t = "SELECT count(*) as total_num 
            from post_detail 
            where {$where}";
        $total_arr = $this->_model->query($sql_t)->rowArr();

        return $total_arr['total_num'];
    }

    /**
     * 获取帖子列表
     * @param $page_obj  分页对象
     * @param $where  条件
     */
    public function getPostListAll($page_obj,$where='1')
    {
        $fields = $this->field;
        $sql = "SELECT $fields from post_detail 
            where $where 
            order by id desc 
            limit {$page_obj->start},{$page_obj->offset}";
        $list = $this->_model->query($sql)->resultArr();

        return $list;
    }

    /**
     * 获取圈子置顶帖子列表
     * @param $where  条件
     */
    public function getTopPostList($where='1',$field='',$sign='1')
    {
        $fields = empty($field) ? $this->field : $field;
        $sql = "SELECT $fields from post_detail 
            where $where ";
        $list = $this->_model->query($sql)->resultArr();
        if($sign == 2){
            if(!empty($list)){
                $newList = array();
                foreach($list as $k=>$v){
                    $newList[$v['id']] = $v;
                }
                return $newList;
            }
        }

        return $list;
    }

    /**
     * 获取帖子列表
     * @param $page_obj  分页对象
     * @param $where  条件
     */
    public function getPostListOne($where='1')
    {
        $fields = $this->field;
        $sql = "SELECT $fields from post_detail 
            where $where ";
        $list = $this->_model->query($sql)->rowArr();

        return $list;
    }

    /**
     * 数据处理
     * @param $page_obj  分页对象
     * @param $where  条件
     */
    public function dataProcessing($list)
    {
        if(empty($list)){
            return array();
        }
        //统计用户手机号--昵称条件
        $uidList  = array_column($list,'u_id');
        //统计币种
        $coinList = array_column($list,'c_id');
        //统计评论数
        $comList  = array_column($list,'id');
        //获取用户列表实例
        $AuthUser = new AuthUser();
        //获取用户手机号列表
        $uidArr   = $AuthUser->fields('id,phone,nickname')->whereIn('id',join(',',$uidList))->get()->resultArr();
        $nameArr  = array_column($uidArr,'nickname','id');
        $uidArr   = array_column($uidArr,'phone','id');
        //获取用户币种列表
        $coinSql  = 'SELECT coin_id,coin_name FROM bn_coin_info where coin_id in ('.join(',',$coinList).')';
        $coinArr  =  $this->_model->query($coinSql)->resultArr();
        //获取币种列表
        $coinArr  = array_column($coinArr,'coin_name','coin_id');
        //获取用户评论列表
        $common   = 'SELECT a_id,count(a_id) as commonNum FROM comments where a_id in('.join(',',$comList).') group by a_id';
        $commonArr= $this->_model->query($common)->resultArr();
        $commonArr= array_column($commonArr,'commonNum','a_id');
        $postList = array();
        //整合数据
        foreach($list as $k => $v)
        {
            $postList[$k] = $v;
            $postList[$k]['phone']        = $uidArr[$v['u_id']] ? $uidArr[$v['u_id']] : '';
            $postList[$k]['coin_name']    = $coinArr[$v['c_id']] ? $coinArr[$v['c_id']] : '';
            $postList[$k]['com_num']      = isset($commonArr[$v['id']]) ? $commonArr[$v['id']] : 0;
            $postList[$k]['nickname']     = $nameArr[$v['u_id']] ? $nameArr[$v['u_id']] : '';
            $postList[$k]['img_urls']     = checkEmpty($v['img_urls']) ? explode(',',$v['img_urls']):false;
            $contentLen = mb_strlen(strip_tags($v['content']),'utf-8');
            //判断
            $postList[$k]['show_detail']  =  $contentLen > 80 ? true : false;
            $postList[$k]['content']      =  $contentLen > 80 ? mb_substr($v['content'],0,80,'utf-8') : $v['content'];
            //返回隐藏数据
            $postList[$k]['hide_content'] = mb_substr($v['content'],80,-1);
        }
        return $postList;
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
     * 修改
     * @param $where  条件
     */
    public function updatePost($data,$where='1')
    {
        if(!is_array($data) || empty($data)){
            return false;
        }
        $fields = '';
        foreach($data as $k=>$v){
            $fields .= "`$k`='{$v}',";
        }
        $fields = trim($fields,',');
        $sql = "UPDATE post_detail SET $fields WHERE $where ";

        return $this->_model->exec($sql);
    }

    /**
     * 添加帖子
     * @param $where  条件
     */
    public function addPost($data)
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
        $sql = "INSERT INTO post_detail ($fields) VALUES $fields_val";

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