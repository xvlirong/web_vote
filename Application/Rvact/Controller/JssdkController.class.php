<?php
namespace Rvact\Controller;
use Think\Controller;
class JssdkController extends BaseController {
    private $appId='wxbd4d33e0bd2dbe99';
    private $appSecret='91b9a69fbb9747a4363659eb201b9146';

    public function __construct($appId, $appSecret) {

        $this->appId = C('weixin.appid');
        $this->appSecret = C('weixin.appsecret');
    }

    public function getSignPackage($url='') {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        if(!$url){
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        // $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        //$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => 'wx25c3d8d3570674cd',
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function  getJsApiTicket(){
        $appid = $this->appId;
        $appsecret = $this->appSecret;
        $weixin=M('weixin_access');
        $now=time();
        //dump($now);die;
        $data=$weixin->where(array('appid'=>$appid,'expires_time'=>array('gt',$now)))->find();
        //dump($data);die;
        if(!$data){
            $new_access_token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
            $new_access_token_json = $this->httpGet($new_access_token_url);
            $new_access_token_array = json_decode($new_access_token_json,true);
            $new_access_token = $new_access_token_array['access_token'];
            $jsapi_ticket_url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$new_access_token&type=jsapi";
            $jsapi_ticket_json = $this->httpGet($jsapi_ticket_url);
            $jsapi_ticket_array = json_decode($jsapi_ticket_json, true);
            $jsapi_ticket=$jsapi_ticket_array['ticket'];
            $data['access_token']=$new_access_token;
            $data['jsapi_ticket']=$jsapi_ticket;
            $data['expires_time']=time()+7000;
            $data['appid']=$appid;
            //dump($data);die;
            $weixin->where(array('appid'=>$appid))->add($data);
        }else{

            $jsapi_ticket=$data['jsapi_ticket'];
        }
        return 	$jsapi_ticket;
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
}