<?php
namespace Game\Controller;

use Think\Controller;

class AliPayController extends Controller
{
    //在类初始化方法中，引入相关类库
    public function _initialize()
    {
        header('Content-type:text/html;charset=utf-8');
        vendor('WapAlipay.alipay_core');
        vendor('WapAlipay.alipay_rsa');
        vendor('WapAlipay.alipay_notify');
        vendor('WapAlipay.alipay_submit');
    }

    //doalipay方法
    /*该方法其实就是将接口文件包下alipayapi.php的内容复制过来
      然后进行相关处理
    */
    public function doalipay()
    {

        /*********************************************************
         * 把alipayapi.php中复制过来的如下两段代码去掉，
         * 第一段是引入配置项，
         * 第二段是引入submit.class.php这个类。
         * 为什么要去掉？？
         * 第一，配置项的内容已经在项目的Config.php文件中进行了配置，我们只需用C函数进行调用即可；
         * 第二，这里调用的submit.class.php类库我们已经在PayAction的_initialize()中已经引入；所以这里不再需要；
         *****************************************************/
        // require_once("alipay.config.php");
        // require_once("lib/alipay_submit.class.php");

        //这里我们通过TP的C函数把配置项参数读出，赋给$alipay_config；
        $alipay_config = C('alipay_config');

        /**************************请求参数**************************/
        $payment_type = "1"; //支付类型 //必填，不能修改
        $notify_url = C('alipay.notify_url'); //服务器异步通知页面路径
        $return_url = C('alipay.return_url'); //页面跳转同步通知页面路径
        $seller_email = C('alipay.seller_email');//卖家支付宝帐户必填
        $out_trade_no = $_POST['trade_no'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
        $subject = $_POST['ordsubject'];  //订单名称 //必填 通过支付页面的表单进行传递
        $total_fee = $_POST['ordtotal_fee'];   //付款金额  //必填 通过支付页面的表单进行传递
        $body = $_POST['ordbody'];  //订单描述 通过支付页面的表单进行传递
        $show_url = $_POST['ordshow_url'];  //商品展示地址 通过支付页面的表单进行传递
        $anti_phishing_key = "";//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
        $exter_invoke_ip = get_client_ip(); //客户端的IP地址
        /************************************************************/

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "alipay.wap.create.direct.pay.by.user",
            "partner" => trim($alipay_config['partner']),
            "payment_type" => $payment_type,
            "notify_url" => $notify_url,
            "return_url" => $return_url,
            "seller_id" => $seller_email,//原来是email 新版 要改成id
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "show_url" => $show_url,
            "anti_phishing_key" => $anti_phishing_key,
            // "exter_invoke_ip" => $exter_invoke_ip,
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);

        $html_text = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
        echo $html_text;
    }

