<?php
namespace Home\Model;

/**
 * 短信服务类
 */
class LogSmsModel extends BaseModel
{

    /**
     * 插入并发送短讯记录
     */
    public function sendSMS($smsSrc, $phoneNumber, $content, $smsFunc, $smsCode)
    {
        $rv = array(
            'status' => - 1
        );
        $USER = session('WST_USER');
        $userId = empty($USER) ? 0 : $USER['userId'];
        $m = M('log_sms');
        // 检测是否超过每日短信发送数
        $date = date('Y-m-d');
        $rsCount = $m->where('smsPhoneNumber=%s and createTime>"%s 00:00:00" and createTime<="%s 23:59:59"', array(
            $phoneNumber,
            $date,
            $date
        ))->getField("count(smsId) counts");
        if ($rsCount < (int) $GLOBALS['CONFIG']['smsLimit']) {
            // $code = WSTSendSMS($phoneNumber,$content);
            import("Vendor.yunsms");
            $mes = new \yunsms();
            $sendResult = $mes->sendMsg($phoneNumber, $content);
            $data = array();
            $data['smsSrc'] = $smsSrc;
            $data['smsUserId'] = $userId;
            $data['smsPhoneNumber'] = $phoneNumber;
            $data['smsContent'] = $content;
            $data['smsReturnCode'] = $sendResult['status'];
            $data['smsCode'] = $smsCode;
            $data['smsFunc'] = $smsFunc;
            $data['createTime'] = date('Y-m-d H:i:s');
            $m->add($data);
            $rv['status'] = ($sendResult['status'] == 1) ? 1 : - 1;
        } else {
            $rv = array(
                'status' => - 2
            );
        }
        return $rv;
    }
}