<?php
namespace Rvact\Controller;
use Think\Controller;
use Rvact\Controller\JssdkController;
class SignController extends BaseController {
    private $appid = 'wx60a219d641d78c8a';
    private $sessionKey = '';
    private $appsecret = '453d4bcb7b2082f1f596bcd47e398e23';
    /**
     * 构造函数
     * @param $sessionKey string 用户在小程序登录后获取的会话密钥
     * @param $appid string 小程序的appid
     */
    public function __construct()
    {
        $sessionKey = $this->sessionKey;
        $appid = $this->appid;
        $appsecret = $this->appsecret;

    }
    public function handleUserLogin()
    {

        $code = I('code');
        $appid = $this->appid;
        $appsecret = $this->appsecret;
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$appsecret."&js_code=".$code."&grant_type=authorization_code";
        $token_json = $this->httpGet($url);
        $token_array = json_decode($token_json);
        $open_id = $token_array -> openid;
        $session_key = $token_array -> session_key;
        session($open_id,$session_key);
        //记录sessionKey
        $arr['open_id'] = $open_id;
        $arr['code'] = 1;
        $pid = M("sign_ticket")->where(array('sort'=>1))->getField('pid');
        $arr['signcity'] = M("activity")->where(array('id'=>$pid))->getField('sign_distance');
        $arr['errMsg'] = '请求成功';
        $this->ajaxReturn($arr);
    }

    public function checkUserLogin()
    {

        $uid = I('uid');
        $session_key = session("$uid");
        if($session_key){
            $arr['code'] = 1;
            $arr['errMsg'] = 'sessionKey未过期';
        }else{
            $arr['code'] = 0;
            $arr['errMsg'] = 'sessionKey已过期';
        }
        $this->ajaxReturn($arr);
    }


    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    public function handleUserPhone()
    {
        $appid = $this->appid;
        $iv = I('iv');
        $uid = I('uid');
        $session_key = session($uid);
        $encryptedData = I('encryptedData');
        $errCode = $this->decryptData($encryptedData, $iv, $data, $session_key,$appid);
        if ($errCode == 0) {
            print($data . "\n");
        } else {
            print($errCode . "\n");
        }
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $encryptedData, $iv, &$data, $session_key, $appid )
    {
        if (strlen($session_key) != 24) {
            return '-41002';
        }
        $aesKey=base64_decode($session_key);
        if (strlen($iv) != 24) {
            return '-41002';
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return '-41003';
        }
        if( $dataObj->watermark->appid != $appid )
        {
            return '-41003';
        }
        $data = $result;
        return 0;
    }

    public function handleUserSign()
    {
        $pid = M("sign_ticket")->where(array('sort'=>1))->getField('pid');
        $userphone = I('userphone');

        $maps['userphone'] = $userphone;
        $maps['act_id'] = $pid;
        $exist = M("act_registration")->where($maps)->find();
        if ($exist) {
           if ($exist['arrival_status'] == 0) {
               $data['arrival_status'] = 1;
               $data['arrival_time'] = time();
               $save_res = M("act_registration")->where($maps)->save($data);
               if($save_res){
                   $res['code'] = 1;
                   $res['sign_time'] = date("Y-m-d H:i",time());
                   $res['msg'] = '签到成功';
               }else{
                   $res['code'] = 0;
                   $res['msg'] = '处理失败';
               }
           }else{
               $res['code'] = 1;
               $res['msg'] = '已签到';
               $res['sign_time'] = date("Y-m-d H:i",$exist['arrival_time']);
           }
        } else {
           // $res['code'] = 0;
           // $res['msg'] = '未查询到报名信息，请完善您的姓名';

            $data['act_id'] = M("sign_ticket")->where(array('sort'=>1))->getField('pid');
            $data['userphone'] = $userphone;
            $data['username'] = I('username');
            $data['arrival_status'] = 1;
            $data['arrival_time'] = time();
            $area_info = $this->getMobileInfo($data['userphone']);
            $data['mobile_province'] = $area_info['prov'];
            if($area_info['prov'] == ''){
                $data['mobile_province'] = $area_info['city'];
            }
            $data['mobile_area'] = $area_info['city'];
            $data['add_time']=time();
            $data['source_title'] = '小程序';
            $addRes = M("act_registration")->add($data);
            if($addRes){
                $res['code'] = 1;
                $res['sign_time'] = date("Y-m-d H:i",time());
                $res['msg'] = '报名及签到成功';
            }else{
                $res['code'] = 0;
                $res['msg'] = '处理失败';
            }
        }
        $this->ajaxReturn($res);
    }

    public function handleUserEnroll()
    {
        $data['act_id'] = M("sign_ticket")->where(array('sort'=>1))->getField('pid');
        $data['userphone'] = I('userphone');
        $data['username'] = I('username');
        $data['arrival_status'] = 1;
        $data['arrival_time'] = time();
        $area_info = $this->getMobileInfo($data['userphone']);
        $data['mobile_province'] = $area_info['prov'];
        if($area_info['prov'] == ''){
            $data['mobile_province'] = $area_info['city'];
        }
        $data['mobile_area'] = $area_info['city'];
        $data['add_time']=time();
        $data['source_title'] = '小程序';
        $addRes = M("act_registration")->add($data);
        if($addRes){
            $res['code'] = 1;
            $res['sign_time'] = date("Y-m-d H:i",time());
            $res['msg'] = '报名及签到成功';
        }else{
            $res['code'] = 0;
            $res['msg'] = '处理失败';
        }
        $this->ajaxReturn($res);
    }

    public function checkSignState()
    {
        $info =  M("sign_ticket")->where(array('sort'=>1))->find();
        $pid = $info['pid'];
        $act_info = M("activity")->where(array("id"=>$pid))->field('sign_status,start_time,end_time')->find();
        $now = time();
        //判断签到状态为可签到且当前时间为可签到时间
        if($act_info['status'] == 1 && $now > $act_info['start_time'] && $now < $act_info['end_time']){
            $status = 1;
        }else{
            $status = 0;
        }
        $res['code'] = $status;
        $res['msg'] = '查询签到开启状态处理成功';
        $this->ajaxReturn($res);
    }

    public function checkSignInfo()
    {
        $info =  M("sign_ticket")->where(array('sort'=>1))->find();
        $res['title'] = M("activity")->where(array('id'=>$info['pid']))->getField('title');
        $res['lat'] = $info['sign_lat'];
        $res['lng'] = $info['sign_lng'];
        $res['img'] = 'https://peoplerv.rvtimes.cn/Public/upload/sign/'.$info['sign_img'];
        $res['msg'] = '查询签到页面信息';
        $this->ajaxReturn($res);
    }
}