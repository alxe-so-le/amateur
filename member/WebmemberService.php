<?php
define('IN_API', true);
date_default_timezone_set('Asia/Shanghai');

require './db.php';
$db = new Db();
/*
 * 使用wsld
 * */
//包函nusoap.php
require_once('./lib/nusoap.php');
//新建一个soap服务
$server = new soap_server();
$server->soap_defencoding = 'UTF-8';
$server->decode_utf8 = false;
$server->xml_encoding = 'UTF-8';
//初始化支持wsdl
$server->configureWSDL('WebmemberService', 'urn:WebmemberService');


/* 会员账户 */
$server->wsdl->addComplexType(
    'Extmember',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'cardNo'=>array('name' => 'cardNo', 'type' => 'xsd:string'),
        'descrip' => array('name' => 'descrip', 'type' => 'xsd:string'),
        'issueDate' => array('name' => 'issueDate', 'type' => 'xsd:date'),
        'memberankId' => array('name' => 'memberankId', 'type' => 'xsd:string'),
        'memberank' => array('name' => 'memberank', 'type' => 'xsd:string'),
        'brankDate' => array('name' => 'balancePoints', 'type' => 'xsd:date'),
        'name' => array('name' => 'name', 'type' => 'xsd:string'),
        'tel' => array('name' => 'tel', 'type' => 'xsd:string'),
        'mobile' => array('name' => 'mobile', 'type' => 'xsd:string'),
        'email' => array('name' => 'email', 'type' => 'xsd:string'),
        'sex' => array('name' => 'sex', 'type' => 'xsd:string'),
        'birthday' => array('name' => 'birthday', 'type' => 'xsd:date'),
        'idcardNo' => array('name' => 'idcardNo', 'type' => 'xsd:string'),
        'cashierId' => array('name' => 'cashierId', 'type' => 'xsd:string'),
        'expiryDate' => array('name' => 'expiryDate', 'type' => 'xsd:date'),
        'points' => array('name' => 'points', 'type' => 'xsd:double'),
        'balancePoints' => array('name' => 'balancePoints', 'type' => 'xsd:double'),
        'node' => array('name' => 'node', 'type' => 'xsd:int')
    )
);
$server->register('getExtmember',                    // 方法名字hello，方法就在下面
    array(
    'mobile'=>'xsd:string'
    ),          // 客户端传来的变量
    array('return' => 'tns:Extmember')                                 // 存档
);

function getExtmember($mobile){
    global $db;
    $condition = '';
    $cardNo && $condition = " and mobile = '".$mobile."'";

    $sql =" select * from ims_ewei_shop_member where 1 {$condition} limit 1";
    $res=$db->fetchOne($sql);
    //查询会员等级
    if($res['level'])
    $sql =" select * from ims_ewei_shop_member_level where id=".$res['level'];
    $level=$db->fetchOne($sql);

    $return = array();
    if($res){
        if($res['birthyear'] && $val['birthmonth'] && $val['birthday']){
            $birthday = $res['birthyear'].'-'.$val['birthmonth'].'-'.$val['birthday'];
        }
        $address = '';
        if($res['province']){
            $address .= $res['province'];
        }
        if($res['city']){
            $address .= $res['city'];
        }
        if($res['area']){
            $address .= $res['area'];
        }
        $fsql = "select follow from ims_mc_mapping_fans where openid='".$res['openid']."' limit 1";
        $follow=$db->fetchOne($sql);
        switch($follow['follow']){
            case 1:
                $node = '已关注';
            break;
            case 2:
                $node = '取消关注';
            default:
                $node = '未关注';
        }
        $esql =" select sum(points) as points from ims_ewei_shop_extscore where cardNo='".$res['openid']."' limit 1";
        $extscore=$db->fetchOne($esql);
        if($extscore['points']>$res['balancePoints'])
            $expense = $extscore['points']-$res['balancePoints'];
        else
            $expense = 0;
        $return = array(
            'cardNo'=>$res['openid'],
            'descrip'=>$res['descrip'],
            'issueDate'=>date('Y-m-d H:i:s',$res['createtime']),
            'memberankId'=>$res['level']?$res['level']:'',
            'memberank'=>$level['levelname']?$level['levelname']:'',
            'brankDate'=>'',
            'name'=>$res['realname'],
            'address'=>$address,
            'tel'=>'',
            'mobile'=>$res['mobile'],
            'postalCode'=>'',
            'email'=>'',
            'sex'=>$res['gender']==1?'男':'女',
            'birthday'=>$birthday,
            'idcardNo'=>$res['idcard'],
            'cashierId'=>'',
            'expiryDate'=>'',
            'points'=>$extscore['points'],
            'expense'=>$expense,
            'balancePoints'=>$res['balancePoints'],
            'node'=>$node
            );

    }
    return $return;
}

