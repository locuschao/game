<?php
namespace Admin\Model;
/**
 * peng
 * 开发者自己使用
 */
class DevelopModel
{
    
   //author: peng descreption:订单完成后的退款
    public function finishedRefund($orderNo) {
        $orderInfo = M('orders')->where(array(
            'orderNo' => $orderNo
        ))->find();
        $orderId = $orderInfo['orderId'];
        $isChangeStatus = $orderInfo['orderStatus'];
        if ($isChangeStatus == -7) {
            return array(
                'status' => - 1,
                'mes' => '订单状态已经是取消'
            );
            
        }
        if ($isChangeStatus != 3) {
            return array(
                'status' => - 1,
                'mes' => '订单不是完成状态'
            );
            
        }
                
        $A = true;
        $B = true;
        $C = true;
        M()->startTrans();
        $res = M('orders')->where(array(
            'orderId' => $orderId
        ))->setField(array(
            'orderStatus' => -7,
            'cancelTime'  => time(),
            'isRead'=>0
        ));
        if ($res) {
            
            if($orderInfo['needPay']>0){  #如果订单被代金券成0则不需要退款余额给用户
                $A = M('users')->where(array(
                'userId' => $orderInfo['userId']
                ))->setInc('userMoney', $orderInfo['needPay']);
            }
            
            // 订单最新日志
            $data = array();
            $data["orderId"] = $orderId;
            $data["logContent"] = "商家没发货，商家主动取消订单！";
            $data["logUserId"] = $orderInfo['userId'];
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            M('log_orders')->add($data);
            // 退款日志表
            $refundLog['orderId'] = $orderId;
            $refundLog['userId'] = $orderInfo['userId'];
            $refundLog['mess'] = '商家没发货，商家主动取消订单';
            $refundLog['time'] = date('Y-m-d H:i:s');
            $B = M('refund_log')->add($refundLog);

            $userMoney = M('users')->where(array(
                'userId' => $orderInfo['userId']
            ))->getField('userMoney');
            // 全额变动记录
            $moneyRecord['type'] = 2;
            $moneyRecord['money'] = $orderInfo['needPay'];
            $moneyRecord['time'] = time();
            $moneyRecord['ip'] = get_client_ip();
            $moneyRecord['orderNo'] = $orderInfo['orderNo'];
            $moneyRecord['IncDec'] = 1;
            $moneyRecord['userid'] = $orderInfo['userId'];
            $moneyRecord['balance'] = $userMoney;
            $moneyRecord['remark'] = '商家没接单，主动取消订单';
            $moneyRecord['payWay'] = 3;
            $C = M('money_record')->add($moneyRecord);
            $messRes = M('mess')->add(array(
                'type'=>1,
                'orderId'=>$orderId,
                'content'=>'商家没发货，取消订单',
                'time'=>date('Y-m-d H:i:s'),
                'userId'=>$orderInfo['userId']
            ));
            if($orderInfo['voucher_reduce'] > 0){
                //添加平台流水记录
                $E = M('platfrom_account')->add(array(
                    'orderId'   =>  $orderId,
                    'time'      =>  time(),
                    'income'    =>  +$orderInfo['voucher_reduce'],
                    'remark'    =>  '商家取消订单，平台获得退款',
                    'orderNo'   =>  $orderInfo['orderNo']
                ));
                //更新暂时的金额
                $F = M('data_tmp')->where('id=1')->setInc('value',$orderInfo['voucher_reduce']);
            }else{
                $E = true;
                $F = true;
            }
            //author: peng descreption:商家退款钱包括用户支付的和平台承担的代金券
            $D = M('shops')->where([
                'shopId'=>$orderInfo['shopId']
            ])->setDec('bizMoney',$orderInfo['voucher_reduce']+$orderInfo['needPay']);
            
            
            //echo '<pre>';
//            var_dump($A , $B , $C , $D , $E , $F ,$messRes);
//            echo '</pre>';

            if ($A && $B && $C && $D && $E && $F && $messRes) {
                M()->commit();
                M('autocomfirm')->where('orderId='.$orderId)->delete();
                
                if($orderInfo['voucherIds']){
                    
                    M('user_voucher')->where(['id'=>['in',$orderInfo['voucherIds']]])->setInc('num',1);
                    
                }
                
                return array(
                    'status' => 0,
                    'mes' => '订单取消成功'
                );
            } else {
                M()->rollback();
                return array(
                    'status' => - 1,
                    'mes' => '订单取消失败'
                );
            }
        } else {
            M()->rollback();
            return array(
                'status' => -1,
                'mes' => '订单取消失败'
            );
        }
        
    }
    
}