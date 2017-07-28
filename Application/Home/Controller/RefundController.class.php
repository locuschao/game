<?php
namespace Home\Controller;

use Think\Model;

/**
 * 区域控制器
 */
class RefundController extends BaseController
{
    // 退款列表
    public function refundList()
    {
        $USER = session('WST_USER');
        $searchKey = I('get.searchKey');
        $biz_status = I('biz_status');
        if (is_numeric($biz_status) && $biz_status >= 0) {
            if ($biz_status == 0) {
                $map['r.type'] = 1;
            } else {
                $map['r.type'] = 2;
                $map['r.biz_status'] = $biz_status - 1;
            }
        }
        if ($searchKey) {
            $map['o.orderNo'] = $searchKey;
        }
        $sday = I('start_day');
        $eday = I('end_day');
        if ($sday && $eday) {
            $map['refundTime'] = array(
                'between',
                array(
                    $sday,
                    $eday
                )
            );
        } else 
            if (! $sday && $eday) {
                $map['refundTime'] = array(
                    'elt',
                    $eday
                );
            } else 
                if ($sday && ! $eday) {
                    $map['refundTime'] = array(
                        'egt',
                        $eday
                    );
                }
        $db = M('refund');
        $map['r.shopId'] = $USER['shopId'];
        $field = "r.*,o.orderNo";
        /*
         * $res['count'] = M('refund')->where($map)->join('as r left join oto_orders as o on o.orderId=r.orderid')->count();
         * $Page = new \Think\PageHome($res['count'],20);
         * $paging = $Page->show();
         * $res=M('refund')->field($field)->join('as r left join oto_orders as o on o.orderId=r.orderid')->where($map)->select();
         */
        $data['count'] = $db->join("as r left join oto_orders as o on o.orderId=r.orderid")
            ->where($map)
            ->count();
        $Page = new \Think\Page($data['count'], 20);
        $data['show'] = $Page->show();
        $data['list'] = $db->field($field)
            ->join("as r left join oto_orders as o on o.orderId=r.orderid")
            ->where($map)
            ->order('id desc')
            ->limit($Page->firstRow, $Page->listRows)
            ->select();
        $this->assign('res', $data);
        $this->display('Shops/refund/list');
    }
    
    // 退款处理
    public function agressRefund()
    {
        $id = I('id');
        $type = I('type', 0); // 0拒绝，1同意
        $isExis = M('refund')->where(array(
            'id' => $id,
            'biz_status' => array(
                'gt',
                0
            )
        ))->find();
        $refundInfo = M('refund')->where(array(
            'id' => $id
        ))->find();
        $orderid = $refundInfo['orderid'];
        if ($isExis) {
            $this->ajaxReturn(array(
                'status' => 0
            )); // 已经操作过了！
            return;
        }
        if (! $id) {
            $this->ajaxReturn(array(
                'status' => 0
            )); // 非法请求
            return;
        } else {
            M()->startTrans();
            if ($type == 0) { // 拒绝退款直接更新订单状态
                
                $a = M('refund')->where(array(
                    'id' => $id
                ))->setField(array(
                    'biz_status' => 2,
                    'biz_time' => date('Y-m-d H:i:s')
                )); // 1为同意退款，2为拒绝
                $b = M('orders')->where(array(
                    'orderId' => $orderid
                ))->setField(array(
                    'orderStatus' => - 6,
                    'isRead'=>0,
                    'lastMessTime'=>time()
                )); // 1为同意，2为拒绝//更新订单退款状态为拒绝，订单继续执行
                                                                                                       
                // 退款日志表
                $refundLog['orderId'] = $orderid;
                $refundLog['userId'] = $refundInfo['userId'];
                $refundLog['mess'] = '商家已拒绝退款';
                $refundLog['time'] = date('Y-m-d H:i:s');
                $c = M('refund_log')->add($refundLog);
                
                // 订单最新日志
                $data = array();
                $data["orderId"] = $orderid;
                $data["logContent"] = "商家已拒绝退款";
                $data["logUserId"] = $refundInfo['userId'];
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                M('log_orders')->add($data);
                
                // 拒绝的话，更新订单状态为完成，并入帐
                $info = M('orders')->where(array(
                    'orderId' => $orderid
                ))
                    ->field('shopId,userId,orderNo,needPay')
                    ->find();
                $needPay = $info['needPay'];

                /**
                 * @author peng	
                 * @date 2016-12
                 * @descreption 拒绝退款不能直接增加收入，要经过确认收货的过程
                 */
            
                /*$d = M('shops')->where(array(
                    'shopId' => $info['shopId']
                ))->setInc('bizMoney', $needPay);*/ // 收入
                
                //if ($a != false && $b != false && $c && $d) {

                if ($a != false && $b != false && $c) {
                    M()->commit();
                    //添加消息  peng
                    D('ImproveAPI/Msg')->addMsgHook([
                        'moduleId'=>2,
                        'info'=>$info,
                        'changeType'=>-6
                    ]);
                    
                    $this->ajaxReturn(array(
                        'status' => 1
                    ));
                } else {
                    M()->rollback();
                    $this->ajaxReturn(array(
                        'status' => 0
                    ));
                }
            } else 
                if ($type == 1) { // 同意退款，则等平台处理，后再更新订单状态
                    $a = M('refund')->where(array(
                        'id' => $id
                    ))->setField(array(
                        'biz_status' => 1,
                        'biz_time' => date('Y-m-d H:i:s')
                    )); // 1为同意退款，2为拒绝
                                                                                                                               // 退款日志表
                    $refundLog['orderId'] = $orderid;
                    $refundLog['userId'] = $refundInfo['userId'];
                    $refundLog['mess'] = '商家已同意退款,等待平台处理';
                    $refundLog['time'] = date('Y-m-d H:i:s');
                    $c = M('refund_log')->add($refundLog);
                    
                    // 订单最新日志
                    $data = array();
                    $data["orderId"] = $orderid;
                    $data["logContent"] = "商家已同意退款,等待平台处理";
                    $data["logUserId"] = $refundInfo['userId'];
                    $data["logType"] = 0;
                    $data["logTime"] = date('Y-m-d H:i:s');
                    M('log_orders')->add($data);
                    if ($a != false && $c) {
                        M()->commit();
                        $this->ajaxReturn(array(
                            'status' => 1
                        ));
                    } else {
                        M()->rollback();
                        $this->ajaxReturn(array(
                            'status' => 0
                        ));
                    }
                }
        }
    }

    public function detail()
    {
        $id = I('id');
        $refundInfo = M('refund')->where(array(
            'id' => $id
        ))->find();
        $orderInfo = M('orders')->where(array(
            'orderId' => $refundInfo['orderid']
        ))->find();
        $goodInfo = M('order_goods as og')->where(array(
            'orderId' => $refundInfo['orderid']
        ))->field('og.*,gg.goodsType')->join('left join oto_goods as gg on gg.goodsId=og.goodsId')->select();

        foreach ($goodInfo as $k => $v) {
            $goodInfo[$k]['singleTotal'] = $v['goodsPrice'] * $v['goodsNums'];
        }

        $this->assign('refundInfo', $refundInfo);
        $this->assign('orderInfo', $orderInfo);
        $this->assign('goodInfo', $goodInfo);
        $this->display('Shops/refund/detail');
    }
}