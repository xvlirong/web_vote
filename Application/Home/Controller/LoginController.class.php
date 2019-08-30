<?php
namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    public function loginwechat(){
        //dump(1);die;
        $appid = 'wxbd4d33e0bd2dbe99';
        echo '请登录';
        //header('location:https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri=https://m.hrpindao.com/oauth.php&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect');
        die;
    }

    public function login()
    {
        $this->display();
    }

    public function loginUser()
    {
        if(!IS_POST){
            $res_info['code'] = 1;
            $res_info['msg'] = '非法操作！稍后重试';
            $this->ajaxReturn($res_info);die;
        }
        $username = I('phone');
        $pwd = md5(md5(I('password')));
        $exist = M("rv_users")->where(array('tel_phone'=>$username,'password'=>$pwd))->find();
        if($exist){
            session('adminId',$exist['id']);
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