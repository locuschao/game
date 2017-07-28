<?php
namespace Native\Model;

use Think\Model;

/**
 * 分销模块类
 */
class AgentModel extends Model
{

    private $userInfo;

    private $info;

    public function __construct()
    {
        // parent::__construct();
        $userId = authCode(I('userId'));
        $info = M('agentset')->find();
        $this->info = $info;
        $this->userInfo = M('users')->where(array(
            'userId' => $userId
        ))->find();
    }

    public function userAgentPrice()
    {
        $uid = $this->userInfo['userId'];
        $userInfo = M('users')->where(array(
            'userId' => $uid
        ))->find();
        return $userInfo;
    }

    public function allFansinfo($level)
    {
        $uid = $this->userInfo['userId'];
        if ((int) $level < 0) {
            return;
        }
        $users = M('users')->field('userId,partnerId,userType,userPhone,loginName,userPhoto,userName')->select();
        $allFans = $this->getMenuTree($users, $uid);
        $levelFans = $this->checkFanLevel($allFans, $level);
        $reslut = $this->fansMoreInfo($levelFans);
        return $reslut;
    }

    /**
     * 解密@DECODE
     * 加密@ENCODE
     * 
     * @param
     *            $string
     * @param string $mode
     *            ENCODE
     * @return string
     *
     */
    public function enCodeString($string, $mode = 'ENCODE')
    {
        return _encrypt($string, $mode);
    }
    
    // 申请提现
    // 处理提现申请
    public function checkApply()
    {
        $data = I('post.');
        $data['userId']=authCode(I('userId'));
        $this->addApplyBank($data);
        $USER = M('users');
        $USER->startTrans();
        $data['userId'] = $this->userInfo['userId'];
        $user = M('users')->where(array(
            'userId' => $data['userId']
        ))->find();
        $checkApply = M('agentApply')->where(array(
            'userId' => $data['userId'],
            'status' => 0
        ))->find();
        
        $money = $user['agentTotalPrice'] - $user['agentWaitPrice'] - $user['agentPayPrice'] - $user['agentBalance'];
        $money = round($money, 2); // 计算异常，保存最后2位小数，如0.000001哪么就=0;
        /*
         * [agentTotalPrice] => 18.80
         * [agentBalance] => 4.80
         * [agentWaitPrice] => 0.00
         * [agentPayPrice] => 14.00
         */
        if ($money != 0) {
            $error = array(
                'status' => - 1,
                'msg' => '钱包金额异常'
            );
            return $error;
        }
        if ($user['agentWaitPrice'] > 0 or $checkApply) {
            $error = array(
                'status' => - 2,
                'msg' => '已有申请审核中'
            );
            return $error;
        }
        if ($this->info['minApplyPrice'] > $data['applyPrice']) {
            $error = array(
                'status' => - 3,
                'msg' => '申请金额小于最小提现金额'
            );
            return $error;
        }
        $setWait = $USER->where(array(
            'userId' => $data['userId']
        ))->setField('agentWaitPrice', $data['applyPrice']);
        $setBalance = $USER->where(array(
            'userId' => $data['userId']
        ))->setDec('agentBalance', $data['applyPrice']);
        if ($setWait && $setBalance) {
            $name=$data['loginName']?$data['loginName']:$user['loginName'];
            $phone=$data['tel']?$data['tel']:$user['userPhone'];
            $nData = array(
                'userId' => $data['userId'],
                'loginName' =>$name,
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
                    'status' => - 4,
                    'msg' => '增加申请记录失败'
                );
                return $error;
            }
        } else {
            $USER->rollback();
            $error = array(
                'status' => - 5,
                'msg' => '申请异常'
            );
            return $error;
        }
        
