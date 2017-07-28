<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class OrdersController extends BaseController
{

    public function _initialize()
    {
        parent::isLogin();
        @header('Content-type: text/html;charset=UTF-8');
    }

    // 普通下单
    public function buy()
    {   
        $num = I('num', 1);
        $_POST['num'] = $num;
        $v = I('v');
        $account = I('cc');
        $id = I('goodsId', 0);
        $_POST['goodsId'] = $id;
        $attrid = I('attrid', 0);
        $url = $_SERVER['HTTP_REFERER'];
        if (!$id) {
            header("location:$url");
            exit();
        }
        $goodsInfo = M('goods')->where(array(
            'goodsId' => $id,
            'goodsStatus' => 1,
            'goodsFlag' => 1,
            'isSale' => 1
        ))
            ->field('goodsThums,goodsId,goodsName,goodsStock,marketPrice,shopPrice,scopeId,gameId,goodsType')
            ->find();
        if (!$goodsInfo) {
            header("location:$url");
            exit();
        }

        if (!$v) {
            // 如果有属性读取属性
            if ($attrid) {
                $attrInfo = M('goods_versions')->where(array(
                    'id' => $attrid
                ))->find();
                

                $attrInfo['vName'] = M('versions')->where(array(
                    'id' => $attrInfo['versionsId']
                ))->getField('vName');
                
                /**
                 * @author peng
                 * @date 2017-01-07
                 * @descreption 加上会员价格的因素
                 */
                
                $attrInfo['attrPrice']=D('Game/orders')->getNeedPay($attrInfo);
               
                $this->attrInfo = $attrInfo;
            }
        } else {
            $daichong = M('white_list as w')->where(array(
                'vid' => $v,
                'account' => $account
            ))
                ->field('w.area,w.vid,w.account,v.vName')
                ->join('oto_versions as v on v.id=w.vid')
                ->find();
            $this->daichong = $daichong;
        }
        
        /**
         * @author peng
         * @date 2017-01
         * @descreption 执行验证代金券以及输出使用代金券的情况。
         */
        $sum_voucher_price=A('Game/Voucher')->checkVoucher($attrInfo,$goodsInfo,$attrInfo['attrPrice']*$num);
        $this->sum_voucher_price=$sum_voucher_price;
        
    
        $type = '';
        if ($goodsInfo['scopeId'] == 1) {
            $type = '首充号';
            if ($goodsInfo['goodsType'] == 1) {
                $type = '会员首充';
            }
        } else
            if ($goodsInfo['scopeId'] == 2) {
                $type = '首充号代充';
                if ($goodsInfo['goodsType'] == 1) {
                    $type = '会员首代';
                }
            }
        $this->type = $type;
        $this->game = M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('gameName');
        $this->goodsInfo = $goodsInfo;
        $this->post = $_POST;
        $this->display();
    }

    // 代充下单页面
    public function daiChongBuy()
    {
        $id = I('goodsId');
        $arr = session('validateGoods');
        if (!$arr) {
            $this->redirect(U('Game/Index/index', '', '', '', ''));
            exit();
        }
        $account = $arr['account'];
        $vid = $arr['vid'];
        $num = I('num', 1);
        $_POST['num'] = $num;
        $goosdId = I('goodsId', 0);
        $_POST['goodsId'] = $id;

        $attrid = I('attrid', 0);
        $url = $_SERVER['HTTP_REFERER'];
        if (!$goosdId) {
            header("location:$url");
            exit();
        }
        $versions = $arr['vid'];
        $goodsInfo = M('goods as g')->where(array(
            'g.goodsId' => $goosdId,
            'g.gameId' => $arr['gameId'],
            'gv.versionsId' => $versions
        ))
            ->join("oto_goods_versions as gv on gv.goodsId=g.goodsId")
            ->field('g.goodsThums,g.goodsId,g.goodsName,g.goodsStock,g.marketPrice,g.shopPrice,g.scopeId,g.gameId,g.goodsType')
            ->find();
            if(!$goodsInfo){
                header("location:$url");
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
            
            /**
             * @author peng	
             * @date 2017-01-07
             * @descreption 加上会员价格的因素
             */
            
            $attrInfo['attrPrice']=D('Game/Orders')->getNeedPay($attrInfo);
            
            $this->attrInfo = $attrInfo;
        }
        
        /**
         * @author peng
         * @date 2017-01
         * @descreption 执行验证代金券以及输出使用代金券的情况。
         */
        $sum_voucher_price=A('Game/Voucher')->checkVoucher($attrInfo,$goodsInfo,$attrInfo['attrPrice']*$num);   
         
        $this->sum_voucher_price=$sum_voucher_price;


        $daichong = M('white_list as w')->where(array(
            'vid' => $vid,
            'account' => $account
        ))
            ->field('w.area,w.vid,w.account,v.vName')
            ->join('oto_versions as v on v.id=w.vid')
            ->find();
        
        $this->daichong = $daichong?:$arr;
 
        $type = '';
        if ($goodsInfo['scopeId'] == 1) {
            $type = '首充号';
            if ($goodsInfo['goodsType'] == 1) {
                $type = '会员首代';
            }
        } else
            if ($goodsInfo['scopeId'] == 2) {
                $type = '首充号代充';
                if ($goodsInfo['goodsType'] == 1) {
                    $type = '会员首代';
                }
            }
        $this->type = $type;
        $this->game = M('game')->where(array(
            'id' => $goodsInfo['gameId']
        ))->getField('gameName');
        $this->goodsInfo = $goodsInfo;
        $this->post = $_POST;
        $this->display();
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
        $morders = D('Game/Orders');
        $totalMoney = 0;
        $totalCnt = 0;
        $userId = session('oto_userId');
        // 收件人
        $consigneeId = I("consigneeId", 0);
        // 支付方式
        $payway = I("payway", 2);
        // 是否需要发票
        $needreceipt = I("needreceipt", 0);
        
        $orderunique = I("orderunique", 0);
        
        $payway = 1; // 默认在线支付，0为货到付款
        
        if(I('num')<=0){
            $this->error('订单数量不能小于1');
        }
        
        $ordersInfo = $morders->addOrders($userId, $consigneeId, $payway, $needreceipt, $orderunique, $isself, $remarks);   //表  Orders 新增记录
        
       
        
        /**
         * @author peng
         * @date 2017-01
         * @descreption 
         */
        if($ordersInfo===false){
            $this->error('订单提交异常！');
        }
        
        // 结算后清空所选的购物车信息
        $createTime = date('Y-m-d H:i:s');
        // 生成付款订单号
//        $payId = M('orders_payid')->add(array(          // 二次开发 要改的代码 表 orders_payid 新增数据
//                    'pid' => 0,
//                    'createTime' => $createTime,
//                    'orderId' => $ordersInfo['orderIds'][0],
//                    'type' => 1
//        ));
        //以下是原来的代码
        $payId = M('orders_payid')->add(array(          // 表 orders_payid 新增数据
            'createTime' => $createTime,
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
        session('payid', $payId);
        header("location:" . U('Confirm/onlinkPay'));
    }
    
    // 提交订单
    public function submitDaichongOrder()
    {
        // 优惠卷
        // 自提还是配送
        $isself = I('isself', 1);
        // 留言
        $remarks = I('remarks');
        // 下单的数据session数据
        $morders = D('Game/Orders');
        $totalMoney = 0;
        $totalCnt = 0;
        $userId = session('oto_userId');
        // 收件人
        $consigneeId = I("consigneeId", 0);
        // 支付方式
        $payway = I("payway", 2);
        // 是否需要发票
        $needreceipt = I("needreceipt", 0);
        
        $orderunique = I("orderunique", 0);
        
        $payway = 1; // 默认在线支付，0为货到付款
        if(I('num')<=0){
            $this->error('订单数量不能小于1');
        }
        $ordersInfo = $morders->addDaichongOrders($userId, $consigneeId, $payway, $needreceipt, $orderunique, $isself, $remarks);
        
         /**
         * @author peng
         * @date 2017-01
         * @descreption 
         */
        if($ordersInfo===false){
            $this->error('订单异常！');
        }
        
        // 结算后清空所选的购物车信息
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

        session('payid', $payId);
        header("location:" . U('Confirm/onlinkPay'));
    }
    
    // 待发货
    public function waitDeliver($page = 0, $limit = 20)
    {
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = session('oto_userId');
        $map['o.orderStatus'] = 1;
        $field = "o.signTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.lastMessTime DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            $goodsType=M('goods')->where(array('goodsId'=>$v['goodsId']))->getField('goodsType');
            if ($v['orderType'] == 1) {
                $type = '首充号';
                if($goodsType==1){
                    $type = '会员首充';
                }
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                    if($goodsType==1){
                        $type = '会员首代';
                    }
                } 
            $waitPay[$k]['type'] = $type;
        }
        return $waitPay;
    }
  

    // 已发货
    public function fahuo($page = 0, $limit = 20)
    {
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = session('oto_userId');
        $map['_string'] = 'o.orderStatus = 2 or o.orderStatus = -3';   // 魏永就   将退款中的上平列入已发货中
        $field = "o.signTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.lastMessTime DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            $goodsType=M('goods')->where(array('goodsId'=>$v['goodsId']))->getField('goodsType');
            if ($v['orderType'] == 1) {
                $type = '首充号';
                if($goodsType==1){
                    $type = '会员首充';
                }
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                    if($goodsType==1){
                        $type = '会员首代';
                    }
                } 
            $waitPay[$k]['type'] = $type;
        }
        return $waitPay;
    }
    // 待
    public function orderFinsh($page = 0, $limit = 20)
    {
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = session('oto_userId');
        $map['o.orderStatus'] = array(
            'in',
            '3,-4,-5,-6,-7,-8'
        );
        $field = "o.signTime,o.orderStatus,o.cancelTime,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.lastMessTime DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
             $goodsType=M('goods')->where(array('goodsId'=>$v['goodsId']))->getField('goodsType');
            if ($v['orderType'] == 1) {
                $type = '首充号';
                if($goodsType==1){
                    $type = '会员首充';
                }
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                    if($goodsType==1){
                        $type = '会员首代';
                    }
                } 
            $waitPay[$k]['type'] = $type;
        }
        return $waitPay;
    }
    // 待收货
    public function waitReceiving($page = 0, $limit = 20)
    {
        $map['isRefund'] = 0;
        $map['isClosed'] = 0;
        $map['orderFlag'] = 1;
        $map['orderType'] = array(
            'neq',
            4
        );
        $map['o.userId'] = session('oto_userId');
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
    public function waitPay($page = 0, $limit = 20)
    {
        // 待付款
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.orderStatus'] = - 2;
        $map['o.userId'] = session('oto_userId');
        $field = "o.signTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.lastMessTime DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            $goodsType=M('goods')->where(array('goodsId'=>$v['goodsId']))->getField('goodsType');
            if ($v['orderType'] == 1) {
                $type = '首充号';
                if($goodsType==1){
                    $type = '会员首充';
                }
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                    if($goodsType==1){
                        $type = '会员首代';
                    }
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
        $field = "o.orderId,o.orderNo,o.areaId1,o.areaId2,o.areaId3,o.shopId,o.deliverMoney,o.paytime,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg";
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
    public function orders($page = 0, $limit = 20)
    {
        parent::isLogin();
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = session('oto_userId');
        $map['o.orderStatus'] = array(
            'in',
            "-2,1,2,3,4,-5,-6,-3,-4,-7,-8,-9"
        );
        $field = "o.isRead,o.signTime,o.cancelTime,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $waitPay = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.lastMessTime DESC')
            ->field($field)
            ->limit($page * $limit, $limit)
            ->select();
         
        foreach ($waitPay as $k => $v) {
            $type = '首充号';
            $goodsType=M('goods')->where(array('goodsId'=>$v['goodsId']))->getField('goodsType');
            if ($v['orderType'] == 1) {
                $type = '首充号';
                if($goodsType==1){
                    $type = '会员首充';
                }
            } else 
                if ($v['orderType'] == 2) {
                    $type = '首充号代充';
                    if($goodsType==1){
                        $type = '会员首代';
                    }
                } 
            $waitPay[$k]['type'] = $type;
        }
        
        return $waitPay;
    }
    
    // 订单详情
    public function orderDetail()
    {
        $id = I('id');
        M('orders')->where('orderId='.$id)->setField('isRead',1);   //点击则就将这条订单消息设置为已读
        if (! $id) {
            $this->redirect(U('Index/index', '', '', 0));
            return;
        }
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        $map['o.userId'] = session('oto_userId');
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
        $goodsType=M('goods')->where(array('goodsId'=>$waitPay['goodsId']))->getField('goodsType');
        if ($waitPay['orderType'] == 1) {
            $type = '首充号';
            if($goodsType==1){
                $type = '会员首充';
            }
            $account = M('fahuo')->where(array(
                'orderId' => $waitPay['orderId']
            ))->select();
            $waitPay['fahuo'] = $account;
        } else 
            if ($waitPay['orderType'] == 2) {
                $type = '首充号代充';
                if($goodsType==1){
                    $type = '会员首代';
                }
            } 
        $waitPay['type'] = $type;
        $fahuoTime = strtotime($waitPay['fahuoTime']);
        $nowTime = time();
        if (($waitPay['orderStatus'] == 3 || $waitPay['orderStatus'] == - 6) && ($nowTime - $fahuoTime) < C('cancelOrderTime')) {
            // 7天内的订单才可能投诉
            // 检查是否已经投诉过
            $isComplain = M('complain')->where(array(
                'orderId' => $id
            ))->find();
            if (! $isComplain) {
                $waitPay['complain'] = 1;
            }
        }
        $this->orderInfo = $waitPay;
        $this->display();
    }
    
    // 取消订单
    public function cancelOrder()
    {
        if (IS_AJAX) {
            if (! session('oto_userId')) {
                $this->ajaxReturn(array(
                    'status' => - 1,
                    'mes' => '请登录后再操作'
                ));
                return;
            }
            $id = I('orderId');
            if (! $id) {
                $this->ajaxReturn(array(
                    'status' => - 1,
                    'mes' => '非法请求'
                ));
            }
            $orderInfo = M('orders')->where(array(          //对表 orders 查询
                'orderId' => $id,
                'userId'=>session('oto_userId')
            ))->find();
            if(!$orderInfo){
                $this->ajaxReturn(array(
                    'status' => - 1,
                    'mes' => '订单不存在'
                ));
                return;
            }
            $isChangeStatus = $orderInfo['orderStatus'];
            if ($isChangeStatus != 1) {                            //判断订单状态是否改变
                $this->ajaxReturn(array(
                    'status' => - 1,
                    'mes' => '订单状态已经改变'
                ));
                return;
            }
            if(time() - $orderInfo['paytime'] < 1800)               //下单并支付后不能在30分钟内取消订单
            {
                $this->ajaxReturn(array(
                    'status'=>-1,
                    'mes'=>'30分钟内不可取消订单'
                ));
            }
            $A = true;
            $B = true;
            $C = true;
            M()->startTrans();
            $res = M('orders')->where(array(                    //  对表 orders 更新
                'orderId' => $id
            ))->setField(array(
                'orderStatus' => - 4,
                'cancelTime'  => time()
            ));
            if ($res) {
                $A = M('users')->where(array(              //对表 users 更新，退款给玩家
                    'userId' => session('oto_userId')
                ))->setInc('userMoney', $orderInfo['needPay']);
                // 订单最新日志
                $data = array();
                $data["orderId"] = $id;
                $data["logContent"] = "商家没发货，买家主动取消订单！";
                $data["logUserId"] = session('oto_userId');
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                M('log_orders')->add($data);
                // 退款日志表
                $refundLog['orderId'] = $id;
                $refundLog['userId'] = session('oto_userId');
                $refundLog['mess'] = '商家没发货，买家主动取消订单';
                $refundLog['time'] = date('Y-m-d H:i:s');
                $B = M('refund_log')->add($refundLog);
                
                $userMoney = M('users')->where(array(
                    'userId' => session('oto_userId')
                ))->getField('userMoney');
                // 全额变动记录
                $moneyRecord['type'] = 2;
                $moneyRecord['money'] = $orderInfo['needPay'];
                $moneyRecord['time'] = time();
                $moneyRecord['ip'] = get_client_ip();
                $moneyRecord['orderNo'] = $orderInfo['orderNo'];
                $moneyRecord['IncDec'] = 1;
                $moneyRecord['userid'] = session('oto_userId');
                $moneyRecord['balance'] = $userMoney;
                $moneyRecord['remark'] = '商家没接单，取消订单';
                $moneyRecord['payWay'] = 3;
                $C = M('money_record')->add($moneyRecord);
                //添加平台流水记录
                $F = M('platfrom_account')->add(array(
                    'orderId'   =>  $id,
                    'time'      =>  time(),
                    'income'    =>  -$orderInfo['needPay'],
                    'remark'    =>  '买家取消订单，平台退款成功',
                    'orderNo'   =>  $orderInfo['orderNo']
                ));
                $dataTemRes = M('data_tmp')->where('id=1')->setDec('value',$orderInfo['needPay']);
                //2017.7.19 修正优惠券信息
                if($orderInfo['voucherIds']){
                    $uservoucher_s = \Org\Order\Order::getInstance()->VoucherUser($orderInfo['voucherIds'],$orderInfo['createTime']);
                }else{
                    $uservoucher_s = true;
                }
                if ($A && $B && $C&& $F && $dataTemRes && $uservoucher_s) {
                    M()->commit();
                    /**
                     * @author peng
                     * @date 2017-01
                     * @descreption 
                     */
                    //D('Game/Voucher')->cancelOrderHook($orderInfo); 
                     
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
                    'status' => - 1,
                    'mes' => '订单取消失败'
                ));
            }
        }
    }
    public function cancelOrder1()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '请登录后再操作'
            ));
            return;
        }
        $orderId = I('post.orderId');
        if (! $orderId) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '非法请求'
            ));
        }
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId
        ))->find();
        $isChangeStatus = $orderInfo['orderStatus'];
        if ($isChangeStatus != -2) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '订单状态已经改变'
            ));
            return;
        }
        M()->startTrans();

        $data = array(
            'orderStatus'=>'-4',
            'cancelTime' =>time()
        );
        $res = M('orders')->where('orderId='.$orderId)->setField($data);
        
        if ($res) {
//            $A = M('users')->where(array(
//                'userId' => session('oto_userId')
//            ))->setInc('userMoney', $orderInfo['needPay']);
            // 订单最新日志
            $data = array();
            $data["orderId"] = $orderId;
            $data["logContent"] = "买家未付款，买家取消订单！";
            $data["logUserId"] = session('oto_userId');
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            $result = M('log_orders')->add($data);
            // 退款日志表
            
            if ($result) {
                M()->commit();
                /**
                 * @author peng
                 * @date 2017-01
                 * @descreption 
                 */
                D('Game/Voucher')->cancelOrderHook($orderInfo);
                
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
                'status' => - 1,
                'mes' => '订单取消失败'
            ));
        }
