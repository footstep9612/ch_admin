<?php
if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     *
     * @return void
     */
    function dd()
    {
        array_map(function ($x) {
            var_dump($x);
        }, func_get_args());
        die();
    }
}

if (!function_exists('get_client_ip')) {
    function get_client_ip()
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                foreach (explode(',', $_SERVER[ $key ]) as $ip) {
                    $ip = trim($ip); // just to be safe

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return 'unknow';
    }
}

if (!function_exists('str_random')) {
    /**
     * 生成一组随机字符串
     *
     * @param int $length
     *
     * @return string
     */
    function str_random($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = openssl_random_pseudo_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}

if (!function_exists('mask_string')) {
    /**
     * 字符串打码，手机号、身份证
     *
     * @param $input
     * @param $start
     * @param $length
     * @param string $symbol
     *
     * @return mixed
     */
    function mask_string($input, $start, $length, $symbol = '*')
    {
        $sublen = strlen(substr($input, $start, $length));

        return substr_replace($input, str_pad('', $sublen, $symbol), $start, $length);
    }
}

if (!function_exists('generate_orderid')) {
    /**
     * 生成商户唯一订单id
     *
     * @return string
     */
    function generate_orderid($prefix = '')
    {
        $tmp = str_replace('.', '', microtime(true)); //毫秒

        return $prefix . str_pad($tmp, 15, '0') . mt_rand(1000, 9999);
    }
}


if (!function_exists('config')) {
    /**
     * 读取配置,支持多维，.分割
     *
     * @param  string $key
     * @param  mixed $default
     *
     * @return mixed
     */
    function config($key, $default = null)
    {
        $array = $GLOBALS['config'];

        if (!is_array($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[ $key ];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[ $segment ];
            } else {
                return $default;
            }
        }

        return $array;
    }
}

if (!function_exists('generate_invite_code')) {
    /**
     * 邀请码生成函数
     *
     * @param $userId
     *
     * @return string
     */
    function generate_invite_code($userId)
    {
        $codeSet = "wxyz";//补位字符集

        $hex32 = base_convert($userId, 10, 32);//id转32位
        $fillStr = "";

        //如果32进制小于6位
        if (($len = strlen($hex32)) < 6) {
            while (6 - $len) {
                $fillStr .= $codeSet[ mt_rand(0, 3) ];
                $len++;
            }
        }

        return $fillStr . $hex32;
    }
}

if (!function_exists('array_orderby')) {
    //多维数组排序
    function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[ $key ] = $row[ $field ];
                $args[ $n ] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }
}

if (!function_exists('parting_table_name')) {

    /**
     * 根据用户id映射分表表名
     *
     * @param $userId
     * @param $table
     *
     * @return string
     */
    function parting_table_name($userId, $table)
    {
        $num = 36;//分表的数量
        $hash = sprintf("%u", crc32($userId));
        $mod = intval(fmod($hash, $num));
        $tableName = $table . ($mod + 1);

        return $tableName;
    }
}

if (!function_exists('generateToken')) {
    /**
     * 生成验签 token
     * @param $data
     * @param $secret
     * @return string
     */
    function generateToken($data, $secret)
    {
        ksort($data);

        return hash('sha256', http_build_query($data) . $secret);
    }
}

if (!function_exists('getUAInfo')) {

    /**
     * 分析请求头
     *
     * @param null $item
     *
     * @return array|mixed
     */
    function getUAInfo($item = null)
    {
        $header_arr = getallheaders();
        $agent = isset($header_arr['User-Agent']) ? $header_arr['User-Agent'] : '';

        $parser = new \Lib\ParseUA();
        $result = $parser->parse($agent)->getParseInfo();

        if (!empty($item) && isset($result[$item])) {
            return $result[$item];
        }
        return $result;
    }
}

if (!function_exists('encrypt_aes')) {
    /**
     * 生成会话token
     *
     * @param $str
     * @param $key
     * @param $iv
     * @return string
     */
    function encrypt_aes($str, $key, $iv)
    {
        return \Lib\Auth::encrypt_aes($str, $key, $iv);
    }
}

if (!function_exists('decrypt_aes')) {
    /**
     * 反解会话token
     *
     * @param $str
     * @param $key
     * @param $iv
     * @return string
     */
    function decrypt_aes($str, $key, $iv)
    {
        return \Lib\Auth::decrypt_aes($str, $key, $iv);
    }
}


