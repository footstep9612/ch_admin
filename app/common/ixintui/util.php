<?php
// Copyright (c) ixintui.com 
// Author: chengxu 
// Date: 2015-11-06

function __my_json_encode($data) {           
    if( is_array($data) || is_object($data) ) {
        $islist = is_array($data) && ( empty($data) || array_keys($data) === range(0,count($data)-1) );

        if( $islist ) {
            $json = '[' . implode(',', array_map('__my_json_encode', $data) ) . ']';
        } else {
            $items = Array();
            foreach( $data as $key => $value ) {
                $items[] = __my_json_encode("$key") . ':' . __my_json_encode($value);
            }
            $json = '{' . implode(',', $items) . '}';
        }
    } elseif( is_string($data) ) {
        # Escape non-printable or Non-ASCII characters.
        #$string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"';
        $json = '"' . addcslashes($data, "\\\"\n\r\t" . chr(8) . chr(12)) . '"';
    } else {
        # int, floats, bools, null
        $json = strtolower(var_export( $data, true ));
    }
    return $json;
}

function array_to_string($data){
    if(!is_array($data)){
        return $data;
    }
    $jsonstr = '[';
    foreach($data as $key => $value){
        $jsonstr = $jsonstr . addcslashes($data, "\\\"\n\r\t" . chr(8) . chr(12)) . ',';
    }
    $jsonstr[strlen($jsonstr)] = ']';
    return $jsonstr;
}

class UtilHelper{
    public static function addSign($JsonData, $SecretKey){
        if(PHP_VERSION >="5.4"){
            return UtilHelper::_addSignHighLevel($JsonData, $SecretKey);
        }else{
            return UtilHelper::_addSignLowLevel($JsonData, $SecretKey);
        }
    }

    private  static function _addSignLowLevel($JsonData, $SecretKey){
        $JsonData = UtilHelper::_arraySort($JsonData);
        $jsonStr = __my_json_encode($JsonData);
        $sign = md5($jsonStr.$SecretKey);
        $JsonData["sign"] = $sign;
        return  __my_json_encode($JsonData);
    }

    private static function _addSignHighLevel($JsonData, $SecretKey){
        $JsonData = UtilHelper::_arraySort($JsonData);
        $jsonStr = json_encode($JsonData,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $sign = md5($jsonStr.$SecretKey);
        $JsonData["sign"] = $sign;
        return json_encode($JsonData,JSON_UNESCAPED_SLASHES);
    }
   
    private static function _arraySort($arr){
        if(!is_array($arr)){
            return $arr;
        }
        foreach($arr as $key => $value){
            $arr[$key] = UtilHelper::_arraySort($value); 
        } 
        if(array_keys($arr) === range(0,count($arr)-1)){
            return $arr;
        }else{
            ksort($arr,SORT_STRING);
            return $arr;
        }
    }
}

?>
