<?php
namespace Wx\Controller;
use think\Model;
class ScoreController extends BaseController{
    //兑换记录
    public function record(){
        parent::isLogin();
        $map['o.orderType']=4;
        $map['o.payType']=4;
        $map['o.userId']=session('oto_userId');
        $field="o.orderId,m.score,m.time,g.goodsNums,g.goodsPrice,g.goodsName,g.goodsThums,g.goodsAttrName,g.goodsAttrId";
        $orderList=M('orders')->field($field)->where($map)->join('as o left join oto_score_record as m on o.orderNo=m.orderNo left join oto_order_goods as g on g.orderId=o.orderId')->select();
        
        $this->orderList=$orderList;     
        $this->display();
    }
    
    //积分分类列表
    public  function scoreList(){
        
        $catid=I('catid');
        $this->catName=M('integral_cats')->where(array('catId'=>$catid))->getField('catName');
        //积分商品列表
        $smap['goodsStock']=array('gt',0);
        $smap['goodsFlag']=1;
        $smap['goodsCatId']=$catid;
        $scoreGoods=M('integral')->where($smap)->select();
        $this->scoreGoodsInfo=$scoreGoods;
        $this->display();
    }
    
    //兑换详情
    public function convertDetail(){
          parent::isLogin();
          $id=I('id');
          if(!$id){
              $this->redirect(U('Index/index',array('r'=>'score'), '', '', 0));
              return;
          }
          $map['o.orderId']=$id;
          $field="o.orderId,o.userPhone,o.userName,o.userAddress,o.orderNo,m.score,m.time,g.goodsNums,g.goodsPrice,g.goodsName,g.goodsThums,g.goodsAttrName,g.goodsAttrId";
          $orderList=M('orders')->field($field)->where($map)->join('as o left join oto_score_record as m on o.orderNo=m.orderNo left join oto_order_goods as g on g.orderId=o.orderId')->find();

          $info=M('orders')->where(array('o.orderId'=>$id))->field('o.createTime,oe.trackNumber,ox.pinyin,ox.expressCompany')->join('as o left join oto_order_express as oe on oe.orderId=o.orderId left join oto_express as ox on ox.id=oe.exId')->find();
          $this->info=$info;
          $expressInfo['data'][0]['time']=$info['createTime'];
          $expressInfo['data'][0]['context']="等待配送";
          if($info['trackNumber']){
              $expressInfo=$this->checkExpress($info['pinyin'],$info['trackNumber']);
          }
          $this->assign('expressInfo',$expressInfo);

          $this->orderList=$orderList;
          $this->display();
    }
    
    //积分商品
    public function scoreGoods(){
        $id=I('id');
        if(!$id){
            $this->redirect(U('Index/index',array('r'=>'score'), '', '', 0));
            return;
        }
		$smap['goodsFlag']=1;
		$smap['goodsId']=$id;
		$goodsInfo=M('integral')->where($smap)->find();
		$img=M('goods_gallerys')->where(array('goodsId'=>$id,'shopId'=>0))->select();
		$this->img=$img;
		$this->goodsInfo=$goodsInfo;
        $this->display();
    }
    
    //积分说明
    public  function explain(){
        $this->display();
    }
    
    //积分兑换在线支付
    public function scorePay(){
        parent::isLogin();
        $id=I('id');
        if(!$id){
            $this->redirect(U('Index/index',array('r'=>'score'), '', '', 0));
            return;
        }
        $smap['goodsFlag']=1;
        $smap['goodsId']=$id;
        $goodsInfo=M('integral')->where($smap)->find();
        $goodsInfo['needPay']=$goodsInfo['shopPrice']/100;
        $this->goodsInfo=$goodsInfo;
        //用户信息
        $userScore=M('users')->where(array('userId'=>session('oto_userId')))->getField('userScore');
        $this->userScore=$userScore;
        
        //默认收货地址
        $addr=M('user_address')->where(array('addressFlag'=>1,'isDefault'=>1,userId=>session('oto_userId')))->find();
        if($addr){
            $db=M('areas');
            $addr['province']=$db->where(array('areaId'=>$addr['areaId1']))->getField('areaName');
            $addr['city']=$db->where(array('areaId'=>$addr['areaId2']))->getField('areaName');
            $addr['area']=$db->where(array('areaId'=>$addr['areaId3']))->getField('areaName');
        }
        
        $exisPwd=M('users')->where(array('userId'=>session('oto_userId')))->getField('payPwd');
        if($exisPwd){
            $exisPwd=1;
        }else{
            $exisPwd=0;
        }
        
        $this->exisPwd=$exisPwd;
        $this->addr=$addr;
        $this->display();
    }
    