function node_merges($node, $access = array(), $pid = 0, $id = 'id')
{
    $arr = array();
    foreach ($node as $k => $nodeList) {
        if (is_array($node)) {
            $nodeList['access'] = in_array($nodeList[ $id ], $access) ? 1 : 0;
        }
        if ($nodeList['parent_id'] == $pid) {
            $nodeList['child'] = node_merges($node, $access, $nodeList[ $id ]);
            if (empty($nodeList['child'])) {
                $nodeList['show'] = 0;
            } else {
                $nodeList['show'] = 1;
            }
            $arr[] = $nodeList;
        }
    }
    return $arr;
}

/**
 * 根据总数目，当前页数，返回分页后的Obj
 *
 * @param $countNum 查询的总数目
 * @param string $page 当前页数
 */
function getPageObj($countNum, $page = '1', $pageSize = '')
{
    $config = array(
        'total'        => $countNum,
        'pagesize'     => $pageSize ? $pageSize : C('PAGE_SIZE'),
        'current_page' => $page,
    );

    return $pagination = new \Lib\Pagination($config);
}


if (!function_exists('throwErrJosn')) {
    function throwErrJosn($errCode, $errMsg)
    {
        ajaxReturn(['err_code' => $errCode, 'err_msg' => $errMsg]);
    }
}

/**
 * 获取上传图片的url
 *
 * @return uploadUrl
 */
function getBaseUploadUrl()
{
    $storageObj = new \Storage\Storage();
    $uploadMsg = $storageObj->getUploadUrl('commonThumb');
    if ($uploadMsg['status'] == '200') {
        return $uploadMsg['msg'];
    }

    return false;
}


if(!function_exists('checkEmpty'))
{
    /**
     * 检测数据是否为空
     * @param  array  $data     Form 表单提交的参数
     * @return boolean          True|False
     */
    function checkEmpty($data='')
    {
        if(!$data || !isset($data) || empty($data)){return false;}
        return true;
    }
}


if(!function_exists('getExamplePage'))
{
    /**
     * 生成分页实例
     * @param $count     Int     分页总数
     * @param $offset    Int     当前页码        default 1
     * @param $pageSize  Int     每页显示记录条数 default 20
     * @param $config    Array   其他配置项
     * @param $config['isAjax']  是否ajax请求
     * @param $config['baseurl'] 请求的url地址
     * @return Array pageObj 分页对象  pageHtml 分页生成的Html
     */
    function getExamplePage($count=0,$offset=1,$pageSize=20,$config=array())
    {
        //设置配置数组
        $pageConfig = array(

            'total'        => $count,     //分页总数
            'current_page' => $offset,    //当前页码
            'pagesize'     => $pageSize,  //每页显示记录
        );
        //合并数组
        if(checkEmpty($config))
        {
            $pageConfig = array_merge($pageConfig,$config);
        }
        //实例化
        $pageObj = new Lib\Pagination($pageConfig);
        //返回数据---判断是否ajax
        return array('pageObj'=>$pageObj,'pageHtml'=>$pageObj->createLink($pageConfig['isAjax'] ? true : false));
    }
}



/**
 * 用户信息缓存失效
 *
 * @param $userid
 *
 * @return mixed
 * @throws Exception
 */
function invalidUserProfileCache($userid)
{
//    $cacheKey = 'userinfo' . $userid;
    $redisInstance = getReidsInstance();

    return $redisInstance->expire($userid, 0);
}

//二维数组去重
function assoc_unique($arr, $key)
{
    $tmpKey = array();
    $data = array();
    foreach ($arr as $item) {
        if (in_array($item[ $key ], $tmpKey)) {
            continue;
        }
        $tmpKey[] = $item[ $key ];
        $data[] = $item;
    }

    return $data;
}

/**
 * 信息修改为*号
 * @param $data
 * return $data
 */
