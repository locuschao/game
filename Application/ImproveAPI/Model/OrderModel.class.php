<?php
namespace ImproveAPI\Model;
use Think\Model;
class OrderModel extends Model
{
    public function addOrder($arr) {
        
        $post = $arr['post'];
        
        $goodsInfo = $arr['goodsInfo'];
        $attrInfo = $arr['attrInfo'];
        $attrId = $post['attrid'];
        $userId = $_SESSION['userId'];
        $shopId = $attrInfo['shopId'];
        $_SESSION['oto_userId'] = $_SESSION['userId'];
        $zhekou = $this->getNeedPay($attrInfo);
        
        $m = M('orderids');
        $m->startTrans();
        $orderNo = D('Game/Orders')->getOrderSn($m->add(array(
            'rnd' => microtime(true)
        )));
        $data["orderNo"] = $orderNo;
        $data["shopId"] = $attrInfo['shopId'];
        $data["userId"] = $_SESSION['userId'];
        $data["orderFlag"] = 1;
        $data["totalMoney"] = $post['totalMoney'];
        $data["isPay"] = 0;
        $data["orderType"] = $post['account']?2:1;
        // 支付方式
        $data["payType"] = 2;
        $data['account'] = $post['account']?:'';
        $data["createTime"] = date("Y-m-d H:i:s");
        $data["orderStatus"] = - 2;
       
        $needPay = $post['totalMoney'] * $zhekou * 0.1;
      
        $vocher_re = D('Voucher')->checkVoucher($attrInfo,$goodsInfo,$needPay,[
            'userId'=>$_SESSION['userId'],
            'vouchers'=>$post['vouchers']
        ]);
        
        if($vocher_re === false){
            return [
                'status'=>-1,
                '代金券异常'
            ];
        }else if($vocher_re>0){
            $result = M('user_voucher')->where(['id'=>['in',$post['vouchers']]])->setDec('num',1);
            $needPay = $needPay-$vocher_re;
        }else{
            $result = true;
        }
        
        $data["needPay"] = $needPay;
        
        if($post['reduce_money'] != $vocher_re){
            return [
                'status'=>0,
                'info'=>'抵消金额校验不一致'
            ];
            
        }
        if($post['pay_money'] != $needPay){
            return [
                'status'=>0,
                'info'=>'需要支付金额校验不一致'
            ];
        }
        
        /**
         * @author peng
         * @date 2017-01
         * @descreption 代金券
         */
        $data['voucherIds'] = $vocher_re>0?$post['vouchers']:'';//如果免去的金额不大于0，则订单不保存代金券ID
        /**
         * @author peng
         * @date 2017-01
         * @descreption 抵消金额
         */
        $data['voucher_reduce']=$vocher_re?:0;
        $orderId = M('orders')->add($data);
        
        // 订单创建成功则建立相关记录
        if ($orderId > 0) {
            $orderIds[] = $orderId;
            // 建立订单商品记录表
            $mog = M('order_goods');
            $data = array();
            $data["orderId"] = $orderId;
            $data["goodsId"] = $attrInfo['goodsId'];
            $data["goodsAttrId"] = $attrId;
            $data["gid"] = $goodsInfo['gameId'];
            $data["vid"] = $attrInfo['versionsId'];
            
            $data["goodsAttrName"] = M('versions')->where(['id'=>$attrInfo['versionsId']])->find()['vName'];
            
            $data["goodsNums"] = 1;
            $data["goodsPrice"] = $zhekou;
            
            $data["goodsName"] = $goodsInfo["goodsName"];
            $data["goodsThums"] = $goodsInfo["goodsThums"];
            $mog->add($data);
            
            if ($payway == 0) {
                // 建立订单记录
                $data = array();
                $data["orderId"] = $orderId;
                $data["logContent"] = "下单成功";
                $data["logUserId"] = $userId;
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                $mlogo = M('log_orders');
                $mlogo->add($data);
                
                // 建立订单提醒
                $sql = "SELECT userId,shopId,shopName FROM __PREFIX__shops WHERE shopId=$shopId AND shopFlag=1  ";
                $users = $this->query($sql);
                $morm = M('order_reminds');
                for ($i = 0; $i < count($users); $i ++) {
                    $data = array();
                    $data["orderId"] = $orderId;
                    $data["shopId"] = $shopId;
                    $data["userId"] = $users[$i]["userId"];
                    $data["userType"] = 0;
                    $data["remindType"] = 0;
                    $data["createTime"] = date("Y-m-d H:i:s");
                    $morm->add($data);
                }
            } else {
                $data = array();
                $data["orderId"] = $orderId;
                $data["logContent"] = "订单已提交，等待支付";
                $data["logUserId"] = $userId;
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                $mlogo = M('log_orders');
                $mlogo->add($data);
            }
        }
       
        if (count($orderIds) > 0 && $result) {
            $m->commit();
            return array(
                'status' => 1,
                "orderId" => $orderId,
                "needPay" => $needPay,
                'orderNo' => $orderNo
            );
        } else {
            $m->rollback();
            return [
                'status'=>0,
                '订单提交失败'
            ];
        }
        
    }
    
    /**
      * @author peng	
      * @date 2017-01-07
      * @descreption 获取应付的单价
      */
     public function getNeedPay($attrInfo){
        $key_asoc = [
            1=>'heigh_member_price',
            2=>'mid_member_price',
            3=>'heigh_member_price'
        ];
        $userPrice = $attrInfo[$key_asoc[M('users')->where(['userId'=>$_SESSION['userId']])->getField('rank')]];
        return $userPrice > 0?$userPrice:$attrInfo['attrPrice'];
     }
     
    
}