//        $data = array(
//            'orderStatus'=>'-4',
//            'cancelTime' =>time()
//        );
//        $res = M('orders')->where('orderId='.$orderId)->setField($data);
//       
//        if($res)
//        {
//            $this->ajaxReturn(array(
//                'status' => 0,
//                'mes' => '订单取消成功'
//            ));
//        }else{
//            $this->ajaxReturn(array(
//                'status' => 0,
//                'mes' => '订单取消失败'
//            ));
//        }

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
    
    // 投诉反馈orders'
    public function applyComplain()
    {
        $userId = session('oto_userId');
        $orderId = I('orderId');
        if (! $userId && ! $orderId) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '数据异常'
            ));
            return;
        }
        $content = I('content');
        $type = I('type');
        $isRefund = M('complain')->where(array(
            'orderId' => $orderId
        ))->find();
        if ($isRefund) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '有投诉正在处理中'
            ));
            return;
        }
        $complain['userId'] = session('oto_userId');
        $complain['orderId'] = $orderId;
        $orderRes = M('orders')->where(array(
            'orderId'=>$orderId,
            'orderStatus'=> 3
        ))->find();
        if ($type == 0) {               //魏永就   complain 表的type字段数字含意改变，现在是 1表示投诉的订单是一完成，0表示未完成
            if($orderRes){
                $complain['content'] = '该订单已完成，买家投诉原因：'.$content;
                $complain['type'] = 1;
            }else{
                $complain['content'] = '该订单未完成，买家投诉原因：'.$content;
                $complain['type'] = 0;
            }
        }else{
            if($orderRes){
                $complain['content'] = '该订单已完成，买家投诉原因：商家已确定，但没有送达';
                $complain['type'] = 1;
            }else{
                $content['content'] = '该订单未完成，买家投诉原因：商家已确定，但没有送达';
                $complain['type'] = 0;
            }
        }
        $complain['time'] = date('Y-m-d H:i:s');
        $complain['isHandle'] = 0;
        $rs = M('complain')->add($complain);
        if ($rs) {
            M('orders')->where('orderId='.$orderId)->setField(array(
                'lastMessTime'=>time()
            ));
            $this->ajaxReturn(array(
                'status' => 0,
                'mes' => '投诉提交成功'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '投诉提交失败'
            ));
        }
    }
    // 申请退款
    public function applyRefund()
    {
        $userId = session('oto_userId');
        $orderId = I('orderId');
        if (! $userId && ! $orderId) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '数据异常'
            ));
            return;
        }
        $content = I('content');
        $type = I('type');
        $orderInfo = M('orders')->where(array(
            'orderId' => $orderId
        ))->find();
        $isRefund = M('refund')->where(array(
            'orderid' => $orderId
        ))->find();
        if ($isRefund) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '退款正在处理中'
            ));
            return;
        }
        // 判断订单状态
        if ($orderInfo['orderStatus'] != 2) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '订单状态已经改变'
            ));
            return;
        }
        
        M()->startTrans();
        $complain['userId'] = session('oto_userId');
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
        ))->setField(array(
            'orderStatus'=>- 3,
            'lastMessTime'=> time()
        ));
        
        // 订单最新日志
        $data = array();
        $data["orderId"] = $orderId;
        $data["logContent"] = "买家申请退款!退款原因：" . $content;
        $data["logUserId"] = session('oto_userId');
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        M('log_orders')->add($data);
        
        // 退款日志表
        $refundLog['orderId'] = $orderId;
        $refundLog['userId'] = session('oto_userId');
        $refundLog['mess'] = '退款原因：' . $content;
        $refundLog['time'] = date('Y-m-d H:i:s');
        $C = M('refund_log')->add($refundLog);
        
        if ($rs && $B && $C) {
            M()->commit();
            $this->ajaxReturn(array(
                'status' => 0,
                'mes' => '退款申请提交成功'
            ));
        } else {
            M()->rollback();
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '退款申请提交失败'
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
            
            //author: peng descreption:只有指定的订单才能删除
            if(!in_array($orderInfo['orderStatus'],[2,3,-4,-5,-7,-8,-9])){
                // 非法操作
                $this->ajaxReturn(array(
                    'status' => - 2
                ));
                return;
            }
            
            
            if ($orderInfo['orderFlag'] == 0) {
                // 订单已经删除
                $this->ajaxReturn(array(
                    'status' => - 3
                ));
                return;
            }
            
            $A = M('orders')->where(array(
                'orderId' => $orderInfo['orderId'],
                'userId' => session('oto_userId')
            ))->setField('orderFlag', 0);
            if ($A) {
                $this->ajaxReturn(array(
                    'status' => 0
                ));
            } else {
                $this->ajaxReturn(array(
                    'status' => - 1
                ));
            }
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
        $money = I('money');
        $payType = I('payType');
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'msg' => '请先登录'
            ));
            exit();
        }
        if (empty($money) || empty($payType) || $money <= 0) {
            $this->ajaxReturn(array(
                'status' => - 2,
                'msg' => '操作超时，请稍候重试'
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
        $C = $db->where('id='.$A)->setField(array(
            'pid'=>$A
        ));
        $tou['userid'] = session('oto_userId');
        $tou['topupNo'] = $A;
        $tou['time'] = $data['createTime'];
        $tou['out_trade_no'] = ' ';
        $tou['money'] = $money;
        $tou['status'] = 0;
        $B = $topDB->add($tou);
        if ($A && $B && $C) {
            M()->commit();
            session('payid', $A);
            $this->ajaxReturn(array(
                'status' => 0,
                'msg' => '订单提交成功'
            ));
            exit();
        } else {
            M()->rollback();
            $this->ajaxReturn(array(
                'status' => - 3,
                'msg' => '操作超时，请稍候重试'
            ));
            exit();
        }
    }
    /**
     * 魏永就
     * 17-1-8
     * 买家确认收货
     */
    public function confirmGoods()
    {
        $orderId = I('post.orderId');
        M()->startTrans();
        $orderInfo = M('orders')->where('orderId='.$orderId)->find();
        if($orderInfo['orderStatus'] == 3)
        {
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>'订单状态已经改变'
            ));
        }
        $goodsId=M('order_goods')->where(array('orderId'=>$orderId))->getFIeld('goodsId');
        $goodsType=M('goods')->where(array('goodsId'=>$goodsId))->getField('goodsType');
        $uInfo=M('users')->where(array('userId'=>$orderInfo['userId']))->field('isAgent,partnerId,rank')->find();
        if($goodsType == 1)
        {
            if($uInfo['isAgent'] == 0)
                $userRes = M('users')->where(array('userId'=>$orderInfo['userId']))->setField('isAgent',1);
            else
                $userRes = true;
        }else{
            $userRes = true;
        }
        
        /**
         * @author peng
         * @date 2017-01
         * @descreption 
         */
        
        if($orderInfo['voucher_reduce']>0){
            $result1=M('data_tmp')->where(['key'=>'platform_money'])->setDec('value',$orderInfo['voucher_reduce']);
            $result2 = M('platfrom_account')->add(array(
                'orderId'   =>  $orderInfo['orderId'],
                'time'      =>  time(),
                'income'    =>  -$orderInfo['voucher_reduce'],
                'remark'    =>  '承担代金券消费',
                'orderNo'   =>  $orderInfo['orderNo']
            ));
            
            $bizIncome=$orderInfo['needPay']+$orderInfo['voucher_reduce'];
        }else{
            $bizIncome=$orderInfo['needPay'];//商家收入
            $result1=true;
            $result2=true;
        }
        
        
        //不是平台商品时候，商家才有收入
        $flag = false;
        
        //商家收入=付款金额-佣金
        if($bizIncome == 0)             //魏永就  如果金额为0的话，则不进行数据操作
        {
            $tempRes = true;
        }else{
            $tempRes = M('shops')->where(array('shopId' => $orderInfo['shopId'] ))->setInc('bizMoney', $bizIncome);
        }
        $platRes = M('platfrom_account')->add(array(        //添加平台流水记录  魏永就
            'orderId' =>$orderId,
            'time'     =>time(),
            'income'   => -$orderInfo['needPay'],
            'remark'   => '买家确认收货，平台退款给商家',
            'orderNo'  => $orderInfo['orderNo']
        ));
        if($orderInfo['needPay'] == 0)
        {
            $dataTemRes = true;
        }else{
            $dataTemRes = M('data_tmp')->where('id=1')->setDec('value',$orderInfo['needPay']);      //更新平台暂时的余额  魏永就
        }
        if ($tempRes && $platRes && $dataTemRes) {
            $flag = true;
        }
        
        //商家收入
        $inCome=array();
        $inCome['orderId']=$orderId;
        $inCome['time']=date('Y-m-d H:i:s',time());
        $inCome['orderStatus']=3;
        $inCome['totalMoney']=$orderInfo['needPay'];
        $inCome['bizIncome']=$bizIncome;
        $inCome['remark']='买家确认收货';
        $autoRes=M('autocomfirm')->add($inCome);

        // 订单最新日志
        $data = array();
        $data["orderId"] = $orderId;
        $data["logContent"] = '买家确认收货';
        $data["logUserId"] = $orderInfo['userId'];
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        $logRes = M('log_orders')->add($data);
        $signTime = date('Y-m-d H:i:s');
        $ordersRes = M('orders')->where('orderId='.$orderId)->setField(array( 'orderStatus' => 3,'signTime' => $signTime,'lastMessTime'=> time() ));
        if($userRes && $flag && $autoRes && $logRes && $ordersRes && $result1 && $result2)
        {
            M()->commit();
            $this->ajaxReturn(array(
                'status'=>0,
                'msg'=>'确认收货成功'
            ));
        }else{
            M()->rollback();
            $this->ajaxReturn(array(
                'status'=>1,
                'msg'=>'确认收货失败'
            ));
        }
    }
    
    
}