function maskData($data)
{
    $sessionObj = new \Lib\Session();
    $session = $sessionObj->get('userData.admin_user');

    if ($session) {
        $adminUserRole = $session['user_role'];

        foreach ($data as $key => &$val) {
            if (is_array($val)) {

                if ($adminUserRole['id_number'] == 2) {
                    if (isset($val['id_number'])) {
                        $val['id_number'] = mask_string($val['id_number'], 6, 8);
                    }
                }
                if ($adminUserRole['phone'] == 2) {

                    if (isset($val['phone'])) {
                        $val['phone'] = mask_string($val['phone'], 3, 6);
                    }
                }
            } else {

                if ($adminUserRole['id_number'] == 2) {
                    if ($key == 'id_number') {
                        $data[ $key ] = mask_string($val, 6, 8);
                    }
                }
                if ($adminUserRole['phone'] == 2) {
                    if ($key == 'phone') {
                        $data[ $key ] = mask_string($val, 3, 6);
                    }
                }
            }

        }
    }

    return $data;
}
/**
 * Created by Wangxiaoming
 * Description:  根据卡号获取银行信息
 * @param $card 银行卡号
 * @throws Exception
 */
function bankInfo_bak($card)
{

    $redis = getReidsInstance();
    $card_6 = substr($card, 0, 6);
    if ($data = $redis->hGetAll('BIN_' . $card_6)) {

        return $data;
    }
    $card_5 = substr($card, 0, 5);
    if ($data = $redis->hGetAll('BIN_' . $card_5)) {

        return $data;
    }
    $card_4 = substr($card, 0, 4);
    if ($data = $redis->hGetAll('BIN_' . $card_4)) {

        return $data;
    }
    $card_3 = substr($card, 0, 3);
    if ($data = $redis->hGetAll('BIN_' . $card_3)) {

        return $data;
    }
    return false;
}

function get_channel_name($channel_id)
{
    switch ($channel_id) {
        case 1:
            return 'YEEPAY';
        case 2:
            return 'KUAIQIAN';
        case 3:
            return 'BAOFOO';
        case 4:
            return 'UNSPAY';
    }


}

function set_bank_channel($bankid)
{
    $sql = 'select b.bank_id,b.app_channel_id,b.is_app_display,p.first_quota,p.times_quota,p.days_quota,b.bank_code,b.bank_name,b.bank_code,p.code,b.bank_logo from bank as b left join bank_channel as p on b.app_channel_id = p.channel_id and b.bank_id=p.bank_id where b.bank_id=:bank_id and p.type=2 and b.is_pay=1';
    $bankModel = new Model\Bank();
    $data = [':bank_id' => $bankid];
    $bankinfo = $bankModel->select($sql, $data);
    if (!$bankinfo) {
        return false;
    }
    $bankinfo[0]['channel_code'] = get_channel_name($bankinfo[0]['app_channel_id']);
    try {
        $redis = getReidsInstance();
        $redis->hMset('pay_bankid_' . $bankinfo[0]['bank_id'], $bankinfo[0]);
    } catch (Exception $e) {

    }
    return $bankinfo[0];
}

//建立在redis不挂掉的前提下,如果挂掉了,也跟着歇菜; getRedisinstance会报异常

function get_bank_info($card, $len)
{
    $redis = getReidsInstance();
    $card_pre = substr($card, 0, $len);
    if ($data = $redis->hGet('BIN_' . $card_pre, 'bank_id')) {
        if ($data0 = $redis->hGetAll('pay_bankid_' . $data)) {
            return $data0;
        } else {
            return set_bank_channel($data);
        }
    } else {
        $BinBank = new \Model\BinBank();
        $sql = "select * from bin_bank where bin=" . $card_pre;
        $res = $BinBank->query($sql)->resultArr();
        if ($res) {
            $redis->hSet('BIN_' . $card_pre, 'bank_id', $res[0]['bank_id']);
            get_bank_info($card, $len);
        } else {
            return false;
        }
    }
    return false;
}

function bankInfo($card)
{
    include_once __APP_LIB_PATH__ . '/yeepay/yeepayMPay.php';
    //$yeepay = new \yeepayMPay(YEEPAY_MERCHANT_ACCOUNT, YEEPAY_MERCHANT_PUBLIC_KEY, YEEPAY_MERCHANT_PRIVATE_KEY, YEEPAY_PUBLIC_KEY);
    $lens = [6, 5, 4, 3];
    foreach ($lens as $len) {
        if ($data = get_bank_info($card, $len)) {
            return $data;
        }
        elseif($data = get_bank_info($card, $len)){
            return $data;
        }
    }
    return false;
}

//返回异常输出
function exception_return($error_code, $msg)
{
    return ['error_code' => $error_code, 'msg' => $msg];
}

