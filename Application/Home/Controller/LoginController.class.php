<?php
namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    public function loginwechat(){
        //dump(1);die;
        $appid = 'wxbd4d33e0bd2dbe99';
        header('location:https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri=https://m.hrpindao.com/oauth.php&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect');
        die;
    }

}

?>