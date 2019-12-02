<?php
namespace Rvact\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        //活动id
        $id = I('id',1);

        $all_url = $_SERVER['REQUEST_URI'];
        $this->assign('all_url',$all_url);
        //基础信息
        $info = M("activity")->where(array('id'=>$id))->find();
        $info['lines'] = explode('|',$info['car_line']);
        $info['act_highlights'] = explode('|',$info['act_highlights']);
        $this->assign('info',$info);

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
            $scene_img = M("scene_img")->where(array('act_id'=>$id,'img_type'=>1))->find();
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
        // ini_set("error_reporting","E_ALL & ~E_NOTICE");
        $obj = array(
            "appid"=>"40135",
            "to"=>$phone,
            "project"=>$msg_mark,
            "signature"=>"1bb05e3b06a5b1e1c4d806d5367fa959"
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
}