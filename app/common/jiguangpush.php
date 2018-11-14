<?php
/**
 * Created by PhpStorm.
 * User: sunqinglin
 * Date: 2018/8/30
 * Time: 11:48
 */

namespace App\common;

use Composer\Package\Loader\ValidatingArrayLoader;

require_once("jpush/autoload.php");
//require_once("jpush/src/JPush/Client.php");

use JPush\Client as JPush;
class jiguangpush{

    //极光推送appkey
    static public function app_key(){

        $app_key = "c3a0dc43e7dc02be361d8415";
        return $app_key;
    }
    //极光推送master_secret
    static public function master_secret(){

        $master_secret = "037f9b1abc8f4df51f69754d";
        return $master_secret;
    }
    //获取alias和tags
    public function getDevices($registrationID){

        //require_once APP_PATH . '/../extend/jpush/autoload.php';

        $app_key = $this->app_key();
        $master_secret = $this->master_secret();

        $client = new JPush($app_key, $master_secret);

        $result = $client->device()->getDevices($registrationID);

        return $result;

    }
    //添加tags
    public function addTags($registrationID,$tags){

        //require_once APP_PATH . '/../extend/jpush/autoload.php';

        $app_key = $this->app_key();
        $master_secret = $this->master_secret();

        $client = new JPush($app_key, $master_secret);

        $result = $client->device()->addTags($registrationID,$tags);

        return $result;

    }

    //移除tags
    public function removeTags($registrationID,$tags){

        //require_once APP_PATH . '/../extend/jpush/autoload.php';

        $app_key = $this->app_key();
        $master_secret = $this->master_secret();

        $client = new JPush($app_key, $master_secret);

        $result = $client->device()->removeTags($registrationID,$tags);

        return $result;

    }
    //标签推送
    public function push_tag($tag,$alert){

        //require_once APP_PATH . '/../extend/jpush/autoload.php';

        $app_key = $this->app_key();
        $master_secret = $this->master_secret();

        $client = new JPush($app_key, $master_secret);

        $tags = implode(",",$tag);

        $client->push()
            ->setPlatform(array('ios', 'android'))
            ->addTag($tags)                          //标签
            ->setNotificationAlert($alert)           //内容
            ->send();

    }

    //别名推送
    public function push_a($alias,$alert){

        //require_once APP_PATH . '/../extend/jpush/autoload.php';

        $app_key = $this->app_key();
        $master_secret = $this->master_secret();

        $client = new JPush($app_key, $master_secret);

        $alias = implode(",",$alias);

        $client->push()
            //->setPlatform(array('ios', 'android'))
            ->setPlatform(array('ios', 'android'))
            ->addAlias($alias)                      //别名
            ->setNotificationAlert($alert)          //内容
            ->send();

    }

    //anzhuo平台推送
    public function push_android($pushData=false,$push_time=''){
        $app_key = $this->app_key();
        $master_secret = $this->master_secret();
        $title = $pushData['title'];
        $content = $pushData['content'];
        $extras = $pushData['extras'];
        $client = new JPush($app_key, $master_secret);

        $cli =  $client->push()
            ->setPlatform('android')
            ->addAllAudience()
            ->setNotificationAlert($content)
            ->androidNotification($title,array(
                'title' => $title,
                // 'builder_id' => 2,
                'extras' => $extras
            ))
            ->message($content, [
                'title' => $title,
                'content_type' => 'text',
                'extras' => $extras
            ]);
        if($push_time != ''){
            $c = $cli->build();
            $push_time.=":00";
            $response = $client->schedule()->createSingleSchedule("定时", $c, array("time"=>"$push_time"));
        }else{
            $response = $cli->send();
        }
        return $response;


    }

    //ios平台推送
    public function push_ios($pushData=false,$push_time=''){
        $app_key = $this->app_key();
        $master_secret = $this->master_secret();
        $title = $pushData['title'];
        $content = $pushData['content'];
        $extras = $pushData['extras'];
        $client = new JPush($app_key, $master_secret);
        $cli = $client->push()
            ->setPlatform('ios')
            ->addAllAudience()
            ->setNotificationAlert($content)
            ->iosNotification($title, [
                'sound' => 'sound',
                'badge' => '+1',
                'extras' => $extras
            ])
            ->options(array(
                'apns_production' => ture,
            ));

        if($push_time != ''){
            $c = $cli->build();
            $push_time.=":00";
            $response = $client->schedule()->createSingleSchedule("定时", $c, array("time"=>"$push_time"));
        }else{
            $response = $cli->send();
        }
      return $response;
    }

    //quan平台推送
    public function push_all($pushData=false,$push_time=''){
        $app_key = $this->app_key();
        $master_secret = $this->master_secret();
        $title = $pushData['title'];
        $content = $pushData['content'];
        $extras = $pushData['extras'];
        $client = new JPush($app_key, $master_secret);
        //var_dump(['key'=>'123']);die;
        $cli = $client->push()
            ->setPlatform(array('ios', 'android'))
            ->addAllAudience()
            ->setNotificationAlert($content)
            ->iosNotification($title, [
                'sound' => 'sound',
                'badge' => '+1',
                'extras' => $extras
            ])
            ->androidNotification($title,array(
                'title' => $title,
                // 'builder_id' => 2,
                'extras' => $extras
            ))
            ->message($content, [
                'title' => $title,
                'content_type' => 'text',
                'extras' => $extras
            ])
            ->options(array(
                'apns_production' => ture,
            ));
        //是否定时
        if($push_time != ''){
            $c = $cli->build();
            $push_time.=":00";
            $response = $client->schedule()->createSingleSchedule("定时", $c, array("time"=>"$push_time"));
        }else{
            $response = $cli->send();
        }
        return $response;

    }

    //别名推送
    public function push_alias($pushData=false,$alias,$push_time=''){
        $app_key = $this->app_key();
        $master_secret = $this->master_secret();
        $title = $pushData['title'];
        $content = $pushData['content'];
        $extras = $pushData['extras'];
        $client = new JPush($app_key, $master_secret);
        $alias_ac = array();
        if(!empty($alias)){
            foreach($alias as $al){
                foreach ($al as $a){
                    $alias_ac[] = $a;
                }
            }
        }
        $cli = $client->push()
            ->setPlatform(array('ios', 'android'))
            ->addAlias($alias_ac)
            ->setNotificationAlert($content)
            ->iosNotification($title, [
                'sound' => 'sound',
                'badge' => '+1',
                'extras' => $extras
            ])
            ->androidNotification($title,array(
                'title' => $title,
                // 'builder_id' => 2,
                'extras' => $extras
            ))
            ->message($content, [
                'title' => $title,
                'content_type' => 'text',
                'extras' => $extras
            ])
            ->options(array(
                'apns_production' => ture,
            ));
        if($push_time != ''){
            $c = $cli->build();
            $push_time.=":00";
            $response = $client->schedule()->createSingleSchedule("定时", $c, array("time"=>"$push_time"));
        }else{
            $response = $cli->send();
        }
        return $response;


    }

}