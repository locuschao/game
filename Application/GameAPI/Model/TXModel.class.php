<?php
namespace GameAPI\Model;

use Think\Model;

/**
 * 分销模块类
 */
class TXModel extends Model
{

    public function __construct()
    {}

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
        $users = M('users')->select();
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

    public function applyBankInfo($userId = 0)
    {
        $data['self'] = M('apply_bank')->where(array(
            'userId' => $userId
        ))->field('a.*,b.bankId')->join(' as a left join oto_banks as b on a.bankName=b.bankName ')
        ->select();
        $data['banks'] = M('banks')->where(array(
            'bankFlag' => 1
        ))->select();
        $data['apply'] = M('ps_tixian')->where(array(
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
            $data[$key]['txTime'] = substr($value['txTime'], 0, 10);
            $data[$key]['statusText'] = $this->checkStatus($value['txStatus']);
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
                $status = '已通过';
                break;
            case - 1:
                $status = '拒绝';
                break;
            case 3:
                $status = '不通过';
                break;
            default:
                
                break;
        }
        
        return $status;
    }

    public function deleteBank()
    {
        $id = (int) I('post.id');
        $userId = (int) I('post.userId');
        
        if ($id < 0 && $userId) {
            return false;
        }
        $re = M('apply_bank')->where(array(
            'userId' => $userId,
            'id' => $id
        ))->delete();
        
        return $re;
    }

    public function fansMoreInfo($levelFans)
    {
        $userId = $this->userInfo['userId'];
        foreach ($levelFans as $key => $value) {
            $sql = "select sum(gain_price) as price,count(*) as count from __PREFIX__distribution_log where `uid`='{$userId}' and `cid`='{$value['userId']}'";
            $order = M()->query($sql);
            $levelFans[$key]['price'] = $order[0]['price'];
            $levelFans[$key]['count'] = $order[0]['count'];
            $levelFans[$key]['userName'] = $this->enCodeString($value['loginName'] . '|' . $value['userId']);
        }
        
        // dump($levelFans);
        return $levelFans;
    }

    public function thisFansOrder($cid)
    {
        $userId = $this->userInfo['userId'];
        $l = I('get.limit');
        $limit = isset($l) ? I('get.limit') : 0;
        
        $data = M('distribution_log')->where(array(
            'uid' => $userId,
            'cid' => $cid
        ))
            ->limit($limit, 10)
            ->select();
        // $data = M('distribution_log')->limit($limit,10)->select();
        // $sql = M()->getLastSql();
        $data['limit'] = $limit + 10;
        
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
    
    // 提现处理
    public function tixianHandle($userId = 0)
    {
        $bankName = I('bankName');
        $bankNo = I('bankNum');
        $money = I('inputMoney', 0);
        $userName = I('bankUserName');
        if (empty($bankName) || empty($bankNo) || empty($userName) || ! is_numeric($bankNo) || ! is_numeric($money)) {
            return array(
                'status' => - 1,
                'msg' => '信息不完整'
            );
            exit();
        }
        if (! $userId) {
            return array(
                'status' => - 1,
                'msg' => '请登录后再操作'
            );
            exit();
        }
        $userinfo = M('users')->where(array(
            'userId' => $userId
        ))->find();
        if ($userinfo['userMoney'] < $money) {
            return array(
                'status' => - 1,
                'msg' => '余额不足'
            );
            exit();
        }
        
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
                'status' => 0,
                'msg' => '申请成功'
            );
            exit();
        } else {
            M()->rollback();
            return array(
                'status' => - 1,
                'msg' => '申请失败'
            );
            exit();
        }
    }
}