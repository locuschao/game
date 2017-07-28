<?php
namespace Home\Controller;

/**
 * 支付控制器
 */
class PaymentsController extends BaseController
{

    /**
     * 获取支付宝URL
     */
    public function getAlipayURL()
    {
        $this->isUserLogin();
        
        $morders = D('Home/Orders');
        $USER = session('WST_USER');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderIds"] = I("orderIds");
        $data = $morders->checkOrderPay($obj);
        
        if ($data["status"] == 1) {
            $m = D('Home/Payments');
            $url = $m->getAlipayUrl();
            $data["url"] = $url;
        }
        
        $this->ajaxReturn($data);
    }

    public function getWeixinURL()
    {
        $this->isUserLogin();
        
        $morders = D('Home/Orders');
        $USER = session('WST_USER');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderIds"] = I("orderIds");
        $data = $morders->checkOrderPay($obj);
        
        if ($data["status"] == 1) {
            $m = D('Home/Payments');
            $pkey = $obj["userId"] . "@" . $obj["orderIds"];
            $data["url"] = U('Home/WxNative2/createQrcode', array(
                "pkey" => base64_encode($pkey)
            ));
        }
        
        $this->ajaxReturn($data);
    }

    /**
     * 支付
     */
    public function toPay()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Orders');
        // 支付方式
        $pm = D('Home/Payments');
        $payments = $pm->getList();
        $this->assign("payments", $payments);
        $orderIds = I("orderIds");
        $obj["orderIds"] = $orderIds;
        $data = $morders->getPayOrders($obj);
        $orders = $data["orders"];
        $needPay = $data["needPay"];
        $this->assign("orderIds", $orderIds);
        $this->assign("payway", I("payway"));
        // 判断是否有支付密码
        if (I("payway") == 3) {
            $m = M('Users');
            $payPwd = $m->where('userId =' . $USER['userId'])->getField('payPwd');
            if (is_null($payPwd)) {
                echo "<script>alert('请先设置支付密码!')</script>";
                $this->redirect("Users/toEditPass");
            }
        }
        $this->assign("orders", $orders);
        $this->assign("needPay", $needPay);
        $this->assign("orderCnt", count($orders));
        $this->display('payment/order_pay');
    }

    /**
     * 支付结果同步回调
     */
    function response()
    {
        $request = $_GET;
        unset($request['_URL_']);
        $pay_res = D('Payments')->notify($request);
        if ($pay_res['status']) {
            $this->redirect("Orders/queryByPage");
            // 支付成功业务逻辑
        } else {
            $this->error('支付失败');
        }
    }

    /**
     * 支付结果异步回调
     */
    function notify()
    {
        $pm = D('Home/Payments');
        $request = $_POST;
        $pay_res = $pm->notify($request);
        if ($pay_res['status']) {
            
            // 商户订单号
            $obj = array();
            $obj["trade_no"] = $_POST['trade_no'];
            $obj["out_trade_no"] = $_POST['out_trade_no'];
            $obj["total_fee"] = $_POST['total_fee'];
            $obj["userId"] = $_POST['extra_common_param'];
            // 支付成功业务逻辑
            
            $payments = $pm->complatePay($obj);
            echo 'success';
        } else {
            
            echo 'fail';
        }
    }
    // 验证支付密码
    public function payPwd()
    {
        $rs = array(
            'status' => - 1
        );
        $this->isUserLogin();
        $USER = session('WST_USER');
        $userId = (int) $USER['userId'];
        $m = M('Users');
        $users = $m->where('userId =' . $userId)->find();
        if ($users['payPwd'] == md5(I('password') . $users['loginSecret']) && $users['userFlag'] == 1) {
            $rs['status'] = 1;
        }
        $this->ajaxReturn($rs);
    }
    // 余额支付
    public function yuePay()
    {
        $data = array(
            'status' => - 1
        );
        $this->isUserLogin();
        $morders = D('Home/Orders');
        $USER = session('WST_USER');
        $obj["userId"] = (int) $USER['userId'];
        $obj["orderIds"] = I("orderIds");
        $data = $morders->checkOrderPay($obj);
        $needPay = I('needPay');
        if ($data["status"] == 1) {
            $usersModel = M('Users');
            $user = $usersModel->where('userId =' . $USER['userId'])->find();
            $lastMoney = $user['userMoney'] - $needPay;
            if ($lastMoney < 0) {
                // 余额不足
                $data['status'] = - 3;
            } else {
                // 扣除余额
                $rd = $usersModel->where('userId =' . $USER['userId'])->setField('userMoney', $lastMoney);
                // 修改订单状态
                $obj["out_trade_no"] = I("orderIds");
                $pm = D('Payments');
                $pm->complatePay($obj);
                $data['status'] = 2;
            }
        }
        $this->ajaxReturn($data);
    }

    public function paySuccess()
    {
        $this->display("payment/pay_success");
    }
}
;
?>