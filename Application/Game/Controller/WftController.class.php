<?php
/**
 * 魏永就
 * 接受微富通的通知
 */
namespace Game\Controller;
use Think\Controller;
class WftController extends  Controller
{
    function xmlToArray($xml){
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    public function notice()
    {
        $xml = file_get_contents('php://input');

        $arr = $this->xmlToArray($xml);        //将xml 字符串转化为数组
        
        $real_pay_money = $arr['total_fee']/100; //author: peng descreption:实际支付金额,单位元
        
        if($arr)
        {

            if($arr['status'] == 0 && $arr['result_code'] == 0 && $arr['pay_result'] == 0)
            {

                $payInfo = M('ordersPayid')->where('pid='.$arr['out_trade_no'])->field('type,orderId')->find();      //对ordersPayid 查找
                if(!$payInfo)        //判断单号是否存在
                {
                    echo 'fail3';
                    die;
                }
                if($payInfo['type'] == 1)
                {
                    $orderFlag = M('orders')->where(array(                  //对表orders 查询
                        'orderId'=>$payInfo['orderId'],
                        'isPay'   => 1,
                    ))->find();
                   if($orderFlag)       //判断通知是否重复
                   {
                       echo 'success';
                       die;
                   }
                    
                    M()->startTrans();
                    $orderInfo = M('orders')->where('orderId='.$payInfo['orderId'])->field('needPay,userId,orderNo,shopId')->find();      //对orders表查找
                    
                    /**
                     * @author peng
                     * @date 2017-04
                     * @descreption 加上订单应付金额跟实际支付相比
                     */
                    if($orderInfo['needPay'] != $real_pay_money){
                        writelog('wftPay','订单id:'.$payInfo['orderId'].'支付异常，实际支付:'.$real_pay_money);
                        writelog('wftPay','行：'.__LINE__);
                        exit;
                    }
                    
                    $orderRes = M('orders')->where('orderId='.$payInfo['orderId'])->setField(array(                          //对orders表更新
                        'isPay'      =>  1,
                        'payType'   =>   2,
                        'paytime'   =>   time(),
                        'orderStatus' => 1
                    ));
                    $userMoney = M('users')->where('userId='.$orderInfo['userId'])->field('userMoney')->find();                 //对user表查找用户余额
                    $moneyRecordRes = M('moneyRecord')->add(array(                                                                      //对 meoney_record 更新
                        'type'      =>      1,
                        'money'     =>      $orderInfo['needPay'],
                        'time'      =>      time(),
                        'ip'         =>      get_client_ip(),
                        'orderNo'   =>      $orderInfo['orderNo'],
                        'IncDec'    =>      0,
                        'userid'    =>      $orderInfo['userId'],
                        'balance'   =>      $userMoney['userMoney'],
                         'remark'    =>      '微信购物',
                        'payWay'    =>        2,
                    ));
                    /**
                     * 魏永就
                     * 添加平台流水记录
                     * 2017-2-7
                     */
                    $platRes = M('platfrom_account')->add(array(
                        'orderId' =>$payInfo['orderId'],
                        'time'     =>time(),
                        'income'   => $orderInfo['needPay'],
                        'remark'   => '买家购物支付成功',
                        'orderNo'  => $orderInfo['orderNo']
                    ));
                    $dataRes = M('data_tmp')->where('id=1')->setInc('value',$orderInfo['needPay']);
                    $logRes = M('log_orders')->add(array(
                        'orderId'=>$payInfo['orderId'],
                        'logContent'=>'付款成功',
                        'logUserId'=>$orderInfo['userId'],
                        'logType'=>0,
                        'logTime'=>date('Y-m-d H:i:s')
                    ));
//                    if($orderRes && $moneyRecordRes)
                    if($orderRes && $moneyRecordRes && $platRes && $logRes && $dataRes)
                    {
                        M()->commit();
                        
                        /**
                        * @author peng
                        * @date 2017-01
                        * @descreption 代金券的发货
                        */
                        if($orderInfo['shopId']==0) D('Game/Voucher')->voucherFahuo($payInfo['orderId']);
                        
                        
                        /**
                        * @author peng
                        * @date 2017-01
                        * @descreption 手游狗版本支付完成自动发货
                        */
                        D('Home/SdkAgent')->autoFahuo($payInfo['orderId']);
                        ob_clean();
                        echo 'success';
                        
                    }else{
                        M()->rollback();
                        echo 'orderFail';
                        
                    }
                }else if($payInfo['type'] == 2){
                    
                    
                    $topUpFlag = M('topUp')->where(array(
                        'topupNo'   =>  $arr['out_trade_no'],
                        'status'    =>      1
                    ))->find();
                    if($topUpFlag)
                    {
                        echo 'success';
                        die;
                    }
                    
                    M()->startTrans();
                    $topUpRes = M('topUp')->where('topupNo='.$arr['out_trade_no'])->setField(array(        //对topUp更新
                        'status'    =>      1,
                        ));
                    $topUoInfo = M('topUp')->where('topupNo='.$arr['out_trade_no'])->field('userid,money')->find();       //对toppuNo查询
                    $userUpRe = M('users')->where('userId='.$topUoInfo['userid'])->setInc('userMoney',$topUoInfo['money']);     //对user表更新
                    $money = M('users')->where('userId='.$topUoInfo['userid'])->getField('userMoney');         //对users表查询
                    $moneyRecordRes = M('moneyRecord')->add(array(                                                  //对moneyRecord 更新
                        'type'      =>      3,
                        'money'     =>      $topUoInfo['money'],
                        'time'      =>      time(),
                        'ip'         =>      get_client_ip(),
                        'orderNo'   =>     $arr['out_trade_no'],
                        'IncDec'    =>      1,
                        'userid'    =>      $topUoInfo['userid'],
                        'balance'   =>      $money,
                        'remark'    =>       '微信充值',
                        'payWay'    =>        2,
                    ));
                    
                    /**
                     * @author peng
                     * @date 2017-04
                     * @descreption 加上订单应付金额跟实际支付相比
                     */
                    if($topUoInfo['money'] != $real_pay_money){
                        writelog('wftPay','充值订单out_trade_no:'.$arr['out_trade_no'].'支付异常，实际支付:'.$real_pay_money);
                        writelog('wftPay','行：'.__LINE__);
                        exit;
                    }
                    
                    if($topUpRes && $userUpRe && $moneyRecordRes)
                    {
                        M()->commit();
                        echo 'success';
                    }else{
                        M()->rollback();
                        echo 'fail';
                    }
                }
            }else{
                echo 'fail2';
            }
        }else{
            echo 'fail1';
        }
//        $str = print_r($arr,true);
//
//        echo 'success';
    }
    public function writeFile($str)
    {
        $myfile = fopen("testXml.txt", "w") or die("Unable to open file!");
        fwrite($myfile,$str);
        fclose($myfile);
    }

}