//post方法
function postData($url, $data)
{
    $ch = curl_init();
    $timeout = 300;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, "");   //构造来路
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $handles = curl_exec($ch);
    curl_close($ch);
    return $handles;
}

function get_pc_bankChannel_code($bank_code, $utype = 'b2c')
{
    switch ($utype) {
        case 'b2b':
            $bank2code = array(
                'CMBCHINA' => '6001',
                'CMBC' => '6006',
            );
            break;
        case 'b2c':
            $bank2code = array(
                'BOCO' => '3020',
                'CIB' => '3009',
                'ECITIC' => '3039',
                'CEB' => '3022',
                'PAB' => '3035',
                'POST' => '3038',
                'SHB' => '3059',
                'SPDB' => '3004',
                'CMBC' => '3006',
                'CMBCHINA' => '3001',
                'GDB' => '3036',
                'HXB' => '3050',
                'ICBC' => '3002',
                'ABC' => '3005',
                'CCB' => '3003',
                'BOC' => '3026',
                'BCCB' => '3032');
            break;
    }

    return $bank2code[$bank_code];
}

if (!function_exists('encrypt_aes')) {
    /**
     * 生成会话token
     *
     * @param $str
     * @param $key
     * @param $iv
     * @return string
     */
    function encrypt_aes($str, $key, $iv)
    {
        return \Lib\Auth::encrypt_aes($str, $key, $iv);
    }
}

if (!function_exists('decrypt_aes')) {
    /**
     * 反解会话token
     *
     * @param $str
     * @param $key
     * @param $iv
     * @return string
     */
    function decrypt_aes($str, $key, $iv)
    {
        return \Lib\Auth::decrypt_aes($str, $key, $iv);
    }

}

function randString($len,$isnumber=0,$upperOrLower=3){
    if($len<1){
        return;
    }
    $numbers = '0123456789';
    $strtolower = 'abcdefghijklmnopqrstuvwxyz';
    $strtoupper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $string = '';
    if($isnumber==1){
        $string .= $numbers;
    }
    if($upperOrLower==1){
        $string .= $strtolower;
    }
    if($upperOrLower==2){
        $string .= $strtoupper;
    }
    if($upperOrLower==3){
        $string .= $strtolower;
        $string .= $strtoupper;
    }
    $stringLen = strlen($string)-1;
    $str = '';
    for($i=0 ; $i<$len; $i++){
        $str .= $string[rand(0,$stringLen)];
    }
    return $str;
}

/**
 * @param $name
 * @param string $val
 * @return bool|null
 */
function config_admin($name, $val = '')
{
    $appDevConfig = include __APP_CONF_PATH__ . DIRECTORY_SEPARATOR . 'config_admin.php';
    $GLOBALS['config'] = array_merge($GLOBALS['config'], $appDevConfig);
    if(file_exists( __APP_CONF_PATH__ . DIRECTORY_SEPARATOR . 'test_config_admin.php')){
        $appMyDevConfig = include __APP_CONF_PATH__ . DIRECTORY_SEPARATOR . 'test_config_admin.php';
        $GLOBALS['config'] = array_merge($GLOBALS['config'], $appMyDevConfig);
    }
    if ($val) {
        $GLOBALS['config'][$name] = $val;
        return true;
    } else {
        return isset($GLOBALS['config'][$name]) ? $GLOBALS['config'][$name] : null;
    }
}


/**
 * @param $num  截取三位数字，
 * @return array|bool|string
 */
function num_format($num){
    if( is_numeric($num) && is_int($num+0)){
        $num = number_format($num,0,'.',',');
    }else{
        $num = number_format($num,2,'.',',');
    }

    $num = str_replace('/\.(0+$)|(0+$)/', '', $num);
    return $num;
}

function get_now_date(){
    return  date('Y-m-d H:i:s');
}


if (!function_exists('success_result')) {
    function success_result($data=[])
    {
        if(is_array($data)){
            $result= ['code' => 0, 'message' => 'success', 'data' => $data];
        }else{
            $result= ['code' => 0, 'message' => 'success', 'data' => json_decode($data,true)];
        }
        return $result;
    }

}

if (!function_exists('error_result')) {
    function error_result($msg='error')
    {
        return ['code' => 0, 'message' => $msg, 'data' =>[]];
    }
}

