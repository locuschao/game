<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;

class OrdersController extends BaseController
{

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }
    
    // 普通下单
    public function buy()
    {
        $num = I('num', 1);
        $id = I('goodsId', 0, 'intval');
        $attrid = I('attrid', 0);
        if (! $id) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '非法数据请求'
            ));
        }
        $goodsInfo = M('goods')->where(array(
            'goodsId' => $id,
            'goodsStatus' => 1,
            'goodsFlag' => 1,
            'isSale' => 1
        ))
            ->field('goodsThums,goodsId,goodsName,goodsStock,marketPrice,shopPrice,scopeId,gameId')
            ->find();
        if (! $goodsInfo) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '商品已经下架'
            ));
            exit();
        }
        // 如果有属性读取属性
        if ($attrid) {
            $attrInfo = M('goods_versions')->where(array(
                'id' => $attrid
            ))->find();
            $attrInfo['vName'] = M('versions')->where(array(
                'id' => $attrInfo['versionsId']
            ))->getField('vName');
        }
        $type = '';
        if ($goodsInfo['scopeId'] == 1) {
            $type = '首充号';
        } else 
            if ($goodsInfo['scopeId'] == 2) {
                $type = '首充号代充';
            }
        $game = M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('gameName');
        $arr = array();
        $arr['type'] = $type;
        $arr['gameName'] = $game;
        $arr['goodsInfo'] = $goodsInfo;
        $arr['attrInfo'] = $attrInfo;
        $arr['status'] = 0;
        if ($attrInfo) {
            $arr['totalMoney'] = sprintf('%0.2f', $attrInfo['attrPrice'] * $num);
        } else {
            $arr['totalMoney'] = sprintf('%0.2f', $goodsInfo['shopPrice'] * $num);
        }
        $arr['chongMoney'] = sprintf('%0.2f', $goodsInfo['shopPrice'] * $num);
        $this->returnJson($arr);
    }
    
    // 代充下单页面
    public function daiChongBuy()
    {
        $num = I('num', 1);
        $account = I('account');
        $versions = I('vid', 0, 'intval');
        $goosdId = I('goodsId', 0, 'intval');
        $attrid = I('attrid', 0, 'intval');
        
        if (! $goosdId) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '数据异常'
            ));
            return;
            exit();
        }
        
        $goodsInfo = M('goods as g')->where(array(
            'g.goodsId' => $goosdId,
            'gv.versionsId' => $versions
        ))
            ->join("oto_goods_versions as gv on gv.goodsId=g.goodsId")
            ->field('g.goodsThums,g.goodsId,g.goodsName,g.goodsStock,g.marketPrice,g.shopPrice,g.scopeId,g.gameId')
            ->find();
        if (! $goodsInfo) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '数据异常'
            ));
            return;
            exit();
        }
        
        // 如果有属性读取属性
        if ($attrid) {
            $attrInfo = M('goods_versions')->where(array(
                'id' => $attrid
            ))->find();
            $attrInfo['vName'] = M('versions')->where(array(
                'id' => $attrInfo['versionsId']
            ))->getField('vName');
        }
        
        $daichong = M('white_list as w')->where(array(
            'vid' => $versions,
            'account' => $account
        ))
            ->field('w.area,w.vid,w.account,v.vName')
            ->join('oto_versions as v on v.id=w.vid')
            ->find();
        if (! $daichong) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '数据异常'
            ));
            return;
            exit();
        }
        $type = '';
        if ($goodsInfo['scopeId'] == 1) {
            $type = '首充号';
        } else 
            if ($goodsInfo['scopeId'] == 2) {
                $type = '首充号代充';
            }
        
        $game = M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('gameName');
        $this->goodsInfo = $goodsInfo;
        $arr = array();
        $arr['type'] = $type;
        $arr['gameName'] = $game;
        $arr['goodsInfo'] = $goodsInfo;
        $arr['attrInfo'] = $attrInfo;
        $arr['daichong'] = $daichong;
        $arr['status'] = 0;
        if ($attrInfo) {
            $arr['totalMoney'] = sprintf('%0.2f', $attrInfo['attrPrice'] * $num);
        } else {
            $arr['totalMoney'] = sprintf('%0.2f', $goodsInfo['shopPrice'] * $num);
        }
        $arr['chongMoney'] = sprintf('%0.2f', $goodsInfo['shopPrice'] * $num);
        $this->returnJson($arr);
    }
    
    // 检查订单是否符合支付
    public function checkPayInfo()
    {
        $userId = authCode(I('userId'));
        // 用户信息
        $userInfo = M('users')->where(array(
            'userId' => $userId
        ))
            ->field('userStatus,userFlag,userPhone,userPhoto,payPwd,userMoney')
            ->find();
        $userInfo['payPwd'] = $userInfo['payPwd'] ? 1 : 0;
        if ($userInfo['userFlag'] == 0 || $userInfo['userStatus'] == 0) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '用户状态异常'
            ));
            exit();
        }
        $payid = I('payId', 0, 'intval');
        $orderIds = M('orders_payid')->where(array(
            'pid' => $payid
        ))
            ->field('orderId,type')
            ->select();
        
        //店铺营业时间
        $orderId=M('orders_payid')->where(array('pid'=>$payid))->getField('orderId');
        $shopId=M('orders')->where(array('orderId'=>$orderId))->getField('shopId');
        $shopInfo=M('shops')->where(array('shopId'=>$shopId))->find();
        
        if(!$orderId||!$shopId||!$shopInfo){
            $this->returnJson(array('status'=>-2,'msg'=>'非法数据请求'));exit();
        }
        
        date_default_timezone_set('PRC');
        //判断营业时间
        $currentH=date("G");
        $fen=date('i');
        if($fen>=30&&$fen<60){
            $currentH=$currentH.'.5';
        }
        
        if($currentH>=$shopInfo['serviceStartTime']&&$currentH<=$shopInfo['serviceEndTime']){
            
        }else{
            $starTime=$shopInfo['serviceStartTime'];
            $endTime=$shopInfo['serviceEndTime'];
            $this->returnJson(array('status'=>-1,'msg'=>"店铺营业时间是{$starTime} 至 {$endTime}"));exit();
        }
        // 营业时间结束
        
        
        // 订单
        $ids = '';
        foreach ($orderIds as $K => $v) {
            $ids .= $v['orderId'] . ',';
        }
        $ids = rtrim($ids, ',');
        $orderNos = M('orders')->where(array(
            'orderId' => array(
                'in',
                $ids
            )
        ))
            ->field('orderNo,isPay')
            ->select();
        $Nos = '';
        $ispay = false;
        foreach ($orderNos as $k => $v) {
            $Nos .= $v['orderNo'] . ',';
            if ($v['isPay'] == 1) {
                $ispay = true;
            }
        }
        if ($ispay) {
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '订单状态已经改变'
            ));
            exit();
        }
        $Nos = rtrim($Nos, ',');
        $needPay = M('orders')->where(array(
            'orderId' => array(
                'in',
                $ids
            )
        ))->sum('needPay');
        $title = M('order_goods')->where(array(
            'orderId' => array(
                'in',
                $ids
            )
        ))->getField('goodsName');
        $arr = array();
        $arr['userInfo'] = $userInfo;
        $arr['title'] = $title;
        $arr['needPay'] = $needPay;
        $arr['status'] = 0;
        $arr['orderId'] = $orderIds[0]['orderId'];
        $arr['IP'] = get_client_ip();
        $arr['isBalance'] = $userInfo['userMoney'] >= $needPay ? 1 : 0;
        $this->returnJson($arr);
    }
    
    // 提交订单
    public function submitOrder()
    {
        // 优惠卷
        // 自提还是配送
        $isself = I('isself', 1);
        // 留言
        $remarks = I('remarks');
        // 下单的数据session数据
        $morders = D('Native/Orders');
        $totalMoney = 0;
        $totalCnt = 0;
        $userId = authCode(I('userId'));
        // 收件人
        $consigneeId = I("consigneeId", 0);
        // 支付方式
        $payway = I("payway", 2);
        // 是否需要发票
        $needreceipt = I("needreceipt", 0);
        
        $orderunique = I("orderunique", 0);
        
        $payway = 1; // 默认在线支付，0为货到付款
        $ordersInfo = $morders->addOrders($userId, $consigneeId, $payway, $needreceipt, $orderunique, $isself, $remarks);
        
        if (count($ordersInfo['orderIds']) <= 0) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '订单提交失败'
            ));
        }
        $createTime = date('Y-m-d H:i:s');
        // 生成付款订单号
        $payId = M('orders_payid')->add(array(
            'createTime' => $createTime
        ));
        if ($payId) {
            foreach ($ordersInfo['orderIds'] as $k => $v) {
                M('orders_payid')->add(array(
                    'pid' => $payId,
                    'createTime' => $createTime,
                    'orderId' => $v,
                    'type' => 1
                ));
            }
        }
        $this->returnJson(array(
            'status' => 0,
            'msg' => '订单提交成功',
            'payid' => $payId
        ));
    }
    
    // 提交订单
    public function submitDaichongOrder()
    {
        // 优惠卷
        // 自提还是配送
        $userId = authCode(I('userId'));
        
        $isself = I('isself', 1);
        // 留言
        $remarks = I('remarks');
        // 下单的数据session数据
        $morders = D('Native/Orders');
        $totalMoney = 0;
        $totalCnt = 0;
        // 收件人
        $consigneeId = I("consigneeId", 0);
        // 支付方式
        $payway = I("payway", 2);
        // 是否需要发票
        $needreceipt = I("needreceipt", 0);
        
        $orderunique = I("orderunique", 0);
        
        $payway = 1; // 默认在线支付，0为货到付款
        $ordersInfo = $morders->addDaichongOrders($userId, $consigneeId, $payway, $needreceipt, $orderunique, $isself, $remarks);
        // 结算后清空所选的购物车信息
        $createTime = date('Y-m-d H:i:s');
        
        if (count($ordersInfo['orderIds']) <= 0) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '订单提交失败'
            ));
        }
        
        // 生成付款订单号
        $payId = M('orders_payid')->add(array(
            'createTime' => $createTime
        ));
        if ($payId) {
            foreach ($ordersInfo['orderIds'] as $k => $v) {
                M('orders_payid')->add(array(
                    'pid' => $payId,
                    'createTime' => $createTime,
                    'orderId' => $v,
                    'type' => 1
                ));
            }
        }
        $this->returnJson(array(
            'status' => 0,
            'msg' => '订单提交成功',
            'payid' => $payId
        ));
    }
    
    // 待发货
    public function waitDeliver($page = 0, $limit = 20, $userId = 0)
    {
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = $userId;
        $map['o.orderStatus'] = 1;
        $field = "o.signTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.orderId DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            if ($v['orderType'] == 1) {
                $type = '首充号';
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                } else 
                    if ($v['orderType'] == 3) {
                        $type = '首充号分销';
                    } else 
                        if ($v['orderType'] == 4) {
                            $type = '自主充值';
                        }
            $waitPay[$k]['type'] = $type;
        }
        return $waitPay;
    }
    // 已发货
    public function fahuo($page = 0, $limit = 20, $userId = 0)
    {
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = $userId;
        $map['o.orderStatus'] = 2;
        $field = "o.signTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.orderId DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            if ($v['orderType'] == 1) {
                $type = '首充号';
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                } else 
                    if ($v['orderType'] == 3) {
                        $type = '首充号分销';
                    } else 
                        if ($v['orderType'] == 4) {
                            $type = '自主充值';
                        }
            $waitPay[$k]['type'] = $type;
        }
        return $waitPay;
    }
    // 已完成
    public function orderFinsh($page = 0, $limit = 20, $userId = 0)
    {
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = $userId;
        $map['o.orderStatus'] = array(
            'in',
            '3,-4,-5,-6,-3'
        );
        $field = "o.signTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.orderId DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            if ($v['orderType'] == 1) {
                $type = '首充号';
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                } else 
                    if ($v['orderType'] == 3) {
                        $type = '首充号分销';
                    } else 
                        if ($v['orderType'] == 4) {
                            $type = '自主充值';
                        }
            $waitPay[$k]['type'] = $type;
        }
        return $waitPay;
    }
    // 待收货
    public function waitReceiving($page = 0, $limit = 20, $userId = 0)
    {
        $map['isRefund'] = 0;
        $map['isClosed'] = 0;
        $map['orderFlag'] = 1;
        $map['orderType'] = array(
            'neq',
            4
        );
        $map['o.userId'] = $userId;
        // $map['_string']="isPay=1 or payType=0";
        // 待收货
        $map['orderStatus'] = array(
            'in',
            '2,3'
        );
        $field = "o.signTime,o.createTime,o.orderId,o.orderNo,o.areaId1,o.areaId2,o.paytime,o.areaId3,o.shopId,o.deliverMoney,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg,oe.trackNumber,ex.pinyin,ex.expressCompany";
        $waitReceiving = M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId left join oto_order_express as oe on oe.orderId=o.orderId left join oto_express as ex on ex.id=oe.exId')
            ->where($map)
            ->field($field)
            ->order('o.orderId  DESC')
            ->select();
        
        $goodsDB = M('order_goods');
        foreach ($waitReceiving as $k => $v) {
            $waitReceiving[$k]['ntime'] = $v['createTime'];
            if ($v['trackNumber']) {
                $expressInfo = $this->checkExpress($v['pinyin'], $v['trackNumber']);
                if ($expressInfo['data'][0]) {
                    $waitReceiving[$k]['first'] = $expressInfo['data'][0]['context'];
                    $waitReceiving[$k]['ntime'] = $expressInfo['data'][0]['time'];
                } else {
                    $waitReceiving[$k]['first'] = '等待快递收件';
                }
            } else {
                $waitReceiving[$k]['first'] = '等待快递收件';
            }
        }
        $areaDB = M('areas');
        foreach ($waitReceiving as $k => $v) {
            $waitReceiving[$k]['goods'] = $goodsDB->where(array(
                'orderId' => $v['orderId']
            ))->select();
        }
        $this->waitReceiving = $waitReceiving;
        $this->display();
    }
    // 待付款
    public function waitPay($page = 0, $limit = 20, $userId = 0)
    {
        // 待付款
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.orderStatus'] = - 2;
        $map['o.userId'] = $userId;
        $field = "o.signTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.orderId DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            if ($v['orderType'] == 1) {
                $type = '首充号';
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                } else 
                    if ($v['orderType'] == 3) {
                        $type = '首充号分销';
                    } else 
                        if ($v['orderType'] == 4) {
                            $type = '自主充值';
                        }
            $waitPay[$k]['type'] = $type;
        }
        return $waitPay;
    }
    // 评价中心
    public function evaluate()
    {
        // 签收7天内可评价
        $map['isRefund'] = 0;
        $map['isClosed'] = 0;
        $map['orderFlag'] = 1;
        // $map['_string']="isPay=1 or payType=0";
        $map['orderStatus'] = 4;
        $map['orderType'] = array(
            'neq',
            4
        );
        $map['userId'] = session('oto_userId');
        // $map['_string']="date_sub(curdate(), INTERVAL 7 DAY) <= date(`signTime`)";
        $field = "o.signTime,o.orderId,o.orderNo,o.areaId1,o.areaId2,o.areaId3,o.shopId,o.deliverMoney,o.paytime,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg";
        $evaluate = M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId')
            ->where($map)
            ->field($field)
            ->order('o.orderId DESC')
            ->select();
        $goodsDB = M('order_goods');
        $areaDB = M('areas');
        foreach ($evaluate as $k => $v) {
            $evaluate[$k]['goods'] = $goodsDB->where(array(
                'orderId' => $v['orderId']
            ))->select();
        }
        $this->evaluate = $evaluate;
        $this->display();
    }
    
    // 全部订单
    public function orders($page = 0, $limit = 20, $userId = 0)
    {
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = $userId;
        $map['o.orderStatus'] = array(
            'in',
            "-2,0,1,2,3,4,-5,-6,-3,-4"
        );
        $field = "o.signTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.orderId DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            if ($v['orderType'] == 1) {
                $type = '首充号';
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                } else 
                    if ($v['orderType'] == 3) {
                        $type = '首充号分销';
                    } else 
                        if ($v['orderType'] == 4) {
                            $type = '自主充值';
                        }
            $waitPay[$k]['type'] = $type;
        }
        return $waitPay;
    }
    
    // 订单详情
    public function orderDetail()
    {
        $id = I('orderId');
        $userId = I('userId');
        $userId = authCode($userId);
        if (! $id || ! $userId) {
            $this->returnJson(array());
            return;
        }
        $map['o.isClosed'] = 0;
        // $map['o.orderFlag']=1;
        $map['o.userId'] = $userId;
        $map['o.orderId'] = $id;
        $field = "o.totalMoney,o.kfQQ,o.orderNo,o.userAddress,o.orderStatus,o.orderId,o.shopId,o.needPay,o.fahuoTime,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.orderId DESC')
            ->field($field)
            ->find();
        $type = '首充号';
        if ($waitPay['orderType'] == 1) {
            $type = '首充号';
            $account = M('fahuo')->where(array(
                'orderId' => $waitPay['orderId']
            ))->select();
            $waitPay['fahuo'] = $account;
        } else 
            if ($waitPay['orderType'] == 2) {
                $type = '首充号代充';
            } else 
                if ($waitPay['orderType'] == 3) {
                    $type = '首充号分销';
                    $account = M('fahuo')->where(array(
                        'orderId' => $waitPay['orderId']
                    ))->select();
                    $waitPay['fahuo'] = $account;
                } else 
                    if ($waitPay['orderType'] == 4) {
                        $type = '自主充值';
                        $account = M('fahuo')->where(array(
                            'orderId' => $waitPay['orderId']
                        ))->select();
                        $waitPay['fahuo'] = $account;
                    }
        $waitPay['type'] = $type;
        $fahuoTime = strtotime($waitPay['fahuoTime']);
        $nowTime = time();
        if (($waitPay['orderStatus'] == 3 || $waitPay['orderStatus'] == - 6) && ($nowTime - $fahuoTime) < 604800) {
            // 7天内的订单才可能投诉
            // 检查是否已经投诉过
            $isComplain = M('complain')->where(array(
                'orderId' => $id
            ))->find();
            if (! $isComplain) {
                $waitPay['complain'] = 1;
            }
        }
        $this->returnJson($waitPay);
    }
    
    // 取消订单
    public function cancelOrder()
    {
        $userId = authCode(I('userId'));
        
        if (! $userId) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '请登录后再操作'
            ));
            return;
        }
        $id = I('orderId', '0', 'intval');
        if (! $id) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '非法数据请求'
            ));
        }
        $orderInfo = M('orders')->where(array(
            'orderId' => $id,
            'userId' => $userId
        ))->find();
        if (! $orderInfo) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '非法数据请求'
            ));
        }
        $isChangeStatus = $orderInfo['orderStatus'];
        if ($isChangeStatus != 1) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '订单状态已经改变'
            ));
            return;
        }
        $A = true;
        $B = true;
        $C = true;
        M()->startTrans();
        $res = M('orders')->where(array(
            'orderId' => $id,
            'userId' => $userId
        ))->setField(array(
            'orderStatus' => - 4
        ));
        if ($res) {
            $A = M('users')->where(array(
                'userId' => $userId
            ))->setInc('userMoney', $orderInfo['needPay']);
            // 订单最新日志
            $data = array();
            $data["orderId"] = $id;
            $data["logContent"] = "商家没发货，买家主动取消订单！";
            $data["logUserId"] = $userId;
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            M('log_orders')->add($data);
            // 退款日志表
            $refundLog['orderId'] = $id;
            $refundLog['userId'] = $userId;
            $refundLog['mess'] = '商家没发货，买家主动取消订单';
            $refundLog['time'] = date('Y-m-d H:i:s');
            $B = M('refund_log')->add($refundLog);
            
            $userMoney = M('users')->where(array(
                'userId' => $userId
            ))->getField('userMoney');
            // 全额变动记录
            $moneyRecord['type'] = 2;
            $moneyRecord['money'] = $orderInfo['needPay'];
            $moneyRecord['time'] = time();
            $moneyRecord['ip'] = get_client_ip();
            $moneyRecord['orderNo'] = $orderInfo['orderNo'];
            $moneyRecord['IncDec'] = 1;
            $moneyRecord['userid'] = $userId;
            $moneyRecord['balance'] = $userMoney;
            $moneyRecord['remark'] = '商家没接单，取消订单';
            $moneyRecord['payWay'] = 3;
            $C = M('money_record')->add($moneyRecord);
            if ($A && $B && $C) {
                M()->commit();
                $this->returnJson(array(
                    'status' => 0,
                    'msg' => '订单取消成功'
                ));
            } else {
                M()->rollback();
                $this->returnJson(array(
                    'status' => - 1,
                    'msg' => '订单取消失败'
                ));
            }
        } else {
            M()->rollback();
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '订单取消失败'
            ));
        }
    }
    
    // 退款处理页面
    public function refund()
    {
        $id = I('id');
        if (! $id) {
            $this->redirect(U('Game/Index/index', '', '', 0));
            return;
        }
        $field = "orderId,needPay,deliverMoney";
        $map['payType'] = array(
            'gt',
            0
        );
        $map['isPay'] = 1;
        $map['isRefund'] = 0;
        $map['userId'] = session('oto_userId');
        $orderInfo = M('orders')->where(array(
            'orderId' => $id
        ))
            ->field($field)
            ->find();
        if (! $orderInfo) {
            $this->redirect(U('Game/Index/index', '', '', 0));
            return;
        }
        $this->orderInfo = $orderInfo;
        $this->display();
    }
    
    // 退款列表
    public function refundList()
    {
        $map['isClosed'] = 0;
        $map['orderFlag'] = 1;
        $map['o.userId'] = session('oto_userId');
        $map['orderStatus'] = array(
            'in',
            '-6,-7,-3'
        );
        $field = "o.orderId,o.orderNo,o.areaId1,o.isRefund,o.areaId2,o.areaId3,o.shopId,o.deliverMoney,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg,s.shopTel,r.money as refundMoney";
        $refund = M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId left join oto_refund as r on r.orderId=o.orderId')
            ->where($map)
            ->field($field)
            ->order('o.orderId DESC')
            ->select();
        $goodsDB = M('order_goods');
        $areaDB = M('areas');
        foreach ($refund as $k => $v) {
            $refund[$k]['goods'] = $goodsDB->where(array(
                'orderId' => $v['orderId']
            ))->select();
        }
        $this->refund = $refund;
        $this->display();
    }
    
    // 退款详情
    public function refundDetail()
    {
        $id = I('id');
        if (! $id) {
            $this->redirect(U('Wx/Index/index', '', '', 0));
            return;
        }
        $field = "o.orderId,o.isRefund,s.shopName,r.*";
        $info = M('orders')->where(array(
            'o.orderId' => $id
        ))
            ->field($field)
            ->join('as o join oto_refund as r on o.orderId=r.orderId left join oto_shops as s on s.shopId=o.shopId')
            ->find();
        $this->info = $info;
        $this->display();
    }
    
    // 投诉反馈
    public function applyComplain()
    {
        $userId = authCode(I('userId'));
        $orderId = I('orderId');
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId
        ))->find();
        if (! $userId || ! $orderId || empty($orderInfo)) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'msg' => '数据异常'
            ));
            return;
        }
        $content = I('content');
        $type = I('type');
        $isRefund = M('complain')->where(array(
            'orderId' => $orderId
        ))->find();
        if ($isRefund) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '有投诉正在处理中'
            ));
            return;
        }
        $complain['userId'] = $userId;
        $complain['orderId'] = $orderId;
        $complain['type'] = $type;
        if ($type == 0) {
            $complain['content'] = $content;
        }
        $complain['time'] = date('Y-m-d H:i:s');
        $complain['isHandle'] = 0;
        $rs = M('complain')->add($complain);
        if ($rs) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '投诉提交成功'
            ));
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '投诉提交失败'
            ));
        }
    }
    
    // 获取付款ID
    public function getPayId()
    {
        $userId = authCode(I('userId'));
        $orderId = I('orderId', 0, 'intval');
        
        // 判断订单状态
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId,
            'userId' => $userId
        ))->find();
        if (empty($orderInfo)) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '非法数据请求'
            ));
            return;
        }
        if ($orderInfo['orderStatus'] != - 2 || $orderInfo['isPay'] == 1) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '订单状态已改变'
            ));
            return;
        }
        
        $payId = M('orders_payid')->where(array(
            'type' => 1,
            'orderId' => $orderId
        ))->getField('pid');
        if ($payId) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => 'success',
                'payId' => $payId
            ));
            return;
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '订单状态异常'
            ));
            return;
        }
    }
    
    // 申请退款
    public function applyRefund()
    {
        $userId = authCode(I('userId'));
        $orderId = I('orderId', 0, 'intval');
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId,
            'userId' => $userId
        ))->find();
        if (! $userId || ! $orderId || empty($orderInfo)) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '数据异常'
            ));
            return;
        }
        $content = I('content');
        $type = I('type', 0, 'intval');
        
        $isRefund = M('refund')->where(array(
            'orderid' => $orderId
        ))->find();
        if ($isRefund) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '退款正在处理中'
            ));
            return;
        }
        // 判断订单状态
        if ($orderInfo['orderStatus'] != 2) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '订单状态已经改变'
            ));
            return;
        }
        
        M()->startTrans();
        $complain['userId'] = $orderId;
        $complain['orderid'] = $orderId;
        $complain['type'] = 2;
        $complain['reason'] = $content;
        $complain['explain'] = $content;
        $complain['refundTime'] = date('Y-m-d H:i:s');
        $complain['biz_status'] = 0;
        $complain['pf_status'] = 0;
        $complain['apply_money'] = $orderInfo['needPay'];
        $complain['way'] = 0;
        $complain['shopId'] = $orderInfo['shopId'];
        $rs = M('refund')->add($complain);
        
        $B = M('orders')->where(array(
            'orderId' => $orderId
        ))->setField('orderStatus', - 3);
        
        // 订单最新日志
        $data = array();
        $data["orderId"] = $orderId;
        $data["logContent"] = "买家申请退款!退款原因：" . $content;
        $data["logUserId"] = $orderId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        M('log_orders')->add($data);
        
        // 退款日志表
        $refundLog['orderId'] = $orderId;
        $refundLog['userId'] = $orderId;
        $refundLog['mess'] = '退款原因：' . $content;
        $refundLog['time'] = date('Y-m-d H:i:s');
        $C = M('refund_log')->add($refundLog);
        
        if ($rs && $B && $C) {
            M()->commit();
            $this->returnJson(array(
                'status' => 0,
                'msg' => '退款申请提交成功'
            ));
        } else {
            M()->rollback();
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '退款申请提交失败'
            ));
        }
    }
    
    // 上传退款的三张图片
    public function refundUploadImg()
    {
        // 上传头像
        if (! session('oto_userId')) {
            echo "文件上传失败";
            return;
        }
        import('ORG.Net.UploadFile');
        $upload = new \UploadFile();
        $upload->autoSub = true;
        $upload->subType = 'custom';
        $data = date('Y-m', time());
        if ($upload->upload('./upload/refund/' . $data . '/')) {
            $info = $upload->getUploadFileInfo();
        }
        $file_newname = $info['0']['savename'];
        $MAX_SIZE = 20000000;
        if ($info['0']['type'] != 'image/jpeg' && $info['0']['type'] != 'image/jpg' && $info['0']['type'] != 'image/pjpeg' && $info['0']['type'] != 'image/png' && $info['0']['type'] != 'image/x-png') {
            echo "2";
            exit();
        }
        if ($info['0']['size'] > $MAX_SIZE)
            echo "上传的文件大小超过了规定大小";
        
        if ($info['0']['size'] == 0)
            echo "请选择上传的文件";
        switch ($info['0']['error']) {
            case 0:
                echo 'upload/users/' . $data . '/' . $file_newname;
                break;
            case 1:
                echo "上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值";
                break;
            case 2:
                echo "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
                break;
            case 3:
                echo "文件只有部分被上传";
                break;
            case 4:
                echo "没有文件被上传";
                break;
        }
    }

    public function delOrder()
    {
        $oid = I('orderId');
        $userId = I('userId');
        $userId = authCode($userId);
        if (! $oid || ! $userId) {
            // 非法操作
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '非法数据请求'
            ));
            return;
        }
        $orderInfo = M('orders')->where(array(
            'orderId' => $oid
        ))->find();
        if (! $orderInfo) {
            // 非法操作
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '非法数据请求'
            ));
            return;
        }
        if ($orderInfo['orderFlag'] == 0) {
            // 订单已经删除
            $this->returnJson(array(
                'status' => - 3,
                'msg' => '订单已删除'
            ));
            return;
        }
        $A = M('orders')->where(array(
            'orderId' => $orderInfo['orderId'],
            'userId' => $userId
        ))->setField('orderFlag', 0);
        if ($A) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '订单删除成功'
            ));
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '订单删除失败'
            ));
        }
    }
    
    // 确认收货
    public function confirmOrder()
    {
        if (IS_AJAX) {
            $oid = I('oid');
            if (! $oid) {
                // 非法操作
                $this->ajaxReturn(array(
                    'status' => - 2
                ));
                return;
            }
            $orderInfo = M('orders')->where(array(
                'orderId' => $oid
            ))->find();
            if (! $orderInfo) {
                // 非法操作
                $this->ajaxReturn(array(
                    'status' => - 2
                ));
                return;
            }
            if ($orderInfo['orderStatus'] == 4) {
                // 订单已经是收货
                $this->ajaxReturn(array(
                    'status' => - 3
                ));
                return;
            }
            // 更新商家金额
            M()->startTrans();
            $A = M('shops')->where(array(
                'shopId' => $orderInfo['shopId']
            ))->setInc('bizMoney', $orderInfo['needPay']);
            
            // 商家收入记录
            $data['orderId'] = $oid;
            $data['money'] = $orderInfo['needPay'];
            $data['time'] = date('Y-m-d H:i:s', time());
            $data['shopId'] = $orderInfo['shopId'];
            $B = M('biz_income')->add($data);
            
            $C = $this->scoreRecord(1, $orderInfo['needPay'], $orderInfo['orderNo'], 1, $orderInfo['userId']);
            $D = M('orders')->where(array(
                'orderId' => $orderInfo['orderId'],
                'userId' => $orderInfo['userId']
            ))->setField(array(
                'orderStatus' => 4,
                'signTime' => date("Y-m-d H:i:s"),
                'isConfirm' => 1
            ));
            if ($A && $B && $C && $D) {
                M()->commit();
                $this->ajaxReturn(array(
                    'status' => 0
                ));
            } else {
                M()->rollback();
                $this->ajaxReturn(array(
                    'status' => - 1
                ));
            }
        }
    }
    
    // 创建 去付款，代付，批量付
    public function caretePayInfo()
    {
        $backUrl = $_SERVER['HTTP_REFERER'];
        $ids = I('ids');
        $orderId = explode(',', $ids);
        if (! $orderId) {
            $this->redirect(U($backUrl, '', '', 0));
            return;
        }
        // 判断订单状态
        $ispayInfo = M('orders')->where(array(
            'orderId' => array(
                'in',
                $ids
            )
        ))
            ->field('isPay,orderNo')
            ->select();
        $ispay = true;
        foreach ($ispayInfo as $k => $v) {
            if ($v['isPay'] == 1) {
                $ispay = false;
            }
        }
        // 已经付过
        if (! $ispay) {
            $this->redirect(U('Orders/orders'), '', '', 0);
            return;
        }
        
        $pMap['orderId'] = 0;
        $pMap['pid'] = 0;
        $pMap['createTime'] = date('Y-m-d H:i:s', time());
        M()->startTrans();
        $pDB = M('orders_payid');
        $pid = $pDB->add($pMap);
        $sonNum = 0;
        foreach ($orderId as $k => $v) {
            $sMap['orderId'] = $v;
            $sMap['pid'] = $pid;
            $sMap['createTime'] = date('Y-m-d H:i:s', time());
            $sRes = $pDB->add($sMap);
            if ($sRes) {
                $sonNum ++;
            }
        }
        if (count($orderId) == $sonNum && $pid) {
            M()->commit();
            session('payid', $pid); // 付款ID
            $this->redirect(U('Confirm/onlinkPay', array(
                'payid' => $pid
            ), '', 0));
        } else {
            M()->rollback();
            $this->redirect(U($backUrl, '', '', 0));
        }
    }
    
    // 积分操作记录
    /**
     * 构造函数
     * 
     * @param $type 1购物，2取消订单，3充值，4订单无效，5活动,6评价订单            
     * @param $score 积分            
     * @param $shopid 店铺ID            
     * @param $orderid 订单ID或者充值ID            
     * @param $IncDec 积分变动0为减，1加            
     * @param $userid 用户ID            
     * @param $totalscore 用户剩余总积分            
     */
    public function scoreRecord($type = '', $payMoney = 0, $orderNo = '', $IncDec = '', $userid = 0)
    {
        $score = floor($payMoney);
        if ($score <= 0) {
            return;
        }
        $totalscore = M('users')->where(array(
            'userId' => $userid
        ))->getField('userScore');
        $db = M('score_record');
        $data['score'] = $score;
        $data['type'] = $type;
        $data['time'] = time();
        $data['ip'] = get_client_ip();
        $data['orderNo'] = $orderNo;
        $data['IncDec'] = $IncDec;
        $data['userid'] = $userid;
        $data['totalscore'] = $totalscore;
        $res = $db->add($data);
        return $res;
    }
    
    // 测试快递单号
    public function test()
    {
        $info = $this->checkExpress('tiantian', 666553920947);
        print_r($info);
    }

    public function expressDetail()
    {
        $id = I('id');
        $info = M('orders')->where(array(
            'o.orderId' => $id
        ))
            ->field('o.createTime,oe.trackNumber,ox.pinyin,ox.expressCompany')
            ->join('as o left join oto_order_express as oe on oe.orderId=o.orderId left join oto_express as ox on ox.id=oe.exId')
            ->find();
        $this->info = $info;
        $expressInfo['data'][0]['time'] = $info['createTime'];
        $expressInfo['data'][0]['context'] = "等待发货";
        
        if ($info['orderStatus'] == 1 || $info['orderStatus'] == 2) {
            $expressInfo['data'][0]['context'] = "打包中";
        }
        if ($info['orderStatus'] == 3) {
            $expressInfo['data'][0]['context'] = "等待快递取件";
        }
        // $this->expressInfo=$this->checkExpress( 'tiantian',666553920947);
        $this->expressInfo = $this->checkExpress($info['pinyin'], $info['trackNumber']);
        $this->display();
    }
    
    // 充值生成订单号
    public function touUp()
    {
        $userId = authCode(I('userId'));
        $money = I('money');
        $payType = I('payType');
        if (! $userId) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '请先登录'
            ));
            exit();
        }
        if (empty($money) || empty($payType) || $money <= 0) {
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '数据异常！'
            ));
            exit();
        }
        $db = M('orders_payid');
        $topDB = M('top_up');
        $data['orderId'] = time();
        $data['pid'] = 0;
        $data['type'] = 2;
        $data['createTime'] = date('Y-m-d H:i:s');
        M()->startTrans();
        $A = $db->add($data);
        $tou['userid'] = $userId;
        $tou['topupNo'] = $A;
        $tou['time'] = $data['createTime'];
        $tou['out_trade_no'] = ' ';
        $tou['money'] = $money;
        $tou['status'] = 0;
        $B = $topDB->add($tou);
        if ($A && $B) {
            M()->commit();
            $this->returnJson(array(
                'status' => 0,
                'msg' => '订单提交成功',
                'payId' => $A,
                'money' => $money,
                'IP' => get_client_ip()
            ));
            exit();
        } else {
            M()->rollback();
            $this->returnJson(array(
                'status' => - 3,
                'msg' => '操作超时，请稍候重试'
            ));
            exit();
        }
    }
}