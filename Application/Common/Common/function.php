<?php 
function dd($data)
{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}
//检测用户session跟cookie是否存在
function check_cookie_exist(){
    //获取课程上一步地址
    $url= 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    cookie('category_url',$url);
    /*获取上一次用户微信授权的id cookie记录*/
    $cookie_user_id = cookie('auth_user_id');
    $user_id =session('adminId');
    if(!$user_id){
        //判断cookie是否存在
        if($cookie_user_id){
            //cookie存在，建立session，完成自动登录
            session('adminId',$cookie_user_id);
            return true;
        }else{

            //cookie不存在
            return false;
        }
    } else {
        return true;
    }
}
/**
 * 获取排序后的分类
 * @param  [type]  $data  [description]
 * @param  integer $pid   [description]
 * @param  string  $html  [description]
 * @param  integer $level [description]
 * @return [type]         [description]
 */
function getSortedCategory($data,$uid=0,$html="|---",$level=0)
{
	$temp = array();
	foreach ($data as $k => $v) {
		if($v['uid'] == $uid){
	
			$str = str_repeat($html, $level);
			$v['html'] = $str;
			$temp[] = $v;

			$temp = array_merge($temp,getSortedCategory($data,$v['uid'],'|---',$level+1));
		}
		
	}
	return $temp;
}

/**
 * 根据key，返回当前行的所有数据
 * @param  string  $key  字段key
 * @return array         当前行的所有数据
 */
function getSettingValueDataByKey($key)
{
	return M('setting')->getByKey($key);
}

/**
 * 根据key返回field字段
 * @param  string $key   [description]
 * @param  string $field [description]
 * @return string        [description]
 */
function getSettingValueFieldByKey($key,$field)
{
	return M('setting')->getFieldByKey($key,$field);
}


/*
*输入字符串，返回指定长度的字符串
*/
function get_sub_string($string,$length){
	if(mb_strlen($string,'utf-8') >$length)
	return mb_substr($string,0,$length,'UTF-8')."…";
	else
	return $string;
}

function sendSMS($uid,$pwd,$mobile,$content,$template='')
{
	$apiUrl = 'http://api.sms.cn/sms/';		//短信接口地址
	$data = array(
		'ac' =>		'send',
		'uid'=>		$uid,					//用户账号
		'pwd'=>		md5($pwd.$uid),					//MD5位32密码,密码和用户名拼接字符
		'mobile'=>	$mobile,				//号码
		'content'=>	$content,				//内容
		'template'=>$template,				//变量模板ID 全文模板不用填写
		'format' => 'json',					//接口返回信息格式 json\xml\txt
		);
	
	$result = postSMS($apiUrl,$data);			//POST方式提交
	$re = json_to_array($result);			    //JSON数据转为数组
	//$re = getSMS($apiUrl,$data);				//GET方式提交
	
	return $re;
	/*
	if( $re['stat']=='100' )
	{
		return "发送成功!";
	}
	else if( $re['stat']=='101')
	{
		return "验证失败! 状态：".$re;
	}
	else 
	{
		return "发送失败! 状态：".$re;
	}
	*/
}

function postSMS($url,$data='')
{
	$row = parse_url($url);
	$host = $row['host'];
	$port = $row['port'] ? $row['port']:80;
	$file = $row['path'];
	while (list($k,$v) = each($data)) 
	{
		$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
	}
	$post = substr( $post , 0 , -1 );
	$len = strlen($post);
	$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
	if (!$fp) {
		return "$errstr ($errno)\n";
	} else {
		$receive = '';
		$out = "POST $file HTTP/1.1\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Content-Length: $len\r\n\r\n";
		$out .= $post;		
		fwrite($fp, $out);
		while (!feof($fp)) {
			$receive .= fgets($fp, 128);
		}
		fclose($fp);
		$receive = explode("\r\n\r\n",$receive);
		unset($receive[0]);
		return implode("",$receive);
	}
}

function getSMS($url,$data='')
{
	$get='';
	while (list($k,$v) = each($data)) 
	{
		$get .= $k."=".urlencode($v)."&";	//转URL标准码
	}
	return file_get_contents($url.'?'.$get);
}
//数字随机码
function randNumber($len = 6)
{
	$chars = str_repeat('0123456789', 10);
	$chars = str_shuffle($chars);
	$str   = substr($chars, 0, $len);
	return $str;
}
//把数组转json字符串
function array_to_json($p)
{
	return urldecode(json_encode(json_urlencode($p)));
}
//url转码
function json_urlencode($p)
{
	if( is_array($p) )
	{
		foreach( $p as $key => $value )$p[$key] = json_urlencode($value);
	}
	else
	{
		$p = urlencode($p);
	}
	return $p;
}

//把json字符串转数组
function json_to_array($p)
{
	if( mb_detect_encoding($p,array('ASCII','UTF-8','GB2312','GBK')) != 'UTF-8' )
	{
		$p = iconv('GBK','UTF-8',$p);
	}
	return json_decode($p, true);
}
/*
*CURL获取图片并存储到本地
*@path 图片的URL地址
*@file_dir 图片存储在服务器的地址
*/
function saveImage($path,$file_dir) {
// 	if(!preg_match('/\/([^\/]+\.[a-z]{3,4})$/i',$path,$matches))
// 		die('Use image please');
	$image_name = uniqid().".jpg";//strToLower($matches[1]);
	$ch = curl_init ($path);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	$img = curl_exec ($ch);
	if(curl_exec($ch)===false) return false;
	curl_close ($ch);
	$fp = fopen($file_dir.$image_name,'w');
	fwrite($fp, $img);
	fclose($fp);
	return $image_name;
}

