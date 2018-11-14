<?php
defined("__FRAMEWORKNAME__") or die("No permission to access!");
use Lib\Upload;

use Lib\JsonRpcClient;

/**
 * @pageroute
 */
function upload_old(){
    $upload = new Upload();// 实例化上传类
    $upload->maxSize   =     31457280000 ;// 设置附件上传大小
    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
    $upload->rootPath  =     __WEBROOT__.'/source/pics/'; // 设置附件上传根目录
    $upload->savePath  =     '/'; // 设置附件上传（子）目录
    // 上传文件
    $info   =   $upload->upload();
    logs($info,'upload');
    //var_dump($info);
    if(!$info) {// 上传错误提示错误信息
        $ar['code'] = 400;
        $ar['msg'] = $upload->getError();
        ajaxReturn($ar);
    }else{// 上传成功
        //var_dump($info);
        $file = $info['file'];
        $filePath = 'bniu_pics/'.$file['savepath'].$file['savename'];
        $new_pdo = new \Model\Model('dh_cms');
        $now_time = date('Y-m-d H:i:s');
        $picSql ="INSERT INTO cms_medias ( path, create_time) VALUES('{$filePath}','{$now_time}')";
        $pic_id = $new_pdo->query($picSql)->getInsertId();
        $ar['code'] = 200;
        $ar['msg'] = $upload->getError();
        $ar['pic_id'] = $pic_id;
        $ar['url'] = C("BASE_HOST") . $filePath;
        $ar['path'] =  $filePath;
        ajaxReturn($ar);
        //dump('上传成功！');
    }
    //dump(200);
}



/**
 * @pageroute
 */
function test() {
    $framework = getFrameworkInstance();
    $framework->smarty->display('upload/upload.html');
}


/**
 * @pageroute
 */
function upload(){

        if(empty($_FILES['filename']) && empty($_FILES['file'])){
            $res = [];
            $res['err_code'] = 0;
            $res['err_msg'] = 'filename 不存在';
            ajaxReturn($res);
            exit;
        }
       if(isset($_FILES['file'])){
            $_FILES['filename'] = $_FILES['file'];
            unset($_FILES['file']);
       }
        //$tt= new OssBase();
        //var_dump($tt);
        $result = (new \App\common\OssBase())->uploadFile($_FILES);
        $ar = [];
        if($result['err_code'] == 0){
            $ar['code'] = 200;
            $ar['msg'] = "上传成功";
            $ar['pic_id'] = $result['pic_id'];
            $ar['url'] = $result['err_msg']['filename']['url'];
        }else{
            $ar['code'] = 400;
            $ar['msg'] = "上传失败";
            $ar['pic_id'] = 0;
            $ar['url'] = '';
        }
        ajaxReturn($ar);
}


/**
 * 资源不入库
 * @pageroute
 */
function upload_url(){

    if(empty($_FILES['filename']) && empty($_FILES['file'])){
        $res = [];
        $res['err_code'] = 0;
        $res['err_msg'] = 'filename 不存在';
        ajaxReturn($res);
        exit;
    }
    if(isset($_FILES['file'])){
        $_FILES['filename'] = $_FILES['file'];
        unset($_FILES['file']);
    }
    //$tt= new OssBase();
    //var_dump($tt);
    $result = (new \App\common\OssBase())->uploadFile($_FILES,'',false);
    $ar = [];
    if($result['err_code'] == 0){
        $ar['code'] = 200;
        $ar['msg'] = "上传成功";
        $ar['pic_id'] = $result['pic_id'];
        $ar['url'] = $result['err_msg']['filename']['url'];
        $ar['types'] = $result['types'];
    }else{
        $ar['code'] = 400;
        $ar['msg'] = "上传失败";
        $ar['pic_id'] = 0;
        $ar['url'] = '';
    }
    ajaxReturn($ar);
}

/**
 * 远程图片抓取上传到本地
 * @pageroute
 */
function preview(){
    $img = I('post.url');

    $roorPath =  __WEBROOT__.'/source/pics/';
    $date = date('Y-m-d');
    $path = $roorPath.$date.'/';
    if(!is_dir($path)){
        mkdir ( $path, 0777, true );
    }
    $info = crabImage($img,$path);
    if(!$info) {// 上传错误提示错误信息
        $ar['code'] = 400;
        $ar['msg'] = '上传失败';
        $ar['pic_id'] = 0;
        $ar['url'] = '';
        $ar['path'] =  '';
        ajaxReturn($ar);
    }else{// 上传成功
        $filePath = '/source/pics/'.$date.'/'.$info['file_name'];
        $new_pdo = new \Model\Model('dh_cms');
        $now_time = date('Y-m-d H:i:s');
        $picSql ="INSERT INTO cms_medias ( path, create_time) VALUES('{$filePath}','{$now_time}')";
        $pic_id = $new_pdo->query($picSql)->getInsertId();
        $ar['code'] = 200;
        $ar['msg'] = "上传成功";
        $ar['pic_id'] = $pic_id;
        $ar['url'] = C("BASE_HOST") . $filePath;
        $ar['path'] =  $filePath;
        ajaxReturn($ar);
    };
}

/**
 * PHP将网页上的图片攫取到本地存储
 * @param $imgUrl  图片url地址
 * @param string $saveDir 本地存储路径 默认存储在当前路径
 * @param null $fileName 图片存储到本地的文件名
 * @return mix
 */
function crabImage($imgUrl, $saveDir='./', $fileName=null){
    if(empty($imgUrl)){
        return false;
    }

    //获取图片信息大小
    $imgSize = getImageSize($imgUrl);
    if(!in_array($imgSize['mime'],array('image/jpg', 'image/gif', 'image/png', 'image/jpeg'),true)){
        return false;
    }

    //获取后缀名
    $_mime = explode('/', $imgSize['mime']);
    $_ext = '.'.end($_mime);

    if(empty($fileName)){  //生成唯一的文件名
        $fileName = time().$_ext;
    }

    //开始攫取
    ob_start();
    readfile($imgUrl);
    $imgInfo = ob_get_contents();
    ob_end_clean();

    if(!file_exists($saveDir)){
        mkdir($saveDir,0777,true);
    }
    $fp = fopen($saveDir.$fileName, 'a');
    $imgLen = strlen($imgInfo);    //计算图片源码大小
    $_inx = 1024;   //每次写入1k
    $_time = ceil($imgLen/$_inx);
    for($i=0; $i<$_time; $i++){
        fwrite($fp,substr($imgInfo, $i*$_inx, $_inx));
    }
    fclose($fp);

    return array('file_name'=>$fileName,'save_path'=>$saveDir.$fileName);
}
