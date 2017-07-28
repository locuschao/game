<?php
namespace Home\Controller;

/**
 * 订单控制器
 */
class OrdersController extends BaseController
{

    /**
     * 获取待付款的订单列表
     */
    public function queryByPage()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        session('WST_USER.loginTarget', 'User');
        // 判断会员等级
        $morders = D('Home/UserRanks');
        session('WST_USER.userRank', $morders->checkUserRank($USER['userScore']));
        // 获取订单列表
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $orderList = $morders->queryByPage($obj);
        $statusList = $morders->getUserOrderStatusCount($obj);
        $this->assign("umark", "queryByPage");
        $this->assign("orderList", $orderList);
        $this->assign("statusList", $statusList);
        $this->display("Users/orders/list");
    }

    /**
     * 获取待付款的订单列表
     */
    public function queryPayByPage()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        self::WSTAssigns();
        $obj["userId"] = (int) $USER['userId'];
        $payOrders = $morders->queryPayByPage($obj);
        $this->assign("umark", "queryPayByPage");
        $this->assign("payOrders", $payOrders);
        $this->display("Users/orders/list_pay");
    }

    /**
     * 获取待发货的订单列表
     */
    public function queryDeliveryByPage()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        self::WSTAssigns();
        $obj["userId"] = (int) $USER['userId'];
        $deliveryOrders = $morders->queryDeliveryByPage($obj);
        // 如果订单商品为积分商品，则订单价格转为int型
        foreach ($deliveryOrders['root'] as $k => $v) {
            if ($v['shopId'] == 0 && $v['payType'] == 4) {
                $deliveryOrders['root'][$k]['totalMoney'] = (int) $v['totalMoney'];
            }
        }
        $this->assign("umark", "queryDeliveryByPage");
        $this->assign("receiveOrders", $deliveryOrders);
        $this->display("Users/orders/list_delivery");
    }

    /**
     * 获取退款订单列表
     */
    public function queryRefundByPage()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        self::WSTAssigns();
        $obj["userId"] = (int) $USER['userId'];
        $refundOrders = $morders->queryRefundByPage($obj);
        $this->assign("umark", "queryRefundByPage");
        $this->assign("receiveOrders", $refundOrders);
        $this->display("Users/orders/list_refund");
    }

    /**
     * 获取收货的订单列表
     */
    public function queryReceiveByPage()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        self::WSTAssigns();
        $obj["userId"] = (int) $USER['userId'];
        $receiveOrders = $morders->queryReceiveByPage($obj);
        // 如果订单商品为积分商品，则订单价格转为int型
        foreach ($receiveOrders['root'] as $k => $v) {
            if ($v['shopId'] == 0 && $v['payType'] == 4) {
                $receiveOrders['root'][$k]['totalMoney'] = (int) $v['totalMoney'];
            }
        }
        $this->assign("umark", "queryReceiveByPage");
        $this->assign("receiveOrders", $receiveOrders);
        $this->display("Users/orders/list_receive");
    }

    /**
     * 获取已取消订单
     */
    public function queryCancelOrders()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        self::WSTAssigns();
        $obj["userId"] = (int) $USER['userId'];
        $receiveOrders = $morders->queryCancelOrders($obj);
        // 如果订单商品为积分商品，则订单价格转为int型
        foreach ($receiveOrders['root'] as $k => $v) {
            if ($v['shopId'] == 0 && $v['payType'] == 4) {
                $receiveOrders['root'][$k]['totalMoney'] = (int) $v['totalMoney'];
            }
        }
        $this->assign("umark", "queryCancelOrders");
        $this->assign("receiveOrders", $receiveOrders);
        $this->display("Users/orders/list_cancel");
    }

    /**
     * 获取待评价订单
     */
    public function queryAppraiseByPage()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        self::WSTAssigns();
        $obj["userId"] = (int) $USER['userId'];
        $appraiseOrders = $morders->queryAppraiseByPage($obj);
        // 如果订单商品为积分商品，则订单价格转为int型
        foreach ($appraiseOrders['root'] as $k => $v) {
            if ($v['shopId'] == 0 && $v['payType'] == 4) {
                $appraiseOrders['root'][$k]['totalMoney'] = (int) $v['totalMoney'];
            }
        }
        $this->assign("umark", "queryAppraiseByPage");
        $this->assign("appraiseOrders", $appraiseOrders);
        $this->display("Users/orders/list_appraise");
    }

    /**
     * 获取待拍卖订单 ajax->queryUserAuctionOrders
     */
    public function queryAuctionOrderByPage()
    {
        $this->isUserLogin();
        $this->assign("umark", "queryAuctionOrderByPage");
        $this->display("Users/orders/list_auction_order");
    }

    /**
     * 获取用户参与的拍卖记录 0429
     */
    public function queryAuctionJoinRecordByPage()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $pcurr = (int) I("pcurr", 1);
        self::WSTAssigns();
        $userId = (int) $USER['userId'];
        // 取得用户参加拍卖的记录
        $m = M('GoodsAuctionAddprice');
        $record['root'] = $m->join('INNER  JOIN oto_goods on oto_goods_auction_addprice.goodsId=oto_goods.goodsId')
            ->join('INNER  JOIN oto_goods_auction on oto_goods_auction_addprice.goodsId=oto_goods_auction.goodsId')
            ->join('INNER  JOIN oto_shops on oto_goods_auction_addprice.shopId=oto_shops.shopId')
            ->where('oto_goods_auction_addprice.userId=' . $userId)
            ->order('oto_goods_auction.isDeal asc,oto_goods_auction_addprice.joinPrice asc')
            ->getField('oto_goods_auction_addprice.goodsId,oto_goods_auction_addprice.joinPrice,oto_goods_auction_addprice.actCreateTime,oto_goods_auction_addprice.isWin,oto_goods.goodsName,oto_goods.goodsThums,oto_goods_auction.auctionEndTime,oto_goods_auction.isDeal,oto_goods_auction.auctionStatus,oto_goods_auction.auctionStartTime,oto_goods_auction.auctionMarginMoney,oto_shops.shopName,oto_shops.shopId', true);
        // 数据的分页
        $record['totalPage'] = ceil(count($record['root']) / 15);
        $record['root'] = array_slice($record['root'], (15 * ($pcurr - 1)), 15);
        // 更新开奖，更新状态
        $morders = D('Home/Orders');
        $record = $morders->checkWin($record, $userId);
        
        $this->assign("umark", "queryAuctionJoinRecordByPage");
        $this->assign("record", $record);
        $this->display("Users/orders/list_auction_join_record");
    }

    /**
     * 订单詳情-买家专用
     */
    public function getOrderInfo()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->getOrderDetails($obj);
        $data["orderInfo"] = $rs;
        $this->assign("orderInfo", $rs);
        $this->display("orders/order_details");
    }

    /**
     * 取消订单
     */
    public function orderCancel()
    {
        $this->isUserAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->orderCancel($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 用户确认收货订单
     */
    public function orderConfirm()
    {
        $this->isUserAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderId"] = I("orderId");
        $obj["type"] = (int) I("type");
        $rs = $morders->orderConfirm($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 核对拍卖订单信息
     */
    public function checkAuctionOrderInfo()
    {
        $this->isUserLogin();
        $mareas = D('Home/Areas');
        $morders = D('Home/Orders');
        $mgoods = D('Home/Goods');
        $maddress = D('Home/UserAddress');
        $gtotalMoney = 0; // 商品总价（去除配送费）
        $totalMoney = 0; // 商品总价（含配送费）
        $totalCnt = 0;
        $shopcat = session("WST_CART") ? session("WST_CART") : array();
        $catgoods = array();
        
        $shopColleges = array();
        $startTime = 0;
        $endTime = 24;
        // if(empty($shopcat)){
        // $this->assign("fail_msg",'不能提交空商品的订单!');
        // $this->display('order_fail');
        // exit();
        // }
        // $paygoods = array();
        // foreach($shopcat as $key=>$cgoods){
        // $obj = array();
        // $temp = explode('_',$key);
        // //获取团购标志
        // $isGroup = $temp[count($temp)-1];
        // //获取秒杀标志
        // $isSeckill = $temp[count($temp)-2];
        // //遍历分别取出商品id和商品属性id
        // $obj["goodsAttrId"] = array();
        // foreach($temp as $k=>$v) {
        // if ($k == 0) {
        // $obj["goodsId"] = (int)$v;
        // } else {
        // $obj["goodsAttrId"][] = (int)$v;
        // }
        // }
        // $obj["goodsAttrId"] = implode(',',$obj["goodsAttrId"]);
        // if($cgoods["ischk"]==1){
        // $paygoods[] = $obj["goodsId"];
        // $goods = $mgoods->getGoodsForCheck($obj);
        // if($goods["isBook"]==1){
        // $goods["goodsStock"] = $goods["goodsStock"]+$goods["bookQuantity"];
        // }
        // $goods["ischk"] = $cgoods["ischk"];
        // $goods["cnt"] = $cgoods["cnt"];
        // $totalCnt += $cgoods["cnt"];
        // //每件商品总价
        // $totalMoneys = 0;
        // //如果是团购商品
        // if($isGroup){
        // $goodsGroupModel = M('GoodsGroup');
        // $group = $goodsGroupModel->where("goodsId = ".$obj["goodsId"])->find();
        // //加入团购信息
        // $goods = array_merge($goods,$group);
        // //把商品价变为团购价
        // $goods['shopPrice'] = $goods['groupPrice'];
        // $totalMoneys += $goods['shopPrice'];
        // }elseif($isSeckill){
        // $goodsGroupModel = M('GoodsSeckill');
        // $group = $goodsGroupModel->where("goodsId = ".$obj["goodsId"])->find();
        // //加入秒杀信息
        // $goods = array_merge($goods,$group);
        // //把商品价变为秒杀价
        // $goods['shopPrice'] = $goods['seckillPrice'];
        // $totalMoneys += $goods['shopPrice'];
        // }else{
        // foreach ($goods as $k => $shopPrice) {
        // if(is_numeric($k)){
        // $totalMoneys += $shopPrice['shopPrice'];
        // }
        // }
        // }
        
        // if($totalMoneys==0){
        // $totalMoneys = $cgoods['shopPrice'];
        // }
        // $goods['isGroup'] = $isGroup;
        // $goods['isSeckill'] = $isSeckill;
        // //所有商品应付金额
        // $totalMoney += $goods["cnt"]*$totalMoneys;
        // $gtotalMoney += $goods["cnt"]*$goods["shopPrice"];
        // 编写
        $morderGoods = M('orderGoods');
        $goodsId = I('goodsId');
        $orderId = I('orderId');
        
        $goods = $mgoods->getGoodsForCheck(array(
            'goodsId' => $goodsId
        ));
        
        $orderGoods = $morderGoods->where('goodsId=' . $goodsId . ' and orderId=' . $orderId)->find();
        $orderGoods['shopId'] = $mgoods->where('goodsId=' . $goodsId)
            ->limit(1)
            ->getField('shopId');
        
        $goods = array_merge($goods, $orderGoods);
        
        if ($goods['goodsPrice'] >= $goods['deliveryFreeMoney']) {
            $goods['deliveryMoney'] = 0;
        }
        $goods['totalMoney'] = $goods['goodsPrice'] + $goods['deliveryMoney'];
        $this->assign('goods', $goods);
        
        $ommunitysId = $maddress->getShopCommunitysId($goods["shopId"]);
        $shopColleges[$goods["shopId"]] = $ommunitysId;
        if ($startTime < $goods["startTime"]) {
            $startTime = $goods["startTime"];
        }
        if ($endTime > $goods["endTime"]) {
            $endTime = $goods["endTime"];
        }
        
        /*
         * $catgoods[$goods["shopId"]]["shopgoods"][] = $goods;
         * $catgoods[$goods["shopId"]]["deliveryFreeMoney"] = $goods["deliveryFreeMoney"];//店铺免运费最低金额
         * $catgoods[$goods["shopId"]]["deliveryMoney"] = $goods["deliveryMoney"];//店铺配送费
         * $catgoods[$goods["shopId"]]["deliveryStartMoney"] = $goods["deliveryStartMoney"];//店铺配送费
         * $catgoods[$goods["shopId"]]["totalCnt"] = $catgoods[$goods["shopId"]]["totalCnt"]+$cgoods["cnt"];
         * $catgoods[$goods["shopId"]]["totalMoney"] = $catgoods[$goods["shopId"]]["totalMoney"]+($goods["cnt"]*$totalMoneys);
         */
        // }
        // }
        // foreach($catgoods as $key=> $cshop){
        // if($cshop["totalMoney"]<$cshop["deliveryFreeMoney"]){
        // $totalMoney = $totalMoney + $cshop["deliveryMoney"];
        // }
        // }
        // session('WST_PAY_GOODS',$paygoods);
        $USER = session('WST_USER');
        // 获取地址列表
        $areaId2 = $this->getDefaultCity();
        $addressList = $maddress->queryByUserAndCity($USER['userId'], $areaId2);
        $this->assign("addressList", $addressList);
        $this->assign("areaId2", $areaId2);
        // 支付方式
        $pm = D('Home/Payments');
        $payments = $pm->getList();
        $this->assign("payments", $payments);
        
        // 获取当前市的县区
        $m = D('Home/Areas');
        $areaList2 = $m->getDistricts($areaId2);
        $this->assign("areaList2", $areaList2);
        if ($endTime == 0) {
            $endTime = 24;
            $cstartTime = (floor($startTime)) * 4;
            $cendTime = (floor($endTime)) * 4;
        } else {
            $cstartTime = (floor($startTime) + 1) * 4;
            $cendTime = (floor($endTime) + 1) * 4;
        }
        if (floor($startTime) < $startTime) {
            $cstartTime = $cstartTime + 2;
        }
        if (floor($endTime) < $endTime) {
            $cendTime = $cendTime + 2;
        }
        
        // 获取购物车里商户对应的商品id索引
        foreach ($shopColleges as $key => $value) {
            $shopId[] = $key;
        }
        // 把优惠券内容写进$catgoods
        $catgoods = D('Home/Orders')->getYouhui($USER['userId'], $shopId, $catgoods, $paygoods, $totalMoney);
        
        $this->assign("startTime", $cstartTime);
        $this->assign("endTime", $cendTime);
        $this->assign("shopColleges", $shopColleges);
        $this->assign("catgoods", $catgoods);
        $this->assign("gtotalMoney", $gtotalMoney);
        $this->assign("totalMoney", $totalMoney);
        $this->display('auction_check_order');
    }

    /**
     * 核对订单信息0509
     */
    public function checkOrderInfo()
    {
        $this->isUserLogin();
        D('Youhui')->checkeffective();
        $mareas = D('Home/Areas');
        $morders = D('Home/Orders');
        $mgoods = D('Home/Goods');
        $maddress = D('Home/UserAddress');
        $gtotalMoney = 0; // 商品总价（去除配送费）
        $totalMoney = 0; // 商品总价（含配送费）
        $totalCnt = 0;
        $shopcat = session("WST_CART") ? session("WST_CART") : array();
        $catgoods = array();
        $shopColleges = array();
        $startTime = 0;
        $endTime = 24;
        if (empty($shopcat)) {
            $this->assign("fail_msg", '不能提交空商品的订单!');
            $this->display('order_fail');
            exit();
        }
        $paygoods = array();
        foreach ($shopcat as $key => $cgoods) {
            $obj = array();
            $temp = explode('_', $key);
            // 获取团购标志
            $isGroup = $temp[count($temp) - 1];
            // 获取秒杀标志
            $isSeckill = $temp[count($temp) - 2];
            // 遍历分别取出商品id和商品属性id
            $obj["goodsAttrId"] = array();
            $obj["isSeckill"] = $isSeckill;
            foreach ($temp as $k => $v) {
                if ($k == 0) {
                    $obj["goodsId"] = (int) $v;
                } else {
                    $obj["goodsAttrId"][] = (int) $v;
                }
            }
            $obj["goodsAttrId"] = implode(',', $obj["goodsAttrId"]);
            if ($cgoods["ischk"] == 1) {
                $paygoods[] = $obj["goodsId"];
                $goods = $mgoods->getGoodsForCheck($obj);
                if ($goods["isBook"] == 1) {
                    $goods["goodsStock"] = $goods["goodsStock"] + $goods["bookQuantity"];
                }
                $goods["ischk"] = $cgoods["ischk"];
                $goods["cnt"] = $cgoods["cnt"];
                $totalCnt += $cgoods["cnt"];
                // 每件商品总价
                $totalMoneys = 0;
                // 如果是团购商品
                if ($isGroup) {
                    $goodsGroupModel = M('GoodsGroup');
                    $group = $goodsGroupModel->where("goodsId = " . $obj["goodsId"] . " and goodsGroupStatus = 1 and groupStatus = 1")->find();
                    // 加入团购信息
                    $goods = array_merge($goods, $group);
                    // 把商品价变为团购价
                    $goods['shopPrice'] = $goods['groupPrice'];
                    $totalMoneys += $goods['shopPrice'];
                } elseif ($isSeckill) {
                    $goodsGroupModel = M('GoodsSeckill');
                    $group = $goodsGroupModel->where("goodsId = " . $obj["goodsId"])->find();
                    // 加入秒杀信息
                    $goods = array_merge($goods, $group);
                    // 把商品价变为秒杀价
                    $price = 0;
                    foreach ($goods as $k => $shopPrice) {
                        if (is_numeric($k)) {
                            $price += $shopPrice['shopPrice'];
                        }
                    }
                    if ($price != 0) {
                        $goods['shopPrice'] = $price;
                    } else {
                        $goods['shopPrice'] = $goods['seckillPrice'];
                    }
                    $totalMoneys += $goods['shopPrice'];
                } else {
                    foreach ($goods as $k => $shopPrice) {
                        if (is_numeric($k)) {
                            $totalMoneys += $shopPrice['shopPrice'];
                        }
                    }
                }
                
                if ($totalMoneys == 0) {
                    $totalMoneys = $cgoods['shopPrice'];
                }
                $goods['isGroup'] = $isGroup;
                $goods['isSeckill'] = $isSeckill;
                // 所有商品应付金额
                $totalMoney += $goods["cnt"] * $totalMoneys;
                $gtotalMoney += $goods["cnt"] * $goods["shopPrice"];
                $ommunitysId = $maddress->getShopCommunitysId($goods["shopId"]);
                $shopColleges[$goods["shopId"]] = $ommunitysId;
                if ($startTime < $goods["startTime"]) {
                    $startTime = $goods["startTime"];
                }
                if ($endTime > $goods["endTime"]) {
                    $endTime = $goods["endTime"];
                }
                
                $catgoods[$goods["shopId"]]["shopgoods"][] = $goods;
                $catgoods[$goods["shopId"]]["deliveryFreeMoney"] = $goods["deliveryFreeMoney"]; // 店铺免运费最低金额
                $catgoods[$goods["shopId"]]["deliveryMoney"] = $goods["deliveryMoney"]; // 店铺配送费
                $catgoods[$goods["shopId"]]["deliveryStartMoney"] = $goods["deliveryStartMoney"]; // 店铺配送费
                $catgoods[$goods["shopId"]]["totalCnt"] = $catgoods[$goods["shopId"]]["totalCnt"] + $cgoods["cnt"];
                $catgoods[$goods["shopId"]]["totalMoney"] = $catgoods[$goods["shopId"]]["totalMoney"] + ($goods["cnt"] * $totalMoneys);
            }
        }
        foreach ($catgoods as $key => $cshop) {
            if ($cshop["totalMoney"] < $cshop["deliveryFreeMoney"]) {
                $totalMoney = $totalMoney + $cshop["deliveryMoney"];
            }
        }
        session('WST_PAY_GOODS', $paygoods);
        $USER = session('WST_USER');
        // 获取地址列表
        $areaId2 = $this->getDefaultCity();
        $addressList = $maddress->queryByUserAndCity($USER['userId'], $areaId2);
        $this->assign("addressList", $addressList);
        $this->assign("areaId2", $areaId2);
        // 支付方式
        $pm = D('Home/Payments');
        $payments = $pm->getList();
        $this->assign("payments", $payments);
        // 用户余额
        $um = M('Users');
        $userMoney = $um->where('userId =' . $USER['userId'])->getField('userMoney');
        $this->assign('userMoney', $userMoney);
        // 获取当前市的县区
        $m = D('Home/Areas');
        $areaList2 = $m->getDistricts($areaId2);
        $this->assign("areaList2", $areaList2);
        if ($endTime == 0) {
            $endTime = 24;
            $cstartTime = (floor($startTime)) * 4;
            $cendTime = (floor($endTime)) * 4;
        } else {
            $cstartTime = (floor($startTime) + 1) * 4;
            $cendTime = (floor($endTime) + 1) * 4;
        }
        if (floor($startTime) < $startTime) {
            $cstartTime = $cstartTime + 2;
        }
        if (floor($endTime) < $endTime) {
            $cendTime = $cendTime + 2;
        }
        
        // 获取购物车里商户对应的商品id索引
        foreach ($shopColleges as $key => $value) {
            $shopId[] = $key;
        }
        // 把优惠券内容写进$catgoods
        $catgoods = D('Home/Orders')->getYouhui($USER['userId'], $shopId, $catgoods, $paygoods, $totalMoney);
        
        $this->assign("startTime", $cstartTime);
        $this->assign("endTime", $cendTime);
        $this->assign("shopColleges", $shopColleges);
        $this->assign("catgoods", $catgoods);
        $this->assign("gtotalMoney", $gtotalMoney);
        $this->assign("totalMoney", $totalMoney);
        $this->display('check_order');
    }

    /**
     * 处理订单返回的优惠券信息并输出到订单页面
     */
    public function runYouhui()
    {
        $this->isUserLogin();
        // 刷新页面清除session
        $refresh = I('refresh');
        if ($refresh == 'refresh') {
            unset($_SESSION['oto_mall']['catmoney']);
        }
        $USER = session('WST_USER');
        $userId = (int) $USER['userId'];
        $youhuiId = (int) I('val');
        $shop_good_id = session('shop_good_id');
        $catmoney = session('catmoney');
        $shopId = (int) I('shopId');
        $youhuiMsg = D('Home/Orders')->runYouhui($userId, $youhuiId, $shop_good_id, $shopId, $catmoney);
        if ($youhuiId == '0') {
            unset($_SESSION['oto_mall']['catmoney'][$shopId]);
            $youhuiMsg['status'] = 3;
        }
        $this->ajaxReturn($youhuiMsg);
    }

    /**
     * 提交拍卖订单信息
     */
    public function submitAuctionOrder()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $goodsmodel = D('Home/Goods');
        $morders = D('Home/Orders');
        $totalMoney = 0;
        $totalCnt = 0;
        $userId = (int) $USER['userId'];
        $orderId = (int) I('orderId');
        $consigneeId = (int) I("consigneeId");
        $payway = (int) I("payway");
        $isself = (int) I("isself");
        $needreceipt = (int) I("needreceipt");
        $orderunique = I("orderunique");
        $userAddressModel = D('Home/UserAddress');
        $addressInfo = $userAddressModel->getAddressDetails($consigneeId);
        
        // if($isself==1){//自提
        // $deliverMoney = 0;
        // }else{
        // $deliverMoney = ($shopgoods["totalMoney"]<$shopgoods["deliveryFreeMoney"])?$shopgoods["deliveryMoney"]:0;
        // }
        // $data["deliverMoney"] = $deliverMoney;
        $data["payType"] = $payway;
        // $data["deliverType"] = $deliverType;
        $data["userName"] = $addressInfo["userName"];
        $data["areaId1"] = $addressInfo["areaId1"];
        $data["areaId2"] = $addressInfo["areaId2"];
        $data["areaId3"] = $addressInfo["areaId3"];
        $data["communityId"] = $addressInfo["communityId"];
        $data["userAddress"] = $addressInfo["paddress"] . " " . $addressInfo["address"];
        $data["userTel"] = $addressInfo["userTel"];
        $data["userPhone"] = $addressInfo["userPhone"];
        // $data['orderScore'] = round($data["totalMoney"]+$data["deliverMoney"],0);
        $data["isInvoice"] = $needreceipt;
        // $data["orderRemarks"] = $remarks;
        $data["requireTime"] = I("requireTime");
        $data["invoiceClient"] = I("invoiceClient");
        $data["isAppraises"] = 0;
        $data["isSelf"] = $isself;
        // $data["needPay"] = $shopgoods["totalMoney"]+$deliverMoney;
        // $data["createTime"] = date("Y-m-d H:i:s");
        $data["orderType"] = 5;
        $data["needPay"] = I('needPay');
        $data["deliverMoney"] = I('deliverMoney');
        $data["totalMoney"] = I('totalMoney');
        
        if ($payway == 1) {
            $data["orderStatus"] = - 2;
        } else {
            $data["orderStatus"] = 0;
        }
        
        $data["orderunique"] = $orderunique;
        $data["isPay"] = 0;
        
        // 修改拍卖订单信息
        $orderIds = $morders->where('orderId=' . $orderId)
            ->data($data)
            ->save();
        $orderNo = $morders->where('orderId=' . $orderId)
            ->limit(1)
            ->getField('orderNo');
        $this->assign('orderInfos', array(
            0 => array(
                'orderNo' => $orderNo
            )
        ));
        $this->assign('totalMoney', I('needPay'));
        $this->display('Orders/order_success');
    }

    /**
     * 提交订单信息0509
     */
    public function submitOrder()
    {
        $this->changeGroupStatus();
        $this->isUserLogin();
        $USER = session('WST_USER');
        $goodsmodel = D('Home/Goods');
        $morders = D('Home/Orders');
        $totalMoney = 0;
        $totalCnt = 0;
        $userId = (int) $USER['userId'];
        
        $consigneeId = (int) I("consigneeId");
        $payway = (int) I("payway");
        $isself = (int) I("isself");
        $needreceipt = (int) I("needreceipt");
        $orderunique = I("orderunique");
        $shopcat = session("WST_CART") ? session("WST_CART") : array();
        
        $catgoods = array();
        $order = array();
        if (empty($order)) {
            if (empty($shopcat)) {
                $this->display('order_success');
            } else {
                // 整理及核对购物车数据
                $paygoods = session('WST_PAY_GOODS');
                foreach ($shopcat as $key => $cgoods) {
                    // 分开商品id和属性id
                    $temp = explode('_', $key);
                    // 获取团购标志
                    $isGroup = $temp[count($temp) - 1];
                    // 去除末尾团购标志
                    
                    // 获取秒杀标志
                    $isSeckill = $temp[count($temp) - 2];
                    // 去除末尾秒杀标志
                    array_pop($temp);
                    array_pop($temp);
                    $goodsId = (int) $temp[0];
                    $goodsAttrId = array();
                    foreach ($temp as $k => $v) {
                        if ($k != 0) {
                            $goodsAttrId[] = (int) $v;
                        }
                    }
                    if (in_array($goodsId, $paygoods)) {
                        $goods = $goodsmodel->getGoodsSimpInfo($goodsId, $goodsAttrId, $isGroup, $isSeckill);
                        // 核对商品是否符合购买要求
                        
                        if (empty($goods)) {
                            $this->assign("fail_msg", '对不起，该商品不存在!');
                            $this->display('order_fail');
                            exit();
                        }
                        if ($goods['goodsStock'] <= 0) {
                            $this->assign("fail_msg", '对不起，商品' . $goods['goodsName'] . '库存不足!');
                            $this->display('order_fail');
                            exit();
                        }
                        if ($goods['isSale'] != 1) {
                            $this->assign("fail_msg", '对不起，商品库' . $goods['goodsName'] . '已下架!');
                            $this->display('order_fail');
                            exit();
                        }
                        if ($goods['endTime'] < time() && $isGroup == 1) {
                            $this->assign("fail_msg", '对不起，团购活动：' . $goods['goodsName'] . '已结束!');
                            $this->display('order_fail');
                            exit();
                        }
                        if ($goods['seckillEndTime'] < time() && $isSeckill == 1) {
                            $this->assign("fail_msg", '对不起，秒杀商品' . $goods['goodsName'] . '已结束!');
                            $this->display('order_fail');
                            exit();
                        }
                        $goods["cnt"] = $cgoods["cnt"];
                        $totalCnt += $cgoods["cnt"];
                        // 商品价格由多个属性shopPrice相加而成
                        $prices = 0;
                        foreach ($goods['attrs'] as $kk => $price) {
                            $prices += $price['shopPrice'];
                        }
                        // 如果是团购商品则直接显示团购价,如果是秒杀商品直接显示秒杀价
                        if ($prices != 0 && $isGroup == 0 && $isSeckill == 0) {
                            $shopPrice = $prices;
                        } else {
                            $shopPrice = $cgoods['shopPrice'];
                        }
                        
                        // 团购商品标志
                        $goods['isGroup'] = $isGroup;
                        // 团购商品标志
                        $goods['isSeckill'] = $isSeckill;
                        // 所有店铺商品总价
                        if ($isGroup == 0 && $isSeckill == 0) {
                            $totalMoney += $goods["cnt"] * $shopPrice;
                            $catgoods[$goods["shopId"]]["shopgoods"][] = $goods;
                            $catgoods[$goods["shopId"]]["deliveryFreeMoney"] = $goods["deliveryFreeMoney"]; // 店铺免运费最低金额
                            $catgoods[$goods["shopId"]]["deliveryMoney"] = $goods["deliveryMoney"]; // 店铺免运费最低金额
                            $catgoods[$goods["shopId"]]["totalCnt"] = $catgoods[$goods["shopId"]]["totalCnt"] + $cgoods["cnt"];
                            $catgoods[$goods["shopId"]]["totalMoney"] = $catgoods[$goods["shopId"]]["totalMoney"] + ($goods["cnt"] * $shopPrice);
                        } else {
                            $totalMoney += $goods["cnt"] * $shopPrice;
                            $catgoods[$goods["shopId"] . "_" . $cgoods['goodsId'] . '_' . $isGroup]["shopgoods"][] = $goods;
                            $catgoods[$goods["shopId"] . "_" . $cgoods['goodsId'] . '_' . $isGroup]["deliveryFreeMoney"] = $goods["deliveryFreeMoney"]; // 店铺免运费最低金额
                            $catgoods[$goods["shopId"] . "_" . $cgoods['goodsId'] . '_' . $isGroup]["deliveryMoney"] = $goods["deliveryMoney"]; // 店铺免运费最低金额
                            $catgoods[$goods["shopId"] . "_" . $cgoods['goodsId'] . '_' . $isGroup]["totalCnt"] = $catgoods[$goods["shopId"] . "_" . $cgoods['goodsId'] . '_' . $isGroup]["totalCnt"] + $cgoods["cnt"];
                            $catgoods[$goods["shopId"] . "_" . $cgoods['goodsId'] . '_' . $isGroup]["totalMoney"] = $catgoods[$goods["shopId"] . "_" . $cgoods['goodsId'] . '_' . $isGroup]["totalMoney"] + ($goods["cnt"] * $shopPrice);
                        }
                    }
                }
                
                foreach ($catgoods as $key => $cshop) {
                    if ($cshop["totalMoney"] < $cshop["deliveryFreeMoney"]) {
                        if ($isself == 0) {
                            $totalMoney = $totalMoney + $cshop["deliveryMoney"];
                        }
                    }
                }
                // 获取优惠券内容
                $catmoney = session('catmoney');
                $ordersInfo = $morders->addOrders($userId, $consigneeId, $payway, $needreceipt, $catgoods, $orderunique, $isself, $catmoney);
                $newcart = array();
                foreach ($shopcat as $key => $cgoods) {
                    if (! in_array($key, $paygoods)) {
                        $newcart[$key] = $cgoods;
                    }
                }
                // 修改优惠后的价格显示
                foreach ($catmoney as $key => $value) {
                    $totalMoney = $totalMoney - $value['kMoney'];
                }
                session("WST_CART", empty($newcart) ? null : $newcart);
                $orderNos = $ordersInfo["orderNos"];
                $this->assign("torderIds", implode(",", $ordersInfo["orderIds"]));
                $this->assign("orderInfos", $ordersInfo["orderInfos"]);
                $this->assign("isMoreOrder", (count($ordersInfo["orderInfos"]) > 0) ? 1 : 0);
                $this->assign("orderNos", implode(",", $orderNos));
                $this->assign("totalMoney", $totalMoney);
                if ($payway == 0) {
                    $this->display('Orders/order_success');
                } else {
                    $orderIds = $ordersInfo["orderIds"];
                    $this->redirect("Payments/toPay", array(
                        "orderIds" => implode(",", $orderIds),
                        "payway" => $payway
                    )); // 直接跳转，不带计时后跳转
                }
            }
        } else {
            $this->display('default/check_order');
        }
    }

    /**
     * 检查是否已支付
     */
    public function checkOrderPay()
    {
        $morders = D('Home/Orders');
        $USER = session('WST_USER');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderIds"] = I("orderIds");
        $rs = $morders->checkOrderPay($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 订单詳情
     */
    public function getOrderDetails()
    {   
        /**
         * @author peng	
         * @date 2017-01-04
         * @descreption 是平台商品的情况
         */
        $isPtLogin=D('Common/Util')->checkIsPingTai();
        if(!$isPtLogin)
        $this->isUserLogin();
        
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        
        if($isPtLogin) $obj["shopId"]=0;
        else $obj["shopId"] = (int) $USER['shopId'];
        
        $obj["orderId"] = I("orderId");
        $rs = $morders->getOrderDetails($obj);
        $data["orderInfo"] = $rs;
        $this->assign("orderInfo", $rs);
        $this->display("users/orders/details");
    }
    /*
  * 二次开发
  * 编写者 魏永就
  * 卖家取消订单
  */
    public function mjOrderCancel()
    {
        $orderId = I('post.orderId');
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId
        ))->find();
        $isChangeStatus = $orderInfo['orderStatus'];
        if ($isChangeStatus != 1) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '订单状态已经改变'
            )
            );
            return;
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
            $A = M('users')->where(array(
                'userId' => $orderInfo['userId']
            ))->setInc('userMoney', $orderInfo['needPay']);
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
            //添加平台流水记录
            $platRes = M('platfrom_account')->add(array(
                'orderId'   =>  $orderId,
                'time'      =>  time(),
                'income'    =>  -$orderInfo['needPay'],
                'remark'    =>  '商家取消订单，平台退款成功',
                'orderNo'   =>  $orderInfo['orderNo']
            ));
            
            //更新暂时的金额
            $dataTemRes = M('data_tmp')->where('id=1')->setDec('value',$orderInfo['needPay']);
            
            if ($A && $B && $C && $platRes && $dataTemRes && $messRes) {
                M()->commit();
                M('orders')->where('orderId='.$orderId)->setField('isRead',0);
                /**
                 * @author peng
                 * @date 2017-01
                 * @descreption 
                 */
                D('Game/Voucher')->cancelOrderHook($orderInfo);
                
                //添加消息  peng
                D('ImproveAPI/Msg')->addMsgHook([
                    'moduleId'=>2,
                    'info'=>$orderInfo,
                    'changeType'=>-7
                ]);
                
                $this->ajaxReturn(array(
                    'status' => 0,
                    'mes' => '订单取消成功'
                ));
            } else {
                M()->rollback();
                $this->ajaxReturn(array(
                    'status' => - 1,
                    'mes' => '订单取消失败'
                ));
            }
        } else {
            M()->rollback();
            $this->ajaxReturn(array(
                'status' => -1,
                'mes' => '订单取消失败'
            ));
        }
    }
    // 发货页面
    public function fahuo()
    {
        if(!D('Common/Util')->checkIsPingTai()) $this->isUserLogin();
        $USER = session('WST_USER');
        
        $orderId = I("orderId");
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId
        ))->find();
        
        if ($orderInfo['orderStatus'] > 1 || empty($orderInfo ) || $orderInfo['orderStatus'] == -4) {
            $this->redirect(U('Orders/getOrderDetails', array(
                'orderId' => $orderId
            )), '', 0, '');
            exit();
        }
        
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        
        $this->assign('orderId', $obj["orderId"]);
        $rs = $morders->getOrderDetails($obj);
        
        $data["orderInfo"] = $rs;
        $this->assign("orderInfo", $rs);
        
        /**
         * @author peng
         * @copyright 2016
         * @remark
         */
        if(I('auto')==1)
        $this->display("users/orders/autofahuo");
        else 
        $this->display("users/orders/fahuo");
    }

    public function fahuoHandle(){
        $info = I('info');
        $orderId = I('orderId');
    	$this->_fahuoHandle($orderId,$info);
    }
    
    /**
     * @author peng
     * @copyright 2016
     * @remark 自动发货或者一键发货
     */
     public function autoFahuoHandle(){
       
        //订单号需要前台一键发货传入
        header('Content-Type:text/html;charset=utf-8');
     	$orderId = I('orderId');
        
        $this->isUserLogin();
        
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = $orderId;
        $order_info = $morders->getOrderDetails($obj)['order'];
        
        if($order_info['vName']!='手游狗版本'){
            $this->ajaxReturn(array(
                'status' => - 1,'msg'=>'不是手游狗版本'
            ));
            exit;
        }

		if ($order_info['orderStatus'] !=1 || empty($order_info) ){
            $this->ajaxReturn(array(
                'status' => - 5,
                'msg'=>'订单状态已改变'
            ));
            $this->redirect(U('Orders/getOrderDetails', array(
                'orderId' => $orderId
            )), '', 0, '');
            exit();
        }

        
       //查出代理信息
        $agent_info=M('shop_agent')->where([
            'shop_id'=>$obj["shopId"]
        ])->find();
        if(!$agent_info){
            $this->ajaxReturn(array(
                'status' => - 1,'msg'=>'还没绑定代理'
            ));
        }
       
        $result_info=D('Home/AutoFahuo')->autoHandle([
            
                //业务参数
                //'app_id'=>60020,
                'app_name'=>$order_info['gameName'],
                //'mem_id'=>I('mem_id'),
                'amount'=>$order_info['totalMoney'],
                //'agent_id'=>122,
                'agent_id'=>$agent_info['agent_id'],
                //'agentgame'=>'yaowan001'
                'agentgame'=>$agent_info['agentgame'],
                'username'=>$order_info['account']?:'',
                'order_type'=>$order_info['orderType']
                //'username'=>103368
                
        ]);
        if($result_info['error']==0) {
            if ($result_info['info']['username']){
                $info=join('|',[
                    $order_info['userAddress'],
                    $result_info['info']['username'],
                    $result_info['info']['passwd']
                ]).';';
            }else{
                $info='';
            }
            //添加信息
            D('ImproveAPI')->add([
                'moduleId'=>2,
                'info'=>$order_info,
                'changeType'=>2
            ]);
            
            $this->_fahuoHandle($orderId,$info,false);
        }else{
            //首充失败
            $this->ajaxReturn(array(
                'status' => - 1,'msg'=>$result_info['msg']
            ));
            exit;
        }
        
     }
     
    
    
    /**
     * @author peng
     * @copyright 2016
     * @remark  从fahuoHandle分离出来
     */

    private function _fahuoHandle($orderId,$info,$is_fahuoByHand=true)
    {
        //$is_fahuoByHand 标示是否手动发货
        $info = trim($info, ';');
        $data = array();
        $A = true;
        $B = true;
        M()->startTrans();
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId
        ))->find();
        if ($is_fahuoByHand && ( $orderInfo['orderStatus'] !=1 || empty($orderInfo) )){
            $this->ajaxReturn(array(
                'status' => - 5,
                'msg'=>'订单状态已改变'
            ));
            $this->redirect(U('Orders/getOrderDetails', array(
                'orderId' => $orderId
            )), '', 0, '');
            exit();
        }
        
        if ($info) {
            $arr = explode(';', $info);
            
            foreach ($arr as $k => $v) {
                $temp = explode('|', $v);
                
                $data['orderId'] = $orderId;
                $data['account'] = $temp[1];
                $data['password'] = $temp[2];
                $data['area'] = $temp[0];
                $data['ftime'] = date('Y-m-d H:i:s');
                
                /**
                 * @author peng
                 * @date 2017-02
                 * @descreption 账号可以不是数字
                 */
                $tempA = M('fahuo')->add($data);
                if (! $tempA) {
                    $A = false;
                }
                
                //if(is_numeric($data['account'])){
//                    $tempA = M('fahuo')->add($data);
//                    if (! $tempA) {
//                        $A = false;
//                    }
//                }else{
//                    $this->ajaxReturn(array(
//                        'status' => - 1,
//                        'msg'=>'账号必须是数字'
//                    ));
//                    exit;
//                }
            }
        }
       
        // 更改订单状态
        $update_data = array(
            'orderStatus'=>2,
            'fahuoTime'=>date('Y-m-d H:i:s'),
            'isRead'=>0,                 //魏永就    发货动作，订单消息设置为未读
            'lastMessTime'=>time(),
            /**
            * @author peng
            * @date 2017-02
            * @descreption 发货的类型
            */
            'fahuoType'=>$is_fahuoByHand?0:1,//0是手动 1 是一键发货
        );
        $B = M('orders')->where(array(
            'orderId' => $orderId
        ))->setField($update_data);
        // 添加 订单日志
        $log = array();
        $log['orderId'] = $orderId;
        $log['logContent'] = '订单已经发货';
        $log['logUserId'] = $orderInfo['userId'];
        $log['orderId'] = $orderId;
        $log['logType'] = 0;
        $log['logTime'] = date('Y-m-d H:i:s');
        
        M('log_orders')->add($log);
        
        // 添加消息推送给客户
        
        $mes = array();
        $mes['type'] = 1;
        $mes['orderId'] = $orderId;
        $mes['content'] = $orderId . '订单已经发货';
        $mes['time'] = date('Y-m-d H:i:s');
        $mes['isRead'] = 0;
        $mes['userId'] = $orderInfo['userId'];
        M('mess')->add($mes);
        $C = $this->whiteList($orderInfo, $data['account']);
