<?php
namespace ImproveAPI\Model;
use Think\Model;
class WithdrawModel extends Model
{
    // 提现处理
    public function tixianHandle()
    {   
        $post = getData();
        $userId = $_SESSION['userId'];
        $bankName = $post['bankName'];
        $bankNo = $post['bankNo'];
        $money = $post['inputMoney'];
        $userName = $post['bankUserName'];
        $payPwd = $post['payPwd'];
        $userInfo = M('users')->where(['userId'=>$_SESSION['userId']])->find();
        if($userInfo['payPwd'] != md5($payPwd.$userInfo['loginSecret'])) return ['status'=>0,'info'=>'支付密码不正确'];
        if($money<=0 || !is_numeric($money)) return ['status'=>0,'info'=>'提现金额异常'];
        if (empty($bankName) || empty($bankNo) || empty($userName) || ! is_numeric($bankNo) || ! is_numeric($money)) {
            return array(
                'status' => 0,
                'info' => '信息不完整'
            );
        }
        
        $userinfo = M('users')->where(array(
            'userId' => $userId
        ))->find();
        if ($userinfo['userMoney'] < $money) {
            return array(
                'status' => 0,
                'info' => '余额不足'
            );
        }
        
        M()->startTrans();
        // 常用提现帐号
        $isBank = M('apply_bank')->where(array(
            'bankName' => $bankName
        ))->find();
        if (! $isBank) {
            $bankData['userId'] = $userId;
            $bankData['bankUserName'] = $userName;
            $bankData['bankName'] = $bankName;
            $bankData['bankNum'] = $bankNo;
            $bankData['bankAccess'] = '';
            $bankData['isDefault'] = 0;
            $bankData['time'] = time();
            M('apply_bank')->add($bankData);
        }
        
        $txData['userId'] = $userId;
        $txData['txTime'] = date('Y-m-d H:i:s');
        $txData['txMoney'] = $money;
        $txData['txStatus'] = 0;
        $txData['trueName'] = $userName;
        $txData['bankNo'] = $bankNo;
        $txData['bankName'] = $bankName;
        $txData['orderNo'] = D('Game/Orders')->getOrderSn(M('orderids')->add(array(
            'rnd' => microtime(true)
        )));
        $B = M('ps_tixian')->add($txData);
        $C = M('users')->where(array(
            'userId' => $userId
        ))->setDec('userMoney', $money);
        // 余额变动记录
        $YE['type'] = 4;
        $YE['money'] = $money;
        $YE['time'] = time();
        $YE['orderNo'] = $B;
        $YE['IncDec'] = 0;
        $YE['userid'] = $userId;
        $YE['balance'] = $userinfo['userMoney'] - $money;
        $YE['remark'] = '';
        $YE['payWay'] = '0';
        $YE['ip'] = get_client_ip();
        $A = M('money_record')->add($YE);
        if ($B && $C != false && $A) {
            M()->commit();
            return array(
                'status' => 1,
                'info' => '申请成功'
            );
        } else {
            M()->rollback();
            return array(
                'status' => 0,
                'info' => '申请失败'
            );
        }
        
    }
}