<?php
namespace Home\Model;

/**
 * 订单服务类
 */
class OrdersModel extends BaseModel
{

    /**
     * 获以订单列表
     */
    public function getOrdersList($obj)
    {
        $userId = $obj["userId"];
        $m = M('orders');
        $sql = "SELECT * FROM __PREFIX__orders WHERE userId = $userId AND orderStatus <>-1 order by createTime desc";
        return $m->pageQuery($sql);
    }

    /**
     * 取消订单记录
     */
    public function getcancelOrderList($obj)
    {
        $userId = $obj["userId"];
        $m = M('orders');
        $sql = "SELECT * FROM __PREFIX__orders WHERE userId = $userId AND orderStatus =-1 order by createTime desc";
        return $m->pageQuery($sql);
    }

    /**
     * 获取订单详情
     */
    public function getOrdersDetails($obj)
    {
        $orderId = $obj["orderId"];
        $sql = "SELECT od.*,sp.shopName 
				FROM __PREFIX__orders od, __PREFIX__shops sp 
				WHERE od.shopId = sp.shopId And orderId = $orderId ";
        $rs = $this->query($sql);
        ;
        return $rs;
    }

    /**
     * 获取订单商品信息
     */
    public function getOrdersGoods($obj)
    {
        $orderId = $obj["orderId"];
        $sql = "SELECT g.*,og.goodsNums as ogoodsNums,og.goodsPrice as ogoodsPrice 
				FROM __PREFIX__order_goods og, __PREFIX__goods g 
				WHERE og.orderId = $orderId AND og.goodsId = g.goodsId ";
        $rs = $this->query($sql);
        return $rs;
    }

    /**
     * 获取订单商品详情
     */
    public function getOrdersGoodsDetails($obj)
    {
        $orderId = $obj["orderId"];
        $sql = "SELECT g.*,og.goodsNums as ogoodsNums,og.goodsPrice as ogoodsPrice ,ga.id as gaId
				FROM __PREFIX__order_goods og, __PREFIX__goods g 
				LEFT JOIN __PREFIX__goods_appraises ga ON g.goodsId = ga.goodsId AND ga.orderId = $orderId
				WHERE og.orderId = $orderId AND og.goodsId = g.goodsId";
        $rs = $this->query($sql);
        return $rs;
    }

    /**
     * 获取订单商品详情
     */
    public function getPayOrders($obj)
    {
        $orderIds = $obj["orderIds"];
        // 判断是否为积分订单
        $ordersModel = M('Orders');
        $orderType = $ordersModel->where('orderId = ' . $orderIds)->getField('orderType');
        if ($orderType == '4') {
            $goods = '__PREFIX__integral';
        } else {
            $goods = '__PREFIX__goods';
        }
        $sql = "SELECT o.orderId, o.orderNo, o.denomination,g.goodsId, g.goodsName ,og.goodsAttrName , og.goodsNums ,og.goodsPrice 
				FROM __PREFIX__order_goods og," . $goods . " g, __PREFIX__orders o
				WHERE o.orderId = og.orderId AND og.goodsId = g.goodsId AND o.payType in(1,2,3) AND orderFlag =1 AND o.isPay=0 AND o.needPay>0 AND o.orderStatus = -2 AND og.orderId in ($orderIds)";
        $rslist = $this->query($sql);
        $orders = array();
        foreach ($rslist as $key => $order) {
            $orders[$order["orderNo"]][] = $order;
        }
        $sql = "SELECT SUM(needPay) needPay FROM __PREFIX__orders WHERE orderId IN ($orderIds) AND isPay=0 AND payType in(1,2,3) AND needPay>0 AND orderStatus = -2 AND orderFlag =1";
        $payInfo = self::queryRow($sql);
        $data["orders"] = $orders;
        $data["needPay"] = $payInfo["needPay"];
        return $data;
    }

