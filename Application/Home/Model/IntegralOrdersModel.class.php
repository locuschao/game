<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/10
 * Time: 16:43
 */
namespace Home\Model;

class IntegralOrdersModel extends BaseModel
{

    /**
     * 提交订单
     */
    public function addOrders($userId, $goodsId, $gcount, $totalMoney, $consigneeId, $needreceipt, $isself, $payway, $orderunique, $shopPrice)
    {
        $orderInfos = array();
        $orderIds = array();
        $orderNos = array();
        $remarks = I("remarks");
        // 修改库存，必须在这个位置之上才生效BUG
        $sql = "update __PREFIX__integral set goodsStock=(goodsStock-" . $gcount . "),saleCount=(saleCount+" . $gcount . ") where goodsId=" . $goodsId;
        $this->execute($sql);
        
        $m = M('orderids');
        $addressInfo = UserAddressModel::getAddressDetails($consigneeId);
        $m->startTrans();
        // 生成订单ID
        $orderSrcNo = $m->add(array(
            'rnd' => microtime(true)
        ));
        $orderNo = $orderSrcNo . "" . (fmod($orderSrcNo, 7));
        // 创建订单信息
        $data = array();
        $data["orderNo"] = $orderNo;
        $data["shopId"] = 0;
        $data["userId"] = $userId;
        $data["orderFlag"] = 1;
        $data["payType"] = $payway;
        $data["deliverMoney"] = 0;
        $data["deliverType"] = 0;
        $data["userName"] = $addressInfo["userName"];
        $data["communityId"] = $addressInfo["communityId"];
        $data["userAddress"] = $addressInfo["paddress"] . " " . $addressInfo["address"];
        $data["userTel"] = $addressInfo["userTel"];
        $data["userPhone"] = $addressInfo["userPhone"];
        $data["isInvoice"] = $needreceipt;
        $data["orderRemarks"] = $remarks;
        $data["requireTime"] = I("requireTime");
        $data["invoiceClient"] = I("invoiceClient");
        $data["isAppraises"] = 0;
        $data["isSelf"] = $isself;
        $data["createTime"] = date("Y-m-d H:i:s");
        if ($payway == 4) {
            $data["orderStatus"] = 0;
            $data["totalMoney"] = $totalMoney;
            $data["isPay"] = 1;
            $data['orderScore'] = $totalMoney;
        } else {
            $data["orderStatus"] = - 2;
            // 积分商城汇率
            $rate = 100;
            $data["totalMoney"] = $totalMoney / 100;
            $data["isPay"] = 0;
            $data['orderScore'] = 0;
        }
        $data["needPay"] = $data["totalMoney"] + $data["deliverType"];
        $data["orderunique"] = $orderunique;
        $data["orderType"] = 4;
        $morders = M('orders');
        $orderId = $morders->add($data);
        $orderNos[] = $data["orderNo"];
        $orderInfos[] = array(
            "orderId" => $orderId,
            "orderNo" => $data["orderNo"]
        );
        // 订单创建成功则建立相关记录
        if ($orderId > 0) {
            // 建立订单商品记录表
            $orderIds[] = $orderId;
            $mog = M('order_goods');
            $goodsmodel = D('Home/Integral');
            $goods = $goodsmodel->getGoodsDetails($goodsId);
            // 修改表字段类型，存入商品属性id;
            $goodsNums = $gcount;
            if ($payway == 4) {
                $goodsPrice = $shopPrice;
            } else {
                $goodsPrice = $shopPrice / 100;
            }
            $goodsName = $goods["goodsName"];
            $goodsThums = $goods["goodsThums"];
            $sql = " INSERT INTO `oto_order_goods` (`orderId`,`goodsId`,`goodsAttrId`,`goodsAttrName`,`goodsNums`,`goodsPrice`,`goodsName`,`goodsThums`) VALUES ($orderId,$goodsId,'$goodsAttrId','$goodsAttrName',$goodsNums,$goodsPrice,'$goodsName','$goodsThums')";
            // 使用add方法插入，sql语句中的goodsAttrId字段会强制转为int
            $mog->query($sql);
            // 建立订单记录
            $data = array();
            $data["orderId"] = $orderId;
            $data["logUserId"] = $userId;
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            if ($payway == 4) {
                $data["logContent"] = "下单成功";
                // 扣取用户积分
                $userm = M('Users');
                $userScore = $userm->where('userId=' . $userId)->getField('userScore');
                $newUserScore = $userScore - $totalMoney;
                $userm->where('userId=' . $userId)->setField('userScore', $newUserScore);
                // 增加积分记录
                $sql = "select userScore from __PREFIX__users where userId= " . $userId;
                $totalScore = $this->queryRow($sql);
                $srModel = M('score_record');
                $arr = array();
                $arr['userid'] = $userId;
                $arr['orderNo'] = $orderNo;
                $arr['score'] = $totalMoney;
                $arr['totalscore'] = $totalScore['userScore'];
                $arr['time'] = time();
                $arr['ip'] = $_SERVER['REMOTE_ADDR'];
                $arr['IncDec'] = - 1;
                $arr['type'] = 7;
                $rs = $srModel->add($arr);
            } else {
                $data["logContent"] = "订单已提交，等待支付";
            }
            $mlogo = M('log_orders');
            $mlogo->add($data);
        }
        if (count($orderIds) > 0) {
            $m->commit();
        } else {
            $m->rollback();
        }
        return array(
            "orderIds" => $orderIds,
            "orderInfos" => $orderInfos,
            "orderNos" => $orderNos
        );
    }

