<?php
// Copyright (c) ixintui.com 
// Author: chengxu
// Date: 2015-11-06

class IxintuiClient{

    private  $IxintuiPushUrl;
    private  $AppKey;
    private  $SecretKey;

    function __construct($IxintuiPushUrl){
        $this->IxintuiPushUrl = $IxintuiPushUrl;
    }

    public function pushMessage($Message){
        $ReadySendData = $Message->getData();
        $output = "";
        $retObj = (Object)array();
        $retObj->result = -1;
        $retObj->desc = "发送失败";

        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->IxintuiPushUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $ReadySendData);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($ch);
            if(!$output){
                $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
                $retObj->desc = "发送失败,httpcode=".$httpCode;
            }else{
                $retObj = (Object)array();
                $data = @json_decode($output, true);
                if($data != NULL){
                    foreach($data as $key=>$value){
                        $retObj->$key = $value;
                    }
                }
            }
            curl_close($ch);
        }catch(Exception $e){
            $retObj->desc = "发送失败,exception=".$e->getMessage();
        }
        return $retObj;
    }
};

?>
