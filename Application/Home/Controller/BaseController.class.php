<?php

namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller
{
    //用户id
    public $userid;

    public function __construct()
    {
        parent::__construct();

        $act_id = I('id',1);


        //检查用户登录状态
        if (!check_cookie_exist()) {
            //未登录则跳转到登录页面
            $this->redirect('Login/loginwechat');
            die;
        } else {
            $this->userid = session('adminId');
        }

    }

    public function checkIP()
    {
        if (isset($_SERVER)){
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")){
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        $data = $this->getCity($realip);
        print_r($data);die;
    }

    function getCity($ip)
    {
        $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
        $ip=json_decode(file_get_contents($url));
        if((string)$ip->code=='1'){
            return false;
        }
        $data = (array)$ip->data;
        return $data;
    }
}