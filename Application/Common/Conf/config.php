<?php
return array(
	//'配置项'=>'配置值'
    define('HTTP_TYPE',((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'),
    //'配置项' =>'配置值'
    'MODULE_ALLOW_LIST' =>    array('Home','Admin','Wechat','App'),
    //我们用了入口版定 所以下面这行可以注释掉
    //'SHOW_PAGE_TRACE'   =>  true,
    'LOAD_EXT_CONFIG'   => 'db', //加载数据库配置
    'URL_CASE_INSENSITIVE'  =>  true,  //url不区分大小写
    'URL_ROUTER_ON' => true, //URL路由
    'URL_MODEL'   =>2,
    'URL_HTML_SUFFIX'  =>'html',

    //文件上传配置
	'UPLOAD_CONFIG'	=> array(
    'maxSize'	=> 0,	//文件上传的最大文件大小（以字节为单位），0为不限大小
    'rootPath'	=> './Public/',	//文件上传保存的根路径
    'savePath'	=> '',//文件上传的保存路径（相对于根路径）
    'saveName'	=> 'get_date_str',//上传文件的保存规则，支持数组和字符串方式定义
    //'saveExt'	=> 'jpg',//上传文件的保存后缀，不设置的话使用原文件后缀
    'replace'	=> true,//存在同名文件是否是覆盖，默认为false
    'exts'		=> array('jpg', 'gif', 'png', 'jpeg'),//允许上传的文件后缀（留空为不限制），使用数组或者逗号分隔的字符串设置，默认为空
    //'mimes'	//允许上传的文件类型（留空为不限制），使用数组或者逗号分隔的字符串设置，默认为空
    'autoSub'	=> false,//自动使用子目录保存上传文件 默认为true
    'subName'	=> '',	//子目录创建方式，采用数组或者字符串方式定义
    //'hash'		//是否生成文件的hash编码 默认为true
    //'callback'	//检测文件是否存在回调，如果存在返回文件信息数组
),
);