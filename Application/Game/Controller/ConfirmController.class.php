<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class ConfirmController extends BaseController
{

    public $userid;

    public function _initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        @header('Content-type: text/html;charset=UTF-8');
        vendor('WxPayPubHelper.WxPayPubHelper');
        if (session('oto_userId')) {
            $this->userid = session('oto_userId');
        }
    }

    public function checkPayPWD()
    {
        if (!session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => -2,
                'msg' => '请先登录'
            ));
            return;
        }
        $info = M('users')->where(array(
            'userId' => session('oto_userId')
        ))
            ->field('loginSecret,userMoney,payPwd')
            ->find();

        if (empty($info['payPwd'])) {
            $this->ajaxReturn(array(
                'status' => -4,
                'msg' => '请先设置支付密码'
            ));
            return;
        }
        $pwd = I('pwd');
        if (md5($pwd . $info['loginSecret']) != $info['payPwd']) {
            $this->ajaxReturn(array(
                'status' => -1,
                'msg' => '支付密码错误'
            ));
            return;
        } else {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        }
    }

    public function index()
    {
        echo 1;
    }

    public function conFirmOrder()
    {
        parent::isLogin();
        // 接收勾选的购物车商品
        // 商品ID_属性ID18_324_321_320_秒杀_团购，
        $goodsId_attrId = I('get.g_a');
        if (!$goodsId_attrId) {
            $this->redirect(U('Index/Index', '', 0, 0));
        }
        // 默认地址
        $field = "addressId,userName,userPhone,address,areaId1,areaId2,areaId3";
        $map['addressFlag'] = 1;
        $map['isDefault'] = 1;
        $map['userId'] = session('oto_userId');
        $addrInfo = M('user_address')->field($field)
            ->order('isDefault DESC')
            ->where($map)
            ->select();
        $db = M('areas');
        foreach ($addrInfo as $k => $v) {
            $addrInfo[$k]['province'] = $db->where(array(
                'areaId' => $v['areaId1']
            ))->getField('areaName');
            $addrInfo[$k]['city'] = $db->where(array(
                'areaId' => $v['areaId2']
            ))->getField('areaName');
            $addrInfo[$k]['area'] = $db->where(array(
                'areaId' => $v['areaId3']
            ))->getField('areaName');
        }
        $this->checkOrderInfo($goodsId_attrId);
        session('clearCartGoodsIdAttrId', $goodsId_attrId);
        $this->assign('addrInfo', $addrInfo);
        $this->display();
    }

    /**
     * 核对订单信息
     */
    public function checkOrderInfo($goodsId_attrId)
    {
        parent::isLogin();
        // $goodsId_attrId商品id_属性ID ','分开
        $g_a_arr = explode(',', $goodsId_attrId);
        session('goods_attr', $g_a_arr);
        if (!session('oto_userId')) {
            $this->redirect(U('index/login'));
            return;
        }
        $mareas = D('Wx/Areas');
        $morders = D('Wx/Orders');
        $mgoods = D('Wx/Goods');
        $maddress = D('Wx/UserAddress');
        $gtotalMoney = 0; // 商品总价（去除配送费）
        $totalMoney = 0; // 商品总价（含配送费）
        $totalNum = 0; // 商品总数
        $totalCnt = 0;

        // 购物车信息
        $map['userId'] = session('oto_userId');
        $isCarExists = M('car_session')->where($map)
            ->field('car_session,userId')
            ->find();
        $WST_CART = unserialize($isCarExists['car_session']);
        $shopcat = $WST_CART ? $WST_CART : array();
        $catgoods = array();
        $shopColleges = array();
        $startTime = 0;
        $endTime = 24;

        // 去除不在勾选中的购物车订单
        foreach ($shopcat as $k => $v) {
            if (!in_array($k, $g_a_arr)) {
                unset($shopcat[$k]);
            }
        }

        if (empty($shopcat)) {
            // $this->assign("fail_msg",'不能提交空商品的订单!');
            $this->redirect(U('Wx/Orders/orders', '', '', 0));
            exit();
        }
        foreach ($shopcat as $key => $cgoods) {
            $obj = array();
            $temp = explode('_', $key);
            // 遍历分别取出商品id和商品属性id
            $obj["goodsAttrId"] = array();
            foreach ($temp as $k => $v) {
                // 商品ID_属性ID_秒杀_团购
                if ($k == 0) {
                    $obj["goodsId"] = (int)$v;
                } else {
                    $obj["goodsAttrId"][] = (int)$v;
                }
            }
            // 属性ID
            $obj["goodsAttrId"] = implode(',', $obj["goodsAttrId"]);
            if ($cgoods["ischk"] == 1) {
                // 商品信息
                $goods = $mgoods->getGoodsForCheck($obj);
                if ($goods["isBook"] == 1) {
                    $goods["goodsStock"] = $goods["goodsStock"] + $goods["bookQuantity"];
                }
                $goods["ischk"] = $cgoods["ischk"];
                $goods["cnt"] = $cgoods["cnt"];
                $catgoods[$goods["shopId"]]["shopgoods"][] = $goods;
                $catgoods[$goods["shopId"]]["deliveryFreeMoney"] = $goods["deliveryFreeMoney"]; // 店铺免运费最低金额
                $catgoods[$goods["shopId"]]["deliveryMoney"] = $goods["deliveryMoney"]; // 店铺配送费
                $catgoods[$goods["shopId"]]["deliveryStartMoney"] = $goods["deliveryStartMoney"]; // 店铺最低起送费
            }
        }
        // 商品列表
        $arr['cartgoods'] = $catgoods;
        $info = $this->sortingOrder($arr, 1);
        // 待支付的订单分拣好的订单
        session('sortingOrder', $info);
        $this->assign("info", $info);
    }

    /**
     * 核对商品信息
     */
    public function checkGoodsStock()
    {
        parent::isLogin();
        $province = I('province');
        $city = I('city');
        $area = I('area');
        $community = I('community');

        if (empty($province) || empty($city) || empty($area)) {
            $this->ajaxReturn(array(
                'status=>-5'
            ));
            return;
        }
        $g_a_arr = session('goods_attr');
        // 判断 是否在配送范围
        $goodsIds = '';
        foreach ($g_a_arr as $k => $v) {
            $tmp = explode('_', $v);
            $goodsIds .= $tmp[0] . ',';
        }
        $goodsIds = rtrim($goodsIds, ',');
        $goodsIds = array_unique(explode(',', $goodsIds));
        $noRange = '';
        foreach ($goodsIds as $k => $v) {
            if (!$this->range($area, $community, $v)) {
                $noRange .= $v . ',';
            }
        }
        $noRange = rtrim($noRange, ',');
        if ($noRange) {
            // 有商品不在配送范围
            $this->ajaxReturn(array(
                'status' => -6,
                'data' => $noRange
            ));
            return;
        }
        $cartInfo = session('sortingOrder');

        $arr = $this->checkStotck($cartInfo);

        $this->ajaxReturn($arr);
    }

    // 配送范围
    public function range($area, $community, $goodsId)
    {
        parent::isLogin();
        $shopid = M('goods')->where(array(
            'goodsId' => $goodsId
        ))->getField('shopId');
        if ($community) {
            $range = M('shops_communitys')->field('communityId')
                ->where(array(
                    'shopId' => $shopid,
                    'communityId' => $community
                ))
                ->find();
            if (!$range) {
                // 不在配送范围
                return false;
            }
        } else
            if ($area) {
                // 区级配送范围
                $range = M('shops_communitys')->field('areaId3')
                    ->where(array(
                        'shopId' => $shopid,
                        'areaId3' => $area
                    ))
                    ->find();
                if (!$range) {
                    // 不在配送范围
                    return false;
                }
            }
        return true;
    }

    /**
     * 提交订单信息
     */
    public function submitOrder()
    {
        parent::isLogin();
        $goodsmodel = D('Wx/Goods');
        $morders = D('Wx/Orders');
        $totalMoney = 0;
        $totalCnt = 0;
        $userId = session('oto_userId');
        // 收件人
        $consigneeId = (int)I("consigneeId");
        // 支付方式
        $payway = (int)I("payway");
        // isself是否送货上门，0是，1自提
        $isself = (int)I("isself");
        // 是否需要发票
        $needreceipt = (int)I("needreceipt", 0);

        $orderunique = I("orderunique");

        // 读取选择的购物车信息
        $map['userId'] = session('oto_userId');
        $isCarExists = M('car_session')->where($map)
            ->field('car_session,userId')
            ->find();
        $WST_CART = unserialize($isCarExists['car_session']);
        $shopcat = $WST_CART ? $WST_CART : array();
        // 去除不在勾选中的购物车订单
        $g_a_arr = session('goods_attr');
        foreach ($shopcat as $k => $v) {
            if (!in_array($k, $g_a_arr)) {
                unset($shopcat[$k]);
            }
        }
        $catgoods = array();
        if (empty($shopcat)) {
            // 购物车已经为空
            $this->ajaxReturn(array(
                'status' => 1,
                'msg' => '订单提交成功'
            ));
        } else {
            // 整理及核对购物车数据
            $paygoods = session('WST_PAY_GOODS');
            foreach ($shopcat as $key => $cgoods) {
                // 分开商品id和属性id
                $temp = explode('_', $key);
                $goodsId = (int)$temp[0];
                $goodsAttrId = array();
                foreach ($temp as $k => $v) {
                    if ($k != 0) {
                        $goodsAttrId[] = (int)$v;
                    }
                }
                if (in_array($goodsId, $paygoods)) {
                    $goods = $goodsmodel->getGoodsSimpInfo($goodsId, $goodsAttrId);
                    // 核对商品是否符合购买要求
                    if (empty($goods)) {
                        $this->ajaxReturn(array(
                            'status' => -1,
                            'msg' => '对不起，商品' . $goods['goodsName'] . '不存在!'
                        ));
                        exit();
                    }
                    if ($goods['goodsStock'] <= 0) {
                        $this->ajaxReturn(array(
                            'status' => -2,
                            'msg' => '对不起，商品' . $goods['goodsName'] . '库存不足!'
                        ));
                        exit();
                    }
                    if ($goods['isSale'] != 1) {
                        $this->ajaxReturn(array(
                            'status' => -3,
                            'msg' => '对不起，商品库' . $goods['goodsName'] . '已下架!'
                        ));
                        exit();
                    }
                    $goods["cnt"] = $cgoods["cnt"];
                    $totalCnt += $cgoods["cnt"];
                    // 商品价格由多个属性shopPrice相加而成
                    $prices = 0;
                    foreach ($goods['attrs'] as $kk => $price) {
                        $prices += $price['shopPrice'];
                    }
                    if ($prices != 0) {
                        $shopPrice = $prices;
                    } else {
                        $shopPrice = $cgoods['shopPrice'];
                    }
                    // 所有店铺商品总价
                    $totalMoney += $goods["cnt"] * $shopPrice;
                    $catgoods[$goods["shopId"]]["shopgoods"][] = $goods;
                    $catgoods[$goods["shopId"]]["deliveryFreeMoney"] = $goods["deliveryFreeMoney"]; // 店铺免运费最低金额
                    $catgoods[$goods["shopId"]]["deliveryMoney"] = $goods["deliveryMoney"]; // 店铺免运费最低金额
                    $catgoods[$goods["shopId"]]["totalCnt"] = $catgoods[$goods["shopId"]]["totalCnt"] + $cgoods["cnt"];
                    $catgoods[$goods["shopId"]]["totalMoney"] = $catgoods[$goods["shopId"]]["totalMoney"] + ($goods["cnt"] * $shopPrice);
                }
            }
            foreach ($catgoods as $key => $cshop) {
                if ($cshop["totalMoney"] < $cshop["deliveryFreeMoney"]) {
                    if ($isself == 0) {
                        $totalMoney = $totalMoney + $cshop["deliveryMoney"];
                    }
                }
            }
            $payway = 1; // 默认在线支付，0为货到付款
            $ordersInfo = $morders->addOrders($userId, $consigneeId, $payway, $needreceipt, $catgoods, $orderunique, $isself);
            // 结算后清空所选的购物车信息
            $newcart = array();
            $g_a_arr = session('goods_attr');
            foreach ($shopcat as $key => $cgoods) {
                if (!in_array($key, $g_a_arr)) {
                    $newcart[$key] = $cgoods;
                }
            }
            if (empty($newcart)) {
                // M('car_session')->where($map)->delete();
            } else {
                // $isCarExists=M('car_session')->where($map)->save(array('car_session'=>serialize($newcart)));
            }
            $orderNos = $ordersInfo["orderNos"];
            $this->assign("torderIds", implode(",", $ordersInfo["orderIds"]));
            $this->assign("orderInfos", $ordersInfo["orderInfos"]);
            $this->assign("isMoreOrder", (count($ordersInfo["orderInfos"]) > 0) ? 1 : 0);
            $this->assign("orderNos", implode(",", $orderNos));
            $this->assign("totalMoney", $totalMoney);
            $orderIds = $ordersInfo["orderIds"];
            // $this->ajaxReturn(array('status'=>1,'msg'=>'订单提交成功','orderIds'=>implode(",",$orderIds)));
        }
    }

    // 设置支付密码
    public function setPayPwd()
    {
        parent::isLogin();
        $userid = session('oto_userId');
        $pwd = I('pwd');
        if (!is_numeric($pwd) || strlen($pwd) != 6) {
            $this->ajaxReturn(array(
                'status' => -1,
                'msg' => '密码必须是6位数字'
            ));
            return;
        }
        $map['userId'] = $userid;
        $loginSecret = M('users')->where($map)->getField('loginSecret');
        $newPwd = md5($pwd . $loginSecret);
        $res = M('users')->where($map)->save(array(
            'payPwd' => $newPwd
        ));
        if ($res) {
            $this->ajaxReturn(array(
                'status' => 0,
                'msg' => '支付密码设置成功'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => -2,
                'msg' => '请稍候重试'
            ));
        }
    }

    // 余额支付
    public function balancePay()
    {
        parent::isLogin();
        $info = M('users')->where(array(
            'userId' => session('oto_userId')
        ))
            ->field('loginSecret,userMoney,payPwd')
            ->find();
        $pwd = I('pwd');
        $payid = I('orderids');

        $order_id = M('orders_payid')->where(array(
            'pid' => $payid
        ))
            ->field('orderId')
            ->select();
        
        $orderids = '';
        foreach ($order_id as $K => $v) {
            $orderids .= $v['orderId'] . ',';
        }
        $orderids = rtrim($orderids, ',');

        $ispayInfo = M('orders')->where(array(
            'orderId' => array(
                'in',
                $orderids
            )
        ))
            ->field('isPay,orderNo,orderId,shopId,needPay,shopId,userId,userAddress,qq,roleName,profession,userPhone')
            ->select();
        $ispay = true;
        $orderNos = '';
        foreach ($ispayInfo as $k => $v) {
            $orderNos .= $v['orderNo'] . ',';
            if ($v['isPay'] == 1) {
                $ispay = false;
            }
        }
        $orderNos = trim($orderNos, ',');
        if (!$ispay) {
            $this->ajaxReturn(array(
                'status' => -4,
                'msg' => '订单状态已经改变'
            ));
            return;
        }
        $needPay = M('orders')->where(array(
            'orderId' => array(
                'in',
                $orderids
            )
        ))->sum('needPay');

        if (md5($pwd . $info['loginSecret']) != $info['payPwd']) {
            $this->ajaxReturn(array(
                'status' => -2,
                'msg' => '支付密码错误'
            ));
            return;
        }
        if($needPay<0){
            $this->ajaxReturn(array(
                'status' => -4,
                'msg' => '支付金额异常'
            ));
            return;
        }
        if ($needPay > $info['userMoney']) {
            $this->ajaxReturn(array(
                'status' => -1,
                'msg' => '余额不足'
            ));
            return;
        }
        M()->startTrans();
        $saveData['isPay'] = 1;
        $saveData['orderStatus'] = 1;
        $saveData['paytime'] = time();
        $saveData['payType'] = 3; // 余额支付
        $saveData['lastMessTime'] = time();   //魏永就 添加订单改变时间
        $res = M('orders')->where(array(
            'orderId' => array(
                'in',
                $orderids
            )
        ))
            ->data($saveData)
            ->save();
        // 操作用户金额
        if($needPay == 0)       //魏永就  如果需要付款的金额为0，则不需要对用户的余额操作
        {
            $A = true;
        }else{
            $A = M('users')->where(array(
                'userId' => session('oto_userId')
            ))->setDec('userMoney', $needPay);
        }


        // 余额变动记录
        $B = true;
        $balance = $info['userMoney'];
        foreach ($ispayInfo as $k => $v) {
            $balance = $balance - $v['needPay'];
            $tempRes = $this->moneyRecord(1, $v['needPay'], $v['orderNo'], 0, session('oto_userId'), $balance, '', 0);
            $platRes = M('platfrom_account')->add(array(        //添加平台流水记录
                'orderId' =>$v['orderId'],
                'time'     =>time(),
                'income'   => $v['needPay'],
                'remark'   => '买家购物支付成功',
                'orderNo'  => $v['orderNo']
            ));
            if($v['needPay'] == 0)              //如果用户需付款的金额为0，则不需要对平台金额操作
            {
                $dataTemRes = true;
            }else{
                $dataTemRes = M('data_tmp')->where('id=1')->setInc('value',$v['needPay']);      //更新平台暂时的余额
            }
            if (!$tempRes || !$platRes || !$dataTemRes) {
                $B = false;
            }

        }
        // 积分变动记录->移到交易成功后
        // $C=M('users')->where(array('userId'=>session('oto_userId')))->setInc('userScore',floor($needPay));

        $D = $this->scoreRecord(1, $needPay, $orderNos, 1, session('oto_userId'));

        $E = $this->payRecord(0, $orderNos, time(), '', $needPay, session('oto_userId'), 0, '', '', '');

        // 操作库存
        $ogField = "goodsId,goodsNums,goodsAttrId";
        $order_goods = M('order_goods')->where(array(
            'orderId' => array(
                'in',
                $orderids
            )
        ))->select();


        $goodsDB = M('goods');
        $attrDB = M('goods_versions');
        $stock = true;
        foreach ($order_goods as $k => $v) {
            if($ispayInfo[0]['shopId']==0) break;
            
            //商品表增加销量
            M('goods')->where(array('goodsId' => $v['goodsId']))->setInc('saleCount', $v['goodsNums']);

            $goodsRes = $goodsDB->where(array(
                'goodsId' => $v['goodsId']
            ))->setDec('goodsStock', $v['goodsNums']);
            if ($v['vid']) {
                $attrRes = $attrDB->where(array(
                    'id' => array(
                        'in',
                        $v['goodsAttrId']
                    )
                ))->setDec('attrStock', $v['goodsNums']);
                if (!$goodsRes || !$attrRes) {
                    $stock = false;
                }
            }
            if (!$goodsRes) {
                $stock = false;
            }
        }
        
        
        if (!$stock) {
            $this->ajaxReturn(array(
                'status' => -3,
                'msg' => '支付失败'
            ));
            M()->rollback();
            return;
        }
        
        
        // 操作库存结束
        if ($res && $A && $B && $D && $E) {
            M()->commit();
            $this->keFu($orderids);
            // 商品销量增加
            foreach ($ispayInfo as $k => $v) {
                $goods = M('order_goods')->where(array(
                    'orderId' => $v['orderId']
                ))
                    ->field('goodsId,goodsNums')
                    ->find();
                $F = M('goods')->where(array(
                    'goodsId' => $goods['goodsId']
                ))->setField('saleCount', $goods['goodsNums']);
            }

            // 白名单设置
            // $this->whiteList($ispayInfo);

            // 订单日志记录
            $morm = M('order_reminds');
            $mlogo = M('log_orders');
            foreach ($ispayInfo as $k => $v) {
                $data = array();
                $data["orderId"] = $v['orderId'];
                $data["logContent"] = '付款成功';
                $data["logUserId"] = session('oto_userId');
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                $mlogo->add($data);

                // 建立订单提醒
                $data = array();
                $data["orderId"] = $v['orderId'];
                $data["shopId"] = $v['shopId'];
                $data["userId"] = session('oto_userId');
                $data["userType"] = 0;
                $data["remindType"] = 0;
                $data["createTime"] = date("Y-m-d H:i:s");
                $morm->add($data);
            }
             
            /**
            * @author peng
            * @date 2017-01
            * @descreption 代金券的发货
            */
            if($ispayInfo[0]['shopId']==0) D('Game/Voucher')->voucherFahuo($ispayInfo[0]['orderId']);
            
            /**
             * @author peng
             * @date 2017-01
             * @descreption 手游狗版本支付完成自动发货
             */
            D('Home/SdkAgent')->autoFahuo($ispayInfo[0]['orderId']);
            
            $this->ajaxReturn(array(
                'status' => 0,
                'msg' => '支付成功',
                'callback_url'=>U('Orders/orderDetail',['id'=>$ispayInfo[0]['orderId']])
            ));
           
            
        } else {
            M()->rollback();
            $this->ajaxReturn(array(
                'status' => -3,
                'msg' => '支付失败'
            ));
        }
    }

    // 分配客服给订单
    public function keFu($orderId = 0)
    {
        // lastTime
        $minKF = M('qq')->where(array(
            'isOnline' => 1,
            'isReception' => 0
        ))
            ->order('num ASC')
            ->find();
        $nowDay = date('Y-m-d');
        // 截取日期部分，摒弃时分秒
        if ($minKF['lastTime']) {
            $lastTime = substr($minKF['lastTime'], 0, 10);
            if ($nowDay != $lastTime) {
                M('qq')->where('1')->setField('num', 0);
            }
        }
        M('qq')->where(array(
            'id' => $minKF['id']
        ))->setField('lastTime', date('Y-m-d H:i:s'));
        M('qq')->where(array(
            'id' => $minKF['id']
        ))->setInc('num', 1);
        // 设置订单QQ
        M('orders')->where(array(
            'orderId' => array(
                'in',
                $orderId
            )
        ))->setField('kfQQ', $minKF['qq']);
    }

    // 首充号白名单处理
    public function whiteList($orderInfo)
    {
        if (is_array($orderInfo)) {
            foreach ($orderInfo as $k => $v) {
                $goodsInfo = M('order_goods')->where(array(
                    'orderId' => $v['orderId']
                ))->find();
                $fahuo = M('fahuo')->where(array(
                    'orderId' => $v['orderId']
                ))
                    ->field('account')
                    ->select();
                $isExists = M('white_list')->where(array(
                    'userId' => $v['userId'],
                    'goodsId' => $goodsInfo['goodsId']
                ))->find();
                if (!$isExists) {
                    // 查找发货帐号
                    $order_goods = M('order_goods')->where(array(
                        'orderId' => $v['orderId']
                    ))->find();
                    $fahuo = M('fahuo')->where(array(
                        'orderId' => $v['orderId']
                    ))
                        ->field('account')
                        ->select();
                    foreach ($fahuo as $kk => $vv) {
                        $whiteData['account'] = $vv['account'];
                        $whiteData['gid'] = $order_goods['gid'];
                        $whiteData['vid'] = $order_goods['vid'];
                        $whiteData['createTime'] = date('Y-m-d H:i:s');
                        $whiteData['shopId'] = $v['shopId'];
                        $whiteData['orderId'] = $v['orderId'];
                        $whiteData['goodsId'] = $goodsInfo['goodsId'];
                        $whiteData['isDel'] = 0;
                        $whiteData['userId'] = $v['userId'];
                        $whiteData['area'] = $v['userAddress'];
                        $whiteData['mobile'] = $v['userPhone'];
                        $whiteData['qq'] = $v['qq'];
                        $whiteData['profession'] = $v['profession'];
                        $whiteData['roleName'] = $v['roleName'];
                        M('white_list')->add($whiteData);
                    }
                }
            }
        }
    }

    // 金额操作记录
    /**
     * 构造函数
     *
     * @param $type 操作类型,1下单，2取消订单，3充值，4提现,5订单无效
     * @param $money 金额
     * @param $orderNo 订单编号或者充值ID
     * @param $IncDec 余额变动
     *            0为减，1加
     * @param $userid 用户ID
     * @param $balance 余额
     * @param $remark 其它备注信息
     */
    public function moneyRecord($type = '', $money = 0, $orderNo = '', $IncDec = '', $userid = 0, $balance = 0, $remark = '', $payWay = 0)
    {
        $db = M('money_record');
        $data['type'] = $type;
        $data['money'] = $money;
        $data['time'] = time();
        $data['ip'] = get_client_ip();
        $data['orderNo'] = $orderNo;
        $data['IncDec'] = $IncDec;
        $data['userid'] = $userid;
        $data['balance'] = $balance;
        $data['remark'] = $remark;
        $data['payWay'] = $payWay;
        $res = $db->add($data);
        $log_name = '';
        //$log_name = "Public/hey.txt"; // log文件路径
        //$this->log_result($log_name, "【结果】:\n" . $db->_sql() . "\n");
        return $res;
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
            return 1;
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
    // 支付记录
    /**
     * 构造函数
     *
     * @param $payType 支付类型
     *            0余额支付，1支付宝，2微信
     * @param $orderNo 订单编号
     * @param $payTime 付款时间
     * @param $out_trade_no 第三方返回的流水号
     * @param $payMoney 金额
     * @param $userId 用户ID
     * @param $type 0订单，1充值
     * @param $notify_id 通知ID
     * @param $notify_time 通知时间
     * @param $buyer_email 支付宝帐号或者微信OPENID
     *
     */
    public function payRecord($payType = 0, $orderNo, $payTime, $out_trade_no, $payMoney, $userId, $type = 0, $notify_id, $notify_time, $buyer_email)
    {
        $data['payType'] = $payType; // 支付类型 0余额支付，1支付宝，2微信
        $data['orderNo'] = $orderNo;
        $data['payTime'] = $payTime;
        $data['out_trade_no'] = $out_trade_no;
        $data['payMoney'] = $payMoney;
        $data['userId'] = $userId;
        $data['type'] = $type;
        $data['notify_id'] = $notify_id;
        $data['notify_time'] = $notify_time;
        $data['buyer_email'] = $buyer_email;
        $db = M('pay_record');
        $res = $db->add($data);
        return $res;
    }

    // 在线支付页面
    public function onlinkPay()
    {
        parent::isLogin();
        $payid = session('payid');
        
        $this->assign('orderids', $payid);
        // 用户余额
        $info = M('users')->where(array(        //查找  表 user
            'userId' => session('oto_userId')
        ))
            ->field('userMoney,payPwd,userPhoto')
            ->find();
       
        $this->userInfo = $info;
        $orderIds = M('orders_payid')->where(array(
            'pid' => $payid
        ))
            ->field('orderId,type')
            ->select();
        $orderType = M('orders_payid')->where(array(
            'id' => $payid
        ))->getField('type');
        // 充值
        $title = "在线充值";
        if ($orderType == 2) {
            $needPay = M('top_up')->where(array(
                'topupNo' => $payid
            ))->getField('money');
            $this->chongzi = 1;
        } else {
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
                ->field('orderNo')
                ->select();
            $Nos = '';
            foreach ($orderNos as $k => $v) {
                $Nos .= $v['orderNo'] . ',';
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
        }
        session('orderType', $orderType);
        session('orderId', $orderIds[0]['orderId']);

        $goodsId= M('order_goods')->where(array(
            'orderId' => array(
                'in',
                $ids
            )
        ))->getField('goodsId');
       
        $this->assign('client_ip',$_SERVER["REMOTE_ADDR"]);
        $this->assign('orderType', session('orderType'));
        $this->assign('orderId', session('orderId'));
        $this->assign('title', $title);
        $this->assign('goodsId',$goodsId);
        $this->assign('needPay', $needPay);
        $payPwd = $info['payPwd'] ? 1 : 0;
        $this->assign('pwd', $payPwd);
        $this->assign('balance', $info['userMoney']);
        // 微信支付

        /*
          $jsApi = new \JsApi_pub(C('WxPayConf_pub.APPID'),C('WxPayConf_pub.MCHID'),C('WxPayConf_pub.KEY'),C('WxPayConf_pub.APPSECRET'));
          //=========步骤1：网页授权获取用户openid============
          //通过code获得openid
          if (!isset($_GET['code']))
          {
          //触发微信返回code码
          $url = $jsApi->createOauthUrlForCode(C('WxPayConf_pub.JS_API_CALL_URL'));
          Header("Location: $url");
          }else{
          //获取code码，以获取openid
          $code = $_GET['code'];
          $jsApi->setCode($code);
          $openid = $jsApi->getOpenId();
          }
          
          
          //=========步骤2：使用统一支付接口，获取prepay_id============
          //使用统一支付接口
          $unifiedOrder = new \UnifiedOrder_pub(C('WxPayConf_pub.APPID'),C('WxPayConf_pub.MCHID'),C('WxPayConf_pub.KEY'),C('WxPayConf_pub.APPSECRET'));
         
          //设置统一支付接口参数
          //设置必填参数
          //appid已填,商户无需重复填写
          //mch_id已填,商户无需重复填写
          //noncestr已填,商户无需重复填写
          //spbill_create_ip已填,商户无需重复填写
          //sign已填,商户无需重复填写
          $unifiedOrder->setParameter("openid",$openid);//商品描述
          $unifiedOrder->setParameter("body","订单号：".$Nos);//商品描述
          //自定义订单号，此处仅作举例
          $timeStamp = time();
          $out_trade_no = C('WxPayConf_pub.APPID').'_'.$payid;
          $unifiedOrder->setParameter("out_trade_no",$out_trade_no);//商户订单号
          $unifiedOrder->setParameter("total_fee",$needPay*100);//总金额
          $unifiedOrder->setParameter("notify_url",C('WxPayConf_pub.NOTIFY_URL'));//通知地址
          $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
          //非必填参数，商户可根据实际情况选填
          //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
          //$unifiedOrder->setParameter("device_info","XXXX");//设备号
          //$unifiedOrder->setParameter("attach","XXXX");//附加数据
          //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
          //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
          //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
          //$unifiedOrder->setParameter("openid","XXXX");//用户标识
          //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
         
          $prepay_id = $unifiedOrder->getPrepayId();
         
          //=========步骤3：使用jsapi调起支付============
          $jsApi->setPrepayId($prepay_id);
         
          $jsApiParameters = $jsApi->getParameters();
         
         
          session('payid',null);
          $this->assign('jsApiParameters',$jsApiParameters);
         */

        $this->display();
    }

    //微信支付成功后异步通知
    public function notify()
    {
        $log_name = "Public/hey.txt"; // log文件路径
        // 使用通用通知接口
        $notify = new \Notify_pub();

        // 存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        $resultArr = $notify->data;
        // 验证签名，并回应微信。
        // 对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        // 微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        // 尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if ($notify->checkSign() == FALSE) {
            $this->log_result($log_name, "不通过:\n" . $xml . "\n");
            $notify->setReturnParameter("return_code", "FAIL"); // 返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); // 返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); // 设置返回码
        }

        $returnXml = $notify->returnXml();
        // echo $returnXml;
        // ==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        // 以log文件形式记录回调信息
        // $log_ = new Log_();

        // $this->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");

        if ($notify->checkSign() == TRUE) {

            if ($notify->data["return_code"] == "FAIL") {
                // 此处应该更新一下订单状态，商户自行增删操作
                $this->log_result($log_name, "【通信出错】:\n" . $xml . "\n");
            } elseif ($notify->data["result_code"] == "FAIL") {
                // 此处应该更新一下订单状态，商户自行增删操作
                $this->log_result($log_name, "【业务出错】:\n" . $xml . "\n");
            } else {

                $tradeNo = $resultArr['transaction_id']; // 流水号
                $payMoney = $resultArr['total_fee'] / 100;
                $out_trade_no_text = $resultArr['out_trade_no'];
                $temp = explode('_', $out_trade_no_text);
                $out_trade_no = $temp[1];
                /*
                 * if($out_trade_no<1001){
                 * echo 'SUCCESS';return;
                 * }
                 */
                $notify_id = '';
                $time = strtotime($resultArr['time_end']);
                $notify_time = date('Y-m-d H:i:s', $time);
                $buyer_email = $resultArr['openid']; // 买家帐号


                //判断订单类型，1为订单，2为充值
                $orderType = M('orders_payid')->where(array(
                    'id' => $out_trade_no
                ))->getField('type');


                //订单付款
                if ($orderType == 1) {
                    M()->startTrans();
                    $ordersPayid = M('orders_payid')->where(array(
                        'pid' => $out_trade_no
                    ))->find();

                    $orderids = $ordersPayid['orderId'];
                    $ispayInfo = M('orders')->where(array(
                        'orderId' => $orderids
                    ))->field('isPay,orderNo,orderId,shopId,needPay,shopId,userId,userAddress,qq,roleName,profession,userPhone')
                        ->find();
                    $orderNos = $ispayInfo['orderNo'];
                    // 已经付过
                    if ($ispayInfo['isPay'] == 1) {
                        echo 'SUCCESS';
                        return;
                    }

                    $saveData['isPay'] = 1;
                    $saveData['orderStatus'] = 1;
                    $saveData['paytime'] = time();
                    $saveData['payType'] = 2; // 微信支付
                    $map['orderId'] = $orderids;
                    $res = M('orders')->where($map)
                        ->data($saveData)
                        ->save();

                    $userId = $ispayInfo['userId'];
                    if (!$userId) {
                        echo 'SUCCESS';
                        return;
                    }
                    // $this->log_result($log_name,"【用户ID】:\n".$userId."\n");
                    $userMoney = M('users')->where(array(
                        'userId' => $userId
                    ))->getField('userMoney');
                    // 积分变动记录->移到交易成功后
                    // $C=M('users')->where(array('userId'=>session('oto_userId')))->setInc('userScore',floor($needPay));

                    //余额变动记录
                    $D = $this->moneyRecord(1, $payMoney, $orderNos, 1, $userId, $userMoney, '微信支付成功', 2);

                    //$D = $this->scoreRecord(1, $payMoney, $orderNos, 1, $userId);
                    // $this->log_result($log_name,"【操作积分】:\n".$D."\n");

                    //付款记录表
                    $E = $this->payRecord(2, $orderNos, time(), $tradeNo, $payMoney, $userId, 0, $notify_id, $notify_time, $buyer_email);
                    // $this->log_result($log_name,"【操作付款记录】:\n".$E."\n");
                    // 操作库存
                    /*
                    $ogField = "goodsId,goodsNums,goodsAttrId";
                    $order_goods = M('order_goods')->where(array(
                        'orderId' => $orderids
                    ))->select();
                    $goodsDB = M('goods');
                    $attrDB = M('goods_versions');
                    $stock = true;
                   foreach ($order_goods as $k => $v) {
                        $goodsRes = $goodsDB->where(array(
                            'goodsId' => $v['goodsId']
                        ))->setDec('goodsStock', $v['goodsNums']);
                        if ($v['goodsAttrId']) {
                            $attrRes = $attrDB->where(array(
                                'id' => array(
                                    'in',
                                    $v['goodsAttrId']
                                )
                            ))->setDec('attrStock', $v['goodsNums']);
                            if (! $goodsRes || ! $attrRes) {
                                $stock = false;
                            }
                        }
                        if (! $goodsRes) {
                            $stock = false;
                        }
                    }
                    // $this->log_result($log_name,"【操作库存】:\n".$stock."\n");
                    if (! $stock) {
                        M()->rollback();
                        echo 'FAIL';
                        return;
                    } */
                    // 操作库存结束
                    if ($res && $E && $D) {
                        // 更新销量
                        // 商品销量增加
                        $goods = M('order_goods')->where(array(
                            'orderId' => $orderids
                        ))->field('goodsId,goodsNums')
                            ->find();
                        $F = M('goods')->where(array(
                            'goodsId' => $goods['goodsId']
                        ))->setField('saleCount', $goods['goodsNums']);

                        // $this->whiteList($ispayInfo);

                        // 订单日志记录
                        $morm = M('order_reminds');
                        $mlogo = M('log_orders');
                        $data = array();
                        $data["orderId"] = $orderids;
                        $data["logContent"] = '付款成功';
                        $data["logUserId"] = $userId;
                        $data["logType"] = 0;
                        $data["logTime"] = date('Y-m-d H:i:s');
                        $mlogo->add($data);

                        // 建立订单提醒
                        $data = array();
                        $data["orderId"] = $orderids;
                        $data["shopId"] = $ispayInfo['shopId'];
                        $data["userId"] = $userId;
                        $data["userType"] = 0;
                        $data["remindType"] = 0;
                        $data["createTime"] = date("Y-m-d H:i:s");
                        $morm->add($data);

                        M()->commit();
                        $this->keFu($orderids);
                        echo 'SUCCESS';
                        return;
                    } else {
                        $this->log_result($log_name, "【订单状态更新失败】:\n" . $orderids . "\n");
                        M()->rollback();
                        echo 'FAIL';
                        return;
                    }

                } else if ($orderType == 2) {
                    M()->startTrans();
                    $ordersPayid = M('orders_payid')->where(array(
                        'id' => $out_trade_no
                    ))->find();

                    //充值
                    $topUpInfo = M('top_up')->where(array('topupNo' => $ordersPayid['id']))->find();
                    //已经充值成功了
                    if ($topUpInfo['status'] == 1) {
                        echo 'SUCCESS';
                        return;
                    }

                    if ($topUpInfo['money'] == $payMoney) {
                        //更新余额
                        $D = M('top_up')->where(array('topupNo' => $ordersPayid['id']))->setField('status', 1);

                        //$this->log_result($log_name, "【更新充值状态】:\n" . M('top_up')->_sql() . "\n");

                        $userMoney = M('users')->where(array('userId' => $topUpInfo['userid']))->getField('userMoney');

                        $userMoney = $userMoney + $payMoney;


                        $A = M('users')->where(array('userId' => $topUpInfo['userid']))->setInc('userMoney', $payMoney);

                        // $this->log_result($log_name, "【更新用户余额】:\n" . M('users')->_sql() . "\n");


                        //$this->log_result($log_name, "【查询余额】:\n" . M('users')->_sql() . "\n");
                        // 余额变动记录


                        $B = $this->moneyRecord(3, $payMoney, $ordersPayid['orderId'], 1, $topUpInfo['userid'], $userMoney, '微信充值成功', 2);

                        //付款记录表
                        $C = $this->payRecord(2, $ordersPayid['orderId'], time(), $tradeNo, $payMoney, $topUpInfo['userid'], 1, $notify_id, $notify_time, $buyer_email);

                        if ($A && $B && $C && $D) {
                            M()->commit();
                            echo 'SUCCESS';
                            return;
                        } else {
                            $this->log_result($log_name, "【充值订单状态更新失败】:\n" . $ordersPayid['id'] . '==' . $payMoney . "\n");
                            M()->rollback();
                            echo 'FAIL';
                            return;
                        }

                    } else {
                        $this->log_result($log_name, "【充值面额对不上】:\n" . $topUpInfo['money'] . "\n");
                        M()->rollback();
                        echo 'FAIL';
                        return;
                    }
                }

                // 此处应该更新一下订单状态，商户自行增删操作
                // $this-> log_result($log_name,"【支付成功】:\n".$xml."\n");
            }

            // 商户自行增加处理流程,
            // 例如：更新订单状态
            // 例如：数据库操作
            // 例如：推送支付完成信息
        }
    }

    function log_result($file, $word)
    {
        $fp = fopen($file, "a");
        flock($fp, LOCK_EX);
        fwrite($fp, "执行日期：" . strftime("%Y-%m-%d-%H：%M：%S", time()) . "\n" . $word . "\n\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
