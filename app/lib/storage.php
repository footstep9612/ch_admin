<?php
/**
 * Created by PhpStorm.
 * Author: Baykier<1035666345@qq.com>
 * Date: 2017/7/5
 * Time: 17:11
 */
namespace Lib;

use \Storage\Storage as BaseStorage;
class Storage extends BaseStorage
{
    /**
     * @仅支持单个文件上传
     * @param $filename
     * @return array
     */
    public function uploadFile($filename)
    {
        $filename = (strpos('@',$filename) === 0) ? $filename : '@' . $filename;
        $curl = new \Lib\Curl\Curl();
        $authObj = new \Storage\Auth();
        $tokenInfo = $authObj->getToken();
        if($tokenInfo['status']==self::SUCCESS_CODE)
        {
            $data = array('filename'=>$filename,'token'=>$tokenInfo['msg']);
            $curl->post(STORAGE_BASE_URL.self::UPLOAD_URL,$data,true);
            if(!$curl->error)
            {
                $response = $curl->response;
                $response = json_decode($response,true);
                if('0' == $response['err_code'])
                {
                    $response['err_msg'] = json_decode($response['err_msg'],true);
                    return array('status' => 200,'msg' => '上传成功','data' => $response);
                }
                return array('status'=>$response['err_code'],'msg'=>$response['err_msg']);
            }
            return array('status'=>'100','msg'=>$curl->errorMessage);
        }else
        {
            return array('status'=>$tokenInfo['status'],'msg'=>$tokenInfo['msg']);
        }
    }
}