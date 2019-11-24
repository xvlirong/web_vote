<?php

namespace Admin\Controller;
use Think\Controller;
use Think\Upload;

header("Content-type:text/html;charset=utf-8");

/**
 * Class CommonController
 * @package Backend\Controller
 * 通用控制器 公共方法及登录验证
 */
class CommonController extends Controller
{

    public function _initialize()
    {
        // 判断用户是否登录
        $query_str = $_SERVER["QUERY_STRING"];
        if (in_array($query_str,$this->filterArr))
        {
            return;
        }
        if (!session('our_adminId')) {
            $url = U('Login/login');
            echo "<script>window.top.location.href='{$url}';</script>";
            die;
        }
    }

    public function uploadImgs($file, $uploadFolder)
    {
        $upload = new Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     "./Public/upload/"; // 设置附件上传根目录
        $upload->savePath  =     $uploadFolder.'/'; // 设置附件上传（子）目录
        $upload->autoSub = false;
        // 上传单个文件
        $info   =   $upload->uploadOne($file);
        if(!$info) {// 上传错误提示错误信息
            $msg = $upload->getError();
            echo "<script>alert('$msg');location.replace(document.referrer);</script>";
        }else{// 上传成功 获取上传文件信息
            return $info['savename'];
        }

    }
}

?>