/**
 * @生成订单唯一ID
 * @param $creditId
 * @return string
 */
function generate_goods_orders_id($creditId)
{
    $tmp = str_replace('.', '', microtime(true)); //毫秒
    return date('y') .$creditId . date('md') .  substr(str_pad($tmp, 8, '0'),-8) ;
}

/**
 * @获取身份证隐藏显示
 * @param $idcard
 * @return mixed
 */
function get_idcard_mask($idcard)
{
    return (strlen($idcard) == 18) ? preg_replace('/^(\d{3})(\d{11})(.{4})$/',"$1***********$3",$idcard) : $idcard;
}

/**
 * @获取手机号隐藏显示
 * @param $phone
 * @return mixed
 */
function get_phone_mask($phone)
{
    return (strlen($phone) == 11) ? preg_replace('/^(\d{3})(\d+)(\d{4})$/',"$1****$3",$phone) : $phone;
}

/**
 * @获取用户姓名脱敏显示
 * @param $name
 * @return string
 */
function get_name_mask($name)
{
    if (!function_exists('mb_strlen') || empty($name))
    {
        return $name;
    }
    $length = mb_strlen($name);
    return str_pad('',$length-1,"*") . mb_substr($name,-1);
}

/**
 * @获取银行卡mask
 * @param $bank
 * @return mixed
 */
function get_bank_mask($bank)
{
    return preg_replace('/^(\d{3})(\d+)(\d{4})$/',"$3",$bank);
}
/*
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
if (!function_exists('object_to_array')) {
    function object_to_array($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)object_to_array($v);
            }
        }

        return $obj;
    }
}

/*
 * rsa私钥验证加密
 * @param $private_key 私钥路径
 * @$data 需要加密的数据 自然排序法排序 sort()
 *
 */
if (!function_exists('rsa_jiame')) {
    function rsa_jiame($private_key, $data)
    {
        //读取私钥文件
        $private = openssl_get_privatekey(get_private_rsa_key());
        if (!$private) {
            return false;
        }
        //根据key&value规则拼接字符串
        $sign_str = '';
        //自然排序
        asort($data);
        $i = 0;
        foreach($data as $key=>$value){

            if(empty($value) || $key =='sign'){
                continue;
            }
            if($key == 'biz_data'){
                $value=md5(json_encode($value));
            }
            if($i==0){
                $sign_str .= $key.'='.$value;
            }else{
                $sign_str .= '&'.$key.'='.$value;
            }
            $i++;

        }
        //去除第一个&符号
        //echo $sign_str;die;
        openssl_sign($sign_str, $sign, $private);
        openssl_free_key($private);
        return base64_encode($sign);

    }
}






if (!function_exists('debug_trace'))
{
    /**
     * @记录调试信息
     * @param $data
     * @param $method
     * @param $result
     * @return bool
     */
    function debug_trace($data, $method, $result)
    {
        //记录日志
        $debugMsg = "接口{$method}请求结果：" . PHP_EOL;
        $debugMsg .= "请求参数：" . PHP_EOL . var_export($data, true) . PHP_EOL;
        $debugMsg .= "响应结果：" . PHP_EOL . var_export($result, true);
        logs($debugMsg, $method);

        return true;
    }
}
if (!function_exists('json_rpc_call'))
{
    /**
     * @调用远程jsonrpc接口
     * @param $data
     * @param $method
     * @param $url
     * @param bool|true $debug
     * @param array $config
     * @return mixed
     */
    function json_rpc_call(
        $data, $method, $url, $debug = true, $config = array('timeout' => 40)
    )
    {
        $rpcClient = new \Lib\JsonRpcClient($url, $config);
        if (is_array($data)) {
            $result = call_user_func_array(array($rpcClient, $method), $data);
        } else {
            $result = call_user_func(array($rpcClient, $method), $data);
        }
        //记录日志
        if (config('DEBUG', false) || isset($result['error'])) {
            debug_trace($data, $method, $result);
        }
        //        print_r($result);die;
        $debug && debug_trace($data, $method, $result)  ;
        return $result;
    }
}

if(!function_exists('p')){
    function p($data){
        dump($data);
    }
}

if(!function_exists('pd')){
    function pd($data){
        dump($data);die;
    }
}

if (!function_exists('sign_error')) {
    function sign_error()
    {
        return array('code' => 400,'message' => '签名校验失败','data' => array());
    }
}

