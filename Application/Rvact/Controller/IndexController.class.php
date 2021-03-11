<?php
namespace Rvact\Controller;
use Think\Controller;
use Rvact\Controller\JssdkController;
class IndexController extends BaseController {
    public function index(){
        //活动id
        $id = I('id',1);
        cookie('act_id',$id,time()+3600);
        $refer = $_SERVER['HTTP_REFERER'];
        $all_url = $_SERVER['REQUEST_URI'];
        $fx_url = 'http://peoplerv.rvtimes.cn/rvact/index/js_api';
        //基础信息
        $info = M("activity")->where(array('id'=>$id))->find();
        $this->checkIsSign($info);
        $this->assign('all_url',$all_url);
        $this->assign('fx_url',$fx_url);

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


    public function checkIsSign($info)
    {
        $sign_cookie = cookie('sign_cookie');
        if(empty($sign_cookie)){
        }else{
            $cookie_info = explode('+',$sign_cookie);

            if($cookie_info[1]==$info['id']){
                $source = M("act_registration")
                    ->where(array('userphone'=>$cookie_info[0]))
                    ->order(array('id'=>'desc'))
                    ->getField('source');
                redirect("/Rvact/Index/signSuccess/id/".$info['id']."/source/"."$source");
                die;
            }


        }
    }

    //获取报名人数
    private function getActivityNum($id,$basenum){
        $num=M('act_registration')->where("act_id=".$id)->count();
        return $basenum+$num;
    }


    //获取报名数据
    private function getActivityMemberList($id){

        $data = M("show_enroll")->where(array('pid'=>$id))->order(array('usertime'=>'desc'))->limit(50)->select();

        for( $i = 0; $i < count($data); $i++ ){
            $data[$i]['usertimes'] = $this->formatDate($data[$i]['usertime']);
        }
        return $data;
    }
    
    //计算时间差

    public function formatDate($sTime)
    {
        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime  = time();
        $dTime  = $cTime - $sTime;
        $dDay  = intval(date("Ymd",$cTime)) - intval(date("Ymd",$sTime));
        $dYear  = intval(date("Y",$cTime)) - intval(date("Y",$sTime));
        if( $dTime < 60 ){
            $dTime =  $dTime."秒前";
        }elseif( $dTime < 3600 ){
            $dTime =  intval($dTime/60)."分钟前";
        }elseif( $dTime >= 3600 && $dDay == 0  ){
            $dTime =  $text = floor(($cTime-$sTime) / (60 * 60)) . '小时前'; // 一天内;
        }elseif($dYear==0){
            $dTime =  date("m-d H:i",$sTime);
        }else{
            $dTime =  date("Y-m-d H:i",$sTime);
        }
        return $dTime;
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

    /**
     * @param $data
     *
     */
    public function handleShowEnroll($username,$userphone,$usercar,$usertime,$id)
    {
        $data['username'] = mb_substr($username,0,1,'utf-8').'xx';
        $data['userphone'] = substr($userphone,0,3).'****'.substr($userphone,7,4);
        $data['usercar'] = $usercar;
        $data['usertime'] = $usertime;

        $data['pid'] = $id;

        $res = M("show_enroll")->add($data);
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
        $info = M("activity")->where(array('id'=>$data['act_id']))->find();
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
        $activityData=M('act_registration')->data($data)->add();
        if($activityData){
            $this->handleShowEnroll($data['username'],$data['userphone'],$data['car_type'],$data['add_time'],$data['act_id']);
            $now_time = time();
            if($now_time>$info['start_time']&&$now_time<$info['end_time']){
                $sign_cookie = $data['userphone'].'+'.$data['act_id'];
                cookie('sign_cookie',$sign_cookie,86400*5);
            }
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
        $data['url_refer'] = I('url_refer');

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

    public function handleActPlan()
    {
        $now_time = time();
        //活动列表
        $maps['end_time'] = array("GT",$now_time);
        $list = M("act_plan")
            ->join("left join activity on act_plan.pid=activity.id")
            ->where($maps)
            ->field('act_plan.*,activity.title,end_time')
            ->select();

        for ($i=0; $i<count($list); $i++){
            $num = mt_rand($list[$i]['start_num'],$list[$i]['end_num']);
            M("activity")->where(array('id'=>$list[$i]['pid']))->setInc('ticket_base_num',$num);
        }


    }

    /**
     * @param $count
     * @param string $type
     * @param bool $white_space
     * @return array|false|string|string[]|null
     * 虚拟手机号
     */
    public function generate_name($count,$type="array",$white_space=true)
    {
        $arr = array(
            130,131,132,133,134,135,136,137,138,139,
            144,147,
            150,151,152,153,155,156,157,158,159,
            176,177,178,
            180,181,182,183,184,185,186,187,188,189,
        );
        for($i = 0; $i < $count; $i++) {
            $tmp[] = $arr[array_rand($arr)].' **** '.mt_rand(1000,9999);
        }
        if($type==="string"){
            $tmp=json_encode($tmp);//如果是字符串，解析成字符串
        }
        if($white_space===true){
            $tmp=preg_replace("/\s*/","",$tmp);
        }
        return $tmp;
    }

    /**
     * @param int $name_count
     * @return array|string
     * 虚拟姓名
     */
    public function getname($name_count=1)
    {
        $firstname_arr  = array('赵','钱','孙','李','周','吴','郑','王','冯','陈','褚','卫','蒋','沈','韩','杨','朱','秦','尤','许','何','吕','施','张','孔','曹','严','华','金','魏','陶','姜',
            '戚','谢','邹','喻','柏','水','窦','章','云','苏','潘','葛','奚','范','彭','郎','鲁','韦','昌','马','苗','凤','花','方','任','袁','柳','鲍','史','唐','费','薛','雷','贺','倪',
            '汤','滕','殷','罗','毕','郝','安','常','傅','卞','齐','元','顾','孟','平','黄','穆','萧','尹','姚','邵','湛','汪','祁','毛','狄','米','伏','成','戴','谈','宋','茅','庞','熊',
            '纪','舒','屈','项','祝','董','梁','杜','阮','蓝','闵','季','贾','路','娄','江','童','颜','郭','梅','盛','林','钟','徐','邱','骆','高','夏','蔡','田','樊','胡','凌','霍','虞',
            '万','支','柯','管','卢','莫','柯','房','裘','缪','解','应','宗','丁','宣','邓','单','杭','洪','包','诸','左','石','崔','吉','龚','程','嵇','邢','裴','陆','荣','翁','荀','于',
            '惠','甄','曲','封','储','仲','伊','宁','仇','甘','武','符','刘','景','詹','龙','叶','幸','司','黎','溥','印','怀','蒲','邰','从','索','赖','卓','屠','池','乔','胥','闻','莘',
            '党','翟','谭','贡','劳','逄','姬','申','扶','堵','冉','雍','桑','寿','通','燕','浦','尚','农','温','别','庄','晏','柴','瞿','阎','连','习','容','向','古','易','廖','庾',
            '终','步','都','耿','满','弘','匡','国','文','寇','广','禄','阙','东','利','师','巩','聂','关','荆');

        //,$file_name='name.txt'
        // }
        $temp = '';
        for( $j=1 ;$j<=$name_count; $j++ )
        {
            $firstname_rand_key   = mt_rand( 0,count( $firstname_arr )-1 );
            $firstname   =  $firstname_arr[$firstname_rand_key];
            $temp[]=$firstname. 'xx';
        }
        return $temp;
    }



    public function show_enroll()
    {
        $id = I('id');
        //生成用户姓名
        $username = $this->getname(1);
        //生成用户手机号
        $userphone = $this->generate_name(1,'array');
        //生成报名时间
        $start_time = M("show_enroll")->order('id desc')->getField('usertime');
        $end_time = time();
        $use_time = rand($start_time,$end_time);
        //生成用户车型
        $car_class = array('B型房车','C型房车','拖挂式房车');
        $use_num = rand(0,2);

        //新增虚拟用户
        $data['username'] = $username[0];
        $data['userphone'] = $userphone[0];
        $data['usertime'] = $use_time;
        $data['usercar'] = $car_class[$use_num];
        $data['pid'] = $id;

        $res = M("show_enroll")->add($data);
        if($res){
            M("activity")->where(array('id'=>$id))->setInc('ticket_base_num',1);
        }
    }

    /**
     * 分享
     */
    public function js_api(){
        $url=htmlspecialchars_decode(trim(I('url')));
        $jssdk = A("Jssdk");
        $signPackage = $jssdk->GetSignPackage($url);
        $pid = cookie('act_id');
        $info = M("activity")->where(array('id'=>$pid))->find();
        $share_data['title']="【房车展会】".$info['title'];
        $share_data['desc']="点击链接查看展会详情";
        $share_data['link']=HTTP_TYPE."peoplerv.rvtimes.cn/rvact/index/index/id/".$pid."/souce/share";
        $share_data['imgUrl']=HTTP_TYPE."peoplerv.rvtimes.cn/Public/img/logo.jpg";

        if($signPackage){
            $data['result']="success";
            $data['js_data']=$signPackage;
            $data['share_data']=$share_data;
        }else{
            $data['result']="error";
            $data['js_data']='';
            $data['share_data']='';
        }
        $this->ajaxReturn($data);
    }
}