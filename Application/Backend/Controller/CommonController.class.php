<?php

namespace Backend\Controller;
use Think\Controller;

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
}

?>