    /**
     * 获取订单详情
     */
    public function getOrderDetails($obj)
    {
        $userId = (int) $obj["userId"];
        $shopId = (int) $obj["shopId"];
        $orderId = (int) $obj["orderId"];
        $data = array();
        $sql = "SELECT * FROM __PREFIX__orders WHERE orderId = $orderId and (userId=" . $userId . " or shopId=" . $shopId . ")";
        $order = $this->queryRow($sql);
        if (empty($order))
            return $data;
        $data["order"] = $order;
        $sql = "select og.orderId, og.goodsId ,g.goodsSn, og.goodsNums, og.goodsName , og.goodsPrice shopPrice,og.goodsThums,og.goodsAttrName,og.goodsAttrName
				from __PREFIX__integral g , __PREFIX__order_goods og
				WHERE g.goodsId = og.goodsId AND og.orderId = $orderId";
        $goods = $this->query($sql);
        foreach ($goods as $key => $v) {
            $goodsAttrName = explode(',', $v['goodsAttrName']);
            $goods[$key]['goodsAttrName'] = $goodsAttrName;
        }
        $data["goodsList"] = $goods;
        
        for ($i = 0; $i < count($ogoodsList); $i ++) {
            $sgoods = $ogoodsList[$i];
            $sql = "update __PREFIX__goods set goodsStock=goodsStock+" . $sgoods['goodsNums'] . " where goodsId=" . $sgoods["goodsId"];
            $this->execute($sql);
        }
        
        $sql = "SELECT * FROM __PREFIX__log_orders WHERE orderId = $orderId ";
        $logs = $this->query($sql);
        $data["logs"] = $logs;
        // 获取物流信息
        $sql = "select oe.*,e.* from __PREFIX__order_express oe left join __PREFIX__express e on oe.exId = e.id where oe.orderId = " . $data['order']['orderId'];
        $data['express'] = $this->query($sql)[0];
        return $data;
    }

    /**
     * 取消订单
     */
    public function orderCancel($obj)
    {
        $userId = (int) $obj["userId"];
        $orderId = (int) $obj["orderId"];
        $rsdata = array(
            'status' => - 1
        );
        // 判断订单状态，只有符合状态的订单才允许改变
        $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId and orderFlag = 1 and userId=" . $userId;
        $rsv = $this->queryRow($sql);
        $cancelStatus = array(
            0,
            1,
            2,
            - 2
        ); // 未受理,已受理,打包中,待付款订单
        if (! in_array($rsv["orderStatus"], $cancelStatus))
            return $rsdata;
            // 如果是未受理和待付款的订单直接改为"用户取消【受理前】"，已受理和打包中的则要改成"用户取消【受理后-商家未知】"，后者要给商家知道有这么一回事，然后再改成"用户取消【受理后-商家已知】"的状态
        $orderStatus = - 6; // 取对商家影响最小的状态
        if ($rsv["orderStatus"] == 0 || $rsv["orderStatus"] == - 2)
            $orderStatus = - 1;
        if ($orderStatus == - 6 && I('rejectionRemarks') == '')
            return $rsdata; // 如果是受理后取消需要有原因
        $sql = "UPDATE __PREFIX__orders set orderStatus = " . $orderStatus . " WHERE orderId = $orderId and userId=" . $userId;
        $rs = $this->execute($sql);
        
        $sql = "select ord.deliverType, ord.orderId, og.goodsId ,og.goodsId, og.goodsNums 
                from __PREFIX__orders ord , __PREFIX__order_goods og 
                WHERE ord.orderId = og.orderId AND ord.orderId = $orderId";
        $ogoodsList = $this->query($sql);
        // 获取商品库存
        for ($i = 0; $i < count($ogoodsList); $i ++) {
            $sgoods = $ogoodsList[$i];
            $sql = "update __PREFIX__integral set goodsStock=goodsStock+" . $sgoods['goodsNums'] . " where goodsId=" . $sgoods["goodsId"];
            $this->execute($sql);
        }
        $sql = "Delete From __PREFIX__order_reminds where orderId=" . $orderId . " AND remindType=0";
        $this->execute($sql);
        
        $data = array();
        $m = M('log_orders');
        $data["orderId"] = $orderId;
        $data["logContent"] = "用户已取消订单" . (($orderStatus == - 6) ? "：" . I('rejectionRemarks') : "");
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $ra = $m->add($data);
        $rsdata["status"] = $ra;
        return $rsdata;
    }
}