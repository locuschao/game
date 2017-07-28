<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/10
 * Time: 16:40
 */
namespace Home\Controller;

class IntegralOrdersController extends BaseController
{

    /*
     * 跳转到订单确认页面
     */
    public function checkOrder()
    {
        $goodsId = (int) I('goodsId');
        $gcount = (int) I('gcount');
        // 获取指定id商品
        $goodsm = D('Home/Integral');
        $goods = $goodsm->getGoodsDetails($goodsId);
        $goods['gcount'] = $gcount;
        $this->assign('goods', $goods);
        // 获取地址列表
        $USER = session('WST_USER');
        $maddress = D('Home/UserAddress');
        $areaId2 = $this->getDefaultCity();
        $addressList = $maddress->queryByUserAndCity($USER['userId'], $areaId2);
        $this->assign("addressList", $addressList);
        // 支付方式
        $pm = D('Home/Payments');
        $payments = $pm->getList();
        $this->assign("payments", $payments);
        $m = D('Home/Areas');
        $areaList2 = $m->getDistricts($areaId2);
        $this->assign("areaList2", $areaList2);
        // 获取用户积分
        $USER = session('WST_USER');
        $userId = (int) $USER['userId'];
        $userm = M('Users');
        $userScore = $userm->where('userId=' . $userId)->getField('userScore');
        $this->assign('userScore', $userScore);
        $this->display('integral/orders/check_order');
    }

    /**
     * 提交订单信息
     */
    public function submitOrder()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $goodsmodel = D('Home/Integral');
        $morders = D('Home/IntegralOrders');
        $totalMoney = 0;
        $totalCnt = 0;
        $userId = (int) $USER['userId'];
        $consigneeId = (int) I("consigneeId");
        $payway = (int) I("payway");
        $isself = (int) I("isself");
        $needreceipt = (int) I("needreceipt");
        $orderunique = I("orderunique");
        $goodsId = (int) I("goodsId");
        $gcount = (int) I('gcount');
        $catgoods = array();
        $order = array();
        if (empty($order)) {
            $goods = $goodsmodel->getGoodsDetails($goodsId);
            // 核对商品是否符合购买要求
            if (empty($goods)) {
                $this->assign("fail_msg", '对不起，该商品不存在!');
                $this->display('integral/orders/order_fail');
                exit();
            }
            if ($goods['goodsStock'] <= 0) {
                $this->assign("fail_msg", '对不起，商品' . $goods['goodsName'] . '库存不足!');
                $this->display('integral/orders/order_fail');
                exit();
            }
            $totalMoney = $goods['shopPrice'] * $gcount;
            $ordersInfo = $morders->addOrders($userId, $goodsId, $gcount, $totalMoney, $consigneeId, $needreceipt, $isself, $payway, $orderunique, $goods['shopPrice']);
            $orderNos = $ordersInfo["orderNos"];
            $this->assign("torderIds", implode(",", $ordersInfo["orderIds"]));
            $this->assign("orderInfos", $ordersInfo["orderInfos"]);
            $this->assign("isMoreOrder", (count($ordersInfo["orderInfos"]) > 0) ? 1 : 0);
            $this->assign("orderNos", implode(",", $orderNos));
            $this->assign("totalMoney", $totalMoney);
            if ($payway == 4) {
                $this->display('integral/orders/order_success');
            } else {
                $orderIds = $ordersInfo["orderIds"];
                $this->redirect("Payments/toPay", array(
                    "orderIds" => implode(",", $orderIds)
                )); // 直接跳转，不带计时后跳转
            }
        } else {
            $this->display('integral/orders/check_order');
        }
    }

    /**
     * 订单詳情-买家专用
     */
    public function getOrderInfo()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/IntegralOrders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->getOrderDetails($obj);
        // 将积分设置为int
        $rs['order']['totalMoney'] = (int) $rs['order']['totalMoney'];
        foreach ($rs['goodsList'] as $k => $v) {
            $rs['goodsList'][$k]['shopPrice'] = (int) $v['shopPrice'];
        }
        $this->assign("orderInfo", $rs);
        $this->display("integral/orders/order_details");
    }

    /**
     * 订单詳情
     */
    public function getOrderDetails()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/IntegralOrders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["shopId"] = (int) $USER['shopId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->getOrderDetails($obj);
        if ($rs['order']['payType'] == '4') {
            // 将积分设置为int
            $rs['order']['totalMoney'] = (int) $rs['order']['totalMoney'];
            foreach ($rs['goodsList'] as $k => $v) {
                $rs['goodsList'][$k]['shopPrice'] = (int) $v['shopPrice'];
            }
        }
        $this->assign("orderInfo", $rs);
        $this->display("integral/orders/order_detail");
    }

    /**
     * 取消订单
     */
    public function orderCancel()
    {
        $this->isUserAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/IntegralOrders');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderId"] = I("orderId");
        $rs = $morders->orderCancel($obj);
        $this->ajaxReturn($rs);
    }
}
?>