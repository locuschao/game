<?php
namespace GameAPI\Controller;

use Think\Controller;

class AutoExcController extends Controller
{

    public function autoCommitOrder()
    {
        $time = time();
        $where['orderStatus'] = 2;
        $where['orderFlag'] = 1;
        $where['isTixian'] = 0;
        // $where['_string']="($time-paytime)>10800";
        $field = "orderId,needPay,shopId,userId,commission,userId";
        $order = M('orders')->where($where)->field($field)->select();
        M()->startTrans();
        $ids = '';
        $count = 0;
        foreach ($order as $k => $v) {
            
            // 2015.7.26
                //如果是会员商品，判断此用户是否已经成为代理，如果不是代理，则设置为代理
                $goodsId=M('order_goods')->where(array('orderId'=>$v['orderId']))->getFIeld('goodsId');
                $goodsType=M('goods')->where(array('goodsId'=>$goodsId))->getField('goodsType');
                $uInfo=M('users')->where(array('userId'=>$v['userId']))->field('isAgent,partnerId')->find();;
                if($goodsType==1){
                    if($uInfo['isAgent']==0){
                        M('users')->where(array('userId'=>$v['userId']))->setField('isAgent',1);
                    }
                }
            
                $bizIncome=$v['needPay'];//商家收入
                if($uInfo['partnerId']==1){
                    $bizIncome=$v['needPay']-$v['commission'];
                }
                $ids .= $v['orderId'] . ',';
                //商家收入=付款金额-佣金
                $tempRes = M('shops')->where(array('shopId' => $v['shopId'] ))->setInc('bizMoney', $bizIncome);
                if ($tempRes != false) {
                    $count ++;
                }
                
                // 订单最新日志
                $data = array();
                $data["orderId"] = $v['orderId'];
                $data["logContent"] = "3小时自动确认收货";
                $data["logUserId"] = $v['userId'];
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                M('log_orders')->add($data);
                
          }
        $ids = trim($ids, ',');
        $signTime = date('Y-m-d H:i:s');
        $upRes = M('orders')->where(array('orderId' => array( 'in',$ids ) ))->setField(array( 'orderStatus' => 3,'signTime' => $signTime ));
        if ($upRes != false && count($order) == $count) {
            M()->commit();
            echo 0;
        } else {
            M()->rollback();
            echo - 1;
        }
    }
    
    // 自动执行
    public function autoText()
    {
        ignore_user_abort();
        // 即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
        set_time_limit(0);
        // 执行时间为无限制，php默认的执行时间是30秒，通过set_time_limit(0)可以让程序无限制的执行下去
        // 间隔时间
        $sleepTime = 60;
        $condition = true;
        while ($condition) {
            $content = file_get_contents('autoSetting.txt');
            if ($content == 0 || empty($content)) {
                $condition = false;
            }
            $this->autoCommitOrder();
            // 业务逻辑代码开始
            // 业务逻辑代码结束
            file_put_contents('auto.txt', $content . '|' . date('Y-m-d H:i:s'));
            sleep($sleepTime);
        }
    }
}