function getImage($url,$save_dir='',$filename='',$type=0){
    if(trim($url)==''){
        return array('file_name'=>'','save_path'=>'','error'=>1);
    }
    if(trim($save_dir)==''){
        $save_dir='./';
    }
    if(trim($filename)==''){//保存文件名
        $ext=strrchr($url,'.');
        if($ext!='.gif'&&$ext!='.jpg'&&$ext!='.png'&&$ext!='.jpeg'){
            return array('file_name'=>'','save_path'=>'','error'=>3);
        }
        $filename=time().rand(0,10000).$ext;
    }
    if(0!==strrpos($save_dir,'/')){
        $save_dir.='/';
    }
    //创建保存目录
    if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
        return array('file_name'=>'','save_path'=>'','error'=>5);
    }
    //获取远程文件所采用的方法
    if($type){
        $ch=curl_init();
        $timeout=5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $img=curl_exec($ch);
        curl_close($ch);
    }else{
        ob_start();
        readfile($url);
        $img=ob_get_contents();
        ob_end_clean();
    }
    //$size=strlen($img);
    //文件大小
    $fp2=@fopen($save_dir.$filename,'a');
    fwrite($fp2,$img);
    fclose($fp2);
    unset($img,$url);
    return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
}

function saveUserHeadImg($downls){
    if($downls==''){
        $return_head_img='default.png';
    }else{
        $downl=str_replace("/0", "/132", "$downls");
        $saveDir = "./Public/upload/user_head";
        $fileName = uniqid().".jpg";
        $return_head_img = getImage($downl, $saveDir, $fileName,1);
        if($return_head_img['error']==0){
            $return_head_img = $fileName;
        }else{
            $return_head_img='default.png';
        }
        $file_size = filesize("./Public/upload/user_head/".$return_head_img);
        if($file_size==0){
            $return_head_img='default.png';
        }
    }
    return $return_head_img;
}

function encrypt($str){
	return $str;

	/*$key=65571771;
	$iv=65626297;
	//加密
	$size = mcrypt_get_block_size(MCRYPT_DES,MCRYPT_MODE_CBC);
	$pad=$size-(strlen(($str)%$size));
	$str = $str.str_repeat(chr($pad),$pad);
	return base64_encode((mcrypt_cbc(MCRYPT_DES,$key,$str,MCRYPT_ENCRYPT,$iv)));*/
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    $fix='';
    if(strlen($slice) < strlen($str)){
        $fix='...';
    }
    return $suffix ? $slice.$fix : $slice;
}


/**
 * 获取34位的唯一字符串
 *
 * @return string
 */
function createGuid() {
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45);// "-"
    $uuid = //chr(123)// "{"
    substr($charid, 0, 8).$hyphen
    .substr($charid, 8, 4).$hyphen
    .substr($charid,12, 4).$hyphen
    .substr($charid,16, 4).$hyphen
    .substr($charid,20,12);
    //.chr(125);// "}"
    return $uuid;
}

/**
 * 获取指定长度的随机码
 *
 * @param $len 获取长度
 * @return string
 */
function getSpecLenRandomCode($len) {
	$chars = array (
			"a","b","c","d","e","f","g","h","i","j","k","l","m","n","o",
			"p","q","r","s","t","u","v","w","x","y","z","A","B","C","D",
			"E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S",
			"T","U","V","W","X","Y","Z","0","1","2","3","4","5","6","7",
			"8","9"
	);
	$charsLen = count ( $chars ) - 1;
	shuffle ( $chars ); // 将数组打乱

	$output = "";
	for($i = 0; $i < $len; $i ++) {
		$output .= $chars [rand ( 0, $charsLen )];
	}
	return $output;
}

/**
 * 上传图片获取时间组成的字符串
 * @return string
 */
function get_date_str()
{
	return date('YmdHis').rand(10000, 99999);
}

/**
 * VIP价格计算
 * @param $price
 * @return mixed
 */
function checkVipPrice($price)
{
    return floor($price * 0.88);
}

/**
 * 格式化时间戳
 * @param $list
 * @param $key
 * @param $type
 * @return false|string
 */
function formatTimeArr($list,$key,$type = 1)
{
    if (is_array($list)) {
        $count = count($list);
        for ($i = 0; $i < $count; $i++) {
            $list[$i][$key] = formatTimeStr($list[$i][$key],$type);
        }
    }
    return $list;
}

function formatTimeStr($time,$type = 1)
{
    $now = time();
    $diffTime = $now -  $time;

    switch (true) {
        case ($diffTime >= 0 && $diffTime < 3600) :
            return ceil($diffTime / 60).'分钟前';
        case ($diffTime >= 3600 && $diffTime < 24 * 3600) :
            //超过一个小时 但在10分钟内 向下取整
            if (($diffTime % 3600) <= 10 * 60)  return floor($diffTime / 3600).'小时前';
            return ceil($diffTime / 3600).'小时前';
        case ($diffTime >= 24*3600 && $diffTime < 30*24 * 3600) :
            return ceil($diffTime / (24*3600)).'天前';
        default :
            switch ($type) {
                case 1 :
                    return date('Y-m-d',$time);
                case 2 :
                    return date('Y/m/d',$time);
                default :
                    return date('Y-m-d',$time);
            }
    }

}