<?php

defined("__FRAMEWORKNAME__") or die("No permission to access!");
use App\common\AlipayBase;
use App\common\baseCache;
use Model\AuthUserSelect;
use Model\Event;

/**
 * @pageroute
 * 用户列表
 */
function index(){

    //获取实例
    $framework = getFrameworkInstance();
    $model     = new AuthUserSelect();
    $AuthUser =  new \Model\AuthUser();
    //统计用户总数
    $count     = $model->getUserCount();
    //分页类，返回object
    $pageInfo  = getExamplePage($count,1,20,array('isAjax'=>true,'baseurl'=>'admin.php?c=user&a=selectUser'));
    $input['p'] = $pageInfo['pageObj']->start;$input['pageSize'] = $pageInfo['pageObj']->offset;
    //生成Sql语句
    $selectSql = AuthUserSelect::generateSql($input);
    //执行sql语句
    $result = $model->query($selectSql)->resultArr();
    //获取用户邀请码列表
    $uidList = array_column($result,'user_id');
    //查询用户邀请码
    $invatiList = $AuthUser->fields('id,invitation_code')
        ->whereIn('id',join(',',$uidList))
        ->get()
        ->resultArr();
    $invatiList = array_column($invatiList,'invitation_code','id');

    //获取用户列表
    foreach($result as $k => $v)
    {
        $result[$k]['apprentice_count'] = checkEmpty($v['apprentice_count']) ? $v['apprentice_count'] : 0;
        $result[$k]['disciple_count']   = checkEmpty($v['disciple_count']) ? $v['disciple_count'] : 0;
        $result[$k]['nospeech_time']   = $v['nospeech_time'] < time() ? 1 : 2;//2已经禁言 1 未禁言
        $result[$k]['invitation_code'] = checkEmpty($invatiList[$v['user_id']]) ?  $invatiList[$v['user_id']] : '';
    }

    $framework->smarty->assign('result', $result);
    $framework->smarty->assign('pageNum',$pageInfo['pageHtml']);
    $framework->smarty->assign('pageCount',$count);
    $framework->smarty->display('user/index.html');
}



/**
 * @pageroute
 * 用户列表查询 ajax 请求接口
 */
function selectUser()
{
    $model     = new AuthUserSelect();
    $AuthUser =  new \Model\AuthUser();
    //获取输入参数
    $input = I('get.',1);
    //获取分页参数
    $page  = I('get.p/d',1);
    //获取排序参数
    $input['order'] = I('get.order/d',1);
    //统计用户总数
    $count     = $model->getUserCount($input);
    //分页类，返回object
    $pageInfo  = getExamplePage($count,$page,20,array('isAjax'=>true,'baseurl'=>'admin.php?c=user&a=selectUser'));
    //写入数据
    $input['p'] = $pageInfo['pageObj']->start;$input['pageSize'] = $pageInfo['pageObj']->offset;
    //生成Sql语句
    $selectSql = AuthUserSelect::generateSql($input);

    //执行sql语句
    $result = $model->query($selectSql)->resultArr();
    //获取用户邀请码列表
    $uidList = array_column($result,'user_id');
    //查询用户邀请码
    $invatiList = $AuthUser->fields('id,invitation_code')
        ->whereIn('id',join(',',$uidList))
        ->get()
        ->resultArr();
    $invatiList = array_column($invatiList,'invitation_code','id');
    foreach($result as $k => $v)
    {
        $result[$k]['apprentice_count'] = checkEmpty($v['apprentice_count']) ? $v['apprentice_count'] : 0;
        $result[$k]['disciple_count']   = checkEmpty($v['disciple_count']) ? $v['disciple_count'] : 0;
        $result[$k]['nospeech_time']   = $v['nospeech_time'] < time() ? 1 : 2;//2已经禁言 1 未禁言
        $result[$k]['invitation_code'] = checkEmpty($invatiList[$v['user_id']]) ?  $invatiList[$v['user_id']] : '';
    }

    //返回数据
    ajaxReturn(array('code'=>200,'data'=>$result,'pageHtml'=>$pageInfo['pageHtml'],'pageCount'=>$count));
}


/**
 * @pageroute
 */