if (!function_exists('param_error')) {
    function param_error($msg='缺少必要参数')
    {
        return array('code' => 400, 'message' =>$msg, 'data' => array());
    }
}

if (!function_exists('set_return_url')) {
    function set_return_url($file_name,$controllerr_name,$action_name)
    {
        if(strpos($file_name,'.')){
            $file_name=explode('.',$file_name)[0];
        }
        return urldecode(U($file_name.'.php',['c' => $controllerr_name,'a' => $action_name]));
    }
}

if (!function_exists('api_error_result')) {
    function api_error_result($msg = 'error')
    {
        return ['code' => 400, 'message' => $msg, 'data' => []];
    }
}

/**
 * 生成唯一标示
 * @param
 * @return
 */
function getGuid() {
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));

//        $hyphen = chr(45);// "-"
    $uuid = substr($charid, 0, 8)
        .substr($charid, 8, 4)
        .substr($charid,12, 4)
        .substr($charid,16, 4)
        .substr($charid,20,12);
    return $uuid;

}
if (!function_exists('is_url_available')) {
    /**
     * @判断给定的url是否有效
     * @param string $url
     * @return bool
     */
    function is_url_available($url = '')
    {
        if (empty($url)) {
            return false;
        }
        if (!in_array(ini_get('allow_url_fopen'), array('1', 'On', 'ON', 'on'))) {
            return false;
        }
        $headers = @get_headers($url);
        return (bool)substr($headers[0], 9, 3) == '200';

    }
}

if (!function_exists('arraySum')) {
    function arraySum(array $arr){
        $value=0;
        foreach($arr as $item){
            if(strpos($item,',')){
                $value+=str_replace(',','',$item);
            }else{
                $value+=$item;
            }
        }
        return $value;
    }
}

/**
 * @param $arr  数组截取三位数字，
 * @return array
 */
if (!function_exists('num_format_arr')) {
    function num_format_arr(array $arr)
    {
        foreach($arr as &$item){
            $item= num_format($item);
        }
        return $arr;
    }
}
if (!function_exists('date_pad')) {
    /**
     * @生成指定的时间数组
     * @param $startTime
     * @param $endTime
     * @param int $size
     */
    function date_pad($startTime,$endTime,$size = 86400,$format = 'Y-m-d')
    {
        $startTime = strtotime(date($format,is_numeric($startTime) ?: strtotime($startTime) ));
        $endTime = strtotime(date($format,is_numeric($endTime) ?: strtotime($endTime)));
        $array = range($startTime,$endTime,$size);
        foreach ($array as $index => $item) {
            $array[$index] = date($format,$item);
        }
        return $array;
    }
}
if (!function_exists('get_session_user_id')) {
    function get_session_user_id()
    {
        $userId='0';
        $host= $_SERVER['HTTP_HOST'];
        if(!empty($_SESSION[$host]['userData']['user_id'])){
            $userId=$_SESSION[$host]['userData']['user_id'];
        }
        return $userId;
    }
}

if (!function_exists('goods_max_cache')) {
    function goods_max_cache($endTime)
    {
        $val=strtotime($endTime)- time();
        return $val>0 ? $val : 0 ;
    }
}

if (!function_exists('select_result_set')) {
    function select_result_set($selectData,$allData)
    {
        if(is_string($selectData)){
            $selectData=explode(',',$selectData);
        }
        $res=[];
        foreach($selectData as $item){
            $res[$item]=$allData[$item];
        }
        return $res;
    }
}

/**
 * 逗号分割字符串转换数组
 * @params string $str
 * @return array $str
 */
function strToArr($str){
    if(is_string($str)){
        $str = trim($str,',');
        if(strstr($str,',')){
            $str = explode(',',$str);
        }else{
            $str = [$str];
        }
    }
    return $str;
}

/**
 * 二维三维数组筛选字段
 * @params array $array
 * @params array $key
 * @return array $array
 */
function arrayFilter($array,$key){
    foreach($array as $dkey=>&$dvalue){
        foreach($dvalue as $dk=>&$dv) {
            if(!is_array($dv)){
                if(!in_array($dk,$key)){
                    unset($dvalue[$dk]);
                }
            }else{
                foreach($dv as $thk=>&$thv){
//                echo $thv;
                    if(!in_array($thk,$key)){
                        unset($dv[$thk]);
                    }
                }
            }
        }
    }
    return $array;
}

