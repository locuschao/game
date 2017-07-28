<?php
namespace Game\Controller;

use Think\Controller;

class AutoExcController extends Controller
{

    public function autoCommitOrder()
    {
        $content = file_get_contents('auto.txt');
        $content=explode('|', $content);
        $time = time();
        /**
         * @author peng	
         * @date 2016-12
         * @descreption 增加-6拒绝退款的情况。
         */
        //$where['orderStatus'] = 2;
        $where['orderStatus'] = ['in',[2,-6,-8]];
        
        $where['orderFlag'] = 1;
        $where['isTixian'] = 0;
        
        //收货时间
        $shouHuoTime=$content[2]*3600;
//        $where['_string']="($time-paytime)>$shouHuoTime";
        $where['_string']="($time- UNIX_TIMESTAMP(fahuoTime))>$shouHuoTime";

        //$field = "orderId,needPay,shopId,userId,commission,userId";
        $field = "orderId,needPay,shopId,userId,commission,userId,orderStatus,orderNo,voucherIds,voucher_reduce";
        $order = M('orders')->where($where)->field($field)->select();
        
        M()->startTrans();
        $ids = '';
        $count = 0;
        
        foreach ($order as $k => $v) {
            /**
             * @author peng	
             * @date 2016-12
             * @descreption 已存在的在确认表中,或者已经被平台强制退款后则略过
             */
            if ($v['orderStatus']==-6 
                && M('refund')->where([
                    'orderid'=>$v['orderId']
                ])->find()['biz_status']!=3 
                && M('autocomfirm')->where([
                    'orderId'=>$v['orderId']
                ])->find()
            ) {
                unset($order[$k]);
                continue;
            } 

            // 2015.7.26
            //如果是会员商品，判断此用户是否已经成为代理，如果不是代理，则设置为代理
            $goodsId=M('order_goods')->where(array('orderId'=>$v['orderId']))->getFIeld('goodsId');
            $goodsType=M('goods')->where(array('goodsId'=>$goodsId))->getField('goodsType');
            $uInfo=M('users')->where(array('userId'=>$v['userId']))->field('isAgent,partnerId,rank')->find();
            if($goodsType==1){
                if($uInfo['isAgent']==0){
                    M('users')->where(array('userId'=>$v['userId']))->setField('isAgent',1);        //不如果不是代理，则设置为代理
                }
            }
            
            $shopInfo=M('shops')->where(array('shopId'=>$v['shopId']))->find();
            
            /**
             * @author peng
             * @date 2017-01
             * @descreption 
             */
             
            if($v['voucher_reduce']>0){
                $result1=M('data_tmp')->where(['key'=>'platform_money'])->setDec('value',$v['voucher_reduce']);
                $result2 = M('platfrom_account')->add(array(
                    'orderId'   =>  $v['orderId'],
                    'time'      =>  time(),
                    'income'    =>  -$v['voucher_reduce'],
                    'remark'    =>  '承担代金券消费',
                    'orderNo'   =>  $v['orderNo']
                ));
                
                $bizIncome=$v['needPay']+$v['voucher_reduce'];
            }else{
                $bizIncome=$v['needPay'];//商家收入
                $result1=true;
                $result2=true;
            }
            
            
            /**
             * @author peng	
             * @date 2017-01-05
             * @descreption 代理的返利已经做了修改，注释掉原来的返利计算
             */
            
            $agentMoney=0;
            /*
            if($uInfo['partnerId']>0&&$shopInfo['agentStatus']==1){//如果是代理的订单，且此店铺开通分销。则需要减去代理费用
                //没有购物车，一个订单只有一个商品
                $goodsNum=M('order_goods')->where(array('orderId'=>$v['orderId']))->getField('goodsNums');
                $agentMoney=$goodsNum*$v['commission'];
                $bizIncome=$v['needPay']-$agentMoney;
            }*/
            
            //$ids .= $v['orderId'] . ',';
            /**
             * @author peng	
             * @date 2016-12
             * @descreption 只更改已发货状态的订单为已完成
             */
            if($v['orderStatus']==2) $ids .= $v['orderId'] . ',';
            
        
            //商家收入=付款金额-佣金
            if($bizIncome == 0)             //魏永就  如果金额为0的话，则不进行数据操作
            {
                $tempRes = true;
            }else{
                $tempRes = M('shops')->where(array('shopId' => $v['shopId'] ))->setInc('bizMoney', $bizIncome);
            }
            $platRes = M('platfrom_account')->add(array(        //添加平台流水记录  魏永就
                'orderId' =>$v['orderId'],
                'time'     =>time(),
                'income'   => -$v['needPay'],
                'remark'   => '已确认收货，平台退款给商家',
                'orderNo'  => $v['orderNo']
            ));
            if($v['needPay'] == 0)
            {
                $dataTemRes = true;
            }else{
                $dataTemRes = M('data_tmp')->where('id=1')->setDec('value',$v['needPay']);      //更新平台暂时的余额  魏永就
            }
            if ($tempRes && $platRes && $dataTemRes) {
                $count ++;
            }
            
            //商家收入
            $inCome=array();
            $inCome['orderId']=$v['orderId'];
            $inCome['time']=date('Y-m-d H:i:s',time());
            $inCome['orderStatus']=3;
            $inCome['totalMoney']=$v['needPay'];
            
            /**
             * @author peng	
             * @date 2017-01-05
             * @descreption 已经修改规则注释掉
             */
            //是代理则扣代理费
            /*if($uInfo['partnerId']>0&&$shopInfo['agentStatus']==1){
                $inCome['agentMoney']=$agentMoney;
            }else{
                $inCome['agentMoney']=0;
            }*/
            
            $inCome['bizIncome']=$bizIncome;
            $inCome['remark']=$content[2].'小时自动确认收货';
            
            $re=M('autocomfirm')->add($inCome);
            // 订单最新日志
            $data = array();
            $data["orderId"] = $v['orderId'];
            $data["logContent"] = $content[2]."小时自动确认收货";
            $data["logUserId"] = $v['userId'];
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            $logRes = M('log_orders')->add($data);

        }
        $ids = trim($ids, ',');
        $signTime = date('Y-m-d H:i:s');
        
        /**
         * @author peng	
         * @date 2016-12
         * @descreption ids等于空不更改订单状态
         */
        if($ids!=''){
            $upRes = M('orders')->where(array('orderId' => array( 'in',$ids ) ))->setField(array( 'orderStatus' => 3,'signTime' => $signTime ));
        }else{
            $upRes=true;
        }
        if ($upRes != false && count($order) == $count && $result1 && $result2) {
            M()->commit();
            echo 0;
        } else {
            M()->rollback();
            echo - 1;
        }
    }
    