/* 积分明细 */
$server->wsdl->addComplexType(
    'Extscore',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'id'=>array('name' => 'id', 'type' => 'xsd:long'),
        'cardNo' => array('name' => 'cardNo', 'type' => 'xsd:string'),
        'date' => array('name' => 'date', 'type' => 'xsd:date'),
        'remarks' => array('name' => 'remarks', 'type' => 'xsd:string'),
        'points' => array('name' => 'points', 'type' => 'xsd:double'),
        'balancePoints' => array('name' => 'balancePoints', 'type' => 'xsd:double'),
        'expense' => array('name' => 'expense', 'type' => 'xsd:double'),
        'terminalId' => array('name' => 'terminalId', 'type' => 'xsd:string'),
        'saleDeptId' => array('name' => 'saleDeptId', 'type' => 'xsd:string'),
        'billType' => array('name' => 'billType', 'type' => 'xsd:string'),
        'srcId' => array('name' => 'srcId', 'type' => 'xsd:string'),
    )
);
$server->wsdl->addComplexType(
    'ExtscoreArray',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(
        array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Extscore[]')
    ),
    'tns:Extscore'
);

//-------------返回开始-------------------------

$server->register('queryExtscores',                    // 方法名字hello，方法就在下面
    array(
    'cardNo'=>'xsd:string',
    'from'=>'xsd:string',
    'thru'=>'xsd:string',
    'billType'=>'xsd:string',
    'srcId'=>'xsd:string'
    ),          // 客户端传来的变量
    array('return' => 'tns:ExtscoreArray')                                 // 存档
);


function queryExtscores($cardNo,$from,$thru,$billType,$srcId) {
    global $db;
    $condition = '';
    $cardNo && $condition = " and cardNo = '".$cardNo."'";
    if($from && $thru){
        $from = substr($from, 0,-3);
        $thru = substr($thru, 0,-3);
        $condition .= " and date >= ".$from." AND date<= ".$thru;
    }
    if(trim($billType)){
        $condition .= " and billType = '".$billType."'";
    }
    if(trim($srcId)){
        $condition .= " and srcId = '".$srcId."'";
    }
    $sql = " select * from ims_ewei_shop_extscore where 1 {$condition} order by id desc";
    $res=$db->fetchAll($sql);
    $return = array();
    if($res){
        foreach($res as $val){
            $return[] = array(
                'id'=>$val['id'],
                'cardNo'=>$val['cardNo'],
                'date'=>date('Y-m-d H:i:s',$val['date']),
                'remarks'=>$val['cardNo'],
                'points'=>$val['points'],
                'balancePoints'=>$val['balancePoints'],
                'expense'=>$val['expense'],
                'terminalId'=>$val['terminalId'],
                'saleDeptId'=>$val['saleDeptId'],
                'billType'=>$val['billType'],
                'srcId'=>$val['srcId'],
                );
        }
    }
    return $return;
}

//-3--------------------------------------积分明细结束



