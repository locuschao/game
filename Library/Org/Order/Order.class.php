<?php

/* 
 * 订单类
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Org\Order;
use Org\Order\OrderExtension;
class Order extends OrderExtension{
    //存储类的实例的静态成员变量
    private static $_instance;
    private function __construct() {}  
    /** 
     * 实例化 
     */  
    public static function getInstance() {  
        //判断是否被实例化  
        if(!(self::$_instance instanceof self)) {  
            self::$_instance = new self();  
        }  
        return self::$_instance;  
    }
    /**
     * 取消订单 用户或者平台
     * @param type $orderid 订单ID
     * @param type $msg 提示
     * @param type $type user = 用户取消 merchant = 商家取消
     */
    public function UserCancelOrder($orderid,$msg,$type = 'user'){
        //获取订单基本信息
        $this->_GetOrderInfo($orderid, "orderId,orderNo,shopId,userId,needPay,shopId,userId,orderStatus,voucherIds,voucher_reduce,createTime");
        if(empty($this->OrderInfo)){
            return false;
        }
        //获取用户信息
        $this->_GetUserInfo($this->OrderInfo['userId'], "userId,userMoney,userScore");
        if(empty($this->UserInfo)){
            return false;
        }
        
        $datetime = time();
        $userid = $this->OrderInfo['userId'];
        $money = $this->OrderInfo['needPay'];
        $trans = M();
        $trans->startTrans();
        try{
            switch ($type){
                case 'user':
                    $order_date = array(
                        'orderStatus' => -4,
                        'cancelTime'  => $datetime,
                        'cancel_reason' => $msg
                    );
                    break;
                case 'merchant':
                    $order_date = array(
                        'orderStatus' => -7,
                        'cancelTime'  => $datetime,
                        'cancel_reason' => $msg
                    );
                    break;
            }
            // 更新订单状态
            $ordersupdate_s = $this->UpdateOrder($orderid,$userid,$order_date,$msg);
            //插入退款记录
            $rd_date = array();
            $rd_date['userId'] = $userid;
            $rd_date['orderid'] = $orderid;
            $rd_date['type'] = 1;
            $rd_date['reason'] = $msg;
            $rd_date['explain'] = $msg;
            $rd_date['refundTime'] = date('Y-m-d H:i:s');
            $rd_date['biz_status'] = 0;
            $rd_date['pf_status'] = 1;
            $rd_date['apply_money'] = $money;
            $rd_date['way'] = 0;
            $rd_date['actual_money'] = $money;
            $rd_date['pf_time'] = date('Y-m-d H:i:s');
            $rd_date['shopId'] = $this->OrderInfo['shopId'];
            $rf_s = $this->UpdateRefund($orderid,$userid,$rd_date,'inster',$msg);
            //更新用户金额
            $user_s = $this->UpdateUsersMoney($this->UserInfo,  $money,  $this->OrderInfo['orderNo'],$msg);
            //更新平台金额
            $plat_s = $this->UpdatePlatformMoney($orderid,$this->OrderInfo['orderNo'],$money,'setDec',$msg);
            //更新用户优惠券信息
            $uservoucher_s = false;
            if($this->OrderInfo['voucherIds']){
                $uservoucher_s = $this->VoucherUser($this->OrderInfo['voucherIds']);
            }else{
                $uservoucher_s = true;
            }
            if($ordersupdate_s && $rf_s && $user_s && $plat_s && $this){
                $trans->commit();
                return true;
            }else{
                $trans->rollback();
                return false;
            }
        } catch (Exception $ex) {
            $trans->rollback();
            return false;
        }
    }
    /**
     * 退款流程->用户申请退款，商家同意，平台同意退款
     * @param type $orderid 订单ID
     * @param type $msg 备注
     */
    public function PlatformOkRefund($orderid,$msg){
        //获取订单基本信息
        $this->_GetOrderInfo($orderid, "orderId,orderNo,shopId,userId,needPay,shopId,userId,orderStatus,voucherIds,voucher_reduce,createTime");
        if(empty($this->OrderInfo)){
            return false;
        }
        //获取用户信息
        $this->_GetUserInfo($this->OrderInfo['userId'], "userId,userMoney,userScore");
        if(empty($this->UserInfo)){
            return false;
        }
        //获取退款订单表信息
        $refund_info = M('refund')->where("orderid = {$orderid}")->find();
        if(!$refund_info){
            return false;
        }
        if ($refund_info['pf_status'] > 0) {
            return false;
        }
        if ($refund_info['biz_status'] == 2) {
            return false;
        }
        $userid = $this->OrderInfo['userId'];
        $money = $this->OrderInfo['needPay'];
        $trans = M();
        $trans->startTrans();
        try{
            //更新退款订单信息
            $rd_date = array(
                    'pf_status' => 1,
                    'actual_money' => $money,
                    'pf_time' => date('Y-m-d H:i:s'),
                    'way' => 0
                );
            $rf_s = $this->UpdateRefund($orderid,$userid,$rd_date,'update',$msg);
            // 更新订单状态
            $order_date = array(
                        'orderStatus' => -5,
                        'isRead'=>0,
                        'lastMessTime'=>time()
                    );
            $ordersupdate_s = $this->UpdateOrder($orderid,$userid,$order_date,$msg);
            //更新用户金额
            $user_s = $this->UpdateUsersMoney($this->UserInfo,  $money,  $this->OrderInfo['orderNo'],$msg);
            //更新平台金额
            $plat_s = $this->UpdatePlatformMoney($orderid,$this->OrderInfo['orderNo'],$money,'setDec',$msg);
            //更新用户优惠券信息
            $uservoucher_s = false;
            if($this->OrderInfo['voucherIds']){
                $uservoucher_s = $this->VoucherUser($this->OrderInfo['voucherIds']);
            }else{
                $uservoucher_s = true;
            }
            if($rf_s && $ordersupdate_s && $user_s && $plat_s && $uservoucher_s){
                $trans->commit();
                return true;
            }else{
                $trans->rollback();
                return false;
            }
        } catch (Exception $ex) {
            $trans->rollback();
            return false;
        }
    }
    
    /**
     * 平台强制退款,订单状态必须是退款状态或者是商家拒绝退款，平台已给商家钱
     * @param type $orderid 订单ID
     * @param type $msg 备注
     */
    public function PlatformCompelRefund($orderid,$msg){
        //获取订单基本信息
        $this->_GetOrderInfo($orderid, "orderId,orderNo,shopId,userId,needPay,shopId,userId,orderStatus,voucherIds,voucher_reduce,createTime");
        if(empty($this->OrderInfo)){
            return false;
        }
        //获取用户信息
        $this->_GetUserInfo($this->OrderInfo['userId'], "userId,userMoney,userScore");
        if(empty($this->UserInfo)){
            return false;
        }
        
        if($this->OrderInfo['orderStatus'] !== '3' && $this->OrderInfo['orderStatus'] !== '-6'){//订单必须是以完成状态
            return false;
        }
        //查找平台是否已给钱给商家
        $autocomfirm_info = M('autocomfirm')->where("orderId = $orderid")->find();
        if(!$autocomfirm_info){
            return false;
        }
        $userid = $this->OrderInfo['userId'];
        $money = $this->OrderInfo['needPay'];
        //查看使用代金卷
        if($this->OrderInfo['voucherIds']){//使用代金卷
            $datatmpMoney = $this->OrderInfo['voucher_reduce'];//平台获得的金额    
            $businessMoney = $datatmpMoney + $money;//商家扣除金额
        }else{
            $datatmpMoney = 0;
            $businessMoney = $money;
        }
        
        $trans = M();
        $trans->startTrans();
        try{
            $complainRes = M('complain')->where(array('orderId' => $orderid))->setField(array('isHandle'=> 1));//将投诉改为 “已处理状态"
            //获取退款订单表信息
            $refund_info = M('refund')->where("orderid = {$orderid}")->find();
            if($refund_info){//表示已走过退款流程，但商家拒绝，平台强制退款
                $rd_date = array(
                    'pf_status' => 1,
                    'biz_status' => 3,          // biz 为 3 表示商家拒绝买家退款，而平台强制商家退款
                    'actual_money' => $money,
                    'pf_time' => date('Y-m-d H:i:s'),
                    'way' => 0
                );
                $rf_s = $this->UpdateRefund($orderid,$userid,$rd_date,'update',$msg);
            }else{//用户没有走提款流程，平台强制退款
                //插入退款记录
                $rd_date = array();
                $rd_date['userId'] = $userid;
                $rd_date['orderid'] = $orderid;
                $rd_date['type'] = 2;
                $rd_date['reason'] = $msg;
                $rd_date['explain'] = $msg;
                $rd_date['refundTime'] = date('Y-m-d H:i:s');
                $rd_date['biz_status'] = 3;
                $rd_date['pf_status'] = 1;
                $rd_date['apply_money'] = $money;
                $rd_date['way'] = 0;
                $rd_date['actual_money'] = $money;
                $rd_date['pf_time'] = date('Y-m-d H:i:s');
                $rd_date['shopId'] = $this->OrderInfo['shopId'];
                $rf_s = $this->UpdateRefund($orderid,$userid,$rd_date,'inster',$msg);
            }
             // 更新订单状态
            $order_date = array(
                'orderStatus' => '-5',
                 'isRead'=>0,
                 'lastMessTime'=>time()
             );
            $ordersupdate_s = $this->UpdateOrder($orderid,$userid,$order_date,$msg);
             //更新用户金额
            $user_s = $this->UpdateUsersMoney($this->UserInfo,  $money,  $this->OrderInfo['orderNo'],$msg);
            //扣除商家金额
            $tempRes_s = M('shops')->where(array('shopId' => $this->OrderInfo['shopId'] ))->setDec('bizMoney', $businessMoney);
            //修正确认订单表状态
            $autocomfirm_s = M('autocomfirm')->where("orderId = $orderid")->setField("orderStatus",'-5');
            //更新平台金额，有优惠券平台才有收入
            $dataRes_flag = false; 
            if($datatmpMoney > 0){
                //更新平台暂时的金额
                $dataRes_flag = $this->UpdatePlatformMoney($orderid,$this->OrderInfo['orderNo'],$datatmpMoney,'setInc',$msg);
            }else{
                $dataRes_flag = true;
            }
            //更新用户优惠券信息
            $uservoucher_s = false;
            if($this->OrderInfo['voucherIds']){
                $uservoucher_s = $this->VoucherUser($this->OrderInfo['voucherIds']);
            }else{
                $uservoucher_s = true;
            }
            if($complainRes && $rf_s && $ordersupdate_s && $user_s && $tempRes_s && $autocomfirm_s && $dataRes_flag && $uservoucher_s){
                $trans->commit();
                return true;
            }else{
                $trans->rollback();
                return false;
            }
        } catch (Exception $ex) {
            $trans->rollback();
            return false;
        }
    }
}
