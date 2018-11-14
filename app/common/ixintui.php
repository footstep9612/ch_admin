<?php
/**
 * Created by PhpStorm.
 * User: zhujingtao
 * Date: 2018/1/15
 * Time: 17:48
 */

namespace App\common;

use Composer\Package\Loader\ValidatingArrayLoader;

require_once("ixintui/message.php");
require_once("ixintui/client.php");

class ixintui{

    function sendAll($IxintuiPushUrl, $AndroidAppKey=false, $AndroidSecretKey=false, $IosAppKey=false, $IosSecretKey=false,$pushData=false,$sendTime=''){
        $client = new \IxintuiClient($IxintuiPushUrl);
        //a. generate message
        $title = $pushData['title'];
        $sound = TRUE;
        $vibrate = TRUE;
        $unremovable = FALSE;
        $clickAction = \ClickAction::NONE;
        $clickActionValue = 1;
        $badge =-1;
        $messageContent = $pushData['content'];//消息内容
        $period = 86400; //消息存活周期，单位秒
        $AndroidNotif = new \AndroidNotification($title, $sound, $vibrate, $unremovable,$clickAction,$clickActionValue);
        $AndroidNotif->setBadge($badge);
        //b. send

        $IosNotif = new \IosNotification();
        $IosNotif->setBadge($badge);

        $message  = new \MessageAll($messageContent, $AndroidAppKey, $AndroidSecretKey, $IosAppKey, $IosSecretKey);
        $message->setPeriod($period);
        $message->setAndroidNotification($AndroidNotif);
        $message->setIosNotification($IosNotif);
        $message->setExtra($pushData['exter']);
        $message->setQps(0); //可不调用，默认Qps为0，代表以最大速度发送
        //判断是否设置时间
        if(isset($sendTime) && !empty($sendTime) && $sendTime)
        {
            $message->setTimed(1,$sendTime);
        }

        //b. send
        return $client->pushMessage($message);
    }

    function  sendAndroidMessage($IxintuiPushUrl,$AndroidAppKey=false, $AndroidSecretKey=false,$pushData=false,$sendTime=''){
        $client = new \IxintuiClient($IxintuiPushUrl);
        $title = $pushData['title'];
        $sound = TRUE;
        $vibrate = TRUE;
        $unremovable = false;
        $clickAction = \ClickAction::NONE;
        $clickActionValue = 1;
        $messageContent = $pushData['content'];//消息内容
        $period = 86400; //消息存活周期，单位秒
        $badge =-1;
        $AndroidNotif = new \AndroidNotification($title, $sound, $vibrate, $unremovable,$clickAction,$clickActionValue);
        $AndroidNotif->setBadge($badge);
        $message = new \MessageAndroid($messageContent,$AndroidAppKey,$AndroidSecretKey);
        $message->setAndroidNotification($AndroidNotif);
        $message->setPeriod($period);

        $message->setAndroidNotification(1);
        $message->setExtra($pushData['exter']);
        $message->setQps(0); //可不调用，默认Qps为0，代表以最大速度发送
        if(isset($sendTime) && !empty($sendTime) && $sendTime)
        {
            $message->setTimed(1,$sendTime);
        }

        //b. send
        return $client->pushMessage($message);
    }

    function sendIosMessage($IxintuiPushUrl, $IosAppKey, $IosSecretKey,$pushData,$sendTime=''){
        $client = new \IxintuiClient($IxintuiPushUrl);

        //a. generate message
        $messageContent = $pushData['content'];//消息内容
        $period = 86400 ; //消息存活周期，单位秒
        $extraInfo = $pushData['exter'];
        $badge = -1;
        $sound = "default";

        $message  = new \MessageIos($messageContent, $IosAppKey, $IosSecretKey);
        $message->setPeriod($period);
        $message->setExtra($extraInfo); //可不调用
        if(isset($sendTime) && !empty($sendTime) && $sendTime)
        {
            $message->setTimed(1,$sendTime);
        }
        $IosNotif = new \IosNotification();
        $IosNotif->setBadge($badge);
        $IosNotif->setSound($sound);

        $message->setIosNotification($IosNotif);

        //b. send
        return $client->pushMessage($message);
    }

    function sendTagsMessage($IxintuiPushUrl, $AndroidAppKey=false, $AndroidSecretKey=false, $IosAppKey=false, $IosSecretKey=false,$pushData=false,$tag,$sendTime='')
    {

        $MessageContent = $pushData['content'];
        $period = 86400; //消息存活周期，单位秒
        $message = new \MessageTag($MessageContent,$tag);
        $message->setAndroidApp($AndroidAppKey,$AndroidSecretKey);
        $message->setIosApp($IosAppKey,$IosSecretKey);
        $message->setExtra($pushData['exter']);
        $message->setPeriod($period);
        $message->setIsNotif(1);
        $message->setQps(0);
        if(isset($sendTime) && !empty($sendTime) && $sendTime)
        {
            $message->setTimed(1,$sendTime);
        }
        $client = new \IxintuiClient($IxintuiPushUrl);
        return $client->pushMessage($message);
    }
}