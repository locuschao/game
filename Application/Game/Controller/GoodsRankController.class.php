<?php
/**
 * @author peng	
 * @date 2016-12-31
 * @descreption 会员等级商品
 */
namespace Game\Controller;
use Think\Controller;

class GoodsRankController extends Controller{
    public function index(){
        
        $this->assign('goods_list',M('goods_voucher')
        ->where([
        'isSale'=>1
        ])
        ->order('rank desc')
        ->select());
    	$this->display();
    }
    public function addOrder(){
        $this->isLogin();
        $m = M('orderids');
        $m->startTrans();
        // 生成订单ID
        $orderSrcNo = $m->add(array(
            'rnd' => microtime(true)
        ));
        $userId=session('oto_userId');
        
        $orderNo = D('Game/orders')->getOrderSn($orderSrcNo);
        
        // 创建订单信息
        $goodsId = I('goodsId', 0);
        $num = (int)I('num');
        
        if($num<=0){
            $this->error('购买数量异常');
        }
        
        $goodsInfo=M('goods_voucher')->find($goodsId);
        
        $userInfo=M('users')->find($userId);
        
        if(D('Game/Voucher')->has_buy($userInfo['rank'],$goodsInfo['rank'])){
            $this->error('您已经购买过此产品');
        }
        
        $totalMoney = $goodsInfo['price'] * $num;
        if($totalMoney<0){
            $this->error('需要支付金额异常');
        }
        $data = array();
        $shopId = 0;
        $data["orderNo"] = $orderNo;
        $data["shopId"] = $shopId;
        $deliverType = 0;
        $data["userId"] = $userId;
        
        $data["orderFlag"] = 1;
        $data["totalMoney"] = $totalMoney;
        $data["deliverMoney"] = 0;
        //$data["payType"] = ;
        $data["deliverType"] = $deliverType;
        $data["userName"] = '';
        $data["areaId1"] = 0;
        $data["areaId2"] = 0;
        $data["areaId3"] = 0;
        // 新字段
        $data["roleName"] = I('roleName');
        
        $data["communityId"] = 0;
        $data["userAddress"] = I('area'); // 区服
        $data["userTel"] = '';
        $data["userPhone"] = I('mobile');
        
        $data['orderScore'] = round($data["totalMoney"], 0);
        
        $data["orderType"] = I('orderType', 0);
        $data["orderRemarks"] = '购买代金券商品';
       
        $data["isAppraises"] = 0;
        
        
        $data["createTime"] = date("Y-m-d H:i:s");
        
        $data["orderStatus"] = - 2;
        
        //$data["orderunique"] = $orderunique;
        $data["isPay"] = 0;
        $data['lastMessTime'] = time();         // 魏永就   添加订单改变时间
        
        $data["needPay"] = $totalMoney;
        if($userInfo['partnerId']>0 && $userInfo['rank']==0){
            $data["is_fencheng"] = 1;
        }
        
        $morders = M('orders');
        $orderId = $morders->add($data);
        
        $orderNos[] = $data["orderNo"];
        $orderInfos[] = array(
            "orderId" => $orderId,
            "orderNo" => $data["orderNo"]
        );
        // 订单创建成功则建立相关记录
        if ($orderId > 0) {
                $orderIds[] = $orderId;
                // 建立订单商品记录表
                $mog = M('order_goods');
                $data = array();
                $data["orderId"] = $orderId;
                $data["goodsId"] = $goodsId;
                
                $data["goodsNums"] = $num;
                
                $data["goodsPrice"] = $goodsInfo['price'];
               
                $data["goodsName"] = $goodsInfo["name"];
                $data["goodsThums"] =$goodsInfo["pictureUrl"]?:'Tpl/present.png';
                
                $re=$mog->add($data);
                
                $data = array();
                $data["orderId"] = $orderId;
                $data["logContent"] = "订单已提交，等待支付";
                $data["logUserId"] = $userId;
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                $mlogo = M('log_orders');
                $re2=$mlogo->add($data);
            }
         
        if ($orderId && $re && $re2) {
            $m->commit();
            $this->redirect('Game/Orders/orderDetail',['o'=>-2,'id'=>$orderId]);
        } else {
            $m->rollback();
            $this->success('订单提交失败!');
        }
        
    }
    public function myVoucherList(){
        $this->isLogin();
        $get=I('get.');
        
        $user_voucher=M('user_voucher');
        if(!$get['type']){
            $condition=[
            'userId'=>session('oto_userId')
            ];
        }else if($get['type']==1){
            $condition=[
            'userId'=>session('oto_userId'),
            'num'=>['gt',0],
            #date('Ymd',strtotime("+ $row[validTime] day",$row['add_time'])) >= date('Ymd')
            'expireTime'=>['egt',time()]
            ];
        }else if($get['type']==2){
            $condition=[
            'userId'=>session('oto_userId'),
            '_complex'=>[
                '_logic'=>'or',
                'num'=>0,
                'expireTime'=>['lt',time()]
                ]
            ];
            
        }
        
        $page = new \Think\Page($user_voucher->where($condition)->count(), 10); // 实例化分页类 传入总记录数和每页显示的记录数
        
        $data=$user_voucher->where($condition)
        ->limit($page->firstRow,$page->listRows)
        ->order('id desc')
        ->select();
       
        foreach($data as $k=>$row){
            $data[$k]['expireTime']=date('Y-m-d H:i',$row['expireTime']);
        }
        if($data){
            $this->success($data);
        }else{
            $this->error('没有数据');
        }
    }
    public function isLogin(){
        if(!session('oto_userId')){
            $this->redirect('/Game/Login/login');
        }
    }
    
    
}