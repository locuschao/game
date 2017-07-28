<?php

/* 
 * 订单类
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Org\Order;

abstract class OrderExtension{
    //用户id
    protected $UserInfo = array();
    //订单信息
    protected $OrderInfo = array();
    
    //备注信息
    protected $msg = '';
    
    /**
     * 更新平套金额
     * @param type $orderid 订单ID
     * @param type $orderno 订单编号
     * @param type $money   更新金额
     * @param type $type    setDec 金额减 setInc = 金额加
     * @param type $msg     备注
     * @return boolean
     */
    public function UpdatePlatformMoney($orderid,$orderno,$money,$type = 'setDec',$msg = ''){
        if(!$orderid || !$money){
            return false;
        }
        switch ($type){
            case 'setDec':
                $dataRes_s = M('data_tmp')->where('id=1')->setDec('value',$money);
                //添加平台金额流水记录
                $platRes_s = M('platfrom_account')->add(array(
                   'orderId'    =>  $orderid,
                    'time'      =>  time(),
                    'income'    =>  -$money,
                    'remark'    =>  $msg ? $msg : $this->msg,
                    'orderNo'   =>   $orderno
                ));
                break;
            case 'setInc':
                $dataRes_s = M('data_tmp')->where('id=1')->setInc('value',$money);
                //添加平台金额流水记录
                $platRes_s = M('platfrom_account')->add(array(
                   'orderId'    =>  $orderid,
                    'time'      =>  time(),
                    'income'    =>  $money,
                    'remark'    =>  $msg ? $msg : $this->msg,
                    'orderNo'   =>   $orderno
                ));
                break;
        }
        if($dataRes_s && $platRes_s){
            return true;
        }
        return false;
    }

    /**
     * 更新订单信息
     * @param type $orderid 订单ID
     * @param type $userid  用户ID
     * @param type $data    更新数据
     * @param type $msg     备注
     */
    public function UpdateOrder($orderid,$userid,$data = array(),$msg = ''){
        if(!$orderid || !$userid){
            return false;
        }
        // 更新退款状态
        if($data){
            $orders_s = M('orders')->where(array('orderId' => $orderid))->setField($data);
        }else{
            $orders_s = true;
        }
        // 订单最新日志
        $log_data = array();
        $log_data["orderId"] = $orderid;
        $log_data["logContent"] = $msg ? $msg : $this->msg;
        $log_data["logUserId"] = $userid;
        $log_data["logType"] = 0;
        $log_data["logTime"] = date('Y-m-d H:i:s');
        $log_s = M('log_orders')->add($log_data);
        if($orders_s && $log_s){
            return true;
        }
        return false;
    }

    /**
     * 更新用户金额
     * @param type $userinfo    用户信息
     * @param type $money   变动金额
     * @param type $orderno 订单编号
     * @param type $msg 备注信息
     */
    public function UpdateUsersMoney($userinfo,$money,$orderno,$msg = ''){
        if(!$userinfo || !$money || !$orderno){
            return false;    
        }
        $user_s = M('users')->where(array(              //对表 users 更新，退款给玩家
                    'userId' => $userinfo['userId']
                ))->setInc('userMoney', $money);
        //添加用户金额变更记录
        $moneyRecord['type'] = 2;
        $moneyRecord['money'] = $money;
        $moneyRecord['time'] = time();
        $moneyRecord['ip'] = get_client_ip();
        $moneyRecord['orderNo'] = $orderno;
        $moneyRecord['IncDec'] = 1;
        $moneyRecord['userid'] = $userinfo['userId'];
        $moneyRecord['balance'] = $userinfo['userMoney'] + $money;
        $moneyRecord['remark'] = $msg ? $msg : $this->msg;
        $moneyRecord['payWay'] = 3;
        $record_s = M('money_record')->add($moneyRecord);
        if($user_s && $record_s){
            return true;
        }
        return false;
        
    }
    
    /**
     * 更新退款表信息
     * @param type $orderid 订单ID
     * @param type $userid  用户ID
     * @param type $date    插入或者更新的数据
     * @param type $type    操作类型 uupadte = 更新 inster = 插入 
     * @param type $msg
     * @return boolean
     */
    public function UpdateRefund($orderid,$userid,$date,$type = 'update',$msg = ''){
        if(!$orderid || !$date){
            return false;
        }
        switch ($type){
            case 'update':
                $rf_s = M('refund')->where(array('orderid' => $orderid))->setField($date);
                break;
            case 'inster':
                $rf_s = M('refund')->add($date);
                break;
        }
        //插入退款日志信息
        $refundLog['orderId'] = $orderid;
        $refundLog['userId'] = $userid;
        $refundLog['mess'] = $msg;
        $refundLog['time'] = date('Y-m-d H:i:s');
        $log_s = M('refund_log')->add($refundLog);
        if($rf_s && $log_s){
            return true;
        }
        return false;
    }
    /**
     * 用户代金卷的处理
     * @param type $voucherIds  代金卷的ID
     *  @param type $ordercreate  订单下单时间
     */
    public function VoucherUser($voucherIds,$ordercreate = ''){
        if(!$voucherIds){
            return false;
        }
        $voucherid_list = explode(",", $voucherIds);//代金卷列表ID
        //计算过期时间
        $nowdate = time();
        $createTime = strtotime($this->OrderInfo['createTime'] ? $this->OrderInfo['createTime'] : $ordercreate);
        $map = array();
        foreach($voucherid_list as $v){
            $uservoucher_info = M('user_voucher')->field("num,expireTime")->where("id = {$v}")->find();
            //计算优惠价的还有多少天过期
            $date_overdue = $uservoucher_info['expireTime'] - $createTime;
            $map[$v]['expireTime'] = $nowdate + $date_overdue;
            $map[$v]['num'] = $uservoucher_info['num'] + 1;
        }
        $uservoucher_s = false;
        if(is_array($map) && $map){
            $flat_count = 0;
            $map_count = count($map);
            foreach ($map as $k => $vs){
                $is_true = M("user_voucher")->where("id = {$k}")->save($vs);
                if($is_true){
                    $flat_count ++;
                }
            }
            if($flat_count == $map_count){
                $uservoucher_s = true;
            }
        }
        return $uservoucher_s;
    }
    /**
     * 获取订单信息
     * @param type $orderid 订单ID
     */
    public function _GetOrderInfo($orderid,$field = ""){
        if(!$orderid){
            return FALSE;
        }
        
        if($field){
            $info = M('orders')->field($field)->where("orderId = {$orderid}")->find();
        }else{
            $info = M('orders')->where("orderId = {$orderid}")->find();
        }
        if(empty($info)){
            return false;
        }
        
        $this->OrderInfo = $info;
    }
    /**
     * 获取用户信息
     * @param type $userid  用户ID
     * @param type $field   查询的字段
     * @return boolean
     */
    protected function _GetUserInfo($userid,$field = ""){
        if(!$userid){
            return false;
        }
        if($field){
            $userinfo = M('users')->field($field)->where("userId = {$userid}")->find();
        }else{
            $userinfo = M('users')->where("userId = {$userid}")->find();
        }
        
        if(empty($userinfo)){
            return false;
        }
        
        $this->UserInfo = $userinfo;
        
    }
}
