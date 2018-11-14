<?php
/**
 * Created by PhpStorm.
 * User: jixuedong
 * Date: 2018/9/1
 * Time: 17:48
 */

namespace App\common;
use App\Common\CrossLibrary\BnCms\MessageNotification;
use App\Common\CrossLibrary\BnCms\PostDetail;
use App\Common\CrossLibrary\BnCms\BnCoinInfo;
use Model\CoinChangeStatements;

/**
 * 消息记录推送类
 */
class MessageSend
{
    /**
     * 发送消息
     * @param $sign     类型标识，system：系统 comment：评论 like：点赞 fans：粉丝
     * @param $user_id  被操作的用户id
     * @param $about_id  操作相关的ID，如评论ID、点赞ID
     * @param $link_type 跳转类型，1：h5  2：站内
     * @param $url      跳转地址
     * @param $custom   自定义参数
     */
    public function sendMessageInfo($sign,$user_id,$about_id=0,$link_type=1,$url='',$custom=array())
    {
        //获取相关配置
        $mes_config = C('STATION_MESSAGE');
        $type = $mes_config[$sign]['type'];
        $operation_type = 0;
        $Model = new MessageNotification();
        $where = [
            'type'=>$type,
            'user_id'=>$user_id,
            'about_id'=>$about_id,
        ];
        //区分通知类型，1为系统
        if($type==1){
            //区分跳转类型，1：h5 2：站内
            if($link_type == 1){
                if($Model->getMessageByWhere($where,'id')){
                    return false;die;
                }
                $mes_title = '';
            }else{
                //检测消息是否存在
                $operation_type = $mes_config[$sign][$url]['operation_type'];
                $where['operation_type'] = $operation_type;
                if($Model->getMessageByWhere($where,'id')){
                    return false;die;
                }
                $mes_title = $mes_config[$sign][$url]['title'];
                $url_old = $url;
                $url = $mes_config[$sign][$url]['url'];
                //如果操作与帖子相关则取出帖子相关信息,组装消息标题
                if($url === 'article'){
                    $postModel = new PostDetail();
                    $postInfo = $postModel->getPostListOne(" id=$about_id ");
                    if(empty($postInfo)){
                        return false;die;
                    }
                    $coin_name = $postModel->customStatement("SELECT coin_symbol FROM bn_coin_info WHERE id={$postInfo['c_id']}")->row()->coin_symbol;
                    $title = empty($postInfo['title']) ? mb_substr($postInfo['content'], 0,20).'...' : $postInfo['title'];
                    $mes_title = str_replace('[coin]', $coin_name, $mes_title);
                    $mes_title = str_replace('[title]', $title, $mes_title);
                    if($url_old == 'post_delete'){
                        $mes_title = str_replace('[num]', $custom['score'], $mes_title);
                    }
                }
                //提币相关
                if($url === 'exchange'){
                    //提币信息
                    $CoinChangeModel = new CoinChangeStatements();
                    $changeInfo = $CoinChangeModel->getWithdrawInfo(" id=$about_id ",'currency_num,create_time,coin_id');
                    if(empty($changeInfo)){
                        return false;die;
                    }
                    $coinModel = new BnCoinInfo();
                    $coin_name = $coinModel->customStatement("SELECT coin_symbol FROM bn_coin_info WHERE id={$changeInfo['coin_id']}")->row();
                    if(!$coin_name){
                        return false;die;
                    }
                    $coin_name = $coin_name->coin_symbol;
                    $mes_title = str_replace('[time]', date('Y-m-d',strtotime($changeInfo['create_time'])), $mes_title);
                    $mes_title = str_replace('[num]', $changeInfo['currency_num'], $mes_title);
                    $mes_title = str_replace('[coin]', $coin_name, $mes_title);
                }
            }
        }else{
            if($Model->getMessageByWhere($where,'id')){
                return false;die;
            }
            $mes_title = $mes_config[$sign]['title'];
        }
        $mes_info = [
            'mes_title'=>$mes_title,
            'mes_content'=>'',
            'user_id'=>$user_id,
            'type'=>$type,
            'link_type'=>$link_type,
            'url'=>$url,
            'about_id'=>$about_id,
            'status'=>1,
            'operation_type'=>$operation_type,
            'create_time'=>date('Y-m-d H:i:s'),
            'send_time'=>date('Y-m-d H:i:s'),
        ];
        if($Model->addMessage($mes_info)){
            //消息记录成功发送推送消息
            // $this->pushMessage();
        }
    }
    
    /**
     * 推送消息
     * @param $sign     类型标识
     * @param $user_id  用户id
     */
    public function pushMessage()
    {

    }


}