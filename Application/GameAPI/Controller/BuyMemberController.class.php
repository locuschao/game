<?php
namespace GameAPI\Controller;

use Think\Controller;
use Think\Model;
/**
 * @author peng
 * @date 2017-04
 * @descreption 购买会员
 */
class BuyMemberController extends BaseController
{
    public function goodsList() {
        
        $goods_rank_arr=[1=>'钻石',2=>'白金',3=>'黄金'];
        $rank=M('users')->find(authCode(I('userId')))['rank'];
        $remark_url = '/Game/Agent/explain.html';
        $goods_rank_content=[
        1=>'<p>1、商品价格享受钻石VIP优惠价。</p>
        						<p><a href="#">2、会员特权与推广有奖功能介绍。</a></p>
        						<p>3、专属VIP客服。</p>
        						<p>4、手游狗钻石VIP定制水杯</p>
        						<p>5、手游狗定制钻石VIP版充电宝</p>
        						<p>6、游戏陪玩服务（即将开通）</p>',
        2=>'<p>1、商品价格享受白金VIP优惠价。</p>
        						<p><a href="#">2、会员特权与推广有奖功能介绍。</a></p>
        						<p>3、专属VIP客服。</p>
        						<p>4、手游狗白金VIP定制水杯</p>',                       
        3=>'<p>1、商品价格享受黄金VIP优惠价。</p>
        				<p><a href="#">2、会员特权与推广有奖功能介绍。</a></p>
        				<p>3、更多会员特权敬请期待...</p>',
        
        
        ];
        
        foreach(M('goods_voucher')
                ->where([
                'isSale'=>1
                ])
                ->order('rank')
                ->select() as $k=>$row) {
                $goodslist[$k]['rank'] = $row['rank'];
                $goodslist[$k]['rank_text'] = $goods_rank_arr[$row['rank']];
                $goodslist[$k]['id'] = $row['id'];
                $goodslist[$k]['rank_content'] = $goods_rank_content[$row['rank']];
                $goodslist[$k]['is_buy'] = D('Game/Voucher')->has_buy($rank,$row['rank']);
        }
        if($goodslist) $this->ajaxReturn([
                            'status'=>1,
                            'info'=>$goodslist
                        ]); 
        else  $this->ajaxReturn([
                            'status'=>0,
                            'info'=>''
                        ]); 
        
    }
    public function goodsDetail() {
        $goods_info=M('goods_voucher')->find(I('id'));
        $voucher_ids=M('voucher_goods_relation')->where(['goods_id'=>I('id')])->getField('voucher_id',true);
        $voucherList=M('voucher')->where(['id'=>['in',$voucher_ids]])->select();
        if($goods_info && $voucherList) $this->ajaxReturn([
                            'status'=>1,
                            'info'=>[
                                'goods_info'=>$goods_info,
                                'voucherlist'=>$voucherList                            
                                ]
                        ]); 
        else  $this->ajaxReturn([
                            'status'=>0,
                            'info'=>''
                        ]); 
    }
    public function addOrder(){
        
        $m = M('orderids');
        $m->startTrans();
        // 生成订单ID
        $orderSrcNo = $m->add(array(
            'rnd' => microtime(true)
        ));
        $userId= authCode(I('userId'));
        
        if(!is_numeric($userId)) $this->ajaxReturn([
            'status'=>0,
            'info'=>'没有登录'
        ]);
        
        $orderNo = D('Game/orders')->getOrderSn($orderSrcNo);
        
        // 创建订单信息
        $goodsId = I('goodsId', 0);
        $num = intval(trim(I('num')));
        
        if($num<=0){
            $this->ajaxReturn([
                'status'=>0,
                'info'=>'购买数量异常'
            ]);
        }
        
        $goodsInfo=M('goods_voucher')->find($goodsId);
        
        $userInfo=M('users')->find($userId);
        
        if(D('Game/Voucher')->has_buy($userInfo['rank'],$goodsInfo['rank'])){
            $this->ajaxReturn([
                'status'=>0,
                'info'=>'您已经购买过此产品'
            ]);
        }
        
        $totalMoney = $goodsInfo['price'] * $num;
        if($totalMoney<0){
            $this->ajaxReturn([
                'status'=>0,
                'info'=>'需要支付金额异常'
            ]);
        }
        $data = array();
        $shopId = 0;
        $data["orderNo"] = $orderNo;
        $data["shopId"] = $shopId;
        $deliverType = 0;
        $data["userId"] = $userId;
        
        $data["orderFlag"] = 1;
        $data["totalMoney"] = $totalMoney;
        $data["deliverMoney"] = 0;
        //$data["payType"] = ;
        $data["deliverType"] = $deliverType;
        $data["userName"] = '';
        $data["areaId1"] = 0;
        $data["areaId2"] = 0;
        $data["areaId3"] = 0;
        // 新字段
        $data["roleName"] = I('roleName');
        
        $data["communityId"] = 0;
        $data["userAddress"] = I('area'); // 区服
        $data["userTel"] = '';
        $data["userPhone"] = I('mobile');
        
        $data['orderScore'] = round($data["totalMoney"], 0);
        
        $data["orderType"] = I('orderType', 0);
        $data["orderRemarks"] = '购买代金券商品';
       
        $data["isAppraises"] = 0;
        
        
        $data["createTime"] = date("Y-m-d H:i:s");
        
        $data["orderStatus"] = - 2;
        
        //$data["orderunique"] = $orderunique;
        $data["isPay"] = 0;
        $data['lastMessTime'] = time();         // 魏永就   添加订单改变时间
        
        $data["needPay"] = $totalMoney;
        if($userInfo['partnerId']>0 && $userInfo['rank']==0){
            $data["is_fencheng"] = 1;
        }
        
        $morders = M('orders');
        $orderId = $morders->add($data);
        
        $orderNos[] = $data["orderNo"];
        $orderInfos[] = array(
            "orderId" => $orderId,
            "orderNo" => $data["orderNo"]
        );
        // 订单创建成功则建立相关记录
        if ($orderId > 0) {
                $orderIds[] = $orderId;
                // 建立订单商品记录表
                $mog = M('order_goods');
                $data = array();
                $data["orderId"] = $orderId;
                $data["goodsId"] = $goodsId;
                
                $data["goodsNums"] = $num;
                
                $data["goodsPrice"] = $goodsInfo['price'];
               
                $data["goodsName"] = $goodsInfo["name"];
                $data["goodsThums"] =$goodsInfo["pictureUrl"]?:'Tpl/present.png';
                
                $re=$mog->add($data);
                
                $data = array();
                $data["orderId"] = $orderId;
                $data["logContent"] = "订单已提交，等待支付";
                $data["logUserId"] = $userId;
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                $mlogo = M('log_orders');
                $re2=$mlogo->add($data);
            }
            
        $createTime = date('Y-m-d H:i:s');
        // 生成付款订单号
        $payId = M('orders_payid')->add(array(
            'createTime' => $createTime
        ));
        if ($payId) {
            
            $re3 = M('orders_payid')->add(array(
                'pid' => $payId,
                'createTime' => $createTime,
                'orderId' => $orderId,
                'type' => 1
            ));
            
        }
         
        if ($orderId  && $re && $re2 && $re3) {
            $m->commit();
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$orderId
            ]);
        } else {
            $m->rollback();
            $this->ajaxReturn([
                'status'=>0,
                'info'=>'订单提交失败!'
            ]);
        }
        
    }
}