//4----------------------------登记积分开始
//返回参数定义
$server->wsdl->addComplexType(
    'ResponseOobtain',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'statusCode'=>array('name' => 'statusCode', 'type' => 'xsd:string'),
        'statusText' => array('name' => 'statusText', 'type' => 'xsd:string')
    )
);
//接收参数定义
$server->wsdl->addComplexType(
    'obtain',
    'complexType',
    'struct',
    'all',
    '',
    array(
        //后面的type定义数据的类型，这个是string
        //'Salescore' => array('name' => 'Salescore', 'type' => 'xsd:string'),
        //'SalescoreItem' => array('name' => 'SalescoreItem', 'type' => 'xsd:string'),

        'cardNo' => array('name' => 'cardNo', 'type' => 'xsd:string'),
        'terminalid' => array('name' => 'terminalid', 'type' => 'xsd:string'),
        'saleDeptId' => array('name' => 'saleDeptId', 'type' => 'xsd:string'),
        'billType' => array('name' => 'billType', 'type' => 'xsd:string'),
        'srcId' => array('name' => 'srcId', 'type' => 'xsd:string'),
        'points' => array('name' => 'points', 'type' => 'xsd:string'),
        'amount' => array('name' => 'amount', 'type' => 'xsd:string'),
        'remarks' => array('name' => 'remarks', 'type' => 'xsd:string'),
        'salescoreItems'>array('name' => 'salescoreItems', 'type' => 'xsd:salescoreItems[]'),
        'scoreId' => array('name' => 'scoreId', 'type' => 'xsd:long'),

    )
);
$server->wsdl->addComplexType(
    'obtainArray',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(
        array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:obtain[]')
    ),
    'tns:obtain'
);
$server->register('obtain',                    // 方法名字hello，方法就在下面
    array('salescore' => 'tns:obtainArray'),          // 客户端传来的变量
    array('return' => 'tns:ResponseOobtain'),    //返回参数
    'urn:WebmemberService',                         // soap名
    'urn:WebmemberService#obtain',                   // soap的方法名
    'rpc',                                    // 使用的方式
    'encoded',                                // 编码
    ''                                    // 存档
);
function obtain($person) {

     global $db;

    $sql =" select * from ims_ewei_shop_member where openid='".$person['cardNo']."'";
    $memberinfo=$db->fetchOne($sql);

    if(empty($memberinfo)){
        return array(
            'statusCode' =>"FALSE",
            'statusText' => '会员不存在',
        );
    }

    $data=array(
        'cardNo'=>$person['cardNo'],
        'date'=>time(),
        'terminalid'=> $person['terminalId'],
        'saleDeptId'=>$person['saleDeptId'],
        'billType'=>$person['billType'],
        'srcId'=>$person['srcId'],
        'points'=> $person['points'],
        'balancePoints'=>$person['points']+$memberinfo['balancePoints'],
        'expense'=>$amount,
        'remarks'=>$person['remarks'],
    );
    $id=$db->insert($data,"ims_ewei_shop_extscore");
    if ($id){
        $update['balancePoints'] = $memberinfo['balancePoints']+$person['points'];
        $where = "  openid='".$person['cardNo']."'";
        $db->update($update,'ims_ewei_shop_member',$where);
        return array(
            'statusCode' =>"SUCCESS",
            'statusText' => $id,
        );
    }else{
        return array(
            'statusCode' =>"FALSE",
            'statusText' => '保存失败',
        );
    }

}
//4---------------------------登记积分结束
//5--------------------------------------积分扣减开始

//返回参数
$server->wsdl->addComplexType(
    'ResponseSpend',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'statusCode'=>array('name' => 'statusCode', 'type' => 'xsd:string'),
        'statusText' => array('name' => 'statusText', 'type' => 'xsd:string')
    )
);
//接收参数定义
$server->wsdl->addComplexType(
    'spend',
    'complexType',
    'struct',
    'all',
    '',
    array(
        //后面的type定义数据的类型，这个是string
        'cardNo' => array('name' => 'cardNo', 'type' => 'xsd:string'),
        'terminalId' => array('name' => 'terminalId', 'type' => 'xsd:string'),
        'saleDeptId' => array('name' => 'saleDeptId', 'type' => 'xsd:datetime'),
        'billType' => array('name' => 'billType', 'type' => 'xsd:string'),
        'srcId' => array('name' => 'srcId', 'type' => 'xsd:string'),
        'points' => array('name' => 'points', 'type' => 'xsd:string'),
        'amount' => array('name' => 'amount', 'type' => 'xsd:string'),
        'remarks' => array('name' => 'remarks', 'type' => 'xsd:string'),
    )
);

$server->register('spend',                    // 方法名字hello，方法就在下面
    array(
    'cardNo' => 'xsd:string',
    'terminalId' => 'xsd:string',
    'saleDeptId' => 'xsd:string',
    'billType' => 'xsd:string',
    'srcId' => 'xsd:string',
    'points' => 'xsd:string',
    'amount' => 'xsd:string',
    'remarks' => 'xsd:string',
    ),          // 客户端传来的变量
    array('return' => 'tns:ResponseSpend'),    //返回参数
    'urn:WebmemberService',                         // soap名
    'urn:WebmemberService#spend',                   // soap的方法名
    'rpc',                                    // 使用的方式
    'encoded',                                // 编码
    ''                                    // 存档
);

function spend($cardNo,$terminalId,$saleDeptId,$billType,$srcId,$points,$amount,$remarks) {
    global $db;
    $sql =" select * from ims_ewei_shop_member where openid='".$cardNo."'";
    $memberinfo=$db->fetchOne($sql);
    if(empty($memberinfo)){
        return array(
            'statusCode' =>"FALSE",
            'statusText' => '会员不存在',
        );
    }
    if($memberinfo['balancePoints']<$points){
        return array(
            'statusCode' =>"FALSE",
            'statusText' => '积分余额不足',
        );
    }
     $data=array(
            'cardNo'=>$cardNo,
            'date'=>time(),
            'terminalid'=> $terminalId,
            'saleDeptId'=>$saleDeptId,
            'billType'=>$billType,
            'srcId'=>$srcId,
            'points'=> '-'.$points,
            'balancePoints'=>$memberinfo['balancePoints']-$points,
            'expense'=>$amount,
            'remarks'=>$remarks,
        );
    $id=$db->insert($data,"ims_ewei_shop_extscore");
    if ($id){
        $update['balancePoints'] = $memberinfo['balancePoints']-$points;
        $where = "  openid='".$cardNo."'";
        $db->update($update,'ims_ewei_shop_member',$where);
        return array(
            'statusCode' =>"SUCCESS",
            'statusText' => $id,
        );
    }else{
        return array(
            'statusCode' =>"FALSE",
            'statusText' => '保存失败',
        );
    }

}
//5--------------------------------------积分扣减结束