        $re = array(
            'status' => 0,
            'msg' => '提现申请成功！'
        );
        return $re;
    }
    
    // 常用帐号，提现时如果不存在则新增
    public function addApplyBank($data)
    {
       
        if ($data['userId'] && $data['bankUserName'] && $data['bankName'] && $data['bankNum']) {
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
                'bankNum' => $data['bankNum']
            );
            $re = M('apply_bank')->where($where)->find();
            if (!$re) {
                M('apply_bank')->add($nData);
            }
        }
    }

    public function applyBankInfo()
    {
        $userId = $this->userInfo['userId'];
        $data['self'] = M('apply_bank')->where(array(
            'userId' => $userId
        ))->select();
        $data['banks'] = M('banks')->select();
        $data['apply'] = M('agent_apply')->where(array(
            'userId' => $userId
        ))
            ->order('id DESC')
            ->select();
        $data['userInfo'] = M('users')->where(array(
            'userId' => $userId
        ))->find();
        $this->applyFormat($data['apply']);
        return $data;
    }

    public function applyFormat(&$data)
    {
        foreach ($data as $key => $value) {
            
            $data[$key]['time'] = date("Y-m-d", $value['time']);
            
            $data[$key]['statusText'] = $this->checkStatus($value['status']);
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

    public function deleteBank($userId)
    {
        $id = I('id', 0, 'intval');
        if ($id < 0 && $userId) {
            return false;
        }
        $re = M('apply_bank')->where(array(
            'userId' => $userId,
            'id' => $id
        ))->delete();
        if ($re) {
            return array(
                'status' => 0,
                'msg' => '删除成功'
            );
        } else {
            return array(
                'status' => - 1,
                'msg' => '删除失败'
            );
        }
    }

    public function fansMoreInfo($levelFans)
    {
        $userId = $this->userInfo['userId'];
        foreach ($levelFans as $key => $value) {
            $sql = "select sum(gain_price) as price,count(*) as count from __PREFIX__distribution_log where `uid`='{$userId}' and `cid`='{$value['userId']}'";
            $order = M()->query($sql);
            $levelFans[$key]['price'] = $order[0]['price'];
            $levelFans[$key]['count'] = $order[0]['count'];
            $levelFans[$key]['userName'] = substr($value['userPhone'], 0,3).'*****'.substr($value['userPhone'], 8,3);
            $levelFans[$key]['userId'] = $this->enCodeString($value['userId']);
        }
        // dump($levelFans);
        
        return $levelFans;
    }

    public function thisFansOrder($cid,$page=0)
    {
        $userId = $this->userInfo['userId'];
    
        $data = M('distribution_log as d')->field('d.orderId,d.agentPrice,d.gain_price,bili,o.orderNo')
        ->join('oto_orders as o on o.orderId=d.orderId')
            ->where(array(
            'uid' => $userId,
            'cid' => $cid
        ))->order('o.orderId desc')
            ->limit($page * 10, 30)
            ->select();
        return $data;
    }

    public function fansCount()
    {
        $uid = $this->userInfo['userId'];
        $data = array();
        $users = M('users')->select();
        $allFans = $this->getMenuTree($users, $uid);
        $data['all'] = count($allFans);
        $data['one'] = count($this->checkFanLevel($allFans, 1));
        $data['two'] = count($this->checkFanLevel($allFans, 2));
        $data['three'] = count($this->checkFanLevel($allFans, 3));
        
        return $data;
    }

    public function checkFanLevel($data, $level)
    {
        foreach ($data as $key => $value) {
            if ($value['level'] != $level) {
                unset($data[$key]);
            }
        }
        return $data;
    }

    public function fanslevel($get)
    {
        switch ($get) {
            case 1:
                return '一级会员';
                break;
            case 2:
                return '二级会员';
                break;
            case 3:
                return '三级会员';
                break;
            default:
                return '';
                break;
        }
    }

    private function getMenuTree($arrCat, $parent_id = 0, $level = 0)
    {
        static $arrTree = array(); // 使用static代替global
        if (empty($arrCat)) {
            return FALSE;
        }
        $level ++;
        foreach ($arrCat as $key => $value) {
            // if($value['partnerId'] == $parent_id)
            if ($value['partnerId'] == $parent_id) {
                $value['level'] = $level;
                $arrTree[] = $value;
                unset($arrCat[$key]); // 注销当前节点数据，减少已无用的遍历
                if ($level < $this->info['agentLevel']) {
                    self::getMenuTree($arrCat, $value['userId'], $level);
                }
            }
        }
        return $arrTree;
    }
}