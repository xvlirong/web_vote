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
    'URL_HTML_SUFFIX'  =>'html'
);