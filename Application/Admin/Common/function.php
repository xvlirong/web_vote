<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 21:22
 */

header("Content-type: text/html; charset=utf-8");

/**
 * 图片上传
 * @param $file 上传的文件夹名称
 * @return array|bool
 */
function uploadImg($file,$file_info)
{
    $config = C('UPLOAD_CONFIG');
    $config['savePath'] = 'upload/'.$file.'/';
    $upload = new \Think\Upload($config);
    $info = $upload->upload($file_info);
    if (!$info) {
        $errMsg = $upload->getError();
        echo "<script>alert('$errMsg');history.go(-1);</script>";
        die;
    }
    return $info;
}



function uploadVideo($file)
{
    $config = C('UPLOAD_CONFIG');
    $config['savePath'] = 'upload/'.$file.'/';
    $config['exts'] = array('mp4');
    $upload = new \Think\Upload($config);
    $info = $upload->upload();
    if (!$info) {
        $errMsg = $upload->getError();
        echo "<script>alert('$errMsg');location.replace(document.referrer);</script>";
        die;
    }
    return $info;
}

