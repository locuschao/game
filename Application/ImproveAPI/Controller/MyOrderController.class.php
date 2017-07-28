<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class MyOrderController extends BaseController
{
    public function __construct() {
        parent::__construct();
        //parent::isLogin();
    }
    /**
     * @api {post} MyOrder/orderList 订单列表
     * @apiParam {Number} type  2=>- 2,// 待付款
            3=>1,// 待发货
            4=>2,// 已发货
            5=>3,// 已完成
            6=>[-3,-8],// 退款中
            7=>[-9,-7]// 取消或无效
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info":数据 ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": 没有数据,
     *      }
     */
    public function orderList()
    {
        if($data = D('MyOrder')->getOrder()){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$data
            ]);
        }else{
            $this->error('没有数据');
        }
    }
    
    /**
     * @api {post} MyOrder/delOrder 删除订单
     * @apiParam {int} orderId 订单id
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
    public function delOrder()
    {   
        $post = getData();
        if (! $post['orderId']) {
            $this->error('非法操作');
        }
        $orderInfo = M('orders')->where(array(
            'orderNo' => $post['orderId']
        ))->find();
        
        if (! $orderInfo) {
            $this->error('订单不存在');
        }
        
        //author: peng descreption:只有指定的订单才能删除
        if(!in_array($orderInfo['orderStatus'],[2,3,-4,-5,-7,-8,-9])){
            // 非法操作
            $this->error('非法操作');
        }
        
        if ($orderInfo['orderFlag'] == 0) {
            // 订单已经删除
            $this->error('订单已删除');
        }
        if (M('orders')->where(array(
            'orderNo' => $post['orderId'],
            'userId' => $_SESSION['userId']
        ))->setField('orderFlag', 0)) {
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '删除成功'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '删除失败'
            ));
        }
        
    }
        
    /**
     * @api {post} MyOrder/orderDetail 订单详情
     * @apiParam {Number} orderId 订单id
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": 数据,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function orderDetail()
    {   
        $post = getData();
        M('orders')->where(['orderNo'=>$post['orderId']])->setField('isRead',1);   //点击则就将这条订单消息设置为已读,为了兼容网站
        if (! $post['orderId']) {
            $this->error('orderId不能空');
        }
        if($data = D('MyOrder')->orderDetail([
            'post'=>$post
        ])){
            $this->ajaxReturn([
                'status'=>1,
                'info'=>$data
            ]);
        }else{
            $this->error('没有数据');
        }
    }
    
    /**
     * @api {post} MyOrder/cancelWaitPayOrder 买取消待支付订单
     * @apiParam {Number} orderId 订单id
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info":订单取消成功 ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function cancelWaitPayOrder()
    {   
        $post = getData();
        
        $orderInfo = M('orders')->where(array(
            'orderNo' => $post['orderId']
        ))->find();
        
        $orderId = $orderInfo['orderId'];
        if ($orderInfo['orderStatus'] != -2) {
            $this->error('订单状态已经改变');
        }
        M()->startTrans();
        
        $res = M('orders')->where(['orderId'=>$orderId])->setField([
            'orderStatus'=>'-4',
            'cancelTime' =>time()
        ]);
        
        if ($res) {
            $data = array();
            $data["orderId"] = $orderInfo['orderId'];
            $data["logContent"] = "买家未付款，买家取消订单！";
            $data["logUserId"] = $_SESSION['userId'];
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            $result = M('log_orders')->add($data);
            // 退款日志表
            if ($result) {
                M()->commit();
                
                D('Game/Voucher')->cancelOrderHook($orderInfo);
                
                $this->ajaxReturn(array(
                    'status' => 1,
                    'info' => '订单取消成功'
                ));
            } else {
                M()->rollback();
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '订单取消失败'
                ));
            }
        } else {
            M()->rollback();
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '订单取消失败'
            ));
        }

    }
    
    /**
     * @api {post} MyOrder/cancelWaitDeliverGoodsOrder 买家取消代发货订单
     * @apiParam {Number} orderId 订单id
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
    public function cancelWaitDeliverGoodsOrder()
    {
        $post = getData();
        $orderInfo = M('orders')->where(array(          //对表 orders 查询
            'orderNo' => $post['orderId'],
            'userId'=>$_SESSION['userId']
        ))->find();
        $id = $orderInfo['orderId'];
        if(!$orderInfo){
            $this->error('订单不存在');
        }
        if ($orderInfo['orderStatus'] != 1) {                            //判断订单状态是否改变
            $this->error('订单状态已经改变');
        }
        
        if(time() - $orderInfo['paytime'] < 1800)               //下单并支付后不能在30分钟内取消订单
        {
            $this->error('30分钟内不可取消订单');
        }
        $A = true;
        $B = true;
        $C = true;
        M()->startTrans();
        $res = M('orders')->where(array(                    //  对表 orders 更新
            'orderId' => $id
        ))->setField(array(
            'orderStatus' => - 4,
            'cancelTime'  => time()
        ));
        if ($res) {
            $A = M('users')->where(array(              //对表 users 更新，退款给玩家
                'userId' => $_SESSION['userId']
            ))->setInc('userMoney', $orderInfo['needPay']);
            // 订单最新日志
            $data = array();
            $data["orderId"] = $id;
            $data["logContent"] = "商家没发货，买家主动取消订单！";
            $data["logUserId"] = $_SESSION['userId'];
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            M('log_orders')->add($data);
            // 退款日志表
            $refundLog['orderId'] = $id;
            $refundLog['userId'] = $_SESSION['userId'];
            $refundLog['mess'] = '商家没发货，买家主动取消订单';
            $refundLog['time'] = date('Y-m-d H:i:s');
            $B = M('refund_log')->add($refundLog);
            
            $userMoney = M('users')->where(array(
                'userId' => $_SESSION['userId']
            ))->getField('userMoney');
            // 全额变动记录
            $moneyRecord['type'] = 2;
            $moneyRecord['money'] = $orderInfo['needPay'];
            $moneyRecord['time'] = time();
            $moneyRecord['ip'] = get_client_ip();
            $moneyRecord['orderNo'] = $orderInfo['orderNo'];
            $moneyRecord['IncDec'] = 1;
            $moneyRecord['userid'] = $_SESSION['userId'];
            $moneyRecord['balance'] = $userMoney;
            $moneyRecord['remark'] = '商家没接单，取消订单';
            $moneyRecord['payWay'] = 3;
            $C = M('money_record')->add($moneyRecord);
            //添加平台流水记录
            $F = M('platfrom_account')->add(array(
                'orderId'   =>  $id,
                'time'      =>  time(),
                'income'    =>  -$orderInfo['needPay'],
                'remark'    =>  '买家取消订单，平台退款成功',
                'orderNo'   =>  $orderInfo['orderNo']
            ));
            $dataTemRes = M('data_tmp')->where('id=1')->setDec('value',$orderInfo['needPay']);
            
            if ($A && $B && $C&& $F && $dataTemRes) {
                M()->commit();
                /**
                 * @author peng
                 * @date 2017-01
                 * @descreption 
                 */
                D('Game/Voucher')->cancelOrderHook($orderInfo); 
                 
                $this->ajaxReturn(array(
                    'status' => 1,
                    'info' => '订单取消成功'
                ));
            } else {
                M()->rollback();
                $this->ajaxReturn(array(
                    'status' => 0,
                    'info' => '订单取消失败'
                ));
            }
        } else {
            M()->rollback();
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '订单取消失败'
            ));
        }
        
    }
    
    /**
     * @api {post} MyOrder/applyRefund 申请退款
     * @apiParam {string} content 申请退款理由
     * @apiParam {int} orderId 订单id
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
    public function applyRefund()
    {
        $post = getData();
        $userId = $_SESSION['userId'];
        
        $content = $post['content'];
        if (! $userId && ! $post['orderId']) {
            $this->error('数据异常');
        }
        $orderInfo = M('orders')->where(array(
            'orderNo' => $post['orderId']
        ))->find();
        $orderId = $orderInfo['orderId'];
        $isRefund = M('refund')->where(array(
            'orderid' => $orderId
        ))->find();
        if ($isRefund) {
            $this->error('退款正在处理中');
        }
        // 判断订单状态
        if ($orderInfo['orderStatus'] != 2) {
            $this->error('订单状态已经改变');
        }
        
        M()->startTrans();
        $complain['userId'] = $userId;
        $complain['orderid'] = $orderId;
        $complain['type'] = 2;
        $complain['reason'] = $content;
        $complain['explain'] = $content;
        $complain['refundTime'] = date('Y-m-d H:i:s');
        $complain['biz_status'] = 0;
        $complain['pf_status'] = 0;
        $complain['apply_money'] = $orderInfo['needPay'];
        $complain['way'] = 0;
        $complain['shopId'] = $orderInfo['shopId'];
        $rs = M('refund')->add($complain);
        
        $B = M('orders')->where(array(
            'orderId' => $orderId
        ))->setField(array(
            'orderStatus'=>- 3,
            'lastMessTime'=> time()
        ));
        
        // 订单最新日志
        $data = array();
        $data["orderId"] = $orderId;
        $data["logContent"] = "买家申请退款!退款原因：" . $content;
        $data["logUserId"] = $userId;
        $data["logType"] = 0;
        $data["logTime"] = date('Y-m-d H:i:s');
        M('log_orders')->add($data);
        
        // 退款日志表
        $refundLog['orderId'] = $orderId;
        $refundLog['userId'] = $userId;
        $refundLog['mess'] = '退款原因：' . $content;
        $refundLog['time'] = date('Y-m-d H:i:s');
        $C = M('refund_log')->add($refundLog);
        
        if ($rs && $B && $C) {
            M()->commit();
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '退款申请提交成功'
            ));
        } else {
            M()->rollback();
            $this->error('退款申请提交失败');
        }
    }
    
    /**
     * @api {post} MyOrder/applyComplain 投诉反馈
     * @apiParam {string} content 申请退款理由
     * @apiParam {int} orderId 订单id
     * @apiParam {int} type 1 商家已确定，但没有送达 2 其它
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
    public function applyComplain()
    {   
        $post = getData();
        $userId = $_SESSION['userId'];
        $content = $post['content'];
        $type = $post['type'];
        if (! $userId && ! $post['orderId']) {
            $this->error('数据异常');
        }
        $orderId = M('orders')->where(['orderNo'=>$post['orderId']])->getField('orderId');
        $isRefund = M('complain')->where(array(
            'orderId' => $orderId
        ))->find();
        if ($isRefund) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'mes' => '有投诉正在处理中'
            ));
            return;
        }
        $complain['userId'] = $_SESSION['userId'];
        $complain['orderId'] = $orderId;
        $orderRes = M('orders')->where(array(
            'orderId'=>$orderId,
        ))->find();
        if(!$orderRes) $this->error('订单不存在');
        if($orderRes['orderStatus'] != 3) $this->error('订单还没完成，不能投诉');
        $complain['content'] = ($type == 1) ?'该订单已完成，买家投诉原因：商家已确定，但没有送达':'该订单已完成，买家投诉原因：'.$content;
        $complain['type'] = 1; //魏永就   complain 表的type字段数字含意改变，现在是 1表示投诉的订单是一完成，0表示未完成
        $complain['time'] = date('Y-m-d H:i:s');
        $complain['isHandle'] = 0;
        $rs = M('complain')->add($complain);
        if ($rs) {
            M('orders')->where('orderId='.$orderId)->setField(array(
                'lastMessTime'=>time()
            ));
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '投诉提交成功'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '投诉提交失败'
            ));
        }
    }
    
}