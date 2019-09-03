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

}

?>