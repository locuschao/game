<?php
namespace Wx\Controller;

use Think\Model;
use Wx\Controller\BaseController;

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
            ->where($month)->order('id desc')
            ->select();
        $this->display();
    }
    // 钱包
    public function wallet()
    {
        parent::isLogin();
        $map['userId'] = session('oto_userId');
        $this->balance = M('users')->where($map)->getField('userMoney');
        $field = "m.type as actionType,m.money,m.time,m.orderNo,m.balance,m.payWay,m.IncDec,s.shopImg";
        $bmap['m.userid'] = session('oto_userId');
        $balanceList = M('money_record')->where($bmap)
        ->join('as m left join oto_orders as o on o.orderNo=m.orderNo join oto_shops as s on s.shopId=o.shopId')
        ->field($field)->select();
        foreach ($balanceList as $k=>$v){
            if($v['actionType']==0){
                $balanceList[$k]['yongtu']='其它';
            }else if($v['actionType']==1){
                $balanceList[$k]['yongtu']='购物消费';
            }else if($v['actionType']==2){
                $balanceLis[$k]['yongtu']='取消订单';
            }else if($v['actionType']==3){
                $balanceList[$k]['yongtu']='充值';
            }else if($v['actionType']==4){
                $balanceList[$k]['yongtu']='提现';
            }else if($v['actionType']==5){
                $balanceList[$k]['yongtu']='无效订单';
            }else{
                $balanceList[$k]['yongtu']='其它';
            }
        }
        $this->balanceList=$balanceList;
        $this->display();
    }
    //充值
    public function topUp(){
        $this->display();
    }
}