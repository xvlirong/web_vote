<?php
namespace SaleMS\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function login(){
        $this->display();
    }
    /**
     * 验证登录
     */
//    public function login_user()
//    {
//        if(!IS_POST){
//            $res_info['code'] = 1;
//            $res_info['msg'] = '非法操作！稍后重试';
//            $this->ajaxReturn($res_info);die;
//        }
//        $username = I('username');
//        $pwd = md5(md5(I('password')));
//        $exist = M("admin_user")->where(array('username'=>$username,'password'=>$pwd))->find();
//        if($exist){
//            $data = array(
//                'login_time' => time(),
//                'login_ip' => get_client_ip()
//            );
//            M("admin_user")->where(array('id'=>$exist['id']))->save($data);
//            cookie('admin_id',$exist['id'],time()+86400);
//            session('our_adminId',$exist['id']);
//            $url = U('Index/index');
//            $res_info['code'] = 0;
//            $res_info['msg'] = '登陆成功';
//            $res_info['url'] = $url;
//        }else{
//            $res_info['code'] = 2;
//            $res_info['msg'] = '账号或密码错误';
//        }
//        $this->ajaxReturn($res_info);
//
//    }

    /**
     * @param $phone
     * @param $project
     * @return mixed
     * 处理发送验证码
     */
    public function handleSendMs($phone,$project)
    {
        $msg_info = M("msg_token")->find();
        $url = "http://api.mysubmail.com/message/xsend.json";
        $appid = $msg_info['msg_appid'];
        $appkey = $msg_info['msg_appkey'];
        $code = $this->randNumber(6);
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
            session('sale_msg_code',$code);
            $res_info['code'] = 1;
            $res_info['msg'] = '短信发送成功，请查收';
        }else{
            $res_info['code'] = 2;
            $res_info['msg'] = '验证码发送失败';
        }
        return $res_info;

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
        }else{
            $exist = M("sms_user")->where(array('tel_phone'=>$phone))->find();
            if($exist){
                $res = $this->handleSendMs($phone,$project);
                $this->ajaxReturn($res);
            }else{
                $res['code'] = 4;
                $res['msg'] = '手机号未注册';
                $this->ajaxReturn($res);die;
            }
        }

    }
    /**
     * 验证登录
     */
    public function login_users()
    {
        if(!IS_POST){
            $res_info['code'] = 1;
            $res_info['msg'] = '非法操作！稍后重试';
            $this->ajaxReturn($res_info);die;
        }
        $username = I('username');
        $code = I('code');
        $exist = M("sms_user")->where(array('tel_phone'=>$username))->find();
        $msg_code = session('sale_msg_code');
        if(empty($msg_code)){
            $res_info['code'] = 1;
            $res_info['msg'] = '请先获取验证码';
            $this->ajaxReturn($res_info);die;
        }
        // $pwd = md5(md5(I('password')));
        // $exist = M("admin_user")->where(array('username'=>$username,'password'=>$pwd))->find();
        if($exist && $code==$msg_code){
            $data = array(
                'login_time' => time(),
                'login_ip' => get_client_ip()
            );
            M("sms_user")->where(array('id'=>$exist['id']))->save($data);
            session('sale_our_adminId',$exist['id']);
            cookie('admin_id',$exist['id'],time()+86400);
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
    //数字随机码
    public function randNumber($len = 6)
    {
        $chars = str_repeat('0123456789', 10);
        $chars = str_shuffle($chars);
        $str   = substr($chars, 0, $len);
        return $str;
    }

    //退出
    public function logout(){
        session('sale_our_adminId',null);
        redirect(U('Login/login'));
    }

}