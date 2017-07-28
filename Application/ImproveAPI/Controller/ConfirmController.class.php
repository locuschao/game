<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class ConfirmController extends BaseController
{  
    /**
     * @api {post} Confirm/balancePay 余额支付
     * @apiParam {string} orderId 订单编号
     * @apiParam {string} pwd 支付密码
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
   
    public function balancePay()
    {   
        parent::isLogin();
        $post = getData();
        $userId = $_SESSION['userId'];

        $info = M('users')->where(array(
            'userId' => $_SESSION['userId']
        ))
            ->field('loginSecret,userMoney,payPwd')
            ->find();
        $pwd = $post['pwd'];
       
        $ispayInfo = M('orders')->where(array(
            'orderNo' => $post['orderId']
        ))
            ->field('isPay,orderNo,orderId,shopId,needPay,shopId,userId,userAddress,qq,roleName,profession,userPhone')
            ->select();
        $orderId = $ispayInfo[0]['orderId'];    
        
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
                'info' => '订单状态已经改变'
            ));
            return;
        }
        $needPay = M('orders')->where(array(
            'orderId' => $orderId
        ))->sum('needPay');

        if (md5($pwd . $info['loginSecret']) != $info['payPwd']) {
            $this->ajaxReturn(array(
                'status' => -2,
                'info' => '支付密码错误'
            ));
            return;
        }
        if($needPay<0){
            $this->ajaxReturn(array(
                'status' => -4,
                'info' => '支付金额异常'
            ));
            return;
        }
        if ($needPay > $info['userMoney']) {
            $this->ajaxReturn(array(
                'status' => -1,
                'info' => '余额不足'
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
            'orderId' => $orderId
        ))
            ->data($saveData)
            ->save();
       
        // 操作用户金额
        if($needPay == 0)       //魏永就  如果需要付款的金额为0，则不需要对用户的余额操作
        {
            $A = true;
        }else{
            $A = M('users')->where(array(
                'userId' => $userId
            ))->setDec('userMoney', $needPay);
        }


        // 余额变动记录
        $B = true;
        $balance = $info['userMoney'];
        foreach ($ispayInfo as $k => $v) {
            $balance = $balance - $v['needPay'];
            $tempRes = $this->moneyRecord(1, $v['needPay'], $v['orderNo'], 0, $userId, $balance, '', 0);
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
        // $C=M('users')->where(array('userId'=>$userId))->setInc('userScore',floor($needPay));

        //$D = $this->scoreRecord(1, $needPay, $orderNos, 1, $userId);

        $E = $this->payRecord(0, $orderNos, time(), '', $needPay, $userId, 0, '', '', '');

        // 操作库存
        $ogField = "goodsId,goodsNums,goodsAttrId";
        $order_goods = M('order_goods')->where(array(
            'orderId' => $orderId
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
                'info' => '支付失败'
            ));
            M()->rollback();
            return;
        }
        
        // 操作库存结束
        if ($res && $A && $B && $E) {
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
                $data["logUserId"] = $userId;
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                $mlogo->add($data);

                // 建立订单提醒
                $data = array();
                $data["orderId"] = $v['orderId'];
                $data["shopId"] = $v['shopId'];
                $data["userId"] = $userId;
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
                'status' => 1,
                'info' => '支付成功',
             
            ));
        } else {
            M()->rollback();
            $this->ajaxReturn(array(
                'status' => -3,
                'info' => '支付失败'
            ));
        }
    }
   
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
    
}