    /******************************
     * 服务器异步通知页面方法
     * 其实这里就是将notify_url.php文件中的代码复制过来进行处理
     *******************************/
    public function notifyurl()
    {
        //logResult('data/payLog.txt', serialize($_POST));die;
        /*
        同理去掉以下两句代码；
        */
        //这里还是通过C函数来读取配置项，赋值给$alipay_config
        $alipay_config = C('alipay_config');
        //计算得出通知验证结果


        if ($_POST ['trade_status'] == 'TRADE_SUCCESS'&&$_POST['seller_id']==C('alipay_config.partner')){
            goto noValidtae;
        }

        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            $out_trade_no = $_POST['out_trade_no'];      //商户订单号
            $trade_no = $_POST['trade_no'];          //支付宝交易号
            $trade_status = $_POST['trade_status'];      //交易状态
            $total_fee = $_POST['total_fee'];         //交易金额
            $notify_id = $_POST['notify_id'];         //通知校验ID。
            $notify_time = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_email = $_POST['buyer_email'];       //买家支付宝帐号；
            $parameter = array(
                "out_trade_no" => $out_trade_no, //商户订单编号；
                "trade_no" => $trade_no,     //支付宝交易号；
                "total_fee" => $total_fee,    //交易金额；
                "trade_status" => $trade_status, //交易状态
                "notify_id" => $notify_id,    //通知校验ID。
                "notify_time" => $notify_time,  //通知的发送时间。
                "buyer_email" => $buyer_email,  //买家支付宝帐号；
            );
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                //
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                noValidtae:
                $log_name = 'data/payLog.txt';
                /**
                 * $out_trade_no =$_POST['out_trade_no'];//订单号
                 * $tradeNo = $_POST['trade_no'];//流水号
                 * $payMoney = $_POST['total_fee'];
                 * $notify_id = $_POST ['notify_id'];
                 * $time=strtotime($_POST ['notify_time']);
                 * $notify_time = $_POST ['notify_time'];
                 * $buyer_email = $_POST ['buyer_email']; // 买家帐号
                 */

                $tradeNo = $_POST['trade_no']; // 流水号
                $payMoney = $_POST['total_fee'];
                /*
                $out_trade_no_text = $_POST['out_trade_no'];
                $temp = explode('_', $out_trade_no_text);
                $out_trade_no = $temp[1];*/

                $out_trade_no = $_POST['out_trade_no'];//订单号payId
                $notify_id = $_POST ['notify_id'];
                $time = strtotime($_POST ['notify_time']);
                $notify_time = $_POST ['notify_time'];
                $buyer_email = $_POST ['buyer_email']; // 买家帐号

                //判断订单类型，1为订单，2为充值
                $orderType = M('orders_payid')->where(array(
                    'id' => $out_trade_no
                ))->getField('type');
                //日志文件

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
                    $saveData['payType'] = 1; // 支付宝支付
                    $map['orderId'] = $orderids;
                    $res = M('orders')->where($map)
                        ->data($saveData)
                        ->save();
                    logResult($log_name,"【修改订单状态】:\n".$res."\n");
                    $userId = $ispayInfo['userId'];
                    if (!$userId) {
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
                     logResult($log_name,"【操作积分】:\n".$D."\n");

                    //付款记录表
                    $E = $this->payRecord(1, $orderNos, time(), $tradeNo, $payMoney, $userId, 0, $notify_id, $notify_time, $buyer_email);
                    logResult($log_name,"【操作付款记录】:\n".$E."\n");

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
                        logResult($log_name, "【订单状态更新失败】:\n" . $orderids . "\n");
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

                        //logResult($log_name, "【更新充值状态】:\n" . M('top_up')->_sql() . "\n");

                        $userMoney = M('users')->where(array('userId' => $topUpInfo['userid']))->getField('userMoney');

                        $userMoney = $userMoney + $payMoney;


                        $A = M('users')->where(array('userId' => $topUpInfo['userid']))->setInc('userMoney', $payMoney);

                        // logResult($log_name, "【更新用户余额】:\n" . M('users')->_sql() . "\n");


                        //logResult($log_name, "【查询余额】:\n" . M('users')->_sql() . "\n");
                        // 余额变动记录


                        $B = $this->moneyRecord(3, $payMoney, $ordersPayid['orderId'], 1, $topUpInfo['userid'], $userMoney, '支付宝充值成功', 1);

                        //付款记录表
                        $C = $this->payRecord(1, $ordersPayid['orderId'], time(), $tradeNo, $payMoney, $topUpInfo['userid'], 1, $notify_id, $notify_time, $buyer_email);

                        if ($A && $B && $C && $D) {
                            M()->commit();
                            echo 'SUCCESS';
                            return;
                        } else {
                            logResult($log_name, "【充值订单状态更新失败】:\n" . $ordersPayid['id'] . '==' . $payMoney . "\n");
                            M()->rollback();
                            echo 'FAIL';
                            return;
                        }

                    } else {
                        logResult($log_name, "【充值面额对不上】:\n" . $topUpInfo['money'] . "\n");
                        M()->rollback();
                        echo 'FAIL';
                        return;
                    }
                }
            }
            // echo "success";        //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }

    /*
        页面跳转处理方法；
        这里其实就是将return_url.php这个文件中的代码复制过来，进行处理；
        */
    public function returnurl()
    {
        //头部的处理跟上面两个方法一样，这里不罗嗦了！
        $alipay_config = C('alipay_config');
		
        $alipayNotify = new AlipayNotify($alipay_config);//计算得出通知验证结果
        $verify_result = $alipayNotify->verifyReturn();
        if ($verify_result) {
            //验证成功
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $out_trade_no = $_GET['out_trade_no'];      //商户订单号
            $trade_no = $_GET['trade_no'];          //支付宝交易号
            $trade_status = $_GET['trade_status'];      //交易状态
            $total_fee = $_GET['total_fee'];         //交易金额
            $notify_id = $_GET['notify_id'];         //通知校验ID。
            $notify_time = $_GET['notify_time'];       //通知的发送时间。
            $buyer_email = $_GET['buyer_email'];       //买家支付宝帐号；

            $parameter = array(
                "out_trade_no" => $out_trade_no,      //商户订单编号；
                "trade_no" => $trade_no,          //支付宝交易号；
                "total_fee" => $total_fee,         //交易金额；
                "trade_status" => $trade_status,      //交易状态
                "notify_id" => $notify_id,         //通知校验ID。
                "notify_time" => $notify_time,       //通知的发送时间。
                "buyer_email" => $buyer_email,       //买家支付宝帐号
            );

            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                if (!checkorderstatus($out_trade_no)) {
                    orderhandle($parameter);  //进行订单处理，并传送从支付宝返回的参数；
                }
                $this->redirect(C('alipay.successpage'));//跳转到配置项中配置的支付成功页面；
            } else {
                echo "trade_status=" . $_GET['trade_status'];
                $this->redirect(C('alipay.errorpage'));//跳转到配置项中配置的支付失败页面；
            }
        } else {
            //验证失败
            //如要调试，请看alipay_notify.php页面的verifyReturn函数
            echo "支付失败！";
        }
    }



    //在线交易订单支付处理函数
    //函数功能：根据支付接口传回的数据判断该订单是否已经支付成功；
    //返回值：如果订单已经成功支付，返回true，否则返回false；
    public function checkorderstatus($ordid)
    {
        $Ord = M('Orderlist');
        $ordstatus = $Ord->where('ordid=' . $ordid)->getField('ordstatus');
        if ($ordstatus == 1) {
            return true;
        } else {
            return false;
        }
    }
    //处理订单函数
    //更新订单状态，写入订单支付后返回的数据
    public function orderhandle($parameter)
    {
        $ordid = $parameter['out_trade_no'];
        $data['payment_trade_no'] = $parameter['trade_no'];
        $data['payment_trade_status'] = $parameter['trade_status'];
        $data['payment_notify_id'] = $parameter['notify_id'];
        $data['payment_notify_time'] = $parameter['notify_time'];
        $data['payment_buyer_email'] = $parameter['buyer_email'];
        $data['ordstatus'] = 1;
        $Ord = M('Orderlist');
        $Ord->where('ordid=' . $ordid)->save($data);
    }
    /*-----------------------------------
   2013.8.13更正
   下面这个函数，其实不需要，大家可以把他删掉，
   具体看我下面的修正补充部分的说明
   ------------------------------------*/
    //获取一个随机且唯一的订单号；
    public function getordcode()
    {
        $Ord = M('Orderlist');
        $numbers = range(10, 99);
        shuffle($numbers);
        $code = array_slice($numbers, 0, 4);
        $ordcode = $code[0] . $code[1] . $code[2] . $code[3];
        $oldcode = $Ord->where("ordcode='" . $ordcode . "'")->getField('ordcode');
        if ($oldcode) {
            getordcode();
        } else {
            return $ordcode;
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