    // 自动执行
    public function autoRun_bak()
    {
        //fastcgi_finish_request();
        
        ignore_user_abort();
        // 即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
        set_time_limit(0);
        // 执行时间为无限制，php默认的执行时间是30秒，通过set_time_limit(0)可以让程序无限制的执行下去
        // 间隔时间
        $sleepTime = 60;
        $condition = true;
        while ($condition) {
            //0|2016-08-10 12:52:53|1|#自动收货配置:竖线分隔，开启或关闭。最后一次更新时间。多久收货一次单位小时
            $content = file_get_contents('auto.txt');
            $content=explode('|', $content);
            if ($content[0] == 0 || empty($content[0])) {
                $condition = false;
            }
            $this->autoCommitOrder();
            
            // 业务逻辑代码开始
            // 业务逻辑代码结束
            file_put_contents('auto.txt', $content[0] . '|' . date('Y-m-d H:i:s').'|'.$content[2].'|'.$content[3]);
            sleep($sleepTime);
            
        }
    }
    /**
     * @author peng
     * @date 2017-01
     * @descreption 改成linux计划任务每一分钟执行
     */
    public function autoRunByCrontab(){
        
            $this->notValidOrders();        //将超时未支付的订单转为无效订单
            
            /**
             * @author peng
             * @date 2017-03
             * @descreption 执行所有拓展的计划任务
             */
            D('Api/CronAppend')->excute();
            
            $content = file_get_contents('auto.txt');
            $content=explode('|', $content);
            if ($content[0] == 0 || empty($content[0])) {
                $condition = false;
            }else{
                $condition=true;
            }
            
            
            if($condition) $this->autoCommitOrder();
            
            // 业务逻辑代码开始
            // 业务逻辑代码结束
            file_put_contents('auto.txt', $content[0] . '|' . date('Y-m-d H:i:s').'|'.$content[2].'|'.$content[3]); 
    }
    
    
    /**
     * @author 魏永就
     * @date 17-1-18
     * @description 判断下单未支付，如果超过30分钟，则订单失效
     */
    private function notValidOrders()
    {
        $now = time();
        $voucherIds = M('orders')->where("orderStatus=-2  and $now - UNIX_TIMESTAMP(createTime) > 1800 ")->field('voucherIds')->select();
        $res = M('orders')->where("orderStatus=-2  and $now - UNIX_TIMESTAMP(createTime) > 1800 ")->setField(array(
            'orderStatus'=>-9,
            'isRead'=>0
        ));
        foreach ($voucherIds as $key=>$value)
        {
            if($value['voucherIds'] && $value['voucher_reduce']>0)
                M('userVoucher')->where(array('id'=>array('in',$value['voucherIds'])))->setInc('num',1);
        }
    }
}