    /**
     * 提交订单05092
     */
    public function addOrders($userId, $consigneeId, $payway, $needreceipt, $catgoods, $orderunique, $isself, $catmoney)
    {
        $orderInfos = array();
        $orderIds = array();
        $orderNos = array();
        $remarks = I("remarks");
        $addressInfo = UserAddressModel::getAddressDetails($consigneeId);
        $m = M('orderids');
        $m->startTrans();
        foreach ($catgoods as $key => $shopgoods) {
            // 生成订单ID
            $orderSrcNo = $m->add(array(
                'rnd' => microtime(true)
            ));
            $orderNo = $orderSrcNo . "" . (fmod($orderSrcNo, 7));
            // 创建订单信息
            $data = array();
            $pshopgoods = $shopgoods["shopgoods"];
            $shopId = $pshopgoods[0]["shopId"];
            $data["orderNo"] = $orderNo;
            $data["shopId"] = $shopId;
            $deliverType = intval($pshopgoods[0]["deliveryType"]);
            $data["userId"] = $userId;
            
            $data["orderFlag"] = 1;
            $data["totalMoney"] = $shopgoods["totalMoney"];
            if ($isself == 1) { // 自提
                $deliverMoney = 0;
            } else {
                $deliverMoney = ($shopgoods["totalMoney"] < $shopgoods["deliveryFreeMoney"]) ? $shopgoods["deliveryMoney"] : 0;
            }
            $data["deliverMoney"] = $deliverMoney;
            $data["payType"] = $payway;
            $data["deliverType"] = $deliverType;
            $data["userName"] = $addressInfo["userName"];
            $data["areaId1"] = $addressInfo["areaId1"];
            $data["areaId2"] = $addressInfo["areaId2"];
            $data["areaId3"] = $addressInfo["areaId3"];
            $data["communityId"] = $addressInfo["communityId"];
            $data["userAddress"] = $addressInfo["paddress"] . " " . $addressInfo["address"];
            $data["userTel"] = $addressInfo["userTel"];
            $data["userPhone"] = $addressInfo["userPhone"];
            $data['orderScore'] = round($data["totalMoney"] + $data["deliverMoney"], 0);
            $data["isInvoice"] = $needreceipt;
            $data["orderRemarks"] = $remarks;
            $data["requireTime"] = I("requireTime");
            $data["invoiceClient"] = I("invoiceClient");
            $data["isAppraises"] = 0;
            $data["isSelf"] = $isself;
            $data["needPay"] = $shopgoods["totalMoney"] + $deliverMoney;
            $data["createTime"] = date("Y-m-d H:i:s");
            
            $orderType = 1;
            if ($pshopgoods[0]['isGroup'])
                $orderType = 3;
            if ($pshopgoods[0]['isSeckill'])
                $orderType = 2;
            $data["orderType"] = $orderType;
            if ($orderType == 1) {
                // 优惠券ID
                $data["couponId"] = $catmoney[$shopId]['youhuiId'] ? $catmoney[$shopId]['youhuiId'] : 0;
                // 优惠价格
                $data["couponMoney"] = $catmoney[$shopId]['kMoney'] ? $catmoney[$shopId]['kMoney'] : 0;
                $data["needPay"] = $data["needPay"] - $catmoney[$shopId]['kMoney'];
            }
            
            if ($payway == 0) {
                $data["orderStatus"] = 0;
            } else {
                $data["orderStatus"] = - 2;
            }
            
            $data["orderunique"] = $orderunique;
            $data["isPay"] = 0;
            $morders = M('orders');
            $orderId = $morders->add($data);
            // 如有使用优惠券，写入优惠券已使用表
            if (! empty($data["couponId"])) {
                $used_youhui['orderId'] = $orderId;
                $used_youhui['youhui_id'] = $data["couponId"];
                $used_youhui['shopId'] = $shopId;
                $used_youhui['userId'] = $userId;
                $used_youhui['useTime'] = time();
                $used_youhui['money'] = $data["couponMoney"];
                M('youhui_use_record')->add($used_youhui);
                // 用户该优惠券-1
                M('youhui_user_link')->where('youhui_id=' . $data["couponId"], 'user_id=' . $userId, 'shop_id=' . $shopId)->setDec('surplus');
                unset($_SESSION['oto_mall']['shop_good_id']);
            }
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
                foreach ($pshopgoods as $key => $sgoods) {
                    $data = array();
                    $data["orderId"] = $orderId;
                    $goodsId = $sgoods["goodsId"];
                    // 修改表字段类型，存入商品属性id;
                    $attrs = array();
                    $attrsName = array();
                    $price = 0;
                    foreach ($sgoods['attrs'] as $attr => $attrId) {
                        $attrs[] += $attrId['goodsAttrId'];
                        $attrsName[] = $attrId['attrName'] . ':' . $attrId['attrVal'];
                        $price += $attrId['shopPrice'];
                    }
                    if ($price != 0 && $sgoods['isGroup'] == 0 && $sgoods['isSeckill'] == 0) {
                        $shopPrice = $price;
                    } else {
                        $shopPrice = $sgoods['shopPrice'];
                    }
                    $goodsAttrId = implode(',', $attrs);
                    $goodsAttrName = implode(',', $attrsName);
                    $goodsNums = $sgoods["cnt"];
                    $goodsPrice = $shopPrice;
                    $goodsName = $sgoods["goodsName"];
                    $goodsThums = $sgoods["goodsThums"];
                    // 如果是团购商品则保存团购活动id
                    $goodsGroupId = $sgoods['isGroup'] ? (int) $sgoods["id"] : 0;
                    // 是否属于秒杀商品
                    $isSeckillGood = $sgoods['isSeckill'] ? 1 : 0;
                    $sql = " INSERT INTO `oto_order_goods` (`orderId`,`goodsId`,`goodsAttrId`,`goodsAttrName`,`goodsNums`,`goodsPrice`,`goodsName`,`goodsThums`,`goodsGroupId`,`isSeckillGood`) VALUES ($orderId,$goodsId,'$goodsAttrId','$goodsAttrName',$goodsNums,$goodsPrice,'$goodsName','$goodsThums','$goodsGroupId',$isSeckillGood)";
                    // 使用add方法插入，sql语句中的goodsAttrId字段会强制转为int
                    $mog->query($sql);
                }
                
                if ($payway == 0) {
                    // 建立订单记录
                    $data = array();
                    $data["orderId"] = $orderId;
                    $data["logContent"] = ($pshopgoods[0]["deliverType"] == 0) ? "下单成功" : "下单成功等待审核";
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
                    }
                    // 修改库存
                    if ($orderType != 3) {
                        if ($orderType == 2) {
                            foreach ($pshopgoods as $key => $sgoods) {
                                $sql = "update __PREFIX__goods_seckill set seckillStock=seckillStock-" . $sgoods['cnt'] . " where goodsId=" . $sgoods["goodsId"];
                                $this->execute($sql);
                                if ((int) $sgoods["attrs"] > 0) {
                                    foreach ($sgoods["attrs"] as $v) {
                                        $sql = "update __PREFIX__goods_attributes set skAttrStock=skAttrStock-" . $sgoods['cnt'] . " where id=" . $v["goodsAttrId"];
                                        $this->execute($sql);
                                    }
                                }
                            }
                        } else {
                            foreach ($pshopgoods as $key => $sgoods) {
                                $sql = "update __PREFIX__goods set goodsStock=goodsStock-" . $sgoods['cnt'] . " where goodsId=" . $sgoods["goodsId"];
                                $this->execute($sql);
                                if ((int) $sgoods["attrs"] > 0) {
                                    foreach ($sgoods["attrs"] as $v) {
                                        $sql = "update __PREFIX__goods_attributes set attrStock=attrStock-" . $sgoods['cnt'] . " where id=" . $v["goodsAttrId"];
                                        $this->execute($sql);
                                    }
                                }
                            }
                        }
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
     * 获取待付款订单
     */
    public function queryByPage($obj)
    {
        $userId = $obj["userId"];
        $pcurr = (int) I("pcurr", 0);
        $sql = "SELECT o.* FROM __PREFIX__orders o
				WHERE userId = $userId AND orderFlag=1 order by orderId desc";
        $pages = $this->pageQuery($sql, $pcurr);
        $orderList = $pages["root"];
        if (count($orderList) > 0) {
            $orderIds = array();
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $orderIds[] = $order["orderId"];
            }
            // 获取涉及的商品
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsThums,og.orderId FROM __PREFIX__order_goods og
					WHERE og.orderId in (" . implode(',', $orderIds) . ")";
            $glist = $this->query($sql);
            $goodslist = array();
            for ($i = 0; $i < count($glist); $i ++) {
                $goods = $glist[$i];
                $goodslist[$goods["orderId"]][] = $goods;
            }
            // 放回分页数据里
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $order["goodslist"] = $goodslist[$order['orderId']];
                $pages["root"][$i] = $order;
            }
        }
        return $pages;
    }

    /**
     * 获取待付款订单
     */
    public function queryPayByPage($obj)
    {
        $userId = (int) $obj["userId"];
        $orderNo = I("orderNo");
        $orderStatus = (int) I("orderStatus", 0);
        $goodsName = I("goodsName");
        $shopName = I("shopName");
        $userName = I("userName");
        $sdate = I("sdate");
        $edate = I("edate");
        $pcurr = (int) I("pcurr", 0);
        
        $sql = "SELECT o.orderId,o.orderNo,o.shopId,o.orderStatus,o.userName,o.totalMoney,o.needPay,
		        o.createTime,o.payType,o.isRefund,o.isAppraises,sp.shopName,o.orderType
		        FROM __PREFIX__orders o left join __PREFIX__shops sp on o.shopId=sp.shopId
		        WHERE o.userId = $userId AND o.orderStatus =-2 AND o.isPay = 0 AND needPay >0 AND o.payType in(1,2,3) ";
        if ($orderNo != "") {
            $sql .= " AND o.orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND o.userName like '%$userName%'";
        }
        if ($shopName != "") {
            $sql .= " AND sp.shopName like '%$shopName%'";
        }
        if ($sdate != "") {
            $sql .= " AND o.createTime >= $sdate";
        }
        if ($edate != "") {
            $sql .= " AND o.createTime <= $edate";
        }
        $sql .= " order by o.orderId desc";
        $pages = $this->pageQuery($sql, $pcurr);
        $orderList = $pages["root"];
        if (count($orderList) > 0) {
            $orderIds = array();
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $orderIds[] = $order["orderId"];
            }
            // 获取涉及的商品
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsThums,og.orderId FROM __PREFIX__order_goods og
					WHERE og.orderId in (" . implode(',', $orderIds) . ")";
            $glist = $this->query($sql);
            $goodslist = array();
            for ($i = 0; $i < count($glist); $i ++) {
                $goods = $glist[$i];
                $goodslist[$goods["orderId"]][] = $goods;
            }
            // 放回分页数据里
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $order["goodslist"] = $goodslist[$order['orderId']];
                $pages["root"][$i] = $order;
            }
        }
        return $pages;
    }

    /**
     * 获取待确认收货
     */
    public function queryReceiveByPage($obj)
    {
        $userId = (int) $obj["userId"];
        $orderNo = I("orderNo");
        $orderStatus = (int) I("orderStatus", 0);
        $goodsName = I("goodsName");
        $shopName = I("shopName");
        $userName = I("userName");
        $sdate = I("sdate");
        $edate = I("edate");
        $pcurr = (int) I("pcurr", 0);
        
        $sql = "SELECT o.orderId,o.orderNo,o.needPay,o.shopId,o.deliverType,o.orderStatus,o.userName,o.totalMoney,o.deliverMoney,o.createTime,o.payType,o.isRefund,o.isAppraises,o.orderType
		        FROM __PREFIX__orders o
		        WHERE o.userId = $userId AND o.orderStatus=3 ";
        if ($orderNo != "") {
            $sql .= " AND o.orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND o.userName like '%$userName%'";
        }
        if ($shopName != "") {
            $sql .= " AND sp.shopName like '%$shopName%'";
        }
        if ($sdate != "") {
            $sql .= " AND o.createTime >= $sdate";
        }
        if ($edate != "") {
            $sql .= " AND o.createTime <= $edate";
        }
        $sql .= " order by o.orderId desc";
        $pages = $this->pageQuery($sql, $pcurr);
        // 获取商铺名称
        $sql = "select shopId,shopName from  __PREFIX__shops ";
        $shopsm = M('Shops');
        $shops = $shopsm->query($sql);
        foreach ($pages['root'] as $k => $v) {
            foreach ($shops as $vs) {
                if ($v['shopId'] == $vs['shopId']) {
                    $pages['root'][$k]['shopName'] = $vs['shopName'];
                }
            }
        }
        $orderList = $pages["root"];
        if (count($orderList) > 0) {
            $orderIds = array();
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $orderIds[] = $order["orderId"];
            }
            // 获取涉及的商品
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsThums,og.orderId FROM __PREFIX__order_goods og
					WHERE og.orderId in (" . implode(',', $orderIds) . ")";
            $glist = $this->query($sql);
            $goodslist = array();
            for ($i = 0; $i < count($glist); $i ++) {
                $goods = $glist[$i];
                $goodslist[$goods["orderId"]][] = $goods;
            }
            // 放回分页数据里
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $order["goodslist"] = $goodslist[$order['orderId']];
                $pages["root"][$i] = $order;
            }
        }
        return $pages;
    }

    /**
     * 获取待发货订单
     */
    public function queryDeliveryByPage($obj)
    {
        $userId = (int) $obj["userId"];
        $orderNo = I("orderNo");
        $orderStatus = (int) I("orderStatus", 0);
        $goodsName = I("goodsName");
        $shopName = I("shopName");
        $userName = I("userName");
        $sdate = I("sdate");
        $edate = I("edate");
        $pcurr = (int) I("pcurr", 0);
        
        $sql = "SELECT o.orderId,o.orderNo,o.shopId,o.orderStatus,o.userName,o.totalMoney,o.needPay,o.createTime,o.payType,o.isRefund,o.isAppraises,o.orderType,o.deliverMoney
		        FROM __PREFIX__orders o
		        WHERE o.userId = $userId AND o.orderStatus in ( 0,1,2 ) ";
        if ($orderNo != "") {
            $sql .= " AND o.orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND o.userName like '%$userName%'";
        }
        if ($shopName != "") {
            $sql .= " AND sp.shopName like '%$shopName%'";
        }
        if ($sdate != "") {
            $sql .= " AND o.createTime >= $sdate";
        }
        if ($edate != "") {
            $sql .= " AND o.createTime <= $edate";
        }
        $sql .= " order by o.orderId desc";
        $pages = $this->pageQuery($sql, $pcurr);
        // 获取商铺名称
        $sql = "select shopId,shopName from  __PREFIX__shops ";
        $shopsm = M('Shops');
        $shops = $shopsm->query($sql);
        foreach ($pages['root'] as $k => $v) {
            foreach ($shops as $vs) {
                if ($v['shopId'] == $vs['shopId']) {
                    $pages['root'][$k]['shopName'] = $vs['shopName'];
                }
            }
        }
        
        $orderList = $pages["root"];
        if (count($orderList) > 0) {
            $orderIds = array();
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $orderIds[] = $order["orderId"];
            }
            // 获取涉及的商品
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsThums,og.orderId FROM __PREFIX__order_goods og
					WHERE og.orderId in (" . implode(',', $orderIds) . ")";
            $glist = $this->query($sql);
            $goodslist = array();
            for ($i = 0; $i < count($glist); $i ++) {
                $goods = $glist[$i];
                $goodslist[$goods["orderId"]][] = $goods;
            }
            // 放回分页数据里
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $order["goodslist"] = $goodslist[$order['orderId']];
                $pages["root"][$i] = $order;
            }
        }
        return $pages;
    }

    /**
     * 获取待用户的拍卖订单
     */
    public function queryAuctionOrderByPage($obj)
    {
        $userId = (int) $obj["userId"];
        $orderNo = I("orderNo");
        $orderStatus = (int) I("orderStatus", 0);
        $goodsName = I("goodsName");
        $shopName = I("shopName");
        $userName = I("userName");
        $sdate = I("sdate");
        $edate = I("edate");
        $pcurr = (int) I("pcurr", 0);
        
        $sql = "SELECT o.orderId,o.orderNo,o.shopId,o.orderStatus,o.userName,o.totalMoney,
		        o.createTime,o.payType,o.isRefund,o.isAppraises,o.orderType
		        FROM __PREFIX__orders o
		        WHERE o.userId = $userId AND o.orderType=5";
        if ($orderNo != "") {
            $sql .= " AND o.orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND o.userName like '%$userName%'";
        }
        if ($shopName != "") {
            $sql .= " AND sp.shopName like '%$shopName%'";
        }
        if ($sdate != "") {
            $sql .= " AND o.createTime >= $sdate";
        }
        if ($edate != "") {
            $sql .= " AND o.createTime <= $edate";
        }
        $sql .= " order by o.orderId desc";
        $pages = $this->pageQuery($sql, $pcurr);
        
        // 获取商铺名称
        $sql = "select shopId,shopName from  __PREFIX__shops ";
        $shopsm = M('Shops');
        $shops = $shopsm->query($sql);
        foreach ($pages['root'] as $k => $v) {
            foreach ($shops as $vs) {
                if ($v['shopId'] == $vs['shopId']) {
                    $pages['root'][$k]['shopName'] = $vs['shopName'];
                }
            }
        }
        
        $orderList = $pages["root"];
        if (count($orderList) > 0) {
            $orderIds = array();
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $orderIds[] = $order["orderId"];
            }
            // 获取涉及的商品
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsThums,og.orderId,og.goodsPrice FROM __PREFIX__order_goods og
					WHERE og.orderId in (" . implode(',', $orderIds) . ")";
            $glist = $this->query($sql);
            $goodslist = array();
            for ($i = 0; $i < count($glist); $i ++) {
                $goods = $glist[$i];
                $goodslist[$goods["orderId"]][] = $goods;
            }
            // 放回分页数据里
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $order["goodslist"] = $goodslist[$order['orderId']];
                $pages["root"][$i] = $order;
            }
        }
        // print_r($orderList);
        return $pages;
    }

    /**
     * 获取退款
     */
    public function queryRefundByPage($obj)
    {
        $userId = (int) $obj["userId"];
        $orderNo = I("orderNo");
        $orderStatus = (int) I("orderStatus", 0);
        $goodsName = I("goodsName");
        $shopName = I("shopName");
        $userName = I("userName");
        $sdate = I("sdate");
        $edate = I("edate");
        $pcurr = (int) I("pcurr", 0);
        // 必须是在线支付的才允许退款
        
        $sql = "SELECT o.orderId,o.needPay,o.orderNo,o.shopId,o.orderStatus,o.userName,o.totalMoney,
		        o.createTime,o.payType,o.isRefund,o.isAppraises,sp.shopName 
		        FROM __PREFIX__orders o,__PREFIX__shops sp 
		        WHERE o.userId = $userId AND (o.orderStatus in (-3,-4,-5) or (o.orderStatus in (-1,-4,-6,-7) and payType =1 AND o.isPay =1)) AND o.shopId=sp.shopId ";
        if ($orderNo != "") {
            $sql .= " AND o.orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND o.userName like '%$userName%'";
        }
        if ($shopName != "") {
            $sql .= " AND sp.shopName like '%$shopName%'";
        }
        if ($sdate != "") {
            $sql .= " AND o.createTime >= $sdate";
        }
        if ($edate != "") {
            $sql .= " AND o.createTime <= $edate";
        }
        $sql .= " order by o.orderId desc";
        
        $pages = $this->pageQuery($sql, $pcurr);
        $orderList = $pages["root"];
        if (count($orderList) > 0) {
            $orderIds = array();
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $orderIds[] = $order["orderId"];
            }
            // 获取涉及的商品
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsThums,og.orderId FROM __PREFIX__order_goods og
					WHERE og.orderId in (" . implode(',', $orderIds) . ")";
            $glist = $this->query($sql);
            $goodslist = array();
            for ($i = 0; $i < count($glist); $i ++) {
                $goods = $glist[$i];
                $goodslist[$goods["orderId"]][] = $goods;
            }
            // 放回分页数据里
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $order["goodslist"] = $goodslist[$order['orderId']];
                $pages["root"][$i] = $order;
            }
        }
        return $pages;
    }

    /**
     * 获取取消的订单
     */
    public function queryCancelOrders($obj)
    {
        $userId = (int) $obj["userId"];
        $orderNo = I("orderNo");
        $orderStatus = (int) I("orderStatus", 0);
        $goodsName = I("goodsName");
        $shopName = I("shopName");
        $userName = I("userName");
        $sdate = I("sdate");
        $edate = I("edate");
        $pcurr = (int) I("pcurr", 0);
        
        $sql = "SELECT o.orderId,o.needPay,o.orderNo,o.shopId,o.orderStatus,o.userName,o.totalMoney,
		        o.createTime,o.payType,o.isRefund,o.isAppraises,o.orderType
		        FROM __PREFIX__orders o
		        WHERE o.userId = $userId AND o.orderStatus in (-1,-6,-7)";
        if ($orderNo != "") {
            $sql .= " AND o.orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND o.userName like '%$userName%'";
        }
        if ($shopName != "") {
            $sql .= " AND sp.shopName like '%$shopName%'";
        }
        if ($sdate != "") {
            $sql .= " AND o.createTime >= $sdate";
        }
        if ($edate != "") {
            $sql .= " AND o.createTime <= $edate";
        }
        $sql .= " order by o.orderId desc";
        $pages = $this->pageQuery($sql, $pcurr);
        // 获取商铺名称
        $sql = "select shopId,shopName from  __PREFIX__shops ";
        $shopsm = M('Shops');
        $shops = $shopsm->query($sql);
        foreach ($pages['root'] as $k => $v) {
            foreach ($shops as $vs) {
                if ($v['shopId'] == $vs['shopId']) {
                    $pages['root'][$k]['shopName'] = $vs['shopName'];
                }
            }
            $sql = "select logContent from __PREFIX__log_orders where orderId =" . $v['orderId'] . " and logType=0 and logUserId=" . $userId . " order by logId desc limit 1";
            $ors = $this->query($sql);
            $pages['root'][$k]['logContent'] = $ors[0]['logContent'];
            $sql = "select * from __PREFIX__refund where orderid =" . $v['orderId'] . " and userid =" . $userId;
            $rs = $this->query($sql);
            $pages['root'][$k]['explain'] = $rs[0]['explain'];
        }
        $orderList = $pages["root"];
        if (count($orderList) > 0) {
            $orderIds = array();
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $orderIds[] = $order["orderId"];
            }
            // 获取涉及的商品
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsThums,og.orderId FROM __PREFIX__order_goods og
					WHERE og.orderId in (" . implode(',', $orderIds) . ")";
            $glist = $this->query($sql);
            $goodslist = array();
            for ($i = 0; $i < count($glist); $i ++) {
                $goods = $glist[$i];
                $goodslist[$goods["orderId"]][] = $goods;
            }
            // 放回分页数据里
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $order["goodslist"] = $goodslist[$order['orderId']];
                $pages["root"][$i] = $order;
            }
        }
        return $pages;
    }

    /**
     * 获取待评价交易
     */
    public function queryAppraiseByPage($obj)
    {
        $userId = (int) $obj["userId"];
        $orderNo = I("orderNo");
        $goodsName = I("goodsName");
        $shopName = I("shopName");
        $userName = I("userName");
        $sdate = I("sdate");
        $edate = I("edate");
        $pcurr = (int) I("pcurr", 0);
        $sql = "SELECT o.orderId,o.orderNo,o.needPay,o.shopId,o.orderStatus,o.userName,o.totalMoney,
		        o.createTime,o.payType,o.isRefund,o.isAppraises,o.orderType
		        FROM __PREFIX__orders o
		        WHERE o.userId = $userId";
        if ($orderNo != "") {
            $sql .= " AND o.orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND o.userName like '%$userName%'";
        }
        if ($shopName != "") {
            $sql .= " AND sp.shopName like '%$shopName%'";
        }
        if ($sdate != "") {
            $sql .= " AND o.createTime >= $sdate";
        }
        if ($edate != "") {
            $sql .= " AND o.createTime <= $edate";
        }
        $sql .= " AND o.orderStatus = 4";
        $sql .= " order by o.orderId desc";
        $pages = $this->pageQuery($sql, $pcurr);
        // 获取商铺名称
        $sql = "select shopId,shopName from  __PREFIX__shops ";
        $shopsm = M('Shops');
        $shops = $shopsm->query($sql);
        foreach ($pages['root'] as $k => $v) {
            foreach ($shops as $vs) {
                if ($v['shopId'] == $vs['shopId']) {
                    $pages['root'][$k]['shopName'] = $vs['shopName'];
                }
            }
        }
        $orderList = $pages["root"];
        if (count($orderList) > 0) {
            $orderIds = array();
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $orderIds[] = $order["orderId"];
            }
            // 获取涉及的商品
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsThums,og.orderId FROM __PREFIX__order_goods og
					WHERE og.orderId in (" . implode(',', $orderIds) . ")";
            $glist = $this->query($sql);
            $goodslist = array();
            for ($i = 0; $i < count($glist); $i ++) {
                $goods = $glist[$i];
                $goodslist[$goods["orderId"]][] = $goods;
            }
            // 放回分页数据里
            for ($i = 0; $i < count($orderList); $i ++) {
                $order = $orderList[$i];
                $order["goodslist"] = $goodslist[$order['orderId']];
                $pages["root"][$i] = $order;
            }
        }
        return $pages;
    }

    /**
     * 取消订单
     */
    public function orderCancel($obj)
    {
        $userId = (int) $obj["userId"];
        $orderId = (int) $obj["orderId"];
        $isGroup = (int) $obj["group"];
        $rsdata = array(
            'status' => - 1
        );
        if ($isGroup) {
            $orderStatus = - 3;
        } else {
            // 判断订单状态，只有符合状态的订单才允许改变
            $sql = "SELECT orderId,orderType,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId and orderFlag = 1 and userId=" . $userId;
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
        }
        $sql = "UPDATE __PREFIX__orders set orderStatus = " . $orderStatus . " WHERE orderId = $orderId and userId=" . $userId;
        $rs = $this->execute($sql);
        
        $sql = "select ord.deliverType, ord.orderId, og.goodsId ,og.goodsId, og.goodsNums,og.goodsAttrId
				from __PREFIX__orders ord , __PREFIX__order_goods og 
				WHERE ord.orderId = og.orderId AND ord.orderId = $orderId";
        $ogoodsList = $this->query($sql);
        // 获取商品库存
        for ($i = 0; $i < count($ogoodsList); $i ++) {
            $sgoods = $ogoodsList[$i];
            if ($rsv["orderType"] == 2) {
                $sql = "update __PREFIX__goods_seckill set seckillStock=seckillStock+" . $sgoods['goodsNums'] . " where goodsId=" . $sgoods["goodsId"];
            } else {
                $sql = "update __PREFIX__goods set goodsStock=goodsStock+" . $sgoods['goodsNums'] . " where goodsId=" . $sgoods["goodsId"];
            }
            $this->execute($sql);
            
            // 回滚多属性商品 对应库存；
            if (! ! $sgoods['goodsAttrId']) {
                
                $temp = explode(',', $sgoods['goodsAttrId']);
                foreach ($temp as $v) {
                    if ($rsv["orderType"] == 2) {
                        $sql = "update __PREFIX__goods_attributes set skAttrStock=skAttrStock+" . $sgoods['goodsNums'] . " where goodsId=" . $sgoods["goodsId"] . ' AND id=' . $v;
                    } else {
                        $sql = "update __PREFIX__goods_attributes set attrStock=attrStock+" . $sgoods['goodsNums'] . " where goodsId=" . $sgoods["goodsId"] . ' AND id=' . $v;
                    }
                    $this->execute($sql);
                }
            }
        }
        $sql = "Delete From __PREFIX__order_reminds where orderId=" . $orderId . " AND remindType=0";
        $this->execute($sql);
        // refund表增加记录
        $data = array();
        $m = M('refund');
        $data['orderid'] = $orderId;
        $data['time'] = time();
        if (I('reason') == 1) {
            $data['reason'] = '拍错了/订单信息错误';
        } else {
            $data['reason'] = '不想买了';
        }
        $data['explain'] = I('rejectionRemarks');
        $data['money'] = I('needPay');
        $data['userid'] = $userId;
        $data['shopid'] = I('shop');
        $m->add($data);
        // log_orders表增加记录
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

    /**
     * 用户确认收货
     */
    public function orderConfirm($obj)
    {
        $userId = (int) $obj["userId"];
        $orderId = (int) $obj["orderId"];
        $type = (int) $obj["type"];
        $rsdata = array();
        $sql = "SELECT orderId,orderNo,orderScore,orderStatus,orderType FROM __PREFIX__orders WHERE orderId = $orderId and userId=" . $userId;
        $rsv = $this->queryRow($sql);
        if ($rsv["orderStatus"] != 3) {
            $rsdata["status"] = - 1;
            return $rsdata;
        }
        // 收货则给用户增加积分
        if ($type == 1) {
            $sql = "UPDATE __PREFIX__orders set orderStatus = 4 WHERE orderId = $orderId and userId=" . $userId;
            $rs = $this->execute($sql);
            // 修改商品销量
            $sql = "UPDATE __PREFIX__goods g, __PREFIX__order_goods og, __PREFIX__orders o SET g.saleCount=g.saleCount+og.goodsNums WHERE g.goodsId= og.goodsId AND og.orderId = o.orderId AND o.orderId=$orderId AND o.userId=" . $userId;
            $rs = $this->execute($sql);
            // 非积分订单增加积分
            if ($rsv['orderType'] != 4) {
                $sql = "UPDATE __PREFIX__users set userScore=userScore+" . $rsv["orderScore"] . " WHERE userId=" . $userId;
                $rs = $this->execute($sql);
                $sql = "select userScore from __PREFIX__users where userId= " . $userId;
                $totalScore = $this->queryRow($sql);
                // 增加积分记录
                $srModel = M('score_record');
                $data = array();
                $data['userid'] = $userId;
                $data['orderNo'] = $rsv['orderNo'];
                $data['score'] = $rsv['orderScore'];
                $data['totalscore'] = $totalScore['userScore'];
                $data['time'] = time();
                $data['ip'] = $_SERVER['REMOTE_ADDR'];
                $data['IncDec'] = 1;
                $data['type'] = 1;
                $rs = $srModel->add($data);
            }
        } else {
            $sql = "UPDATE __PREFIX__orders set orderStatus = -3 WHERE orderId = $orderId and userId=" . $userId;
            $rs = $this->execute($sql);
            // refund表增加记录
            $data = array();
            $m = M('refund');
            $data['orderid'] = $orderId;
            $data['time'] = time();
            if (I('reason') == 1) {
                $data['reason'] = '拍错了/订单信息错误';
            } else {
                $data['reason'] = '不想买了';
            }
            $data['explain'] = I('rejectionRemarks');
            $data['money'] = I('needPay');
            $data['userid'] = $userId;
            $data['shopid'] = I('shop');
            $m->add($data);
        }
        // 增加记录
        $data = array();
        $m = M('log_orders');
        $data["orderId"] = $orderId;
        $data["logContent"] = ($type == 1) ? "用户已收货" : "用户拒收：" . I('rejectionRemarks');
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $ra = $m->add($data);
        $rsdata["status"] = $ra;
        ;
        return $rsdata;
    }

    /**
     * 获取订单详情
     */
    public function getOrderDetails($obj)
    {
        $userId = (int) $obj["userId"];
        /**
         * @author peng	
         * @date 2017-01-04
         * @descreption 加上平台的情况.
         */
        if(D('Common/Util')->checkIsPingTai()) $shopId=0;
        else $shopId = (int) $obj["shopId"];
        
        $orderId = (int) $obj["orderId"];
        $data = array();
        $where = " where o.orderId = $orderId and (o.userId=" . $userId . " or o.shopId=" . $shopId . ")";
        
        $sql = "select gg.goodsType,o.orderStatus,o.orderType,o.totalMoney,o.payType,o.account,o.pwd,o.userAddress,o.roleName,o.profession,o.userPhone,o.orderNo,o.qq,og.goodsNums,o.userName,o.orderStatus,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums,gg.isMiao,og.vid,o.selfBuildAccount
		from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og
		on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid left join __PREFIX__goods as gg on gg.goodsId=og.goodsId 
		left join __PREFIX__versions as v on v.id=og.vid $where order by orderId desc ";
        
        $order = $this->queryRow($sql);
        
        if ($order['orderType'] == 1 || $order['orderType'] == 2) {
            $data['fahuo'] = M('fahuo')->where(array(
                'orderId' => $orderId
            ))->select();
        }
        
        $data["order"] = $order;
        $sql = "SELECT * FROM __PREFIX__log_orders WHERE orderId = $orderId ";
        $logs = $this->query($sql);
        $data["logs"] = $logs;
        
        return $data;
    }

    /**
     * 获取退款详情
     */
    public function getRefundDetails($obj)
    {
        $userId = (int) $obj["userId"];
        $shopId = (int) $obj["shopId"];
        $orderId = (int) $obj["orderId"];
        $data = array();
        $sql = "SELECT * FROM __PREFIX__orders WHERE orderId = $orderId and (userId=" . $userId . " or shopId=" . $shopId . ")";
        $data['order'] = $this->queryRow($sql);
        $m = M('Refund');
        $data['refund'] = $m->where('orderid = ' . $orderId . ' and userid = ' . $userId)->find();
        $data['refund']['time'] = date('Y-m-d H:i:s', $data['refund']['time']);
        return $data;
    }

    /**
     * 获取用户指定状态的订单数目
     */
    public function getUserOrderStatusCount($obj)
    {
        $userId = (int) $obj["userId"];
        $data = array();
        $sql = "select orderStatus,COUNT(*) cnt from __PREFIX__orders WHERE orderStatus in (0,1,2,3) and orderFlag=1 and userId = $userId GROUP BY orderStatus";
        $olist = $this->query($sql);
        $data = array(
            '-3' => 0,
            '-2' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0
        );
        for ($i = 0; $i < count($olist); $i ++) {
            $row = $olist[$i];
            if ($row["orderStatus"] == 0 || $row["orderStatus"] == 1 || $row["orderStatus"] == 2) {
                $row["orderStatus"] = 2;
            }
            $data[$row["orderStatus"]] = $data[$row["orderStatus"]] + $row["cnt"];
        }
        // 获取未支付订单
        $sql = "select COUNT(*) cnt from __PREFIX__orders WHERE orderStatus = -2 and isRefund=0 and payType in(1,2) and orderFlag=1 and isPay = 0 and needPay >0 and userId = $userId";
        $olist = $this->query($sql);
        $data[- 2] = $olist[0]['cnt'];
        
        // 获取退款订单
        $sql = "select COUNT(*) cnt from __PREFIX__orders WHERE orderStatus in (-3,-4,-6,-7) and isRefund=0 and payType in(1,2) and orderFlag=1 and userId = $userId";
        $olist = $this->query($sql);
        $data[- 3] = $olist[0]['cnt'];
        // 获取待评价订单
        $sql = "select COUNT(*) cnt from __PREFIX__orders WHERE orderStatus =4 and isAppraises=0 and orderFlag=1 and userId = $userId";
        $olist = $this->query($sql);
        $data[4] = $olist[0]['cnt'];
        
        // 获取商城信息
        $sql = "select count(*) cnt from __PREFIX__messages WHERE  receiveUserId=" . $userId . " and msgStatus=0 and msgFlag=1 ";
        $olist = $this->query($sql);
        $data[100000] = empty($olist) ? 0 : $olist[0]['cnt'];
        
        return $data;
    }

    /**
     * 获取用户指定状态的订单数目
     */
    public function getShopOrderStatusCount($obj)
    {
        $shopId = (int) $obj["shopId"];
        $rsdata = array();
        // 待受理订单
        $sql = "SELECT COUNT(*) cnt FROM __PREFIX__orders WHERE shopId = $shopId AND orderStatus = 1 ";
        $olist = $this->queryRow($sql);
        $rsdata[0] = $olist['cnt'];
        
        // 取消-商家未知的 / 拒收订单
        $sql = "SELECT COUNT(*) cnt FROM __PREFIX__orders WHERE shopId = $shopId AND orderStatus in (-3,-6)";
        $olist = $this->queryRow($sql);
        $rsdata[5] = $olist['cnt'];
        $rsdata[100] = $rsdata[0] + $rsdata[5];
        
        //退款中订单数量
        $refund=M('refund')->where(array('biz_status'=>0,'shopId'=>$shopId))->count();
        $rsdata['refund']=$refund;
        
        // 获取商城信息
        $sql = "select count(*) cnt from __PREFIX__messages WHERE  receiveUserId=" . (int) $obj["userId"] . " and msgStatus=0 and msgFlag=1 ";
        $olist = $this->query($sql);
        $rsdata[100000] = empty($olist) ? 0 : $olist[0]['cnt'];
        
        return $rsdata;
    }

    /**
     * 获取商家订单列表
     */
    public function queryShopOrders($obj)
    {
        $userId = (int) $obj["userId"];
        $shopId = (int) $obj["shopId"];
        $pcurr = (int) I("pcurr", 0);
        $orderStatus = (int) I("statusMark");
        $orderNo = I("orderNo");
        $gameName = I("gameName");
        $versions = I("versions");
        $endDay = I("endDay");
        $starDay = I("starDay");
        $rsdata = array();
        
        $shopName = I('shopName');
        $orderNo = I('orderNo');
        $whereOrderStatus = '';
        if ($orderStatus == 0) {
            $whereOrderStatus = " and o.orderStatus>=0 ";
        } else {
            $whereOrderStatus = " and o.orderStatus = $orderStatus ";
        }
        //$where = " where o.orderFlag=1 and o.shopId=$shopId $whereOrderStatus ";
        $where = " where o.shopId=$shopId $whereOrderStatus "; #删除的也要显示
        
        if ($shopName) {
            $where .= " and s.shopName like '%$shopName%' ";
        }
        if ($orderNo) {
            $where .= " and o.orderNo=$orderNo ";
        }
        
        if ($gameName) {
            $where .= " and ga.gameName like '%$gameName%' ";
        }
        
        if ($versions) {
            $where .= " and v.vName like '%$versions%' ";
        }
        
        if ($starDay && $endDay) {
            $where .= " and o.createTime between '$starDay' and '$endDay' ";
        } else 
            if ($starDay && ! $endDay) {
                $where .= " and o.createTime >='$starDay' ";
            } else 
                if (! $starDay && $endDay) {
                    $where .= " and o.createTime<='$endDay' ";
                }
        
        $sql = "select gg.goodsType, og.goodsNums,o.userName,o.orderType,o.orderStatus,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums
		from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og
		on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid left join __PREFIX__goods as gg on gg.goodsId=og.goodsId
		left join __PREFIX__versions as v on v.id=og.vid $where order by orderId desc ";
        // 获取涉及的订单及商品
        
        
        $data = $this->pageQuery($sql, $pcurr);
        return $data;
    }

    /**
     * 获取商家拍卖订单列表 0429
     */
    public function queryShopAuctionOrders($obj)
    {
        $userId = (int) $obj["userId"];
        $shopId = (int) $obj["shopId"];
        $pcurr = (int) I("pcurr", 0);
        $orderStatus = (int) I("statusMark");
        $orderNo = I("orderNo");
        $userName = I("userName");
        $userAddress = I("userAddress");
        $id = (int) I('id');
        $skId = (int) I('skId');
        $rsdata = array();
        $sql = "SELECT orderNo,orderId,shopId,userId,userName,userAddress,totalMoney,orderStatus,createTime,orderType FROM __PREFIX__orders WHERE shopId = $shopId AND orderType=5";
        // 团购活动
        if ($id) {
            // 获取团购活动所有订单
            $orderGoodsModel = M('OrderGoods');
            $orderIds = $orderGoodsModel->where('goodsGroupId = ' . $id)
                ->field('orderId')
                ->select();
            $s = array();
            foreach ($orderIds as $v) {
                $s[] = (int) $v['orderId'];
            }
            $orderId = implode(",", $s);
            $sql .= "AND orderId in(" . $orderId . ")";
        }
        if ($skId) {
            // 获取指定秒杀活动所有订单
            $orderGoodsModel = M('OrderGoods');
            $orderIds = $orderGoodsModel->where('goodsId =' . $skId . ' and isseckillGood=1')
                ->field('orderId')
                ->select();
            $s = array();
            foreach ($orderIds as $v) {
                $s[] = (int) $v['orderId'];
            }
            $orderId = implode(",", $s);
            $sql .= "AND orderId in(" . $orderId . ")";
        }
        if ($orderStatus == 5) {
            $sql .= " AND orderStatus in (-1,-3,-4,-5,-6,-7)";
        } else {
            $sql .= " AND orderStatus = $orderStatus ";
        }
        if ($orderNo != "") {
            $sql .= " AND orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND userName like '%$userName%'";
        }
        if ($userAddress != "") {
            $sql .= " AND userAddress like '%$userAddress%'";
        }
        $sql .= " order by orderId desc ";
        $data = $this->pageQuery($sql, $pcurr);
        // 获取取消/拒收原因
        $orderIds = array();
        $noReadrderIds = array();
        $mo = M('Orders');
        foreach ($data['root'] as $key => $v) {
            if ($v['orderStatus'] == - 6 || $v['orderStatus'] == - 1)
                $noReadrderIds[] = $v['orderId'];
            $sql = "select logContent from __PREFIX__log_orders where orderId =" . $v['orderId'] . " and logType=0 and logUserId=" . $v['userId'] . " order by logId desc limit 1";
            $ors = $this->query($sql);
            $data['root'][$key]['rejectionRemarks'] = $ors[0]['logContent'];
            if ($v['orderType'] == 3) {
                $data['root'][$key]['orderNo'] = $v['orderNo'] . '【团购】';
            }
            if ($v['orderType'] == 2) {
                $data['root'][$key]['orderNo'] = $v['orderNo'] . '【秒杀】';
            }
            $lastTimeStr = (strtotime($v['createTime']) + 60 * 60 * 24 * 2);
            $data['root'][$key]['lastTime'] = date('Y-m-d H:i:s', $lastTimeStr);
            if (time() > $lastTimeStr && $v['orderStatus'] == 6) {
                $mo->where('orderId=' . $v['orderId'])
                    ->data(array(
                    'orderStatus' => 7
                ))
                    ->limit(1)
                    ->save();
                // 超时--保证金操作 买家+卖家保证金都退回给卖家
                unset($data['root'][$key]);
            }
        }
        
        // 要对用户取消【-6】的状态进行处理,表示这一条取消信息商家已经知道了
        if ($orderStatus == 5 && count($noReadrderIds) > 0) {
            $sql = "UPDATE __PREFIX__orders set orderStatus=-7 WHERE shopId = $shopId AND orderId in (" . implode(',', $noReadrderIds) . ")AND orderStatus = -6 ";
            $this->execute($sql);
        }
        return $data;
    }

    /**
     * 获取买家拍卖订单列表 0429
     */
    public function queryUserAuctionOrders($obj)
    {
        $userId = (int) $obj["userId"];
        $pcurr = (int) I("pcurr", 0);
        $orderStatus = (int) I("statusMark");
        $orderNo = I("orderNo");
        $userName = I("userName");
        $shopName = I("shopName");
        $ms = M('Shops');
        $shopId = $ms->where('shopName=' . $shopName)
            ->limit(1)
            ->getField('shopId');
        
        $rsdata = array();
        $sql = "SELECT orderNo,orderId,shopId,userId,userName,userAddress,totalMoney,orderStatus,createTime,needPay,isAppraises,orderType FROM __PREFIX__orders WHERE userId = $userId AND orderType=5";
        
        if ($orderStatus == 5) {
            $sql .= " AND orderStatus in (-1,-3,-4,-5,-6,-7)";
        } else {
            $sql .= " AND orderStatus = $orderStatus ";
        }
        if ($orderNo != "") {
            $sql .= " AND orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND userName like '%$userName%'";
        }
        if ($shopId != "") {
            $sql .= " AND shopId = $shopId ";
        }
        $sql .= " order by orderId desc ";
        $data = $this->pageQuery($sql, $pcurr);
        // 获取取消/拒收原因
        $orderIds = array();
        $noReadrderIds = array();
        $mog = M('OrderGoods');
        $mg = M('Goods');
        $mo = M('Orders');
        $ms = M('Shops');
        foreach ($data['root'] as $key => $v) {
            $goodsId = $mog->where('orderId=' . $v['orderId'])
                ->limit(1)
                ->getField('goodsId');
            $data['root'][$key]['goodsName'] = $mg->where('goodsId=' . $goodsId)
                ->limit(1)
                ->getField('goodsName');
            $data['root'][$key]['goodsId'] = $goodsId;
            $data['root'][$key]['shopName'] = $ms->where('shopId=' . $v['shopId'])
                ->limit(1)
                ->getField('shopName');
            $lastTimeStr = (strtotime($v['createTime']) + 60 * 60 * 24 * 2);
            $data['root'][$key]['lastTime'] = date('Y-m-d H:i:s', $lastTimeStr);
            if (time() > $lastTimeStr && $v['orderStatus'] == 6) {
                $mo->where('orderId=' . $v['orderId'])
                    ->data(array(
                    'orderStatus' => 7
                ))
                    ->limit(1)
                    ->save();
                // 超时--保证金操作 买家+卖家保证金都退回给卖家
                unset($data['root'][$key]);
            }
        }
        
        // 要对用户取消【-6】的状态进行处理,表示这一条取消信息商家已经知道了
        if ($orderStatus == 5 && count($noReadrderIds) > 0) {
            $sql = "UPDATE __PREFIX__orders set orderStatus=-7 WHERE shopId = $shopId AND orderId in (" . implode(',', $noReadrderIds) . ")AND orderStatus = -6 ";
            $this->execute($sql);
        }
        return $data;
    }

    /**
     * 获取商家团购订单列表
     */
    public function queryGroupShopOrders($obj)
    {
        $userId = (int) $obj["userId"];
        $shopId = (int) $obj["shopId"];
        $pcurr = (int) I("pcurr", 0);
        $orderStatus = (int) I("statusMark");
        $orderNo = I("orderNo");
        $userName = I("userName");
        $userAddress = I("userAddress");
        $rsdata = array();
        $sql = "SELECT orderNo,orderId,userId,userName,userAddress,totalMoney,orderStatus,createTime,isGroup FROM __PREFIX__orders WHERE shopId = $shopId ";
        if ($orderStatus == 5) {
            $sql .= " AND orderStatus in (-3,-4,-5,-6,-7)";
        } else {
            $sql .= " AND orderStatus = $orderStatus ";
        }
        if ($orderNo != "") {
            $sql .= " AND orderNo like '%$orderNo%'";
        }
        if ($userName != "") {
            $sql .= " AND userName like '%$userName%'";
        }
        if ($userAddress != "") {
            $sql .= " AND userAddress like '%$userAddress%'";
        }
        $sql .= " order by orderId desc ";
        $data = $this->pageQuery($sql, $pcurr);
        // 获取取消/拒收原因
        $orderIds = array();
        $noReadrderIds = array();
        foreach ($data['root'] as $key => $v) {
            if ($v['orderStatus'] == - 6)
                $noReadrderIds[] = $v['orderId'];
            $sql = "select logContent from __PREFIX__log_orders where orderId =" . $v['orderId'] . " and logType=0 and logUserId=" . $v['userId'] . " order by logId desc limit 1";
            $ors = $this->query($sql);
            $data['root'][$key]['rejectionRemarks'] = $ors[0]['logContent'];
            if ($v['isGroup'] == 1) {
                $data['root'][$key]['orderNo'] = $v['orderNo'] . '【团购】';
            }
        }
        
        // 要对用户取消【-6】的状态进行处理,表示这一条取消信息商家已经知道了
        if ($orderStatus == 5 && count($noReadrderIds) > 0) {
            $sql = "UPDATE __PREFIX__orders set orderStatus=-7 WHERE shopId = $shopId AND orderId in (" . implode(',', $noReadrderIds) . ")AND orderStatus = -6 ";
            $this->execute($sql);
        }
        return $data;
    }

    /**
     * 获取商家团购活动列表
     */
    public function queryGroupByPage()
    {
        $shopId = (int) session('WST_USER.shopId');
        $shopCatId1 = (int) I('shopCatId1', 0);
        $shopCatId2 = (int) I('shopCatId2', 0);
        $goodsName = I('goodsName');
        // 团购活动审核状态
        $goodsGroupStatus = I('goodsGroupStatus', - 1);
        // 团购活动状态
        $groupStatus = I('groupStatus', - 1);
        $sql = "select g.goodsId,g.isGroup,g.goodsName,g.goodsImg,g.goodsThums,g.goodsUnit,gp.id,gp.goodsGroupStatus,gp.groupStatus,gp.groupPrice,gp.groupMaxCount,gp.groupMinCount,gp.endTime from __PREFIX__goods g,__PREFIX__goods_group gp where g.goodsId=gp.goodsId and  g.goodsFlag=1 and g.shopId=" . $shopId . " and g.goodsStatus=1 and g.isGroup=1";
        if ($shopCatId1 > 0)
            $sql .= " and g.shopCatId1=" . $shopCatId1;
        if ($shopCatId2 > 0)
            $sql .= " and g.shopCatId2=" . $shopCatId2;
        if ($goodsName != '')
            $sql .= " and (g.goodsName like '%" . $goodsName . "%') ";
        if ($groupStatus >= 0)
            $sql .= " and gp.groupStatus=" . $groupStatus;
        if ($goodsGroupStatus >= 0)
            $sql .= " and gp.goodsGroupStatus=" . $goodsGroupStatus;
        $sql .= " order by gp.id desc";
        $goods = $this->pageQuery($sql);
        // 团购订单商品信息
        foreach ($goods['root'] as $k => $v) {
            $sql = "select og.* from __PREFIX__order_goods og left join __PREFIX__orders o on o.orderId=og.orderId where o.orderStatus in(0,1,2,3,4) and og.goodsGroupId = " . $v['id'];
            $orderGoods = $this->query($sql);
            $totalNums = 0;
            foreach ($orderGoods as $ks => $vs) {
                $totalNums += $vs['goodsNums'];
            }
            $goods['root'][$k]['totalNums'] = $totalNums;
        }
        $goods['goodsGroupStatus'] = $goodsGroupStatus;
        $goods['groupStatus'] = $groupStatus;
        return $goods;
    }

    /**
     * 获取商家秒杀活动列表
     */
    public function querySeckillByPage()
    {
        $shopId = (int) session('WST_USER.shopId');
        $shopCatId1 = (int) I('shopCatId1', 0);
        $shopCatId2 = (int) I('shopCatId2', 0);
        $goodsName = I('goodsName');
        $goodsGroupStatus = I('status', - 1);
        $sql = "select g.goodsId,g.goodsSn,g.isSeckill,g.goodsName,g.goodsImg,g.goodsThums,g.shopPrice,g.goodsStock,g.goodsUnit,g.saleCount,g.isSale,g.isRecomm,g.isHot,g.isBest,g.isNew from __PREFIX__goods g,__PREFIX__goods_seckill sk
				where g.goodsFlag=1 and g.goodsId=sk.goodsId and g.shopId=" . $shopId . " and g.goodsStatus=1 and g.isSeckill=1 ";
        if ($shopCatId1 > 0)
            $sql .= " and g.shopCatId1=" . $shopCatId1;
        if ($shopCatId2 > 0)
            $sql .= " and g.shopCatId2=" . $shopCatId2;
        if ($goodsGroupStatus >= 0)
            $sql .= " and sk.goodsSeckillStatus=" . $goodsGroupStatus;
        if ($goodsName != '')
            $sql .= " and (g.goodsName like '%" . $goodsName . "%' or g.goodsSn like '%" . $goodsName . "%') ";
        $sql .= " order by sk.seckillStatus asc";
        $goods = $this->pageQuery($sql);
        // 秒杀活动信息
        $goodsGroupModel = M('GoodsSeckill');
        // 团购订单商品信息
        $ordersGoodsModel = M('OrderGoods');
        foreach ($goods['root'] as $k => $v) {
            $group = $goodsGroupModel->where('goodsId =' . $v['goodsId'])->find();
            $goods['root'][$k] = array_merge($v, $group);
            $orderGoods = $ordersGoodsModel->where('goodsId = ' . $v['goodsId'] . ' AND isSeckillGood=1')->select();
            $totalNums = 0;
            $totalOrderArr = array();
            foreach ($orderGoods as $ks => $vs) {
                $totalNums += $vs['goodsNums'];
                $totalOrderArr[$vs['orderId']] = $vs['orderId'];
            }
            // 该商品已下单数
            $goods['root'][$k]['totalOrderNums'] = count($totalOrderArr);
            // 秒杀已出售数量
            $goods['root'][$k]['totalNums'] = $totalNums;
            
            // 0表示未开始，1表示开始中，2表示结束;如果状态不正确，就更新秒杀商品的状态
            if ($group['seckillStartTime'] < time()) {
                if ($group['seckillEndTime'] < time()) {
                    $group['seckillStatus'] = 2;
                } else {
                    $group['seckillStatus'] = 1;
                }
            } else {
                $group['seckillStatus'] = 0;
            }
            
            if ($goods['root'][$k]['seckillStatus'] != $group['seckillStatus']) {
                $goodsGroupModel->where('goodsId =' . $v['goodsId'])->save(array(
                    'seckillStatus' => $group['seckillStatus']
                ));
                $goods['root'][$k]['seckillStatus'] = $group['seckillStatus'];
            }
            
            $goods['root'][$k]['seckillEndTime'] = date('Y-n-d H:i:s', $goods['root'][$k]['seckillEndTime']);
        }
        $goods['status'] = $goodsGroupStatus;
        return $goods;
    }

    /**
     * 获取商家拍卖活动列表 0429
     */
    public function queryAuctionByPage()
    {
        $shopId = (int) session('WST_USER.shopId');
        $shopCatId1 = (int) I('shopCatId1', 0);
        $shopCatId2 = (int) I('shopCatId2', 0);
        $goodsName = I('goodsName');
        // 团购活动审核状态
        $goodsGroupStatus = I('goodsAuctionStatus', - 1);
        
        $sql = "select g.goodsId,g.shopId,g.isAuction,g.goodsName,g.goodsImg,g.goodsThums,g.goodsUnit,act.id,act.goodsAuctionStatus,act.auctionLowPrice,act.auctionAddPrice,act.auctionWinNum,act.isPay,act.auctionEndTime,act.notPassReason from __PREFIX__goods g,__PREFIX__goods_auction act where g.goodsId=act.goodsId and  g.goodsFlag=1 and g.shopId=" . $shopId . " and g.goodsStatus=1 and g.isAuction=1";
        if ($shopCatId1 > 0)
            $sql .= " and g.shopCatId1=" . $shopCatId1;
        if ($shopCatId2 > 0)
            $sql .= " and g.shopCatId2=" . $shopCatId2;
        if ($goodsName != '')
            $sql .= " and (g.goodsName like '%" . $goodsName . "%') ";
        if ($goodsGroupStatus >= 0)
            $sql .= " and act.goodsAuctionStatus=" . $goodsGroupStatus;
        $sql .= " order by act.goodsAuctionStatus desc,act.auctionStatus asc";
        $goods = $this->pageQuery($sql);
        // 拍卖活动信息，即时刷新拍卖状态
        $goods = $this->checkWin($goods);
        $goods['goodsAuctionStatus'] = $goodsGroupStatus;
        return $goods;
    }
    // 拍卖开奖，刷新拍卖状态函数
    public function checkWin($goods, $userId = '')
    {
        $goodsGroupModel = M('GoodsAuction');
        foreach ($goods['root'] as $k => $v) {
            $group = $goodsGroupModel->where('goodsId =' . $v['goodsId'])->find();
            // 0表示未开始，1表示开始中，2表示结束;如果状态不正确，就更新拍卖商品的状态
            if ($group['auctionStartTime'] < time()) {
                if ($group['auctionEndTime'] < time()) {
                    $group['auctionStatus'] = 2;
                } else {
                    $group['auctionStatus'] = 1;
                }
            } else {
                $group['auctionStatus'] = 0;
            }
            if ($goods['root'][$k]['auctionStatus'] != $group['auctionStatus']) {
                $goodsGroupModel->where('goodsId =' . $v['goodsId'])->save(array(
                    'auctionStatus' => $group['auctionStatus']
                ));
                $goods['root'][$k]['auctionStatus'] = $group['auctionStatus'];
            }
            // 更新开奖
            if ($group['auctionEndTime'] < time() && ! $group['isDeal']) {
                $ga = M('GoodsAuction');
                $ap = M('GoodsAuctionAddprice');
                $g = M('Goods');
                $winNum = $ga->where('goodsId=' . $v['goodsId'])->getField('auctionWinNum');
                $goods['root'][$k]['winNum'] = $winNum;
                $winData = $ap->where('goodsId=' . $v['goodsId'])
                    ->order('joinPrice desc')
                    ->getField('userId', true);
                $winData = array_flip(array_flip($winData));
                $winData = array_slice($winData, 0, $winNum);
                // 如果拍卖库存有剩下，则回滚到普通商品库存
                if ($winNum > count($winData)) {
                    $remain = $winNum - count($winData);
                    $g->where('goodsId=' . $v['goodsId'])->setInc('goodsStock', $remain);
                }
                // 竞争失败的用户 退回保证金 应该在这里做
                // print_r($winData);
                
                if ($winData) {
                    $m = M('orderids');
                    $mm = M('orders');
                    $g = M('orderGoods');
                    $ua = M('UserAddress');
                    foreach ($winData as $kk => $vv) {
                        $ap->where('userId=' . $vv . ' and goodsId=' . $v['goodsId'])
                            ->order('joinPrice desc')
                            ->limit(1)
                            ->save(array(
                            'isWin' => 1
                        ));
                        if ($vv == $userId)
                            $goods['root'][$k]['isWin'] = 1; // 增加的 更新获取到的数据
                        $goodsPrice = $ap->where('userId=' . $vv . ' and goodsId=' . $v['goodsId'])
                            ->order('joinPrice desc')
                            ->limit(1)
                            ->getField('joinPrice');
                        // 获取默认地址
                        $uaData = null;
                        $uaData = $ua->where('userId=' . $vv . ' AND isDefault=1')->find();
                        if (! $uaData) {
                            $uaData = $ua->where('userId=' . $vv)->find();
                        }
                        if (! $uaData) {
                            $uaData['userName'] = '不详';
                            $uaData['userPhone'] = '不详';
                        }
                        // 生成订单ID orderIds表
                        $orderSrcNo = $m->add(array(
                            'rnd' => microtime(true)
                        ));
                        // 生成【待下单】的订单 orders 表
                        $orderNo = $orderSrcNo . "" . (fmod($orderSrcNo, 7));
                        $data['orderId'] = $orderSrcNo;
                        $data['orderNo'] = $orderNo;
                        $data['orderStatus'] = 6;
                        $data['orderType'] = 5;
                        $data['userId'] = $vv;
                        $data['shopId'] = $v['shopId'];
                        $data['userName'] = $uaData['userName'];
                        $data['userPhone'] = $uaData['userPhone'];
                        $data['totalMoney'] = $goodsPrice;
                        $data['createTime'] = date('Y-m-d H:i:s', time());
                        $mm->add($data);
                        // 生成订单商品记录 order_goods表
                        $dataG['goodsId'] = $v['goodsId'];
                        $dataG['orderId'] = $orderSrcNo;
                        $dataG['goodsNums'] = 1;
                        $dataG['goodsPrice'] = $goodsPrice;
                        $dataG['goodsName'] = $v['goodsName'];
                        $dataG['goodsThums'] = $v['goodsThums'];
                        $g->add($dataG);
                    }
                    $ga->where('goodsId=' . $v['goodsId'])->save(array(
                        'isDeal' => 1
                    ));
                    $goods['root'][$k]['isDeal'] = 1; // 增加的 更新获取到的数据
                } else {
                    $ga->where('goodsId=' . $v['goodsId'])->save(array(
                        'isDeal' => 2
                    ));
                    $goods['root'][$k]['isDeal'] = 2; // 增加的 更新获取到的数据
                }
            }
            // $goods['root'][$k]['auctionEndTime']=date('Y-n-d H:i:s',$goods['root'][$k]['auctionEndTime']);
        }
        return $goods;
    }

    /**
     * 商家受理订单-只能受理【未受理】的订单
     */
    public function shopOrderAccept($obj)
    {
        $userId = (int) $obj["userId"];
        $orderId = (int) $obj["orderId"];
        $shopId = (int) $obj["shopId"];
        $rsdata = array();
        $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId AND orderFlag=1 and shopId=" . $shopId;
        $rsv = $this->queryRow($sql);
        if ($rsv["orderStatus"] != 0) {
            $rsdata["status"] = - 1;
            return $rsdata;
        }
        
        $sql = "UPDATE __PREFIX__orders set orderStatus = 1 WHERE orderId = $orderId and shopId=" . $shopId;
        $rs = $this->execute($sql);
        
        $data = array();
        $m = M('log_orders');
        $data["orderId"] = $orderId;
        $data["logContent"] = "商家已受理订单";
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $ra = $m->add($data);
        $rsdata["status"] = $ra;
        return $rsdata;
    }

    /**
     * 商家取消团购订单
     */
    public function shopOrderCancel($obj)
    {
        $userId = (int) $obj["userId"];
        $orderId = (int) $obj["orderId"];
        $shopId = (int) $obj["shopId"];
        $rsdata = array(
            'status' => - 1
        );
        // 判断订单状态，只有符合状态的订单才允许改变
        $sql = "SELECT shopId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId and orderFlag = 1 and shopId=" . $shopId;
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
            // $orderStatus = -6;//取对商家影响最小的状态
            // if($rsv["orderStatus"]==0 || $rsv["orderStatus"]==-2 )
        $orderStatus = - 1; // 取消订单状态
                           // if($orderStatus==-6 && I('rejectionRemarks')=='')return $rsdata;//如果是受理后取消需要有原因
        $sql = "UPDATE __PREFIX__orders set orderStatus = " . $orderStatus . " WHERE orderId = $orderId and shopId=" . $shopId;
        $rs = $this->execute($sql);
        $sql = "select ord.deliverType, ord.orderId, og.goodsId ,og.goodsId, og.goodsNums ,og.goodsAttrId ,g.*
				from __PREFIX__orders ord , __PREFIX__order_goods og ,__PREFIX__goods g
				WHERE ord.orderId = og.orderId AND og.goodsId = g.goodsId AND ord.orderId = $orderId";
        $ogoodsList = $this->query($sql);
        // 获取商品库存
        $sql = "update __PREFIX__goods set goodsStock=goodsStock+" . $ogoodsList[0]['goodsNums'] . " where goodsId=" . $ogoodsList[0]["goodsId"];
        $this->execute($sql);
        if ($ogoodsList[0]["goodsAttrId"]) {
            $goodsAttrId = explode(',', $ogoodsList[0]["goodsAttrId"]);
            foreach ($goodsAttrId as $v) {
                $sql = "update __PREFIX__goods_attributes set attrStock=attrStock-" . $ogoodsList[0]['goodsNums'] . " where id=" . $v;
                $arr[] = $sql;
                $this->execute($sql);
            }
        }
        $sql = "Delete From __PREFIX__order_reminds where orderId=" . $orderId . " AND remindType=0";
        $this->execute($sql);
        
        $data = array();
        $m = M('log_orders');
        $data["orderId"] = $orderId;
        $data["logContent"] = "商家取消订单" . (($orderStatus == - 6) ? "：" . I('rejectionRemarks') : "");
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $ra = $m->add($data);
        $rsdata["status"] = $ra;
        return $rsdata;
    }

    /**
     * 商家批量受理订单-只能受理【未受理】的订单
     */
    public function batchShopOrderAccept()
    {
        $USER = session('WST_USER');
        $userId = (int) $USER["userId"];
        $orderIds = I("orderIds");
        $shopId = (int) $USER["shopId"];
        if ($orderIds == '')
            return array(
                'status' => - 2
            );
        $orderIds = explode(',', $orderIds);
        $orderNum = count($orderIds);
        $editOrderNum = 0;
        foreach ($orderIds as $orderId) {
            if ($orderId == '')
                continue; // 订单号为空则跳过
            $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId AND orderFlag=1 and shopId=" . $shopId;
            $rsv = $this->queryRow($sql);
            if ($rsv["orderStatus"] != 0)
                continue; // 订单状态不符合则跳过
            $sql = "UPDATE __PREFIX__orders set orderStatus = 1 WHERE orderId = $orderId and shopId=" . $shopId;
            $rs = $this->execute($sql);
            
            $data = array();
            $m = M('log_orders');
            $data["orderId"] = $orderId;
            $data["logContent"] = "商家已受理订单";
            $data["logUserId"] = $userId;
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            $ra = $m->add($data);
            $editOrderNum ++;
        }
        if ($editOrderNum == 0)
            return array(
                'status' => - 1
            ); // 没有符合条件的执行操作
        if ($editOrderNum < $orderNum)
            return array(
                'status' => - 2
            ); // 只有部分订单符合操作
        return array(
            'status' => 1
        );
    }

    /**
     * 商家打包订单-只能处理[受理]的订单
     */
    public function shopOrderProduce($obj)
    {
        $userId = (int) $obj["userId"];
        $shopId = (int) $obj["shopId"];
        $orderId = (int) $obj["orderId"];
        $rsdata = array();
        $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId AND orderFlag =1 and shopId=" . $shopId;
        $rsv = $this->queryRow($sql);
        if ($rsv["orderStatus"] != 1) {
            $rsdata["status"] = - 1;
            return $rsdata;
        }
        
        $sql = "UPDATE __PREFIX__orders set orderStatus = 2 WHERE orderId = $orderId and shopId=" . $shopId;
        $rs = $this->execute($sql);
        $data = array();
        $m = M('log_orders');
        $data["orderId"] = $orderId;
        $data["logContent"] = "订单打包中";
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $ra = $m->add($data);
        $rsdata["status"] = $ra;
        ;
        return $rsdata;
    }

    /**
     * 商家批量打包订单-只能处理[受理]的订单
     */
    public function batchShopOrderProduce()
    {
        $USER = session('WST_USER');
        $userId = (int) $USER["userId"];
        $orderIds = I("orderIds");
        $shopId = (int) $USER["shopId"];
        if ($orderIds == '')
            return array(
                'status' => - 2
            );
        $orderIds = explode(',', $orderIds);
        $orderNum = count($orderIds);
        $editOrderNum = 0;
        foreach ($orderIds as $orderId) {
            if ($orderId == '')
                continue; // 订单号为空则跳过
            $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId AND orderFlag =1 and shopId=" . $shopId;
            $rsv = $this->queryRow($sql);
            if ($rsv["orderStatus"] != 1)
                continue; // 订单状态不符合则跳过
            
            $sql = "UPDATE __PREFIX__orders set orderStatus = 2 WHERE orderId = $orderId and shopId=" . $shopId;
            $rs = $this->execute($sql);
            $data = array();
            $m = M('log_orders');
            $data["orderId"] = $orderId;
            $data["logContent"] = "订单打包中";
            $data["logUserId"] = $userId;
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            $ra = $m->add($data);
            $editOrderNum ++;
        }
        if ($editOrderNum == 0)
            return array(
                'status' => - 1
            ); // 没有符合条件的执行操作
        if ($editOrderNum < $orderNum)
            return array(
                'status' => - 2
            ); // 只有部分订单符合操作
        return array(
            'status' => 1
        );
    }

    /**
     * 商家发货配送订单
     */
    public function shopOrderDelivery($obj)
    {
        $userId = (int) $obj["userId"];
        $orderId = (int) $obj["orderId"];
        $shopId = (int) $obj["shopId"];
        $deliveryType = (int) I("deliveryType");
        $rsdata = array();
        $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId AND orderFlag =1 and shopId=" . $shopId;
        $rsv = $this->queryRow($sql);
        if ($rsv["orderStatus"] != 2) {
            $rsdata["status"] = - 1;
            return $rsdata;
        }
        $sql = "UPDATE __PREFIX__orders set orderStatus = 3,deliverType = " . $deliveryType . " WHERE orderId = $orderId and shopId=" . $shopId;
        $rs = $this->execute($sql);
        // 如果是物流配送，增加订单物流信息
        if ($deliveryType == 1) {
            $exId = (int) I("expressId");
            $trackNumber = I("trackNumber");
            $sql = "INSERT INTO __PREFIX__order_express (orderId,exId,trackNumber,deliveryTime) VALUES(" . $orderId . "," . $exId . ",'" . $trackNumber . "'," . time() . ")";
            $rs = $this->execute($sql);
        }
        $data = array();
        $m = M('log_orders');
        $data["orderId"] = $orderId;
        $data["logContent"] = "商家已发货";
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $ra = $m->add($data);
        $rsdata["status"] = $ra;
        ;
        return $rsdata;
    }

    /**
     * 商家发货配送订单
     */
    public function batchShopOrderDelivery($obj)
    {
        $USER = session('WST_USER');
        $userId = (int) $USER["userId"];
        $orderIds = I("orderIds");
        $shopId = (int) $USER["shopId"];
        if ($orderIds == '')
            return array(
                'status' => - 2
            );
        $orderIds = explode(',', $orderIds);
        $orderNum = count($orderIds);
        $editOrderNum = 0;
        foreach ($orderIds as $orderId) {
            if ($orderId == '')
                continue; // 订单号为空则跳过
            $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId AND orderFlag =1 and shopId=" . $shopId;
            $rsv = $this->queryRow($sql);
            if ($rsv["orderStatus"] != 2)
                continue; // 状态不符合则跳过
            
            $sql = "UPDATE __PREFIX__orders set orderStatus = 3 WHERE orderId = $orderId and shopId=" . $shopId;
            $rs = $this->execute($sql);
            
            $data = array();
            $m = M('log_orders');
            $data["orderId"] = $orderId;
            $data["logContent"] = "商家已发货";
            $data["logUserId"] = $userId;
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            $ra = $m->add($data);
            $editOrderNum ++;
        }
        if ($editOrderNum == 0)
            return array(
                'status' => - 1
            ); // 没有符合条件的执行操作
        if ($editOrderNum < $orderNum)
            return array(
                'status' => - 2
            ); // 只有部分订单符合操作
        return array(
            'status' => 1
        );
    }

    /*
     * 商家查看物流跟踪信息
     */
    public function shopOrderExpress($obj)
    {
        $userId = (int) $obj["userId"];
        $shopId = (int) $obj["shopId"];
        $orderId = (int) $obj["orderId"];
        // 获取物流公司拼音
        $oeModel = M('OrderExpress');
        $orderExpress = $oeModel->where('orderId = ' . $orderId)->find();
        $expressModel = M('Express');
        $express = $expressModel->where('id = ' . $orderExpress['exId'])->find();
        $type = $express['pinyin'];
        $postid = $orderExpress['trackNumber'];
        // 发送快递跟踪请求
        $url = "http://www.kuaidi100.com/query?type=$type&postid=$postid";
        $html = json_decode(file_get_contents($url));
        // 将得到的数据转换成数组
        $arr = array();
        foreach ($html->data as $k => $v) {
            $arr[$k]['time'] = $v->time;
            $arr[$k]['context'] = $v->context;
            $arr[$k]['ftime'] = $v->ftime;
        }
        krsort($arr);
        $data = array();
        $data['arr'] = $arr;
        $data['express'] = $express;
        $data['orderEx'] = $orderExpress;
        return $data;
    }

    /**
     * 商家确认收货
     */
    public function shopOrderReceipt($obj)
    {
        $userId = (int) $obj["userId"];
        $shopId = (int) $obj["shopId"];
        $orderId = (int) $obj["orderId"];
        $rsdata = array();
        $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId AND orderFlag =1 and shopId=" . $shopId;
        $rsv = $this->queryRow($sql);
        if ($rsv["orderStatus"] != 4) {
            $rsdata["status"] = - 1;
            return $rsdata;
        }
        
        $sql = "UPDATE __PREFIX__orders set orderStatus = 5 WHERE orderId = $orderId and shopId=" . $shopId;
        $rs = $this->execute($sql);
        
        $data = array();
        $m = M('log_orders');
        $data["orderId"] = $orderId;
        $data["logContent"] = "商家确认已收货，订单完成";
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $ra = $m->add($data);
        $rsdata["status"] = $ra;
        ;
        return $rsdata;
    }

    /**
     * 商家确认拒收/不同意拒收
     */
    public function shopOrderRefund($obj)
    {
        $userId = (int) $obj["userId"];
        $orderId = (int) $obj["orderId"];
        $shopId = (int) $obj["shopId"];
        $type = (int) I('type');
        $rsdata = array();
        $sql = "SELECT orderId,orderNo,orderStatus FROM __PREFIX__orders WHERE orderId = $orderId AND orderFlag = 1 and shopId=" . $shopId;
        $rsv = $this->queryRow($sql);
        if ($rsv["orderStatus"] != - 3 && $rsv["orderStatus"] != - 6 && $rsv["orderStatus"] != - 7) {
            $rsdata["status"] = - 1;
            return $rsdata;
        }
        // 同意拒收
        if ($type == 1) {
            $sql = "UPDATE __PREFIX__orders set orderStatus = -4 WHERE orderId = $orderId and shopId=" . $shopId;
            $rs = $this->execute($sql);
            // 加回库存
            if ($rs > 0) {
                $sql = "SELECT goodsId,goodsNums,goodsAttrId from __PREFIX__order_goods WHERE orderId = $orderId";
                $oglist = $this->query($sql);
                foreach ($oglist as $key => $ogoods) {
                    $goodsId = $ogoods["goodsId"];
                    $goodsNums = $ogoods["goodsNums"];
                    $goodsAttrId = $ogoods["goodsAttrId"];
                    $sql = "UPDATE __PREFIX__goods set goodsStock = goodsStock+$goodsNums WHERE goodsId = $goodsId";
                    $this->execute($sql);
                    if ($goodsAttrId > 0) {
                        $sql = "UPDATE __PREFIX__goods_attributes set attrStock = attrStock+$goodsNums WHERE id = $goodsAttrId";
                        $this->execute($sql);
                    }
                }
            }
        } else { // 不同意拒收
            if (I('rejectionRemarks') == '')
                return $rsdata; // 不同意拒收必须填写原因
            $sql = "UPDATE __PREFIX__orders set orderStatus = -5 WHERE orderId = $orderId and shopId=" . $shopId;
            $rs = $this->execute($sql);
        }
        // refund表修改记录
        $data = array();
        $m = M('refund');
        $usersModel = M('Users');
        $loginName = $usersModel->where('userId =' . $userId)->getField('loginName');
        $data['operator'] = $loginName;
        $data['biz_time'] = time();
        $data['biz_status'] = ($type == 1) ? 1 : 2;
        $m->where('orderid =' . $orderId . ' and shopId =' . $shopId)->save($data);
        $data = array();
        $m = M('log_orders');
        $data["orderId"] = $orderId;
        $data["logContent"] = ($type == 1) ? "商家同意退款" : "商家不同意退款：" . I('rejectionRemarks');
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $ra = $m->add($data);
        $rsdata["status"] = $ra;
        ;
        return $rsdata;
    }

    /**
     * 检查订单是否已支付
     */
    public function checkOrderPay($obj)
    {
        $userId = (int) $obj["userId"];
        $orderIds = $obj["orderIds"];
        $sql = "SELECT count(orderId) counts FROM __PREFIX__orders WHERE userId = $userId AND orderId in ($orderIds) AND orderFlag = 1 AND orderStatus = -2 AND isPay = 0 AND payType in (1,2,3)";
        $rsv = $this->query($sql);
        $ocnt = count(explode(",", $orderIds));
        $data = array();
        
        if ($ocnt == $rsv[0]['counts']) {
            
            $sql = "SELECT og.goodsId,og.goodsName,og.goodsAttrName,g.goodsStock,og.goodsNums, og.goodsAttrId, ga.attrStock FROM  __PREFIX__goods g ,__PREFIX__order_goods og
					, __PREFIX__goods_attributes ga where ga.goodsId=og.goodsId and og.goodsAttrId=ga.id
					and og.goodsId = g.goodsId and og.orderId in($orderIds)";
            $glist = $this->query($sql);
            if (count($glist) > 0) {
                $rlist = array();
                foreach ($glist as $goods) {
                    if ($goods["goodsAttrId"] > 0) {
                        if ($goods["attrStock"] < $goods["goodsNums"]) {
                            $rlist[] = $goods;
                        }
                    } else {
                        if ($goods["goodsStock"] < $goods["goodsNums"]) {
                            $rlist[] = $goods;
                        }
                    }
                }
                if (count($rlist) > 0) {
                    $data["status"] = - 2;
                    $data["rlist"] = $rlist;
                } else {
                    $data["status"] = 1;
                }
            } else {
                $data["status"] = 1;
            }
        } else {
            $data["status"] = - 1;
        }
        
        return $data;
    }
    
    // 获取优惠券
    public function getYouhui($userId, $shopId, $catgoods, $paygoods, $totalMoney)
    {
        // 获取商品各项id
        $map['goodsId'] = array(
            'in',
            $paygoods
        );
        $data = M('goods')->where($map)
            ->field('shopId,goodsId,shopCatId1,shopCatId2,goodsCatId1,goodsCatId2,goodsCatId3,brandId,shopPrice')
            ->select();
        // 需要查询的字段
        $field = array(
            'oto_youhui.name,oto_youhui.breaks_menoy,oto_youhui.youhui_scope,oto_youhui.good_id,oto_youhui.deal_cate_id,oto_youhui.shop_cat_type,oto_youhui.shop_cat_id,oto_youhui.brand_id,oto_youhui.youhui_type,oto_youhui.supplier_id,oto_youhui.breaks_menoy,oto_youhui.total_fee,oto_youhui.begin_time,oto_youhui.deal_cate_type,oto_youhui_user_link.*'
        );
        foreach ($shopId as $key => $vo) {
            
            // 查询字段
            $where = array(
                'oto_youhui_user_link.user_id=' . $userId,
                'oto_youhui_user_link.shop_id=' . $vo,
                'oto_youhui_user_link.u_is_effect=1'
            );
            $where['oto_youhui_user_link.surplus'] = array(
                'gt',
                0
            );
            
            // 查询优惠表和用户领取表的内容
            $youhui[$vo] = M('youhui_user_link')->join('oto_youhui ON oto_youhui_user_link.youhui_id = oto_youhui.id')
                ->field($field)
                ->where($where)
                ->select();
        }
        $count = M('youhui_user_link')->where('u_is_effect=1 AND user_id=' . $userId)->count();
        $gcount = count($paygoods);
        
        foreach ($catgoods as $key => $value) {
            for ($i = 0; $i < $count; $i ++) {
                // 获取本次购物车该商户的总金额
                $shop_good_id[$key]['totalMoney'] = $catgoods[$key]['totalMoney'];
                for ($j = 0; $j < $gcount; $j ++) {
                    // 判断是否团购或秒杀
                    $gknum = $catgoods[$key]['shopgoods'][$i]['isGroup'] + $catgoods[$key]['shopgoods'][$i]['isSeckill'];
                    if ($gknum == 0) {
                        if ($catgoods[$key]['shopgoods'][$i]['goodsId'] == $data[$j]['goodsId']) {
                            // 获取商品属性id并按$catgoods顺序排序
                            $shop_good_id[$key][$i] = $data[$j];
                            // 获取商品金额
                            $shop_good_id[$key][$i]['cnt'] = $catgoods[$key]['shopgoods'][$i]['cnt'];
                            $shop_good_id[$key][$i]['totalMoney'] = $shop_good_id[$key][$i]['shopPrice'] * $shop_good_id[$key][$i]['cnt'];
                        }
                    }
                }
            }
        }
        // 获取商品总金额
        $shop_good_id['totalMoney'] = $totalMoney;
        
        // 遍历选择合并获取的优惠券和商品各项id
        foreach ($shop_good_id as $key => $vo) {
            // 循环优惠券
            for ($i = 0; $i < $count; $i ++) {
                
                switch ($youhui[$key][$i]['youhui_scope']) {
                    case '1':
                        $ture = 0;
                        // 优惠券是否在使用期间
                        if ($youhui[$key][$i]['begin_time'] <= time()) {
                            for ($j = 0; $j < $gcount; $j ++) {
                                if ($youhui[$key][$i]['total_fee'] <= $shop_good_id[$key]['totalMoney'] && $shop_good_id[$key][$j]['goodsId']) {
                                    $ture ++;
                                    $shop_good_id[$key]['youhui_scope'][$youhui[$key][$i]['youhui_id']] .= $shop_good_id[$key][$j]['goodsId'] . '/';
                                }
                            }
                            if ($ture >= 1) {
                                $catgoods[$key]['youhui'][] = $youhui[$key][$i];
                            }
                        }
                        break;
                    case '2':
                        // 优惠券是否在使用期间
                        if ($youhui[$key][$i]['begin_time'] <= time()) {
                            // 判断是几级店铺分类
                            switch ($youhui[$key][$i]['shop_cat_type']) {
                                case '2':
                                    $shopCatId = 'shopCatId1';
                                    break;
                                case '3':
                                    $shopCatId = 'shopCatId2';
                                    break;
                            }
                            $ture = 0;
                            // 循环商品
                            $totalMoney = 0;
                            for ($j = 0; $j < $gcount; $j ++) {
                                
                                if ($youhui[$key][$i]['shop_cat_id'] == $shop_good_id[$key][$j][$shopCatId]) {
                                    $totalMoney = $totalMoney + $shop_good_id[$key][$j]['totalMoney'];
                                }
                                
                                if ($youhui[$key][$i]['total_fee'] <= $totalMoney && $shop_good_id[$key][$j]['goodsId']) {
                                    $ture ++;
                                    $shop_good_id[$key]['youhui_scope'][$youhui[$key][$i]['youhui_id']] .= $shop_good_id[$key][$j]['goodsId'] . '/';
                                }
                            }
                            if ($ture >= 1) {
                                $catgoods[$key]['youhui'][] = $youhui[$key][$i];
                            }
                        }
                        
                        break;
                    case '3':
                        // 优惠券是否在使用期间
                        if ($youhui[$key][$i]['begin_time'] <= time()) {
                            $youhui[$key][$i]['good_id'] = explode(',', $youhui[$key][$i]['good_id']);
                            $ture = 0;
                            $totalMoney = 0;
                            for ($j = 0; $j < $gcount; $j ++) {
                                if (in_array($shop_good_id[$key][$j]['goodsId'], $youhui[$key][$i]['good_id'])) {
                                    $totalMoney = $totalMoney + $shop_good_id[$key][$j]['totalMoney'];
                                }
                                
                                if ($youhui[$key][$i]['total_fee'] <= $totalMoney && $shop_good_id[$key][$j]['goodsId'] && in_array($shop_good_id[$key][$j]['goodsId'], $youhui[$key][$i]['good_id'])) {
                                    $ture ++;
                                    $shop_good_id[$key]['youhui_scope'][$youhui[$key][$i]['youhui_id']] .= $shop_good_id[$key][$j]['goodsId'] . '/';
                                }
                            }
                            if ($ture >= 1) {
                                $catgoods[$key]['youhui'][] = $youhui[$key][$i];
                            }
                        }
                        break;
                    case '4':
                        // 优惠券是否在使用期间
                        if ($youhui[$key][$i]['begin_time'] <= time()) {
                            $youhui[$key][$i]['brand_id'] = explode(',', $youhui[$key][$i]['brand_id']);
                            $ture = 0;
                            $totalMoney = 0;
                            for ($j = 0; $j < $gcount; $j ++) {
                                
                                if (in_array($shop_good_id[$key][$j]['brandId'], $youhui[$key][$i]['brand_id'])) {
                                    $totalMoney = $totalMoney + $shop_good_id[$key][$j]['totalMoney'];
                                }
                                if ($youhui[$key][$i]['total_fee'] <= $totalMoney && $shop_good_id[$key][$j]['goodsId'] && in_array($shop_good_id[$key][$j]['brandId'], $youhui[$key][$i]['brand_id'])) {
                                    $ture ++;
                                    $shop_good_id[$key]['youhui_scope'][$youhui[$key][$i]['youhui_id']] .= $shop_good_id[$key][$j]['goodsId'] . '/';
                                }
                            }
                            if ($ture >= 1) {
                                $catgoods[$key]['youhui'][] = $youhui[$key][$i];
                            }
                        }
                        break;
                    case '5':
                        // 优惠券是否在使用期间
                        if ($youhui[$key][$i]['begin_time'] <= time()) {
                            // 判断是几级商城分类
                            switch ($youhui[$key][$i]['deal_cate_type']) {
                                case '1':
                                    $goodscatId = 'goodsCatId1';
                                    break;
                                case '2':
                                    $goodscatId = 'goodsCatId2';
                                    break;
                                case '3':
                                    $goodscatId = 'goodsCatId3';
                                    break;
                            }
                            $ture = 0;
                            $totalMoney = 0;
                            
                            for ($j = 0; $j < $gcount; $j ++) {
                                
                                if ($shop_good_id[$key][$j][$goodscatId] == $youhui[$key][$i]['deal_cate_id']) {
                                    $totalMoney = $totalMoney + $shop_good_id[$key][$j]['totalMoney'];
                                }
                                
                                if ($youhui[$key][$i]['total_fee'] <= $totalMoney && $shop_good_id[$key][$j]['goodsId']) {
                                    $ture ++;
                                    $shop_good_id[$key]['youhui_scope'][$youhui[$key][$i]['youhui_id']] .= $shop_good_id[$key][$j]['goodsId'] . '/';
                                }
                            }
                            if ($ture >= 1) {
                                $catgoods[$key]['youhui'][] = $youhui[$key][$i];
                            }
                        }
                        break;
                }
            }
        }
        session('shop_good_id', $shop_good_id);
        return $catgoods;
    }

    /**
     * 处理订单返回的优惠券信息
     */
    public function runYouhui($userId, $youhuiId, $shop_good_id, $shopId, $catmoney)
    {
        $youhui = M('youhui')->field('youhui_type,breaks_menoy,end_time,total_fee,begin_time')->find($youhuiId);
        // 处理选择优惠券所属的商品id
        $goodsId = substr($shop_good_id[$shopId]['youhui_scope'][$youhuiId], 0, strlen($shop_good_id[$shopId]['youhui_scope'][$youhuiId]) - 1);
        $goodsId = explode('/', $goodsId);
        // 循环优惠券的商品id计算价格
        $gkmoney = 0;
        foreach ($goodsId as $gkey => $gval) {
            for ($i = 0; $i < count($shop_good_id[$shopId]); $i ++) {
                if ($gval == $shop_good_id[$shopId][$i]['goodsId']) {
                    // 计算所属商品的总金额
                    $gtotalMoney = $gtotalMoney + $shop_good_id[$shopId][$i]['totalMoney'];
                }
                // 判断是否团购或秒杀
            }
        }
        // 不属于优惠券的商品金额
        $nMoney = $shop_good_id[$shopId]['totalMoney'] - $gtotalMoney;
        // 计算优惠后金额
        // 判断是折扣还是减免
        if ($youhui['youhui_type'] == 1) {
            $breaks_menoy = $youhui['breaks_menoy'] / 10;
            $ytotalMoney = $gtotalMoney * $breaks_menoy;
            $youhui['breaks_menoy'] = '打' . $youhui['breaks_menoy'] . '折';
        } else 
            if ($youhui['youhui_type'] == 0) {
                $ytotalMoney = $gtotalMoney - $youhui['breaks_menoy'];
                $youhui['breaks_menoy'] = '减免' . $youhui['breaks_menoy'] . '元';
            }
        if ($ytotalMoney < 0) {
            $ytotalMoney = 0;
        }
        // 扣掉的金额
        $Money['kMoney'] = $gtotalMoney - $ytotalMoney;
        // 优惠后该商店的总金额
        $Money['ytotalMoney'] = $shop_good_id[$shopId]['totalMoney'] - $Money['kMoney'];
        $Money['youhui_type'] = $youhui['youhui_type'];
        $Money['status'] = 1;
        $Money['msg'] = '指定商品满' . $youhui['total_fee'] . '元，' . $youhui['breaks_menoy'];
        $Money['youhuiId'] = $youhuiId;
        $catmoney[$shopId] = $Money;
        session('catmoney', $catmoney);
        $now_catmoney = session('catmoney');
        // 应付总额
        $zonge = $shop_good_id['totalMoney'];
        foreach ($now_catmoney as $key => $value) {
            if ($value['youhuiId'] == '0') {
                $zonge = $zonge - $Money['kMoney'];
            } else {
                $zonge = $zonge - $value['kMoney'];
            }
        }
        $Money['zonge'] = $zonge;
        return $Money;
    }
}