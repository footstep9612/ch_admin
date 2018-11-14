<?php
/**
 * @desc Created by PhpStorm.
 * @Auther: lwc
 * @since: 2018/1/26 10:45
 */

namespace App\common;

use Lib\RpcResponse;
use OSS\OssClient;
use OSS\Core\OssException;

class OssBase
{

    //KEY
    public $accessKeyId = 'LTAIyRPgkfx71O06';
    //加密戳
    public $accessKeySecret = 'ixfkZERR4jWtustqYKM1nKwCmyRxxV';
    //域名
    public $endpoint = 'http://oss-cn-beijing.aliyuncs.com';
    //bucket相当于文件名根目录
    public $bucket = 'xinbicaijing';

    public $ossClient;

    public function __construct(){
       $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
    }

    /**
     * @param $params  图片资源
     * @param $dir    目录
     * @param bool $isAddMedias   是否添加入库
     * @return mixed
     * @pageroute
     */
    public   function uploadFile($params, $dir = NULL, $isAddMedias = true)
    {
        $data = $this->validateFile($params);
        if($data['code']){
            $response = new RpcResponse($data);
            $res = $response->getRpcResponseObject();
            return $res;
        }

        try {
            $content = file_get_contents($data['source']);
            $defultDir = 'bniu_pics/source/'.date('Ymd').'/';
            if($params['filename']['type'] == 'application/pdf'){
                $defultDir = 'bniu_pdf/source/'.date('Ymd').'/';
            }
            $object  = empty($dir)?$defultDir:$dir;
            $isExtist =  $this->ossClient->doesObjectExist($this->bucket, $object);

            if(!$isExtist){
                $this->ossClient->createObjectDir($this->bucket, $object);
            }
            $ret =  $this->ossClient->putObject($this->bucket, $object.$data['message'], $content);
            // logs($ret,'ossResult');
            if(isset($ret['info']['url'])){
                $myfile = [];
                $myfile['filename']['name'] = $data['message'];
                $myfile['filename']['url'] = $ret['info']['url'];

                $result['err_code'] = 0;
                $result['err_msg'] = $myfile;
                $result['pic_id'] = 0;
                $result['types'] = $data['type'];
                if($isAddMedias){
                    $result['pic_id'] =  $this->addMedias($ret['info']['url']);
                }
            }else{
                $result['err_code'] = 400;
                $result['err_msg'] = '上传到云端失败';
            }
        } catch (OssException $e) {
            $result['err_code'] = 500;
            $result['err_msg'] = $e->getMessage();
        }
        return $result;
    }

    /**
     * 生成图片名字
     *
     * @return string
     */
    function createFileName(){
        return time().rand(10000, 99999);
    }


    /**
     * 图片类型校验
     *
     * @return array
     */
    function validateFile($params)
    {
        $data = ['code'=>1, 'type'=>'', 'message'=> ''];
        $typeAllow = ['image/jpeg', 'image/png','application/pdf'];

        try{
            //pdf文件处理
            if($params['filename']['type'] == 'application/pdf'){
                $info['mime'] = 'application/pdf';
            }else{
                // $info = getimagesizefromstring($img);
                $info = getimagesize($params['filename']['tmp_name']);
                //logs($info);
                if(false == $info){
                    throw new \Exception('bad image');
                }
                if(!in_array($info['mime'], $typeAllow)){
                    throw new \Exception('Type not allowed');
                }
            }
            switch ($info['mime']) {
                case 'image/jpeg':
                    $data['code'] = 0;
                    $data['type'] = 'jpg';
                    $data['message'] = $this->createFileName().'.jpg';
                    $data['source'] = $params['filename']['tmp_name'];
                    break;
                case 'image/png':
                    $data['code'] = 0;
                    $data['type'] = 'png';
                    $data['message'] = $this->createFileName().'.png';
                    $data['source'] = $params['filename']['tmp_name'];
                    break;
                case 'application/pdf':
                    $data['code'] = 0;
                    $data['type'] = 'pdf';
                    $data['message'] = $this->createFileName().'.pdf';
                    $data['source'] = $params['filename']['tmp_name'];
                    break;
            }
        } catch (\Exception $e){
            $data['message'] = $e->getMessage();
        }
        return $data;
    }

    /**
     * 添加图片入库
     *
     * @param $filePath
     * @return mixed
     */
    function addMedias ($filePath){
        $new_pdo = new \Model\Model('bn_cms');
        $now_time = date('Y-m-d H:i:s');

        $picSql ="INSERT INTO cms_medias ( path, create_time) VALUES('{$filePath}','{$now_time}')";
        $pic_id = $new_pdo->query($picSql)->getInsertId();
        return $pic_id;
    }
}