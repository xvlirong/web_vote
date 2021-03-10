<?php
return array(
	//'配置项'=>'配置值'
    //主题静态文件路径
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__.'/Application/'.MODULE_NAME.'/View/Public/static',
    ),
    'MULTI_MODULE'=> false,
    'weixin'=>array(
        'appid' => 'wx25c3d8d3570674cd',
        'appsecret' => '913e9fe977454d5ba1a98a2ea51b62ad'
    ),
);