<?php
namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    public function loginwechat(){
        //dump(1);die;
        $appid = 'wx25c3d8d3570674cd';
        echo '请登录';
        header('location:https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri=http://votes.rvtimes.cn/login/handleOauthInfo&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect');
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

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 发送验证码
     */
    public function sendMsCode()
    {
        $project = 'Xuc3e';
        $phone = I('phone');
        $check = '/^(1(([35789][0-9])|(47)))\d{8}$/';
        if (!preg_match($check, $phone)) {
            $res['code'] = 3;
            $res['msg'] = '手机号格式错误';
            $this->ajaxReturn($res);die;
        }
        $exist = M("rv_users")->where(array('tel_phone'=>$phone))->find();
        if($exist){
            $res['code'] = 0;
            $res['msg'] = '手机号已注册';
        }else{
            $res = $this->handleSendMs($phone,$project);
        }
        $this->ajaxReturn($res);
    }

    /**
     * @param $phone
     * @param $project
     * @return mixed
     * 处理发送验证码
     */
    public function handleSendMs($phone,$project)
    {
        $url = "http://api.mysubmail.com/message/xsend.json";
        $appid = '40135';
        $appkey = '1bb05e3b06a5b1e1c4d806d5367fa959';
        $code = randNumber(6);
        $vars['code'] = $code;
        $js_code = json_encode($vars);
        $post_data = "appid=$appid&to=$phone&project=$project&vars=$js_code&signature=$appkey";
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        $info = json_decode($data,1);
        if($info['status'] == 'success'){
            $addData['code'] = $code;
            $addData['phone'] = $phone;
            $addData['add_time'] = time();
            $addData['end_time'] = time()+600;
            $res = M("msg_code_log")->add($addData);
            if($res){
                $res_info['code'] = 1;
                $res_info['msg'] = '短信发送成功，请查收';
            }
        }else{
            $res_info['code'] = 2;
            $res_info['msg'] = '验证码发送失败';
        }
        return $res_info;

    }

    public function handleOauthInfo()
    {
        $code = $_GET['code'];
        $state = $_GET['state'];
        $appid = 'wx25c3d8d3570674cd';
        $appsecret = '913e9fe977454d5ba1a98a2ea51b62ad';

        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsecret.'&code='.$code.'&grant_type=authorization_code';
        $token = json_decode(file_get_contents($token_url));
        if (isset($token->errcode)) {
            echo '<h1>错误：</h1>'.$token->errcode;
            echo '<br/><h2>错误信息：</h2>'.$token->errmsg;
            exit;
        }

        $token_user = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$token->access_token.'&openid='.$token->openid.'&lang=zh_CN';
        $user = json_decode(file_get_contents($token_user),1);
        if (isset($user->errcode)) {
            echo '<h1>错误：</h1>'.$user->errcode;
            echo '<br/><h2>错误信息：</h2>'.$user->errmsg;
            exit;
        }
        setcookie('user_info',$user['nickname']."#".$user['unionid']."#".$user['headimgurl']."#".$user['openid'],time()+600);
        $this->getInfoSave();
    }

    public function getInfoSave()
    {
        $info = cookie("user_info");
        $info_arr=(explode("#",$info));

        //dump($weuser);
        $open_id = $info_arr[3];
        $uid = M("rv_users")->where(array('open_id'=>$open_id))->getField('id');
        $timeout = time()+3600*24*365;
        if($uid){
            print_r($info_arr);die;
            setcookie('auth_user_id',$uid,$timeout);
            session('adminId',$uid);
        }else{
            $downls=$info_arr[2];
            $data['head_img'] = saveUserHeadImg($downls);
            $data["username"] = $info_arr[0];
            //$data["union_id"] = $info_arr[1];
            $data["open_id"] = $info_arr[3];
            $data["regist_time"] = time();
            $last_id = M("rv_users")->add($data);
            setcookie('auth_user_id',$last_id,$timeout);
            session('adminId',$last_id);
        }
        $category_url = cookie('category_url');
        if($category_url){
            echo "<script>location.href='{$category_url}';</script>";
        }else{
            $this->redirect('Index/index');
        }
    }

    public function delSessInfo()
    {
        setcookie('auth_user_id',0,time()-1);
        unset($_SESSION['adminId']);
    }

}

?>