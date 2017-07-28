<?php
include "TopSdk.php";

class Alidayu{
    public function sendMsg($code,$phone,$msg)
    {
        $c = new TopClient;
        $c->appkey = '23560438';
        $c->secretKey = 'f964ad85ea49e8b4736b5455d3e1ddf6';
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("123456");
        $req->setSmsType("normal");
        $smsParams = array(
            'msg'     => $msg ,
            'code'    => $code,
        );
        $req->setSmsFreeSignName("手游狗商城");
        $req->setSmsParam(json_encode($smsParams));
        $req->setRecNum($phone);
        $req->setSmsTemplateCode("SMS_34405142");
        $resp = $c->execute($req);
        return $resp;
    }
}







//$c = new TopClient;
//$c->appkey = '23558746';
//$c->secretKey = 'f3474a21884ae60bc1f217b622a269c7';
//$req = new AlibabaAliqinFcSmsNumSendRequest;
//$req->setExtend("123456");
//$req->setSmsType("normal");
//$smsParams = array(
//    'code'    => '1234',
//    'name' => 'hahah'
//);
//$req->setSmsFreeSignName("测试用的");
//$req->setSmsParam(json_encode($smsParams));
//$req->setRecNum("13632212814");
//$req->setSmsTemplateCode("SMS_33565169");
//$resp = $c->execute($req);
//print_r($resp);
?>