    //积分兑换处理
    public function scorePayHandle(){
        if(IS_AJAX){
                if(!session('oto_userId')){
                    $this->ajaxReturn(array('status'=>-3));
                    return;
                }
                $id=I('goodsId');
                $addrId=I('addrId');
                $payType=I('payType');
                if(!$id||!$addrId||!isset($payType)){
                    $this->ajaxReturn(array('status'=>-1));
                    return;
                }
                $addr=M('user_address')->where(array('addressFlag'=>1,'isDefault'=>1,'addressId'=>$addrId,userId=>session('oto_userId')))->find();
                if(!$addr){
                    $this->ajaxReturn(array('status'=>-1));
                    return;
                }
                $db=M('areas');
                $addr['province']=$db->where(array('areaId'=>$addr['areaId1']))->getField('areaName');
                $addr['city']=$db->where(array('areaId'=>$addr['areaId2']))->getField('areaName');
                $addr['area']=$db->where(array('areaId'=>$addr['areaId3']))->getField('areaName');
                
                $goodsInfo=M('integral')->where(array('goodsId'=>$id,'goodsStock'=>array('gt',0),'goodsFlag'=>1))->find();
                if(!$goodsInfo){
                    $this->ajaxReturn(array('status'=>-1));
                    return;
                }
                
                $umap['userStatus']=1;
                $umap['userFlag']=1;
                $umap['userId']=session('oto_userId');
                $userInfo=M('users')->where($umap)->find();
                if(!$userInfo){
                    $this->ajaxReturn(array('status'=>-1));
                    return;
                }
                if($userInfo['userScore']<$goodsInfo['shopPrice']){
                    //积分不足
                    $this->ajaxReturn(array('status'=>-2));
                    return;
                }
                //处理订单
                M()->startTrans();
                $m = M('orderids');
                $orderSrcNo = $m->add(array('rnd'=>microtime(true)));
                $orderNo = $orderSrcNo."".(fmod($orderSrcNo,7));
                //创建订单信息
                $data = array();
                $data["orderNo"] = $orderNo;
                $data["shopId"] = 0;
                $data["userId"] = session('oto_userId');
                $data["orderFlag"] = 1;
                $data["totalMoney"] = $goodsInfo['shopPrice']/100;
                $data["deliverMoney"] = 0;//配送费
                $data["payType"] = 4;//积分兑换
                $data["deliverType"] = 0;//商家配送
                $data["userName"] = $addr["userName"];
                $data["communityId"] = $addr["communityId"];
                $data["userAddress"] = $addr["province"]." ".$addr["city"]." ".$addr["area"]." ".$addr['address'];
                $data["userTel"] = $addr["userTel"];
                $data["userPhone"] = $addr["userPhone"];
                $data['orderScore'] = round($data["totalMoney"]+$data["deliverMoney"],0);
                $data["isInvoice"] = 0;//是否需要收据
                $data["orderRemarks"] = '';
                $data["requireTime"] = date('Y-m-d H:i:s',time());//期望送达时间
                $data["invoiceClient"] = I("invoiceClient");
                $data["isAppraises"] = 0;
                $data["isSelf"] = 0;//是否自提1是，0否
                $data["needPay"] = $data["totalMoney"]+$data["deliverMoney"];
                $data["createTime"] = date("Y-m-d H:i:s");
                $data["orderStatus"] = 0;//订单成功
                $data["paytime"] = time();
                $data["orderunique"] = I('orderunique');
                $data["isPay"] = 1;//积分付款也算付款
                $data["orderType"] = 4;//订单类型为积分兑换
                $morders = M('orders');
                $orderId = $morders->add($data);
                //订单创建成功则建立相关记录
                if($orderId>0&&$orderSrcNo>0){
                    //建立订单商品记录表
                    $mog = M('order_goods');
                    $goods = $goodsInfo;
                    //修改表字段类型，存入商品属性id;
                    $goodsNums = 1;
                    $goodsPrice = $goods['shopPrice']/100;
                    $goodsName = $goods["goodsName"];
                    $goodsThums = $goods["goodsThums"];
                    $goodsid=$goods['goodsId'];
                    $gdata['orderId']=$orderId;
                    $gdata['goodsId']=$goods;
                    $gdata['goodsAttrId']='';
                    $gdata['goodsAttrName']='';
                    $gdata['goodsNums']=$goodsNums;
                    $gdata['goodsPrice']=$goodsPrice;
                    $gdata['goodsName']=$goodsName;
                    $gdata['goodsThums']=ltrim($goodsThums,'/');
                    //使用add方法插入，sql语句中的goodsAttrId字段会强制转为int
                    $mogResult=$mog->add($gdata);
                        //建立订单记录
                        $data = array();
                        $data["orderId"] = $orderId;
                        $data["logContent"] =  "下单成功";
                        $data["logUserId"] = session('oto_userId');
                        $data["logType"] = 0;
                        $data["logTime"] = date('Y-m-d H:i:s');
                        $mlogo = M('log_orders');
                        $logoId = $mlogo->add($data);
                        //建立订单提醒
                        $sql ="SELECT userId,shopId,shopName FROM __PREFIX__shops WHERE shopId=0 AND shopFlag=1  ";
                        $users = M()->query($sql);
                        $morm = M('order_reminds');
                        for($i=0;$i<count($users);$i++){
                            $data = array();
                            $data["orderId"] = $orderId;
                            $data["shopId"] = 0;
                            $data["userId"] = 0;
                            $data["userType"] = 0;
                            $data["remindType"] = 0;
                            $data["createTime"] = date("Y-m-d H:i:s");
                            $morm->add($data);
                        }
                        //操作库存
                        $stock=M('integral')->where(array('goodsId'=>$goodsid))->setDec('goodsStock',1);
                        //扣掉积分
                        $userScore=M('users')->where(array('userId'=>session('oto_userId')))->setDec('userScore',$goodsInfo['shopPrice']);
                        //积分变动记录
                        $record=$this->scoreRecord(7,$goodsInfo['shopPrice'],$orderNo,0,session('oto_userId'));
                        
                        if($mogResult>0&&$logoId>0&&$stock&&$userScore&&$record){
                            M()->commit();
                            $userScore=M('users')->where(array('userId'=>session('oto_userId')))->getField('userScore');
                            $this->ajaxReturn(array('status'=>0,'score'=>$userScore));
                        }else{
                            M()->rollback();
                            $this->ajaxReturn(array('status'=>-1));
                        }
                }else{
                    M()->rollback();
                    $this->ajaxReturn(array('status'=>-1));
                }
                
            }
     }
    //积分兑换支付处理
    public function scorePayMoneyHandle(){
        if(IS_AJAX){
                if(!session('oto_userId')){
                    $this->ajaxReturn(array('status'=>-3));
                    return;
                }
                $id=I('goodsId');
                $addrId=I('addrId');
                $payType=I('payType');
                if(!$id||!$addrId||!isset($payType)){
                    $this->ajaxReturn(array('status'=>-1));
                    return;
                }
                $addr=M('user_address')->where(array('addressFlag'=>1,'isDefault'=>1,'addressId'=>$addrId,userId=>session('oto_userId')))->find();
                if(!$addr){
                    $this->ajaxReturn(array('status'=>-1));
                    return;
                }
                $db=M('areas');
                $addr['province']=$db->where(array('areaId'=>$addr['areaId1']))->getField('areaName');
                $addr['city']=$db->where(array('areaId'=>$addr['areaId2']))->getField('areaName');
                $addr['area']=$db->where(array('areaId'=>$addr['areaId3']))->getField('areaName');
                
                $goodsInfo=M('integral')->where(array('goodsId'=>$id,'goodsStock'=>array('gt',0),'goodsFlag'=>1))->find();
                if(!$goodsInfo){
                    $this->ajaxReturn(array('status'=>-1));
                    return;
                }
                
                $umap['userStatus']=1;
                $umap['userFlag']=1;
                $umap['userId']=session('oto_userId');
                $userInfo=M('users')->where($umap)->find();
                if(!$userInfo){
                    $this->ajaxReturn(array('status'=>-1));
                    return;
                }
              
                //处理订单
                M()->startTrans();
                $m = M('orderids');
                $orderSrcNo = $m->add(array('rnd'=>microtime(true)));
                $orderNo = $orderSrcNo."".(fmod($orderSrcNo,7));
                //创建订单信息
                $data = array();
                $data["orderNo"] = $orderNo;
                $data["shopId"] = 0;
                $data["userId"] = session('oto_userId');
                $data["orderFlag"] = 1;
                $data["totalMoney"] = $goodsInfo['shopPrice']/100;
                $data["deliverMoney"] = 0;//配送费
                $data["payType"] = 0;//积分兑换
                $data["deliverType"] = 0;//商家配送
                $data["userName"] = $addr["userName"];
                $data["communityId"] = $addr["communityId"];
                $data["userAddress"] = $addr["province"]." ".$addr["city"]." ".$addr["area"]." ".$addr['address'];
                $data["userTel"] = $addr["userTel"];
                $data["userPhone"] = $addr["userPhone"];
                $data['orderScore'] = round($data["totalMoney"]+$data["deliverMoney"],0);
                $data["isInvoice"] = 0;//是否需要收据
                $data["orderRemarks"] = '';
                $data["requireTime"] = date('Y-m-d H:i:s',time());//期望送达时间
                $data["invoiceClient"] = I("invoiceClient");
                $data["isAppraises"] = 0;
                $data["isSelf"] = 0;//是否自提1是，0否
                $data["needPay"] = $data["totalMoney"]+$data["deliverMoney"];
                $data["createTime"] = date("Y-m-d H:i:s");
                $data["orderStatus"] = 0;//订单成功
                $data["paytime"] = time();
                $data["orderunique"] = I('orderunique');
                $data["isPay"] = 0;//积分付款也算付款
                $data["orderType"] = 4;//订单类型为积分兑换
                $morders = M('orders');
                $orderId = $morders->add($data);
                //订单创建成功则建立相关记录
                if($orderId>0&&$orderSrcNo>0){
                    //建立订单商品记录表
                    $mog = M('order_goods');
                    $goods = $goodsInfo;
                    //修改表字段类型，存入商品属性id;
                    $goodsNums = 1;
                    $goodsPrice = $goods['shopPrice']/100;
                    $goodsName = $goods["goodsName"];
                    $goodsThums = $goods["goodsThums"];
                    $goodsid=$goods['goodsId'];
                    $gdata['orderId']=$orderId;
                    $gdata['goodsId']=$goods;
                    $gdata['goodsAttrId']='';
                    $gdata['goodsAttrName']='';
                    $gdata['goodsNums']=$goodsNums;
                    $gdata['goodsPrice']=$goodsPrice;
                    $gdata['goodsName']=$goodsName;
                    $gdata['goodsThums']=ltrim($goodsThums,'/');
                    //使用add方法插入，sql语句中的goodsAttrId字段会强制转为int
                    $mogResult=$mog->add($gdata);
                        //建立订单记录
                        $data = array();
                        $data["orderId"] = $orderId;
                        $data["logContent"] =  "下单成功";
                        $data["logUserId"] = session('oto_userId');
                        $data["logType"] = 0;
                        $data["logTime"] = date('Y-m-d H:i:s');
                        $mlogo = M('log_orders');
                        $logoId = $mlogo->add($data);
                        //建立订单提醒
                        $sql ="SELECT userId,shopId,shopName FROM __PREFIX__shops WHERE shopId=0 AND shopFlag=1  ";
                        $users = M()->query($sql);
                        $morm = M('order_reminds');
                        for($i=0;$i<count($users);$i++){
                            $data = array();
                            $data["orderId"] = $orderId;
                            $data["shopId"] = 0;
                            $data["userId"] = 0;
                            $data["userType"] = 0;
                            $data["remindType"] = 0;
                            $data["createTime"] = date("Y-m-d H:i:s");
                            $morm->add($data);
                        }
                        //操作库存
                        //$stock=M('integral')->where(array('goodsId'=>$goodsid))->setDec('goodsStock',1);
                        //扣掉积分
                        //$userScore=M('users')->where(array('userId'=>session('oto_userId')))->setDec('userScore',$goodsInfo['shopPrice']);
                        //积分变动记录
                        //$record=$this->scoreRecord(7,$goodsInfo['shopPrice'],$orderNo,0,session('oto_userId'));
                        
                        if($mogResult>0&&$logoId>0){
                            M()->commit();
                            $createTime=date('Y-m-d H:i:s');
                            //生成付款订单号
                            $payId=M('orders_payid')->add(array('createTime'=>$createTime));
                            if($payId){
                                  M('orders_payid')->add(array('pid'=>$payId,'createTime'=>$createTime,'orderId'=>$orderId));
                            }
                            session('payid',$payId);
                            $userScore=M('users')->where(array('userId'=>session('oto_userId')))->getField('userScore');
                            $this->ajaxReturn(array('status'=>0,'score'=>$userScore));
                        }else{
                            M()->rollback();
                            $this->ajaxReturn(array('status'=>-1));
                        }
                }else{
                    M()->rollback();
                    $this->ajaxReturn(array('status'=>-1));
                }
                
            }
     }
     
   
     // 积分操作记录
     /**
      * 构造函数
      * @param $type 1购物，2取消订单，3充值，4订单无效，5活动,6评价订单,7积分兑换
      * @param $score 积分
      * @param $shopid 店铺ID
      * @param $orderid 订单ID或者充值ID
      * @param $IncDec 积分变动0为减，1加
      * @param $userid 用户ID
      * @param $totalscore 用户剩余总积分
      */
     public function scoreRecord($type = '', $score=0, $orderNo = '', $IncDec = '', $userid = 0) {
         $totalscore=M('users')->where(array('userId'=>$userid))->getField('userScore');
         $db = M ( 'score_record' );
         $data ['score'] = $score;
         $data ['type'] = $type;
         $data ['time'] = time ();
         $data ['ip'] = get_client_ip ();
         $data ['orderNo'] = $orderNo;
         $data ['IncDec'] = $IncDec;
         $data ['userid'] = $userid;
         $data ['totalscore'] = $totalscore;
         $res = $db->add ( $data );
         return $res;
     }
     
     
     
     
     
     
}