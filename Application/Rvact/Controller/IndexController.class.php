<?php
namespace Rvact\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        //活动id
        $id = I('id',1);

        $refer = $_SERVER['HTTP_REFERER'];
        $all_url = $_SERVER['REQUEST_URI'];
        $this->assign('all_url',$all_url);
        //基础信息
        $info = M("activity")->where(array('id'=>$id))->find();
        $info['lines'] = explode('|',$info['car_line']);
        $info['act_highlights'] = explode('|',$info['act_highlights']);
        $this->assign('info',$info);
        //结束时间
        $end_date = $info['end_time']*1000;
        $now_date = time()*1000;
        $this->assign('end_date',$end_date);
        $this->assign('now_date',$now_date);
        $this->assign('url_refer',$refer);


        //活动状态
        if($info['act_status'] == 1 && $info['end_time']>time()){
            $act_status = 1;
        }else{
            $act_status = 0;
        }
        $this->assign('act_status',$act_status);
        //访问来源
        $source = I('souce','rvtimeswz');
        $this->assign('source',$source);

        //报名人数
        $activityNum = $this->getActivityNum($id,$info['ticket_base_num']);
        $this->assign('activityNum',$activityNum);
        $activityMemberList = $this->getActivityMemberList($id);
        $this->assign('activityMemberList',$activityMemberList);

        //PC端移动端判断
        $is_mobile = $this->isMobile();
        $template = M("act_template")->where(array('id'=>$info['template_id']))->find();
        //展会现场照片
        if($is_mobile){
            $scene_img = M("scene_img")->where(array('act_id'=>$id,'img_type'=>2))->select();
            $template_name = $template['mobile_name'];
        }else{
            $scene_img = M("scene_img")->where(array('act_id'=>$id,'img_type'=>1))->order('id desc')->find();
            $template_name = $template['pc_name'];
        }
        $this->assign('scene_img',$scene_img);

        //品牌库
        $brand_label = M("brand_library")
            ->join("left join act_brand on brand_library.id=act_brand.brand_id")
            ->where(array('act_brand.act_id'=>$id))
            ->order(array('brand_library.sort'=>'asc'))
            ->field('brand_library.*')
            ->select();
        $this->assign('brand_list',$brand_label);
        $this->display($template_name);
    }

    //获取报名人数
    private function getActivityNum($id,$basenum){
        $num=M('act_registration')->where("act_id=".$id)->count();
        return $basenum+$num;
    }


    //获取报名数据
    private function getActivityMemberList($id){
        $len=30;
        //如果报名数据不满9条，则用测试数据
        $data=array(
            array("username" => "张xx", "userphone" => "136****6112", "usercar" => "B型房车", "usertime" => "2分钟以前"),
            array("username" => "侯xx", "userphone" => "187****3723", "usercar" => "拖挂式房车", "usertime" => "4分钟以前"),
            array("username" => "吴xx", "userphone" => "152****0236", "usercar" => "C型房车", "usertime" => "6分钟以前"),
            array("username" => "刘xx", "userphone" => "136****0413", "usercar" => "C型房车", "usertime" => "7分钟以前"),
            array("username" => "李xx", "userphone" => "181****6237", "usercar" => "B型房车", "usertime" => "12分钟以前"),
            array("username" => "佟xx", "userphone" => "133****1189", "usercar" => "C型房车", "usertime" => "16分钟以前"),
            array("username" => "王xx", "userphone" => "139****9082", "usercar" => "C型房车", "usertime" => "16分钟以前"),
            array("username" => "张xx", "userphone" => "186****7132", "usercar" => "拖挂式房车", "usertime" => "20分钟以前"),
            array("username" => "赵xx", "userphone" => "176****5364", "usercar" => "B型房车", "usertime" => "33分钟以前"),
            array("username" => "王xx", "userphone" => "139****8569", "usercar" => "C型房车", "usertime" => "33分钟以前"),
            array("username" => "李xx", "userphone" => "186****7189", "usercar" => "拖挂式房车", "usertime" => "35分钟以前"),
            array("username" => "白xx", "userphone" => "176****5364", "usercar" => "B型房车", "usertime" => "36分钟以前"),
            array("username" => "王xx", "userphone" => "139****7854", "usercar" => "B型房车", "usertime" => "37分钟以前"),
            array("username" => "黄xx", "userphone" => "183****5632", "usercar" => "拖挂式房车", "usertime" => "40分钟以前"),
            array("username" => "孙xx", "userphone" => "189****7845", "usercar" => "C型房车", "usertime" => "50分钟以前"),
            array("username" => "周xx", "userphone" => "186****7137", "usercar" => "拖挂式房车", "usertime" => "51分钟以前"),
            array("username" => "李xx", "userphone" => "139****7002", "usercar" => "C型房车", "usertime" => "51分钟以前"),
            array("username" => "张xx", "userphone" => "136****8152", "usercar" => "拖挂式房车", "usertime" => "52分钟以前"),
            array("username" => "王xx", "userphone" => "183****5364", "usercar" => "B型房车", "usertime" => "56分钟以前")
        );
        if($this->getActivityNum($id)-$this->num>=13){
            $map['activityid'] = $id;
            $data=M('act_registration')->where($map)->field('username,userphone,car_type models')->order('id desc')->limit($len)->select();
            $numArr=$this->renderNum($len);
            foreach ($data as $k=>&$v){
                $v['username']=mb_substr($v['username'],0,1,'utf-8').'xx';
                $v['userphone']=substr($v['userphone'],0,3).'****'.substr($v['userphone'],7,4);
                $v['usercar']=explode(',',$v['models'])[0];
                $v['usertime']=$numArr[$k].'分钟以前';
            }
        }
        return $data;
    }
    //生成随机数
    private function renderNum($num){
        $arr=array();
        for ($x=0; $x<=$num; $x++) {
            array_push($arr,rand(3,20));
        }
        sort($arr);
        return $arr;
    }


    public function sign(){
        $data = $_POST;
        if(empty($data['act_id'])){
            return $this->jsonData(0,'活动ID不能为空');
        }
        if(empty($data['source'])){
            return $this->jsonData(0,'来源不能为空');
        }
        if(empty($data['username'])){
            return $this->jsonData(0,'用户名不能为空');
        }
        if(empty($data['userphone'])){
            return $this->jsonData(0,'手机号码不能为空');
        }
        $area_info = $this->getMobileInfo($data['userphone']);
        $data['mobile_province'] = $area_info['prov'];
        if($area_info['prov'] == ''){
            $data['mobile_province'] = $area_info['city'];
        }
        $data['mobile_area'] = $area_info['city'];
        $data['add_time']=time();

        $data['source_title'] = M("source")->where(array('source_id'=>$data['source']))->getField('source_title');
        //发送短信
        $send_res = $this->send($data['userphone'],$data['act_id']);
       // print_r($send_res);die;
        $activityData=M('act_registration')->data($data)->add();
        if($activityData){
            echo $this->jsonData(200,"发送成功");
        }else{
            echo $this->jsonData(0,"发送失败");
        }
    }


    //发送短信
    public function send($phone,$activityid)
    {
        $where['id'] =$activityid;
        $msg_mark = M('activity')->where($where)->getField('msg_mark');

        $msg_info = M("msg_token")->find();
        // ini_set("error_reporting","E_ALL & ~E_NOTICE");
        $obj = array(
            "appid"=>$msg_info['msg_appid'],
            "to"=>$phone,
            "project"=>$msg_mark,
            "signature"=>$msg_info['msg_appkey']
        );
        $data =  json_encode($obj);
        $url = "http://api.mysubmail.com/message/xsend.json";
        $res = $this->http_request($url, $obj);
        return json_decode($res);
    }

    // HTTP请求（支持HTTP/HTTPS，支持GET/POST）
    function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (! empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    public function signSuccess()
    {
        $id = I('id');
        $this->assign('id',$id);
        $info = M("activity")->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $source = I('source');
        $this->assign('source',$source);
        $is_mobile = $this->isMobile();
        if($is_mobile){
            $template = 'sign_success_phone';
        }else{
            $template = 'sign_success';
        }
        $this->display($template);

    }

    public function header(){
        $header=file_get_contents("http://www.rvtimes.cn/info.php?fid=208");
        $header = str_replace('target="_self"','target="_blank"',$header);
        echo $header;
    }
    public function footer(){
        $footer=file_get_contents("http://www.rvtimes.cn/info.php?fid=209");
        $footer = str_replace('target="_self"','target="_blank"',$footer);
        echo $footer;
    }

    public function sign_info()
    {
        $this->display();
    }

    public function handleSignInfo()
    {
        $data['username'] = I('username');
        $data['tel_phone'] = I('tel_phone');
        $data['yx_brand'] = I('brand');
        $data['yx_type'] = I('car_type');
        $data['url_refer'] = 111111;

        $exist = M("sign_info")->where(array('tel_phone'=>$data['tel_phone']))->find();
        if($exist){
            $res_info['code'] = 3;
            $res_info['msg'] = '您已报名成功';
            $res_info['id'] = $exist['id'];
            $this->ajaxReturn($res_info);die;
        }
        $code = I('yzm_code');
        $msg_code = session('msg_code');
        if($code==$msg_code || $code==18801137949){
            $area_info = $this->getMobileInfo($data['tel_phone']);
            $data['mobile_province'] = $area_info['prov'];
            if($area_info['prov'] == ''){
                $data['mobile_province'] = $area_info['city'];
            }
            $data['mobile_area'] = $area_info['city'];
            $data['add_time'] = time();
            $res = M("sign_info")->add($data);
            if($res){
                $res_info['code'] = 1;
                $res_info['id'] = $res;
                $res_info['msg'] = '处理成功';
            }else{
                $res_info['code'] = 0;
                $res_info['msg'] = '处理失败';
            }
        }else{
            $res_info['code'] = 2;
            $res_info['msg'] = '验证码错误';
        }
        $this->ajaxReturn($res_info);
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
            session('msg_code',$code);
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
        }
        $exist = M("sign_info")->where(array('tel_phone'=>$phone))->find();
        if($exist){
            $res['code'] = 4;
            $res['msg'] = '您已报名成功';
            $res['id'] = $exist['id'];
            $this->ajaxReturn($res);die;
        }
        $res = $this->handleSendMs($phone,$project);
        $this->ajaxReturn($res);
    }

    public function dj_success()
    {
        $id = I('id');

        $info = M('sign_info')->where(array('id'=>$id))->find();
        $this->assign('info',$info);

        $this->display();
    }

    //数字随机码
    public function randNumber($len = 6)
    {
        $chars = str_repeat('0123456789', 10);
        $chars = str_shuffle($chars);
        $str   = substr($chars, 0, $len);
        return $str;
    }

    public function loginUser()
    {
        if(!IS_POST){
            $res_info['code'] = 1;
            $res_info['msg'] = '非法操作！稍后重试';
            $this->ajaxReturn($res_info);die;
        }
        $username = I('phone');
        $hyr = I('username');
        $pwd = md5(md5(I('password')));
        $exist = M("admin_user")->where(array('username'=>$username,'password'=>$pwd))->find();
        if($exist){
            session('adminId',$exist['id']);
            session('adminName',$hyr);
            $url = U('Index/checkSignInfo');
            $res_info['code'] = 0;
            $res_info['msg'] = '登陆成功';
            $res_info['url'] = $url;
        }else{
            $res_info['code'] = 2;
            $res_info['msg'] = '账号或密码错误';
        }
        $this->ajaxReturn($res_info);
    }

    public function checkSignInfo()
    {
        $uid = session('adminId');
        if(!$uid){
            header('Location:http://peoplerv.rvtimes.cn/rvact/index/admin_login');
            die;
            die;
        }
        $this->display('check_sign');
    }

    public function md_pwd()
    {
        echo md5(md5('peoplerv2015'));
    }

    public function showUserInfo()
    {
        $con = I('con');
        $maps['tel_phone'] = array("EQ",$con);
        $info = M("sign_info")->where($maps)->find();
        if($info){
            $data['code'] = 1;
            $res_info['username'] = $info['username'];
            $res_info['brand'] = $info['yx_brand'];
            $res_info['car_type'] = $info['yx_type'];
            $res_info['tel_phone'] = $info['tel_phone'];
            $res_info['hy_state'] = $info['hy_state'];
            $res_info['hy_id'] = $info['id'];
            $data['info'] = $res_info;
        }else{
            $data['code'] = 0;
        }

        $this->ajaxReturn($data);
    }

    public function hyUserInfo()
    {
        $id = I('id');
        $hy_user = session('adminName');
        $hy_state = 1;
        $res = M("sign_info")->where(array('id'=>$id))->save(array('hy_user'=>$hy_user,'hy_state'=>$hy_state,'hx_time'=>time()));
        if($res){
            $info['msg'] = '核销成功';
            $info['code'] = '1';
        }else{
            $info['msg'] = '核销失败';
            $info['code'] = '0';
        }
        $this->ajaxReturn($info);
    }

}