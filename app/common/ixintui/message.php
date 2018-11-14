<?php

// Copyright (c) ixintui.com 
// Author: chengxu
// Date: 2015-11-06

define('SCOPE_ALL', 0);
define('SCOPE_ANDROID', 1);
define('SCOPE_IOS', 2);
define('SCOPE_ALIAS_LAST_ACTIVE', 3);
define('SCOPE_ALIAS_ALL', 4);
define('SCOPE_TOKEN', 5);
define('SCOPE_TAG', 6);
define('SCOPE_USERINFO', 7);
define('SCOPE_TAG_AND_USERINFO', 8);

require_once("util.php");

    class ClickAction{
        const NONE = 0;
        const OPEN_APP = 1;
        const OPEN_URL = 2;
        const INTENT = 3;
    }
    
    class TimedMessage{
        const TIMED = 1;
        const AFTER = 2;
    }

   abstract class Notification{
       public $NotificationArray;
       function __construct(){

       }
   }

   class AndroidNotification extends Notification{
       function __construct($Title, $Sound, $Vibrate, $Unremovable, $ClickAction, $Value){
           $this->NotificationArray["title"] = $Title;
           $this->NotificationArray["sound"] = $Sound;
           $this->NotificationArray["vibrate"] = $Vibrate;
           $this->NotificationArray["unremovable"] = $Unremovable;
           if(in_array($ClickAction, array(0, 1, 2, 3))){
               $this->NotificationArray["click_action"] = $ClickAction;
               $this->NotificationArray["click_param"] = $Value;
           }
       }
       function setBadge($badge){
           $this->NotificationArray["badge"] = $badge;
       }

   };

   class IosNotification extends Notification{
       function __construct(){

       }

       function setBadge($badge){
           $this->NotificationArray["badge"] = $badge;
       }
       
       // 参数为声音文件名,默认为default
       function setSound($sound){
           $this->NotificationArray["sound"] = $sound;
       }

       // category需要客户端支持
       function setCategory($category){
           $this->NotificationArray["category"] = $category;
       }
   };
 
   abstract class Message{
       protected $MessageArray;
       protected $IosSecretKey;
       protected $AndroidSecretKey;
       protected $hasIosApp;
       protected $hasAndroidApp;

       function __construct($UserScope, $MessageContent){
           $this->MessageArray["sendTo"]["userScope"] = $UserScope;
           $this->MessageArray["msgStyle"]["basic"]["content"] = $MessageContent;
           $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 0; 
           $this->hasIosApp = false;
           $this->hasAndroidApp = false;
       }
      
       //单位秒,消息存活时间,单位秒,取值范围[60s,604800s]
       function setPeriod($Period){
           $PeriodInt = intval($Period);
           if($PeriodInt > 604800 || $PeriodInt <60){
               return false;
           }
           $this->MessageArray["option"]["period"] = $PeriodInt;
           return true;
       }

       // 推送速度，默认为0，表示以最大速度推送 
       function setQps($Qps){
           $this->MessageArray["option"]["qps"] = $Qps;
       }

       // 设置定时消息
       function setTimed($timed, $timeval){
           // timed等于1，为某一时间点发送的定时消息，timeval参数必须是"2015-09-11 08:40"格式的时间字符串
           if($timed == 1){
               $this->MessageArray["option"]["timed"] = 1;
               $this->MessageArray["option"]["send_time"] = $timeval; 
           }
           // timed等于2，为指定秒数后发送的定时消息，至少为120秒之后，timeval参数为指定分钟数的整数表示
           if($timed == 2){
               $this->MessageArray["option"]["timed"] = 2;
               $this->MessageArray["option"]["send_time_interval"] = $timeval;
           }
       }

       // 设置消息内容
       function setMessageContent($MessageContent){
           $this->MessageArray["msgStyle"]["basic"]["message"] = $MessageContent;
       }

       // is_notif设置为0表示透传，1表示通知，默认为0
       function setIsNotif($IsNotif){
           $this->MessageArray["msgStyle"]["basic"]["is_notif"] = $IsNotif; 
       }
      
       // 设置IOS的content-available参数，默认为0 
       function setContentAvailable($value){
           if($value == 0 || $value == 1){
               $this->MessageArray["msgStyle"]["ios"]["content-available"] = $value;
               return true;
           }
           return false;
       }

       // 该函数用户不需要调用，返回的结果为消息内容的json格式串
       function getData(){
           $secretkey = "";
           if($this->hasAndroidApp){
               $secretkey = $secretkey . $this->AndroidSecretKey;
           }
           if($this->hasIosApp){
               $secretkey = $secretkey . $this->IosSecretKey;
           }

           return UtilHelper::addSign($this->MessageArray, $secretkey);
       } 

       // 添加附加信息。参数为字符串
       function setExtra($extra){
            $this->MessageArray["extra"] = $extra;
       }
   	};

    // 发送所有用户的消息类，包括Android和IOS
    class MessageAll extends Message{
        function __construct($MessageContent, $AndroidAppKey, $AndroidSecretKey, $IosAppKey, $IosSecretKey){
            parent::__construct(SCOPE_ALL, $MessageContent);

            if($AndroidAppKey && $AndroidSecretKey){
                $this->setAndroidApp($AndroidAppKey, $AndroidSecretKey);
            }
            if($IosAppKey && $IosSecretKey){
                $this->setIosApp($IosAppKey, $IosSecretKey);
            }

        }

        private function setAndroidApp($AndroidAppKey, $AndroidSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["android"] = $AndroidAppKey;
            $this->hasAndroidApp = true;
            $this->AndroidSecretKey = $AndroidSecretKey;
        }

        private function setIosApp($IosAppKey, $IosSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["ios"] = $IosAppKey;
            $this->hasIosApp = true;
            $this->IosSecretKey = $IosSecretKey;
        }

        // 添加Android通知消息，参数为AndroidNotification的实例
        function setAndroidNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "AndroidNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["android"][$key] = $value;
                }
            }
        }

        // 添加IOS通知消息，参数为IosNotification的实例
        function setIosNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "IosNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["ios"][$key] = $value;
                } 
            }
        }
   };

    // 只发送Android用户的消息类 
    class MessageAndroid extends Message{
        function __construct($MessageContent, $AndroidAppKey, $AndroidSecretKey){
            parent::__construct(SCOPE_ANDROID, $MessageContent);
            $this->setAndroidApp($AndroidAppKey, $AndroidSecretKey);
        }

        private function setAndroidApp($AndroidAppKey, $AndroidSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["android"] = $AndroidAppKey;
            $this->hasAndroidApp = true;
            $this->AndroidSecretKey = $AndroidSecretKey;
        }

        // 添加Android通知消息，参数为AndroidNotification的实例
        function setAndroidNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "AndroidNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["android"][$key] = $value;
                }
            }
        }
    };

    // 只发送IOS用户的消息类 
    class MessageIos extends Message{
        function __construct($MessageContent, $IosAppKey, $IosSecretKey){
            parent::__construct(SCOPE_IOS, $MessageContent);
            $this->setIosApp($IosAppKey, $IosSecretKey);
        }

        private function setIosApp($IosAppKey, $IosSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["ios"] = $IosAppKey;
            $this->hasIosApp = true;
            $this->IosSecretKey = $IosSecretKey;
        }

        // 添加IOS通知消息，参数为IosNotification的实例
        function setIosNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "IosNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["ios"][$key] = $value;
                } 
            }
        }

    };

    // 发送到最后一个活跃别名的设备的消息类，可以包括Android或IOS，但至少选择其一
    // $alias为array类型的参数。array("haizeiwang", "wangzeihei")表示发送的设备包含别名"haizeiwang"或者"wangzeihai"
    class MessageLastAliasActive extends Message{
        function __construct($MessageContent, $alias){
            parent::__construct(SCOPE_ALIAS_LAST_ACTIVE, $MessageContent);
            $this->setAlias($alias);
        }

        function setAndroidApp($AndroidAppKey, $AndroidSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["android"] = $AndroidAppKey;
            $this->hasAndroidApp = true;
            $this->AndroidSecretKey = $AndroidSecretKey;
        }

        function setIosApp($IosAppKey, $IosSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["ios"] = $IosAppKey;
            $this->hasIosApp = true;
            $this->IosSecretKey = $IosSecretKey;
        }
        // 添加Android通知消息，参数为AndroidNotification的实例
        function setAndroidNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "AndroidNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["android"][$key] = $value;
                }
            }
        }

        // 添加IOS通知消息，参数为IosNotification的实例
        function setIosNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "IosNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["ios"][$key] = $value;
                } 
            }
        }

        private function setAlias($alias){
            $this->MessageArray["sendTo"]["alias"] = $alias;
        }
    };

    // 发送到相应别名的设备的消息类，可以包括Android或IOS，但至少选择其一 
    // $alias为array类型的参数
    // array("haizeiwang", "wangzeihei")表示发送的设备包含别名"haizeiwang"或者"wangzeihai"
    class MessageAlias extends Message{
        function __construct($MessageContent, $alias){
            parent::__construct(SCOPE_ALIAS_ALL, $MessageContent);
            $this->setAlias($alias);
        }

        function setAndroidApp($AndroidAppKey, $AndroidSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["android"] = $AndroidAppKey;
            $this->hasAndroidApp = true;
            $this->AndroidSecretKey = $AndroidSecretKey;
        }

        function setIosApp($IosAppKey, $IosSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["ios"] = $IosAppKey;
            $this->hasIosApp = true;
            $this->IosSecretKey = $IosSecretKey;
        }

        // 添加Android通知消息，参数为AndroidNotification的实例
        function setAndroidNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "AndroidNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["android"][$key] = $value;
                }
            }
        }

        // 添加IOS通知消息，参数为IosNotification的实例
        function setIosNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "IosNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["ios"][$key] = $value;
                } 
            }
        }

        private function setAlias($alias){
            $this->MessageArray["sendTo"]["alias"] = $alias;
        }
    };

    // 发送到相应token的设备的消息类，可以包含Android或者IOS，但至少选择其一
    class MessageToken extends Message{
        function __construct($MessageContent){
            parent::__construct(SCOPE_TOKEN, $MessageContent);
        }

        // $token是array类型参数。
        // array("1111", "2222")表示发送到token为"1111"和 "2222"的设备
        function setAndroidApp($AndroidAppKey, $AndroidSecretKey, $token){
            $this->MessageArray["sendTo"]["appkey"]["android"] = $AndroidAppKey;
            $this->hasAndroidApp = true;
            $this->AndroidSecretKey = $AndroidSecretKey;
            $this->setAndroidToken($token);
        }

        // $token是array类型参数，函数同addAndroidApp中的$token参数
        function setIosApp($IosAppKey, $IosSecretKey, $token){
            $this->MessageArray["sendTo"]["appkey"]["ios"] = $IosAppKey;
            $this->hasIosApp = true;
            $this->IosSecretKey = $IosSecretKey;
            $this->setIosToken($token);
        }

        // 添加Android通知消息，参数为AndroidNotification的实例
        function setAndroidNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "AndroidNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["android"][$key] = $value;
                }
            }
        }

        // 添加IOS通知消息，参数为IosNotification的实例
        function setIosNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "IosNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["ios"][$key] = $value;
                } 
            }
        }

        // $token是array类型参数。
        // array("1111", "2222")表示发送到token为"1111"和 "2222"的设备
        private function setAndroidToken($token){
            $this->MessageArray["sendTo"]["token"]["android"] = $token;
        }    

        // $token是array类型参数，函数同addAndroidApp中的$token参数
        private function setIosToken($token){
            $this->MessageArray["sendTo"]["token"]["ios"] = $token;
        }
    };

    // 发送到含有指定标签的设备的消息类
    // $tag为array类型参数
    // array(1,2,3)表示发送到同时含有1、2、3标签的设备
    // array(array(1,2,3), array(4,5))表示发送到同时含有1、2、3标签或者同时含有4、5标签的设备
    class MessageTag extends Message{
        function __construct($MessageContent, $tag){
            parent::__construct(SCOPE_TAG, $MessageContent);
            $this->setTag($tag);
        }

        function setAndroidApp($AndroidAppKey, $AndroidSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["android"] = $AndroidAppKey;
            $this->hasAndroidApp = true;
            $this->AndroidSecretKey = $AndroidSecretKey;
        }

        function setIosApp($IosAppKey, $IosSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["ios"] = $IosAppKey;
            $this->hasIosApp = true;
            $this->IosSecretKey = $IosSecretKey;
        }

        // 添加Android通知消息，参数为AndroidNotification的实例
        function setAndroidNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "AndroidNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["android"][$key] = $value;
                }
            }
        }

        // 添加IOS通知消息，参数为IosNotification的实例
        function setIosNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "IosNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["ios"][$key] = $value;
                } 
            }
        }

        private function setTag($tag){
            $this->MessageArray["sendTo"]["tag"] = $tag;
        }
    };

    // 发送到指定信息的设备的消息类，目前只支持Android,包含的信息有 地域、操作系统、应用版本、应用渠道、手机品牌、运营商,用户活跃度
    // $UserInfo为array类型的参数
    // array("locate" => array("北京市"), "phone" => array("samsung")) 表示地域为北京市，手机品牌为samsung。更多详细用法请访问爱心推开发者手册查看 
    class MessageUserInfo extends Message{
        function __construct($MessageContent, $AndroidAppKey, $AndroidSecretKey, $UserInfo){
            parent::__construct(SCOPE_USERINFO, $MessageContent);
            $this->setAndroidApp($AndroidAppKey, $AndroidSecretKey);
            $this->setAndroidOptFilter($UserInfo);
        }

        function setAndroidApp($AndroidAppKey, $AndroidSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["android"] = $AndroidAppKey;
            $this->hasAndroidApp = true;
            $this->AndroidSecretKey = $AndroidSecretKey;
        }

        function setIosApp($IosAppKey, $IosSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["ios"] = $IosAppKey;
            $this->hasIosApp = true;
            $this->IosSecretKey = $IosSecretKey;
        }

        // 添加Android通知消息，参数为AndroidNotification的实例
        function setAndroidNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "AndroidNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["android"][$key] = $value;
                }
            }
        }

        // 添加IOS通知消息，参数为IosNotification的实例
        function setIosNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "IosNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["ios"][$key] = $value;
                } 
            }
        }

        private function setAndroidOptFilter($optfilter){
            if(!is_array($optfilter)){
                return;
            }
            foreach($optfilter as $key => $value){
                $this->MessageArray["sendTo"]["optfilter"]["android"][$key] = $value;
            }
        } 
    };

    // 发送到指定标签并且指定用户信息的设备的消息类，用法参照MessageTag类和MessageUserInfo类
    class MessageTagAndUserInfo extends Message{
        function __construct($MessageContent, $AndroidAppKey, $AndroidSecretKey, $tag, $UserInfo){
            parent::__construct(SCOPE_TAG_AND_USERINFO, $MessageContent);
            $this->setAndroidApp($AndroidAppKey, $AndroidSecretKey);
            $this->setTag($tag);
            $this->setAndroidOptFilter($UserInfo);
        }

        function setAndroidApp($AndroidAppKey, $AndroidSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["android"] = $AndroidAppKey;
            $this->hasAndroidApp = true;
            $this->AndroidSecretKey = $AndroidSecretKey;
        }

        function setIosApp($IosAppKey, $IosSecretKey){
            $this->MessageArray["sendTo"]["appkey"]["ios"] = $IosAppKey;
            $this->hasIosApp = true;
            $this->IosSecretKey = $IosSecretKey;
        }

        // 添加Android通知消息，参数为AndroidNotification的实例
        function setAndroidNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "AndroidNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["android"][$key] = $value;
                }
            }
        }

        // 添加IOS通知消息，参数为IosNotification的实例
        function setIosNotification($Notif){
            $this->MessageArray["msgStyle"]["basic"]["is_notif"] = 1; 
            if(is_a($Notif, "IosNotification")){
                foreach($Notif->NotificationArray as $key => $value){
                    $this->MessageArray["msgStyle"]["ios"][$key] = $value;
                } 
            }
        }

        private function setTag($tag){
            $this->MessageArray["sendTo"]["tag"] = $tag;
        }

        private function setAndroidOptFilter($optfilter){
            if(!is_array($optfilter)){
                return;
            }
            foreach($optfilter as $key => $value){
                $this->MessageArray["sendTo"]["optfilter"]["android"][$key] = $value;
            }
        } 
    };
?>
