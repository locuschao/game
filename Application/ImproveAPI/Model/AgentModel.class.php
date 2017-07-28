<?php
namespace ImproveAPI\Model;
use Think\Model;
class AgentModel extends Model
{
    // 处理佣金提现到余额或者佣金提现到银行卡
    public function checkApply()
    {
        $data = getData();
        $this->addApplyBank($data);
        $USER = M('users');
        $USER->startTrans();
        $data['userId'] = $_SESSION['userId'];
        
        $user = M('users')->where(array(
            'userId' => $data['userId']
        ))->find();
        
        if(!in_array($data['applyType'],[0,1])) return [
            'status'=>0,
            'info'=>'申请类型异常'
        ];
        
        $data['applyPrice'] = $data['applyType']==1?$user['agentBalance']:$data['applyPrice'];
        
        $checkApply = M('agentApply')->where(array(
            'userId' => $data['userId'],
            'status' => 0
        ))->find();
        
        $money = $user['agentTotalPrice'] - $user['agentWaitPrice'] - $user['agentPayPrice'] - $user['agentBalance'];
        $money = round($money, 2);
        if ($money != 0) {
            $error = array(
                'status' => false,
                'error' => '钱包金额异常'
            );
            return $error;
        }
        
        if ($user['agentWaitPrice'] > 0 or $checkApply) {
            
            $error = array(
                'status' => false,
                'error' => '已有申请审核中'
            );
            return $error;
        }
        
        if ($this->info['minApplyPrice'] > $data['applyPrice']) {
            $error = array(
                'status' => false,
                'error' => '申请金额小于最小提现金额'
            );
            return $error;
        }
        
        #author: peng descreption:提现到余额不需支付密码
        if( $data['applyType'] == 0 && $user['payPwd'] != md5($payPwd.$user['loginSecret'])) 
        return ['status'=>0,'info'=>'支付密码不正确'];
        
        
        $setWait = $USER->where(array(
            'userId' => $data['userId']
        ))->setField('agentWaitPrice', $data['applyPrice']);
        $setBalance = $USER->where(array(
            'userId' => $data['userId']
        ))->setDec('agentBalance', $data['applyPrice']);
        if ($setWait && $setBalance) {
            $name=$user['loginName'];
            $phone=$user['userPhone'];
            $nData = array(
                'userId' => $data['userId'],
                'loginName' => $name,
                'userType' => 0,
                'tel' => $phone,
                'applyType' => $data['applyType'],
                'applyPrice' => $data['applyPrice'],
                'bankUserName' => isset($data['bankUserName']) ? $data['bankUserName'] : '',
                'bankName' => isset($data['bankName']) ? $data['bankName'] : '',
                'bankAccess' => '',
                'bankNum' => isset($data['bankNum']) ? $data['bankNum'] : '',
                'remark' => isset($data['remark']) ? $data['remark'] : '',
                'time' => time(),
                'orderNo' => D('Game/Orders')->getOrderSn(M('orderids')->add(['rnd' => microtime(true)]))
            );
            
            $status = M('agentApply')->data($nData)->add();
            
            if ($status > 0) {
                $USER->commit();
            } else {
                
                $USER->rollback();
                $error = array(
                    'status' => 0,
                    'info' => '增加申请记录失败'
                );
                return $error;
            }
        } else {
            $USER->rollback();
            $error = array(
                'status' => 0,
                'info' => '申请异常'
            );
            return $error;
        }
        
        $re = array(
            'status' => 1,
            'success' => $status,
            'info'=>'提成提交成功，等待审核！'
        );
        
        return $re;
    }
    
    public function addApplyBank($data)
    {
        if ($data['userId'] && $data['bankUserName'] && $data['bankName'] && $data['bankAccess'] && $data['bankNum']) {
            
            $nData = array(
                'userId' => $data['userId'],
                'bankUserName' => $data['bankUserName'],
                'bankName' => $data['bankName'],
                'bankAccess' => $data['bankAccess'],
                'bankNum' => $data['bankNum'],
                'time' => time()
            )
            ;
            $where = array(
                'userId' => $data['userId'],
                'bankUserName' => $data['bankUserName'],
                'bankName' => $data['bankName'],
                'bankAccess' => $data['bankAccess'],
                'bankNum' => $data['bankNum']
            );
            $re = M('apply_bank')->where($where)->find();
            if (! $re) {
                M('apply_bank')->data($nData)->add();
            }
        }
    }
}
