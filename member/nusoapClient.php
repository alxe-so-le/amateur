<?php
header("Content-Type: text/html; charset=utf-8");
require_once("lib/nusoap.php");
$weburl = "127.0.0.1/member/webservice.php";
$client = new soapclient($weburl);
$err = $client->getError();
if($err) {
    echo $err."<br>";exit();
    } else {
$custA['data']['key_val'] =  'IS25R1AGHB';
$custA['data']['start']  = 0;
$custA['data']['num']  = 10;
$result = $client->call('get_cust_info',$custA);
print_r($client-response);
return $result;
}