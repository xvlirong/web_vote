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
}