/**
 * 将一维数组转二维数组（队列使用）
 */

function strArrayToArray($data){
    if(empty($data) || !is_array($data)){
        $arr = [];
    } else{
        $data=implode(',',$data);
        $data='['.$data.']';
        $arr=json_decode($data,true);
    }
    return $arr;
}

/**
 * 判断表名是否存在
 */

function table_exists($tableName){
    //Todo
    return $tableName ? true : false;

}

/**
 *
 */

function inter_mysql($db_name){
    $db = C('DB');
    //var_dump($db);
    var_dump($db[$db_name]);
    if( isset($db[$db_name]) ){
        return \DBDrives\PDODrive::getInstance($db[$db_name]);
    }else{
        return false;
    }
}

if ( ! function_exists('value_for_key'))
{
    function value_for_key($keys, $array, $default = false)
    {
        // Cast all variables as array.
        if ( ! is_array($array) )
        {
            if ( is_object($array) )
            {
                $array = (array)$array;
            }
            else
            {
                return $default;
            }
        }

        // If array is empty return default.
        if ( empty($array) )
        {
            return $default;
        }

        if ( array_key_exists($keys, $array) )
        {
            return $array[$keys];
        }

        // Prepare for loop
        $keys = explode('.', $keys);

        // If there is one key than we can skip the loop and check directly.
        if ( count($keys) == 1 )
        {
            return $default;
        }

        // Loop through array tree and find value.
        do
        {
            // Get the next key
            $key = array_shift($keys);

            if (isset($array[$key]))
            {
                if (is_array($array[$key]) AND ! empty($keys))
                {
                    // Dig down to prepare the next loop
                    $array = $array[$key];
                }
                else
                {
                    // Requested key was found
                    return $array[$key];
                }
            }
            else
            {
                // Requested key is not set
                break;
            }
        }
        while ( ! empty($keys));

        // Nothing found so return default.
        return $default;
    }
}



if ( ! function_exists('get_imgpath'))
{

    function get_imgpath($img)
    {

        if(empty($img)){
            return $img;
        }

        if(strpos($img,'http') !== false || strpos($img,'https') !== false){

            return $img;
        }

        return config('IMG_PATH').$img;
    }

}

/**
 * @param $tag //用户id
 * @param $message //消息内容
 * @pageroute
 */
function pushAppTagMessage($tag, $message){
    $push = new \App\common\ixintui();
    $pushData['content'] = $message;
    //"open_view": "income_detail",
    $a =  ['type'=>3,'open_view'=>'income_detail'];
    $pushData['exter'] =  json_encode($a,JSON_UNESCAPED_UNICODE);
    $uid = $tag;
    $tag = array(array($tag));
    $pushModel = new \Model\AppPush();
    $res = $push->sendTagsMessage(IX_API,IX_ANDROID_KEY,IX_ANDROID_CRE_KEY,IX_IOS_KEY,IX_IOS_CRE_KEY,$pushData,$tag);

    $re= object_to_array($res);
    if($re['result'] == 0){
        $pushModel->status = 1;
        if( $re['android']['result'] == 0){
            $pushModel->ixin_and_id = $re['android']['id'];
        }
        if( $re['ios']['result'] == 0){
            $pushModel->ixin_ios_id = $re['ios']['id'];
        }
    }

    $sessionObj      = new \Lib\Session();
    $adm_id = $sessionObj->get('userData.admin_user.userid');
    $pushModel->type = 3;
    $pushModel->t_id = $uid;
    $pushModel->title = $message;
    $pushModel->content = $message;
    $pushModel->adm_id = $adm_id;
    $pushModel->create_time = date('Y-m-d H:i:s');
    $pushModel->save();
}


/**
 * 二维数组排序
 * @param $arrays
 * @param $sort_key
 * @param int $sort_order
 * @param int $sort_type
 * @return bool
 */
function array_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
    if(is_array($arrays)){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
    return $arrays;
}

/**
 *  手机号脱敏
 *
 */
function getPhone($phone){
    return preg_match("/1[3456789]{1}\d{9}$/",$phone) ? preg_replace('/^(\d{3})(\d+)(\d{4})$/',"$1****$3",$phone) : $phone;

}