//6.---------------------------------撤销积分扣减开始
//返回参数定义
$server->wsdl->addComplexType(
    'ResponseReSpend',
    'complexType',
    'struct',
    'all',
    '',
    array(
        //后面的type定义数据的类型，这个是string
        'statusCode' => array('name' => 'statusCode', 'type' => 'xsd:string'),
        'statusText' => array('name' => 'statusText', 'type' => 'xsd:string'),
    )
);
$server->register('reSpend',                    // 方法名字hello，方法就在下面
    array('scoreid' => 'xsd:long'),          // 客户端传来的变量
    array('return' => 'tns:ResponseReSpend'),    //返回参数
    'urn:WebmemberService',                         // soap名
    'urn:WebmemberService#reSpend',                   // soap的方法名
    'rpc',                                    // 使用的方式
    'encoded',                                // 编码
    ''                                    // 存档
);
//定义上面注册过的函数hello
function reSpend($scoreid) {
    global $db;
    $sql =" select cardNo,points,status from ims_ewei_shop_extscore where id=".$scoreid;
    $extscore=$db->fetchOne($sql);
    if(empty($extscore)){
        return array(
            'statusCode' =>"FALSE",
            'statusText' => '该积分记录不存在',
        );
    }
    if($extscore['status']==2){

        return array(
            'statusCode' =>"FALSE",
            'statusText' => '已扣减',
        );
    }
    $sql =" select balancePoints from ims_ewei_shop_member where openid='".$extscore['cardNo']."'";
    $memberinfo=$db->fetchOne($sql);
    $update['balancePoints'] = $memberinfo['balancePoints']+$extscore['points'];
    $where = "  openid='".$extscore['cardNo']."'";
    $ret = $db->update($update,'ims_ewei_shop_member',$where);
    if($ret){
        $db->update(array('status'=>2),'ims_ewei_shop_extscore','id='.$scoreid);
        return array(
            'statusCode' =>"SUCCESS"
        );
    }
    return array(
            'statusCode' =>"FALSE",
            'statusText' => '撤销扣减失败',
        );
}

//6.---------------------------------撤销积分扣减结束

//7---------------------------------积分确认开始
//返回参数定义
$server->wsdl->addComplexType(
    'ResponseConfirmPay',
    'complexType',
    'struct',
    'all',
    '',
    array(
        //后面的type定义数据的类型，这个是string
        'statusCode' => array('name' => 'statusCode', 'type' => 'xsd:string'),
        'statusText' => array('name' => 'statusText', 'type' => 'xsd:string'),
    )
);

$server->register('confirmPay',                    // 方法名字hello，方法就在下面
    array('scoreid' => 'xsd:long'),          // 客户端传来的变量
    array('return' => 'tns:ResponseConfirmPay'),    //返回参数
    'urn:WebmemberService',                         // soap名
    'urn:WebmemberService#obtain',                   // soap的方法名
    'rpc',                                    // 使用的方式
    'encoded',                                // 编码
    ''                                    // 存档
);
//定义上面注册过的函数hello
function confirmPay($scoreid) {
    global $db;
    $sql =" select * from ims_ewei_shop_extscore where id=".$scoreid;
    $extscore=$db->fetchOne($sql);
    if(empty($extscore)){
        return array(
            'statusCode' =>"FALSE",
            'statusText' => '该积分记录不存在',
        );
    }
    if($extscore['status']==3){

        return array(
            'statusCode' =>"FALSE",
            'statusText' => '已确认',
        );
    }

    $ret = $db->update(array('status'=>3),'ims_ewei_shop_extscore','id='.$scoreid);
    if($ret){
        return array(
            'statusCode' =>"SUCCESS"
        );
    }
    return array(
            'statusCode' =>"FALSE",
            'statusText' => '确认失败',
        );
}

//7.---------------------------------撤销积分扣减结束

// 请求时（试图）调用服务
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

?>