<?php
namespace Game\Controller;

use Think\Model;
use Game\Controller\BaseController;

class UcenterController extends BaseController
{

    public function _initialize()
    {
        parent::isLogin();
        @header('Content-type: text/html;charset=UTF-8');
    }
    // 积分
    public function score()
    {
        parent::isLogin();
        $map['userId'] = session('oto_userId');
        $this->score = M('users')->where($map)->getField('userScore');
        $month['userid'] = session('oto_userId');
        $month['_string'] = "date_sub(curdate(), INTERVAL 30 DAY) <= FROM_UNIXTIME(time)";
        $this->scoreList = M('score_record')->field('score,time,IncDec,type')
            ->where($month)
            ->order('id desc')
            ->select();
        $this->display();
    }
    // 钱包
    public function wallet()
    {
        parent::isLogin();
        $map['userId'] = session('oto_userId');
        $this->balance = M('users')->where($map)->getField('userMoney');
        $field = "type,money,time,orderNo,balance,payWay,IncDec";
        $bmap['userid'] = session('oto_userId');
        $balanceList = M('money_record')->where($bmap)
           // ->join('as m left join oto_orders as o on o.orderNo=m.orderNo join oto_shops as s on s.shopId=o.shopId')
            ->field($field)
            ->order('id DESC')
            ->select();
        foreach ($balanceList as $k => $v) {
            if ($v['type'] == 0) {
                $balanceList[$k]['yongtu'] = '其它';
            } else 
                if ($v['type'] == 1) {
                    $balanceList[$k]['yongtu'] = '购物消费';
                } else 
                    if ($v['type'] == 2) {
                        $balanceList[$k]['yongtu'] = '订单取消';
                    } else 
                        if ($v['type'] == 3) {
                            $balanceList[$k]['yongtu'] = '充值';
                        } else 
                            if ($v['type'] == 4) {
                                $balanceList[$k]['yongtu'] = '提现';
                            } else 
                                if ($v['type'] == 5) {
                                    $balanceList[$k]['yongtu'] = '无效订单';
                                } else if($v['type']==11) {
                                    $balanceList[$k]['yongtu'] = '分销提现';
                                }else {
                                    $balanceList[$k]['yongtu'] = '其它';
                                }
        }
        $this->balanceList = $balanceList;
        $this->YE = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->getField('userMoney');
        $this->display();
    }
    // 充值
    public function topUp()
    {
        $this->display();
    }
    
    // 余额说明
    public function explain()
    {
        $this->display();
    }
}