//        //添加平台金额流水记录
//        $platRes = M('platfrom_account')->add(array(
//           'orderId'    =>  $orderId,
//            'time'      =>  time(),
//            'income'    =>  -$orderInfo['needPay'],
//            'remark'    =>  '商家发货成功',
//            'orderNo'   =>   $orderInfo['orderNo']
//        ));
//        //更新平台暂时的金额
//        $dataRes = M('data_tmp')->where('id=1')->setDec('value',$orderInfo['needPay']);
        if ($A && $B && $C ) {
            M()->commit();
            //添加消息  peng
            D('ImproveAPI/Msg')->addMsgHook([
                'moduleId'=>2,
                'info'=>$orderInfo,
                'changeType'=>2
            ]);
            
            $this->ajaxReturn(array(
                'status' => 0,'msg'=>'发货成功 !'
            ));
        } else {
            M()->rollback();
            $this->ajaxReturn(array(
                'status' => - 1,'msg'=>'发货失败!'
            ));
        }
    }
    
    // 首充号白名单处理
    public function whiteList($orderInfo, $account = 0)
    {
        
        //首充号才可以有白名单
        //if($orderInfo['orderType']!=1||empty($account)||!$account){
//            return 1;
//        }
        if ($orderInfo['orderType']==2) {
            $account=$orderInfo['account'];
        }
        
        if(empty($account)||!$account){
            return 1;
        }
        /**
         * @author peng
         * @date 2017-01
         * @descreption 代充不存在的白名单也可以重新插入到白名单中，只要针对的是通过接口验证的代充的情况
         */
        $order_goods = M('order_goods')->where(array(
                'orderId' => $orderInfo['orderId']
            ))->find();
        
        if($orderInfo['orderType']==2){
            $isWhite = M('white_list')->where(array(
                'account' => $account,
                'shopId' => $orderInfo['shopId'],
                'gid' => $order_goods['gid'],
                'vid' => $order_goods['vid'],
                'userId' => $orderInfo['userId']
            ))->find();
        }else{
            $isWhite = M('white_list')->where(array(
                'account' => $account,
                'shopId' => $orderInfo['shopId'],
                'goodsId' => $orderInfo['goodsId'],
                'userId' => $orderInfo['userId']
            ))->find();
        }
        
        if (! $isWhite) {
            
            $whiteData['account'] = $account;
            $whiteData['gid'] = $order_goods['gid'];
            $whiteData['vid'] = $order_goods['vid'];
            $whiteData['createTime'] = date('Y-m-d H:i:s');
            $whiteData['shopId'] = $orderInfo['shopId'];
            $whiteData['orderId'] = $orderInfo['orderId'];
            $whiteData['goodsId'] = $order_goods['goodsId'];
            $whiteData['isDel'] = 0;
            $whiteData['userId'] = $orderInfo['userId'];
            $whiteData['area'] = $orderInfo['userAddress'];
            $whiteData['mobile'] = $orderInfo['userPhone'];
            $whiteData['qq'] = $orderInfo['qq'];
            $whiteData['profession'] = $orderInfo['profession'];
            $whiteData['roleName'] = $orderInfo['roleName'];
            $rs = M('white_list')->add($whiteData);
            
            if ($rs) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 退款詳情
     */
    public function getRefundDetails()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->getRefundDetails($obj);
        $this->assign("rs", $rs);
        $this->display("users/orders/refund");
    }

    /**
     * 晒单分享
     */
    public function shareList()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->getOrderShare($obj);
        $data["orderInfo"] = $rs;
        $this->assign("orderInfo", $rs);
        $this->display("users/orders/list_share");
    }

    /**
     * **********************************************************************
     */
    /**
     * ******************************商家订单管理****************************
     */
    /**
     * **********************************************************************
     */
    /**
     * 跳转到商家订单列表
     */
    public function toShopOrdersList()
    {   
        
        $this->isShopLogin();
        $morders = D('Home/Orders');
        $this->assign("umark", "toShopOrdersList");
        $this->display("Shops/orders/list");
    }

    /**
     * 跳转到商家拍卖订单列表
     */
    public function toShopAuctionOrdersList()
    {
        $this->isShopLogin();
        $morders = D('Home/Orders');
        $this->assign("umark", "toShopAuctionOrdersList");
        $this->display("Shops/orders/auction_list");
    }

    /**
     * 跳转到商家团购活动
     */
    public function toShopGroupOrders()
    {
        // 修改活动状态
        $this->changeGroupStatus();
        $this->isShopLogin();
        $USER = session('WST_USER');
        // 获取商家商品分类
        $m = D('Home/ShopsCats');
        $this->assign('shopCatsList', $m->queryByList($USER['shopId'], 0));
        $morders = D('Home/Orders');
        $rs = $morders->queryGroupByPage();
        // 分页
        $pager = new \Think\Page($rs['total'], $rs['pageSize']);
        $rs['pager'] = $pager->show();
        $this->assign("umark", "toShopGroupOrders");
        $this->assign("goods", $rs);
        $this->display("Shops/orders/group");
    }

    /**
     * 跳转到商家拍卖活动
     */
    public function toShopAuctionOrders()
    {
        // 修改活动状态
        $this->isShopLogin();
        $USER = session('WST_USER');
        // 获取商家商品分类
        $m = D('Home/ShopsCats');
        $this->assign('shopCatsList', $m->queryByList($USER['shopId'], 0));
        $morders = D('Home/Orders');
        $rs = $morders->queryAuctionByPage();
        // 分页
        $pager = new \Think\Page($rs['total'], $rs['pageSize']);
        $rs['pager'] = $pager->show();
        $this->assign("umark", "toShopAuctionOrders");
        $this->assign("goods", $rs);
        $this->display("Shops/orders/auction");
    }

    /**
     * 跳转到商家秒杀活动
     */
    public function toShopSeckillOrders()
    {
        $this->isShopLogin();
        $USER = session('WST_USER');
        // 获取商家商品分类
        $m = D('Home/ShopsCats');
        $this->assign('shopCatsList', $m->queryByList($USER['shopId'], 0));
        $morders = D('Home/Orders');
        $rs = $morders->querySeckillByPage();
        // 分页
        $pager = new \Think\Page($rs['total'], $rs['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign("umark", "toShopSeckillOrders");
        $this->assign("goods", $rs);
        $this->display("Shops/orders/seckill");
    }

    /**
     * 跳转到商家团购订单列表
     */
    public function toShopGroupOrdersLists()
    {
        $this->isShopLogin();
        $morders = D('Home/Orders');
        $this->assign("umark", "toShopGroupOrders");
        // oto_good_group团购活动id
        $this->assign("id", (int) I("id"));
        $this->display("Shops/orders/grouplist");
    }

    /**
     * 跳转到商家团购订单列表
     */
    public function toShopSeckillOrdersLists()
    {
        $this->isShopLogin();
        $morders = D('Home/Orders');
        $this->assign("umark", "toShopSeckillOrders");
        // goodsId就是秒杀商品活动id
        $this->assign("skId", (int) I("skId"));
        $this->display("Shops/orders/seckilllist");
    }

    /**
     * 获取商家订单列表
     */
    public function queryShopOrders()
    {
        
        $this->isShopAjaxLogin();
        
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
       
        $obj["shopId"] = (int) $USER["shopId"];
        
        $obj["userId"] = (int) $USER['userId'];
        $orders = $morders->queryShopOrders($obj);
        
        $this->ajaxReturn($orders);
    }

    /**
     * 获取商家拍卖订单列表
     */
    public function queryShopAuctionOrders()
    {
        $this->isShopAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["shopId"] = (int) $USER["shopId"];
        $obj["userId"] = (int) $USER['userId'];
        $orders = $morders->queryShopAuctionOrders($obj);
        
        $this->ajaxReturn($orders);
    }

    /**
     * 获取买家拍卖订单列表
     */
    public function queryUserAuctionOrders()
    {
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $orders = $morders->queryUserAuctionOrders($obj);
        $this->ajaxReturn($orders);
    }

    /**
     * 商家受理订单
     */
    public function shopOrderAccept()
    {
        $this->isShopAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->shopOrderAccept($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 商家取消订单
     */
    public function shopOrderCancel()
    {
        $this->isShopAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = (int) I("orderId");
        $rs = $morders->shopOrderCancel($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 商家批量受理订单
     */
    public function batchShopOrderAccept()
    {
        $this->isShopAjaxLogin();
        $morders = D('Home/Orders');
        $rs = $morders->batchShopOrderAccept($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 商家生产订单
     */
    public function shopOrderProduce()
    {
        $this->isShopAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->shopOrderProduce($obj);
        $this->ajaxReturn($rs);
    }

    public function batchShopOrderProduce()
    {
        $this->isShopAjaxLogin();
        $morders = D('Home/Orders');
        $rs = $morders->batchShopOrderProduce($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 商家选择发货配送方式
     */
    public function shopOrderDeliveryType()
    {
        $this->isShopAjaxLogin();
        $this->assign('id', I('orderId'));
        $this->display('Shops/orders/deliveryType');
    }

    /**
     * 商家填写物流配送信息
     */
    public function shopOrderDeliveryInfo()
    {
        $this->isShopLogin();
        $this->assign('id', I('orderId'));
        $this->display('Shops/orders/deliveryInfo');
    }

    /**
     * 商家发货配送订单
     */
    public function shopOrderDelivery()
    {
        $this->isShopAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->shopOrderDelivery($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 商家批量发货配送订单
     */
    public function batchShopOrderDelivery()
    {
        $this->isShopAjaxLogin();
        $morders = D('Home/Orders');
        $rs = $morders->batchShopOrderDelivery($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 商家发查看物流跟踪信息
     */
    public function shopOrderExpress()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->shopOrderExpress($obj);
        $this->assign("rs", $rs);
        $this->display("shops/orders/express");
    }

    /**
     * 商家确认收货订单
     */
    public function shopOrderReceipt()
    {
        $this->isShopAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->shopOrderReceipt($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 商家同意拒收/不同意拒收
     */
    public function shopOrderRefund()
    {
        $this->isShopAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->shopOrderRefund($obj);
        $this->ajaxReturn($rs);
    }

    /**
     * 获取用户订单消息提示
     */
    public function getUserMsgTips()
    {
        $this->isUserAjaxLogin();
        $morders = D('Home/Orders');
        $USER = session('WST_USER');
        $obj["userId"] = (int) $USER['userId'];
        $statusList = $morders->getUserOrderStatusCount($obj);
        $this->ajaxReturn($statusList);
    }

    /**
     * 获取店铺订单消息提示
     */
    public function getShopMsgTips()
    {   
        /**
         * @author peng	
         * @date 2017-01
         * @descreption 如果是平台则不需要判断是否登录
         */
       
        $isPlateformLogin=D('Common/Util')->checkIsPingTai();
        if(!$isPlateformLogin){
            $this->isShopAjaxLogin();
        }
        $morders = D('Home/Orders');
        $USER = session('WST_USER');
        if($isPlateformLogin) $obj["shopId"]=0 ;
        else $obj["shopId"] = (int) $USER['shopId'];
        $obj["userId"] = (int) $USER['userId'];
        $statusList = $morders->getShopOrderStatusCount($obj);
        $this->ajaxReturn($statusList);
    }
}