<?php
namespace Home\Model;

/**
 * 佣金日志类
 */
class AgentModel extends BaseModel
{

    public function __construct()
    {
        parent::__construct();
        $info = M('agentset')->find();
        
        $this->info = $info;
        
        $USER = session('WST_USER');
        $this->userinfo = $USER;
    }

    public function getAdminSet()
    {
        return $this->info;
    }

    public function fansInfo($userId)
    {
        $userinfo = M('users')->field('userId,loginName,partnerId')->select();
        $uName = M('users')->field('loginName')
            ->where(array(
            'userId' => $userId
        ))
            ->find();
        $data = $this->getMenuTree($userinfo, $userId, '', $uName['loginName']);
        
        return $data;
    }

    public function getMenuTree($arrCat, $parent_id = 0, $level = 0, $pname, $fristname)
    {
        static $arrTree = array(); // 使用static代替global
        if (empty($arrCat))
            return FALSE;
        $level ++;
        foreach ($arrCat as $key => $value) {
            // if($value['partnerId'] == $parent_id)
            if ($value['partnerId'] == $parent_id) {
                $value['level'] = $level;
                $value['partnerName'] = isset($pname) ? $pname : $fristname;
                $arrTree[] = $value;
                unset($arrCat[$key]); // 注销当前节点数据，减少已无用的遍历
                self::getMenuTree($arrCat, $value['userId'], $level, $value['loginName'], $fristname);
            }
        }
        
        return $arrTree;
    }

    public function getShopAgent()
    {
        $shop = M('shops')->where(array(
            'userId' => $this->userinfo['userId']
        ))->find();
        if ($shop['agentStatus'] == 1 && $this->info['status'] == 1) {
            
            return true;
        }
        
        return false;
    }

    public function agentPriceLog()
    {
        $USER = session('WST_USER');
        
        $tempdata = M('distribution_log')->where(array(
            'uid' => $USER['userId']
        ))->select();
        // dump($tempdata);
        $data = $this->actionImg($tempdata);
        
        return $data;
    }

    public function selfInfo()
    {
        $USER = session('WST_USER');
        $tempdata = M('users')->where(array(
            'userId' => $USER['userId']
        ))->find();
        $tempdata['agentset'] = $this->info;
        
        return $tempdata;
    }

    public function actionImg($tempdata)
    {
        $data = array();
        foreach ($tempdata as $key => $value) {
            
            $value['goodsThums'] = "<img class='order_img' src=/" . $value['goodsThums'] . ">";
            $data[$key] = $value;
            $data[$key]['goodsThums'] = $value['goodsThums'];
            
            $value['time'] = date("Y-m-d H:i", $value['time']);
            $data[$key] = $value;
            $data[$key]['time'] = $value['time'];
        }
        
        return $data;
    }

    public function checkBalance()
    {
        $USER = session('WST_USER');
        // $data=array('valid'=>false);
        
        $tempdata = M('users')->where(array(
            'userId' => $USER['userId']
        ))->find();
        
        if ($tempdata['agentBalance'] > I('applyPrice')) {
            
            $data = array(
                'valid' => true
            );
        } else {
            $data = array(
                'valid' => false
            );
        }
        
        return $data;
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
    
    // 处理提现申请
    public function checkApply()
    {
        $data = I('post.');
        $this->addApplyBank($data);
        $USER = M('users');
        $USER->startTrans();
        $data['userId'] = session('oto_userId');
        $user = M('users')->where(array(
            'userId' => $data['userId']
        ))->find();
        $checkApply = M('agentApply')->where(array(
            'userId' => $data['userId'],
            'status' => 0
        ))->find();
        // $d = $user['agentTotalPrice']-$user['agentBalance']-$user['agentWaitPrice']-$user['agentPayPrice'];
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
        
        $setWait = $USER->where(array(
            'userId' => $data['userId']
        ))->setField('agentWaitPrice', $data['applyPrice']);
        $setBalance = $USER->where(array(
            'userId' => $data['userId']
        ))->setDec('agentBalance', $data['applyPrice']);
        
        // = $this->info;
        if ($setWait && $setBalance) {
           $name=$data['loginName']?$data['loginName']:$user['loginName'];
           $phone=$data['tel']?$data['tel']:$user['userPhone'];
            $nData = array(
                'userId' => $data['userId'],
                'loginName' => $name,
                'userType' => $data['userType'],
                'tel' => $phone,
                'applyType' => $data['applyType'],
                'applyPrice' => $data['applyPrice'],
                'bankUserName' => isset($data['bankUserName']) ? $data['bankUserName'] : '',
                'bankName' => isset($data['bankName']) ? $data['bankName'] : '',
                'bankAccess' => isset($data['bankAccess']) ? $data['bankAccess'] : '',
                'bankNum' => isset($data['bankNum']) ? $data['bankNum'] : '',
                'remark' => isset($data['remark']) ? $data['remark'] : '',
                'time' => time()
            );
            
            $status = M('agentApply')->data($nData)->add();
            
            if ($status > 0) {
                $USER->commit();
            } else {
                
                $USER->rollback();
                $error = array(
                    'status' => false,
                    'error' => '增加申请记录失败'
                );
                return $error;
            }
        } else {
            $USER->rollback();
            $error = array(
                'status' => false,
                'error' => '申请异常'
            );
            return $error;
        }
        
        $re = array(
            'status' => true,
            'success' => $status,
            'error'=>'提成提交成功，等待审核！'
        );
        
        return $re;
    }
    
    // 申请提现-密码
    public function CheckApplyPassword()
    {
        $USER = session('WST_USER');
        $tempdata = M('users')->where(array(
            'userId' => $USER['userId']
        ))->find();
        
        $PostPs = md5(I("password") . $tempdata['loginSecret']);
        $nowPs = $tempdata['loginPwd'];
        
        if ($PostPs == $nowPs) {
            $data = array(
                'valid' => true
            );
        } else {
            $data = array(
                'valid' => false
            );
        }
        
        return $data;
    }
    
    // 申请提现-日志
    public function checkApplylog()
    {
        $USER = session('WST_USER');
        $data = array();
        $tempdata = M('agentApply')->where(array(
            'userId' => $USER['userId']
        ))->select();
        
        foreach ($tempdata as $key => $value) {
            
            $value['status'] = $this->checkStatus($value['status']);
            $data[$key] = $value;
            $data[$key]['status'] = $value['status'];
            $value['time'] = date("Y-m-d", $value['time']);
            $data[$key] = $value;
            $data[$key]['time'] = $value['time'];
        }
        
        return $data;
    }
    
    // 申请提现-日志
    public function checkStatus($status)
    {
        switch ($status) {
            case 0:
                $status = '待处理';
                break;
            
            case 1:
                $status = '处理中';
                break;
            case 2:
                $status = '通过';
                break;
            
            case 3:
                $status = '不通过';
                break;
            default:
                
                break;
        }
        
        return $status;
    }

    public function checkTime()
    {
        $USER = session('WST_USER');
        $set = M('agentset')->find();
        $tempdata = M('agentApply')->where(array(
            'userId' => $USER['userId']
        ))
            ->order('time DESC')
            ->find();
        if ($tempdata && $set['applyDay'] != 0) {
            $time = time();
            $ok_time = strtotime("+{$set['applyDay']} DAY", $tempdata['time']);
            
            $re['day'] = date("d", $ok_time - $time);
        } else {
            
            $re['day'] = - 1;
        }
        
        return $re;
    }
}