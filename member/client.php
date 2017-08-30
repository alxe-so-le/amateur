<?php
date_default_timezone_set('PRC');
//包函nusoap.php
require_once('./lib/nusoap.php');
//新建一个soap客户端，调用服务端提供的wsdl
//$client = new soapclient('http://localhost/test/hellowsdl2.php?wsdl', true);
//$client = new nusoap_client('http://127.0.0.1/yiling/member/WebmemberService.php?wsdl', true);
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = false;
$client->xml_encoding = 'UTF-8';
//查看一下是不是报错
$err = $client->getError();
if ($err) {
    //显示错误
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}
//要向服务端要传的参数
$person4 = array(
    'cardNo'=>'oMf7YwGzFmyFjIN1cDiQSg31qJ_0',

    'from'=>strtotime('-1 day'),
    //'thru'=>time(),
    'billType'=>'',
    'srcId'=>'',
);
$person1 = array(
    'mobile'=>'13761648299'
);
//调用服务端的方法
$result = $client->call('getExtmember', $person1);
//$result = $client->call('queryExtscores', $person4);
//echo $list=$result['remarks'];die;
//错误审核
if ($client->fault) {
    echo '<h2>Fault</h2><pre>';
    print_r($result);
    echo '</pre>';
} else {
    $err = $client->getError();
    if ($err) {
        echo '<h2>Error</h2><pre>' . $err . '</pre>';
    } else {
        echo '<h2>Result</h2><pre>';
        print_r($result);
        echo '</pre>';
    }
}
//显示请求信息
echo '<h2>Request</h2>';
echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
//显示返回信息
echo '<h2>Response</h2>';
echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
//显示调试信息
echo '<h2>Debug</h2>';
echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';