<?php

namespace Backend\Controller;

use think\Controller;

class LoginController extends Controller
{
    public function login (){
        $this->display();
    }

    /**
     * 验证登录
     */
    public function login_user()
    {
        if(!IS_POST){
            $res_info['code'] = 1;
            $res_info['msg'] = '非法操作！稍后重试';
            $this->ajaxReturn($res_info);die;
        }
        $username = I('username');
        $pwd = md5(md5(I('password')));
        $exist = M("admin_user")->where(array('username'=>$username,'password'=>$pwd))->find();
        if($exist){
            $data = array(
                'login_time' => time(),
                'login_ip' => get_client_ip()
            );
            M("admin_user")->where(array('id'=>$exist['id']))->save($data);
            session('our_adminId',$exist['id']);
            $url = U('Index/index');
            $res_info['code'] = 0;
            $res_info['msg'] = '登陆成功';
            $res_info['url'] = $url;
        }else{
            $res_info['code'] = 2;
            $res_info['msg'] = '账号或密码错误';
        }
        $this->ajaxReturn($res_info);

    }
}

?>