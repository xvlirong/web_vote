<?php
return array(
	//'配置项'=>'配置值'
    //主题静态文件路径
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => __ROOT__.'/Application/'.MODULE_NAME.'/View/Public/static',
    ),
    'MULTI_MODULE'=> false,
    'weixin'=>array(
        'appid' => 'wxbd4d33e0bd2dbe99',
        'appsecret' => '91b9a69fbb9747a4363659eb201b9146'
    ),
);