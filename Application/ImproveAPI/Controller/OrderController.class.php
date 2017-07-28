<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class OrderController extends BaseController
{   
    /**
     * @api {post} Order/addOrder 提交订单
     * @apiParam {int}  attrid 商品属性id
     * @apiParam {int}  totalMoney 充值金额
     * @apiParam {int}  account 充值账号
     * @apiParam {string}  vouchers 狗粮id(逗号分割)(可选)
     * @apiParam {float}  pay_money 支付金额
     * @apiParam {float}  reduce_money 抵消金额 没有就是0
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": {
                'account'=>$post['account'], 充值账号
                'totalMoney'=>$post['totalMoney'],充值金额
                'needPay'=>$ordersInfo['needPay'],需要支付
                'orderId'=>$ordersInfo['orderId'] 订单id
            },
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function addOrder() {
        parent::isLogin();
        $post = getData();
        $attrInfo = M('goods_versions')->where([
            'id'=>$post['attrid']
        ])->find();
        
        $goodsInfo = M('goods')->where(array(
            'goodsId' => $attrInfo['goodsId']
        ))->find();
        
        if($goodsInfo['shopPrice'] > 0){
            $this->error('商品类型不正确');
        }
        if($post['totalMoney']<=0){
            $this->error('充值面额不能等于或小于0');
        }
        $type = 2;//默认是代充
        if($post['account']){
            $validateInfo = D('Game/Validatadc')->_validataAccount([
                'versions' => $attrInfo['versionsId'],
                'account' => $post['account'],
                'goodsType' =>0,
                'goodsId' => $attrInfo['goodsId'],
                'gameId' => 0,
                'from' =>'submit_order'
            ]);
            if($validateInfo['status'] == -1){
                $this->error('账号无效');
            }
            if($validateInfo['status'] == -2){
                $type = 2;//代充
            }
        }else{
             $type = 1;//首充
        }
        if($type == 1){  //首充的attrid
            $goods_attr_info = M('goods_versions')->join('gv left join '.C('DB_PREFIX').'goods g on gv.goodsId=g.goodsId')
            ->where([
                'versionsId'=>$attrInfo['versionsId'],
                'gameId'=>$goodsInfo['gameId'],
                'shopPrice'=>0,
                'scopeId'=>1
            ])->find();
             $attrInfo = [
                'shopId'=>$goods_attr_info['shopId'],
                'goodsId'=>$goods_attr_info['goodsId'],
                'goodsId'=>$goods_attr_info['goodsId'],
                'versionsId'=>$goods_attr_info['versionsId'],
                'attrPrice'=>$goods_attr_info['attrPrice'],
                'low_member_price'=>$goods_attr_info['low_member_price'],
                'mid_member_price'=>$goods_attr_info['mid_member_price'],
                'heigh_member_price'=>$goods_attr_info['heigh_member_price'],
            ];
            $goodsInfo = $goods_attr_info;
        }
        
        $addRes = D('order')->addOrder([
            'attrInfo'=>$attrInfo,
            'goodsInfo'=>$goodsInfo,
            'post'=>$post
        ]);
        
        if($addRes['status'] == -1) $this->error('代金券异常');
        if($addRes['status'] == 0) $this->error('订单提交失败');
        
        $createTime = date('Y-m-d H:i:s');
        // 生成付款订单号
        $payId = M('orders_payid')->add(array(
            'createTime' => $createTime
        ));
        
        if ($payId) {
            M('orders_payid')->add(array(
                    'pid' => $payId,
                    'createTime' => $createTime,
                    'orderId' => $addRes['orderId'],
                    'type' => 1
                ));
        }
        $this->returnJson(array(
            'status' => 1,
            'info' => '订单提交成功',
            'orderInfo' => [
                'account'=>$post['account'],
                'totalMoney'=>$post['totalMoney'],
                'needPay'=>$addRes['needPay'],
                'orderId'=>$addRes['orderNo'],
                'balance'=>M('users')->where(['userId'=>$_SESSION['userId']])->getField('userMoney')
            ]
        ));
        
    }
    
    //暂时不用
    public function caretePayInfo()
    {   
        $post = getData();
        // 判断订单状态
        $ispayInfo = M('orders')->where(array(
            'orderNo' => $post['orderId']
        ))->field('isPay,orderNo')
        ->find();
        if($ispayInfo['isPay'] == 1){
            $this->error('已经支付');
        }
        $pMap['orderId'] = 0;
        $pMap['pid'] = 0;
        $pMap['createTime'] = date('Y-m-d H:i:s', time());
        M()->startTrans();
        $pDB = M('orders_payid');
        $pid = $pDB->add($pMap);
        
        $sMap['orderId'] = $post['orderId'];
        $sMap['pid'] = $pid;
        $sMap['createTime'] = date('Y-m-d H:i:s', time());
        $sRes = $pDB->add($sMap);
        if ($pid && $sRes) {
            M()->commit();
            session('payid', $pid); // 付款ID
            $this->ajaxReturn([
                'out_trade_on'=>$pid
            ]);
        } else {
            M()->rollback();
           $this->error('失败');
        }
    }
    
}