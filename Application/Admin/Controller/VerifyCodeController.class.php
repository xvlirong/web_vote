<?php

namespace Admin\Controller;

class VerifyCodeController extends CommonController
{
    public function verify_list()
    {
        $list = M("msg_code_log")->order(array('add_time'=>'desc'))->limit(100)->select();
        $this->assign('list',$list);

        $this->display();
    }

    public function add_code()
    {
        $this->display();
    }

}