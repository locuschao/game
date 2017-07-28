<?php
namespace ImproveAPI\Controller;
use Think\Controller;
class MemberGoodsController extends BaseController{
    /**
     * @api {post} MemberGoods/goodsList 购买会员商品列表
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
    public function goodsList(){
        $userInfo = M('users')->where(['userId'=>$_SESSION['userId']])->find();
        $rank = $userInfo['rank']?:0;
        $data = M('goods_voucher')->field('id,rank')
        ->where([
        'isSale'=>1
        ])
        ->order('rank desc')
        ->select();
        $rank_change = [
            0=>0,
            1=>3,
            2=>2,
            3=>1
        ];
        foreach($data as $k=>$row){
            $data[$k]['rank'] = $rank_change[$row['rank']];
        }
        
        $desc_arr = [
            0=>'普通会员无等级',
            1=>'VIP黄金会员及特权',
            2=>'VIP白金会员及特权',
            3=>'VIP钻石会员及特权',
        ];
        $current_remark = [
            0=>'普通会员无等级',
            1=>'黄金会员有特权',
            2=>'白金会员有特权',
            3=>'钻石会员有特权',
        ];
        $this->ajaxReturn([
            'status'=>1,
            'info'=>[
                'rank'=>$rank_change[$rank],
                'desc'=>$desc_arr[$rank_change[$rank]],
                'current_remark'=>$current_remark[$rank_change[$rank]],
                'goodsInfo'=>$data,
                'photo'=>C('RESOURCE_URL').$userInfo['userPhoto']
            ]
        ]);
        
    }
    
    /**
     * @api {post} MemberGoods/addOrder 购买会员商品
     * @apiParam {Number} goodsId 商品id
     * @apiParam {Number} num 个数
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
    public function addOrder(){
        parent::isLogin();
        $post = getData();
        $m = M('orderids');
        $m->startTrans();
        
        $userId=$_SESSION['userId'];
        
        $orderNo = D('Game/orders')->getOrderSn($m->add(array(
            'rnd' => microtime(true)
        )));
        
        // 创建订单信息
        $goodsId = $post['goodsId'];
        $num = $post['num'];
        
        if($num<=0 || !is_numeric($num)){
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
        $data["orderNo"] = $orderNo;
        $data["shopId"] = 0;
        $data["userId"] = $userId;
        $data["orderFlag"] = 1;
        $data["totalMoney"] = $totalMoney;
        $data["deliverMoney"] = 0;
        //$data["payType"] = ;
        $data["deliverType"] = 0;
        $data["userName"] = '';
        $data["areaId1"] = 0;
        $data["areaId2"] = 0;
        $data["areaId3"] = 0;
        // 新字段
        $data["roleName"] = '';
        $data["communityId"] = 0;
        $data["userAddress"] = ''; // 区服
        $data["userTel"] = '';
        $data["userPhone"] = $userInfo['userPhone'];
        $data['orderScore'] = round($totalMoney, 0);
        $data["orderType"] = 0;
        $data["orderRemarks"] = '购买代金券商品';
        $data["isAppraises"] = 0;
        $data["createTime"] = date("Y-m-d H:i:s");
        $data["orderStatus"] = - 2;
        $data["isPay"] = 0;
        $data['lastMessTime'] = time();         // 魏永就   添加订单改变时间
        $data["needPay"] = $totalMoney;
        if($userInfo['partnerId']>0 && $userInfo['rank']==0){
            $data["is_fencheng"] = 1;
        }
        $orderId = M('orders')->add($data);
        
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
            $this->ajaxReturn([
            'status'=>1,
            'orderNo'=>$orderNo
            ]);
        } else {
            $m->rollback();
            $this->error('订单提交失败!');
        }
    }
    
    
    
    /**
     * @api {post} MemberGoods/goodsDetail 会员商品详情
     * @apiParam {Number} goodsId 会员商品goodsId
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
    public function goodsDetail() {
        $post = getData();
        $goods_info = M('goods_voucher')->find($post['goodsId']);
        $voucherList = M('voucher')->field('name,validTime,remark')->where([
        'id'=>['in',M('voucher_goods_relation')->where(['goods_id'=>$post['goodsId']])->getField('voucher_id',true)]
        ])->select();
       
        if($voucherList){
            foreach($voucherList as $k=>$row){
                $days = $row['validTime'];
                $voucherList[$k]['validTime'] = date('Y-m-d',strtotime("+ {$days} day"));
            }
            $this->ajaxReturn([
            'status'=>1,
            'info'=>$voucherList,
            'price'=>M('goods_voucher')->where(['id'=>$post['goodsId']])->getField('price'),
            'memberName'=>D('User')->rank_text[$goods_info['rank']]
            ]);
        }else{
            $this->error('没有数据');
        }
    }
    
    
}