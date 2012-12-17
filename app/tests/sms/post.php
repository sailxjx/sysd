<?php 
$url = 'http://123.196.114.68:8080/sms_send2.do?corp_id=1cbq002&corp_pwd=yhsaqx&corp_service=10657516yd&mobile=15021374552&msg_content=xjx%A3%AC%B6%CC%D0%C5%B2%E2%CA%D4';

list($url, $query) = explode('?', $url);
$arr = array(
    'corp_id'=>'1cbq002',
    'corp_pwd'=>'yhsaqx'
);
echo http_build_query($arr);
//echo $query;exit;