function importantData()
{
    $get = I('get.');
    //过滤无用数据
    unset($get['c']);
    unset($get['a']);
    $filename = 'UserList-'.join('-',array_filter(array_values($get),'checkEmpty')).'-'.date('Y-m-d');
    //获取model实例
    $model   = new AuthUserSelect();
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename=\''.$filename.'.csv\'');
    header('Cache-Control: max-age=0');
    //打开PHP文件句柄，php://output 表示直接输出到浏览器
    $fp = fopen('php://output', 'a');
    // 输出Excel列名信息
    $header = array('UID','手机号','创建时间','昵称','圈子关注数','发帖数','徒徒孙','徒弟','积分');
    // 设置CSV的Excel头支持GBK编码，一定要转换，否则乱码
    foreach ($header as $k => $v) {

        $header[$k] = iconv('utf-8', 'gbk', $v);
    }
    //将header写到cvs
    fputcsv($fp, $header);
    //执行批量执行
    $model->important_user($fp,$get);

}

/**
 * @pageroute
 * 查询用户积分详情
 */
function selectUserIntergal()
{
    $uid  = I('get.uid',0,'intval');
    $page = I('get.p',0,'intval');
    //获取实例
    $event = new Event();
    $list = $event->getEventList($uid,$page);
    //返回数据
    ajaxReturn(array('code'=>200,'data'=>$list));

}



/**
 * test
 * @pageroute
 */
function test(){

    $model =   new \Model\UserOrderRecord();
    $array= $model->where(['id'=>2])->get()->rowArr();

    $obj =  new  AlipayBase();
    /*   $array = array(
           'id'=>111,
           "user_id"=>'123',
           'amount_total'=>1,
           'order_sn'=>'152419357574899',
           'pay_account'=>'17710879104@163.com',
           'amount'=>'0.1'
           );*/
    $result = $obj->alipay($array);
    print_r($result);die;
}

/**
 * 提现
 * @pageroute
 */
function putForward(){
    $redisKey = 'putForward';
    $redis =  new BaseCache();
    // $result = $redis->getLpush($redisKey,2);die;
    $result = $redis->getLrange($redisKey);
    if($result){
        $result =array_unique($result);
        $str =  implode(',',$result);
        $model =   new \Model\UserOrderRecord();
        $array= $model->whereIn('id',$str)->where(['order_status'=>11])->get()->resultArr();
        if($array){
            $order_id = array_column($array,'id');
            $newData = array_diff($result,$order_id);
            if($newData){
                foreach($newData as $key =>$val){
                    $redis->getLrem($redisKey,$val['id']);
                }
                foreach ($array as $k=>$v){
                    if(in_array($v['id'],$newData)){
                        unset($array[$k]);
                    }
                }
            }
            $obj =  new  AlipayBase();
            $num =0;
            foreach ($array as $m=>$n){
                $result = $obj->alipay($n);
                if($result){
                    $num++; //成功的
                    $message = '恭喜您提现成功'.$n['amount'].'元';
                    pushAppTagMessage($n['id'],$message);
                }
                $redis->getLrem($redisKey,$n['id']);
            }

            echo  "共处理".$num.'订单';die;
        }

    }
    echo "暂时没有订单";

}



/**
 * @pageroute
 * 用户列表
 */
function nospeech_time(){
    $uid = I('post.uid');
    $time = I('post.time');
    if(!$uid){
        echo json_encode(array('code' => 1,'message' => '参数错误','data' => array()));
    }
    $Model     = new AuthUserSelect();
    $date = time() + $time * 86400;
    $sql = "UPDATE auth_user SET nospeech_time =$date  WHERE id = ".$uid;
    $sql2 = "UPDATE auth_user_select SET nospeech_time = $date  WHERE user_id = ".$uid;

    if($Model->exec($sql) && $Model->exec($sql2)){
        ajaxReturn(array('code' => 0,'message' => '禁言成功','data' => array()));
    }else{
        ajaxReturn(array('code' => 1,'message' => '禁言失败','data' => array()));
    }
}


/**
 * 提现
 * @pageroute
 */
function test2(){
    pushAppTagMessage('1','提现成功');
}

