<?php
namespace GameAPI\Controller;
use Think\Controller;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/12 0012
 * Time: 10:36
 */
class AlipayNoticeController extends Controller
{

    public  function index(){

        logResult('data/payLog.txt','测试结果');

    }


    //支付宝订单异步通知处理
    public function payNotice()
    {


        vendor('Alipay.Corefunction');
        vendor('Alipay.Notify');
        vendor('Alipay.Rsa');
        // 计算得出通知验证结果
        $alipayNotify = new \AlipayNotify (C('alipay_config'));
        $verify_result = $alipayNotify->verifyNotify();
        if ($_POST ['trade_status'] == 'TRADE_SUCCESS'){
            goto noValidtae;
        }
        if ($verify_result) { // 验证成功
            // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // 请在这里加上商户的业务逻辑程序代

            // ——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            // 获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            if ($_POST ['trade_status'] == 'TRADE_FINISHED') {
                // 判断该笔订单是否在商户网站中已经做过处理
                // 如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                // 如果有做过处理，不执行商户的业务程序

                // 注意：
                // 退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
                // 请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的

                // 调试用，写文本函数记录程序运行情况是否正常
                // logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            } else if ($_POST ['trade_status'] == 'TRADE_SUCCESS') {
                // 判断该笔订单是否在商户网站中已经做过处理
                // 如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                // 如果有做过处理，不执行商户的业务程序
                // M('alipaytest')->data(array('out_trade_no'=>$out_trade_no,'trade_no'=>$trade_no,'time'=>date('Y-m-d H:i:s')))->add();
                // 注意：
                // 付款完成后，支付宝系统发送该交易状态通知
                // 请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的

                // 调试用，写文本函数记录程序运行情况是否正常
                noValidtae:

                logResult('data/payLog.txt',serialize($_POST));

                /**
                $out_trade_no =$_POST['out_trade_no'];//订单号
                $tradeNo = $_POST['trade_no'];//流水号
                $payMoney = $_POST['total_fee'];
                $notify_id = $_POST ['notify_id'];
                $time=strtotime($_POST ['notify_time']);
                $notify_time = $_POST ['notify_time'];
                $buyer_email = $_POST ['buyer_email']; // 买家帐号
                  */

                $tradeNo =$_POST['trade_no']; // 流水号
                $payMoney = $_POST['total_fee'];
                /*
                $out_trade_no_text = $_POST['out_trade_no'];
                $temp = explode('_', $out_trade_no_text);
                $out_trade_no = $temp[1];*/

                $out_trade_no=$_POST['out_trade_no'];//订单号payId
                $notify_id = $_POST ['notify_id'];
                $time = strtotime($_POST ['notify_time']);
                $notify_time =  $_POST ['notify_time'];
                $buyer_email =$_POST ['buyer_email']; // 买家帐号


                //判断订单类型，1为订单，2为充值
                $orderType = M('orders_payid')->where(array(
                    'id' => $out_trade_no
                ))->getField('type');
                //日志文件
                $log_name='data/payLog.txt';
                //订单付款
                if($orderType==1){
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
                    if ($ispayInfo['isPay']==1) {
                        echo 'SUCCESS';
                        return;
                    }

                    $saveData['isPay'] = 1;
                    $saveData['orderStatus'] = 1;
                    $saveData['paytime'] = time();
                    $saveData['payType'] = 1; // 支付宝支付
                    $map['orderId'] = $orderids;
                    $res = M('orders')->where($map)
                        ->data($saveData)
                        ->save();

                    $userId = $ispayInfo['userId'];
                    if (! $userId) {
                        echo 'SUCCESS';
                        return;
                    }
                    // logResult($log_name,"【用户ID】:\n".$userId."\n");
                    $userMoney = M('users')->where(array(
                        'userId' => $userId
                    ))->getField('userMoney');
                    // 积分变动记录->移到交易成功后
                    // $C=M('users')->where(array('userId'=>session('oto_userId')))->setInc('userScore',floor($needPay));

                    //余额变动记录
                    $D = $this->moneyRecord(1, $payMoney, $orderNos, 1, $userId, $userMoney, '支付宝支付成功', 1);

                    //$D = $this->scoreRecord(1, $payMoney, $orderNos, 1, $userId);
                    // logResult($log_name,"【操作积分】:\n".$D."\n");

                    //付款记录表
                    $E = $this->payRecord(1, $orderNos, time(), $tradeNo, $payMoney, $userId, 0, $notify_id, $notify_time, $buyer_email);
                    // logResult($log_name,"【操作付款记录】:\n".$E."\n");
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
                    // logResult($log_name,"【操作库存】:\n".$stock."\n");
                    if (! $stock) {
                        M()->rollback();
                        echo 'FAIL';
                        return;
                    } */
                    // 操作库存结束
                    if ($res&&$E&&$D) {
                        // 更新销量
                        // 商品销量增加
                        $goods = M('order_goods')->where(array(
                            'orderId' => $orderids
                        )) ->field('goodsId,goodsNums')
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
                        logResult($log_name, "【订单状态更新失败】:\n" . $orderids . "\n");
                        M()->rollback();
                        echo 'FAIL';
                        return;
                    }

                }else if($orderType==2){
                    M()->startTrans();
                    $ordersPayid = M('orders_payid')->where(array(
                        'id' => $out_trade_no
                    ))->find();

                    //充值
                    $topUpInfo=M('top_up')->where(array('topupNo'=>$ordersPayid['id']))->find();
                    //已经充值成功了
                    if($topUpInfo['status']==1){
                        echo 'SUCCESS';
                        return;
                    }

                    if($topUpInfo['money']==$payMoney){
                        //更新余额
                        $D=M('top_up')->where(array('topupNo'=>$ordersPayid['id']))->setField('status',1);

                        //logResult($log_name, "【更新充值状态】:\n" . M('top_up')->_sql() . "\n");

                        $userMoney=M('users')->where(array('userId'=>$topUpInfo['userid']))->getField('userMoney');

                        $userMoney=$userMoney+$payMoney;



                        $A=M('users')->where(array('userId'=>$topUpInfo['userid']))->setInc('userMoney',$payMoney);

                        // logResult($log_name, "【更新用户余额】:\n" . M('users')->_sql() . "\n");



                        //logResult($log_name, "【查询余额】:\n" . M('users')->_sql() . "\n");
                        // 余额变动记录


                        $B = $this->moneyRecord(3, $payMoney, $ordersPayid['orderId'], 1, $topUpInfo['userid'], $userMoney, '支付宝充值成功', 1);

                        //付款记录表
                        $C = $this->payRecord(1, $ordersPayid['orderId'], time(), $tradeNo, $payMoney, $topUpInfo['userid'], 1, $notify_id, $notify_time, $buyer_email);

                        if($A&&$B&&$C&&$D){
                            M()->commit();
                            echo 'SUCCESS';
                            return;
                        }else{
                            logResult($log_name, "【充值订单状态更新失败】:\n" . $ordersPayid['id'] .'=='.$payMoney. "\n");
                            M()->rollback();
                            echo 'FAIL';
                            return;
                        }

                    }else{
                        logResult($log_name, "【充值面额对不上】:\n" . $topUpInfo['money'] . "\n");
                        M()->rollback();
                        echo 'FAIL';
                        return;
                    }
                }


                //结束
            }

            // ——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            //echo 'SUCCESS';// 请不要修改或删除

            // ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {

            // 验证失败
            // 调试用，写文本函数记录程序运行情况是否正常
            echo 'FAIL';
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
        $log_name='';
        //$log_name = "Public/hey.txt"; // log文件路径
        //logResult($log_name, "【结果】:\n" . $db->_sql() . "\n");
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

}