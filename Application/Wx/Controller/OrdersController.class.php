<?php
namespace Wx\Controller;
use Think\Controller;
use Think\Model;

class OrdersController extends BaseController
{
    public function _initialize()
    {
        parent::isLogin();
        @header('Content-type: text/html;charset=UTF-8');
    }
    
    //删除购物车商品
    public function delCartGoods(){
        $goodsKey = (int)I("goodsId")."_".I("goodsAttrId");
        $ids=I('ids');
        if(!$ids||!session('oto_userId')){
            $this->ajaxReturn(array('stauts'=>-1));
            return;
        }
        $goodsKey=explode(',',$ids);
        
        $map['userId']= session('oto_userId');
        $isCarExists=M('car_session')->where($map)->field('car_session,userId')->find();
        $dbCarInfo=unserialize($isCarExists['car_session']);
        foreach($dbCarInfo as $key=>$cgoods){
            foreach ($goodsKey as $v){
                if($v==$key){
                    unset($dbCarInfo[$key]);
                }
            }
        }
        if(count($dbCarInfo)<=0){
            $isCarExists=M('car_session')->where($map)->delete();
            $this->ajaxReturn(array('status'=>0));
            return;
        }
        if($isCarExists['userId']){
            $isCarExists=M('car_session')->where($map)->save(array('car_session'=>serialize($dbCarInfo)));
            if($isCarExists){
              $this->ajaxReturn(array('status'=>0));
            }else{
                $this->ajaxReturn(array('status'=>-1));
            }
        }else{
            $saveData['car_session']=serialize($dbCarInfo);
            $saveData['userId']=session('oto_userId');
            $isCarExists=M('car_session')->add($saveData);
           if($isCarExists){
              $this->ajaxReturn(array('status'=>0));
            }else{
                $this->ajaxReturn(array('status'=>-1));
            }
        }
    }
    
    //提交订单
    public function submitOrder(){
        //数据格式：40_A|o_o|1|o2o|  ->店铺_订单类型|o_o|值|o2o|  说明
        //优惠卷
        $coupon=I('coupon');
        //自提还是配送
        $isself=I('isself');
        //留言
        $remarks=I('remarks');
        //下单的数据session数据 
        $sortingOrder=session('sortingOrder');
        
        $goodsmodel = D('Wx/Goods');
        $morders = D('Wx/Orders');
        $totalMoney = 0;
        $totalCnt = 0;
        $userId =session('oto_userId');
        //收件人
        $consigneeId = (int)I("consigneeId");
        //支付方式
        $payway = (int)I("payway");
        //是否需要发票
        $needreceipt = (int)I("needreceipt",0);
        
        $orderunique = I("orderunique");
        
        if(empty($sortingOrder)){
            //购物车已经为空
            $this->ajaxReturn(array('status'=>1,'msg'=>'订单提交成功'));
        }else{
            //整理及核对购物车数据
            foreach($sortingOrder['cartgoods'] as $key=>$cgoods){
                //分开商品id和属性id
                $temp = explode('_',$key);
                $shopId = (int)$temp[0];
                $type=$temp[1];
                switch ($type){
                    //查普通商品库存
                    case "A":
                        foreach ($cgoods['shopgoods'] as $k=>$v){
                            $attr='';
                            foreach ($v as $attrkey=>$attrval){
                                if(is_numeric($attrkey)){
                                    $attr.=$attrval['goodsAttrId'].'_';
                                }
                            }
                            $attr=rtrim($attr,'_');
                            $info=$this->plainGoods($v['goodsId'], $v['cnt'],$attr);
                            if($info['isSale']==0){
                                $this->ajaxReturn(array('status'=>-2,'msg'=>'对不起，商品'.$v['goodsName'].'库存不足!'));
                                return;
                            }
                        }
                     break;
                    case 'B':
                        //团购
                        foreach ($cgoods['shopgoods'] as $k=>$v){
                            $info=$this->groupStock($v['goodsId'], $v['cnt']);
                            if($info['isSale']==0){
                                $this->ajaxReturn(array('status'=>-2,'msg'=>'对不起，商品'.$v['goodsName'].'库存不足!'));
                                return;
                            }
                        }
                        break;
                    case 'C':
                        //秒杀
                        foreach ($cgoods['shopgoods'] as $k=>$v){
                            $info=$this->secKillStock($v['goodsId'], $v['cnt']);
                            if($info['isSale']==0){
                                $this->ajaxReturn(array('status'=>-2,'msg'=>'对不起，商品'.$v['goodsName'].'库存不足!'));
                                return;
                            }
                        }
                        break;
                }
            }
            $payway=1;//默认在线支付，0为货到付款
            $ordersInfo = $morders->addOrders($userId,$consigneeId,$payway,$needreceipt,$sortingOrder,$orderunique,$isself,$remarks,$coupon);
            //结算后清空所选的购物车信息
            $newcart = array();
            $map['userId']=session('oto_userId');
            $cartInfo=M('car_session')->where($map)->getField('car_session');
            $shopcat=unserialize($cartInfo);
            $g_a_arr= session('clearCartGoodsIdAttrId');
            $g_a_arr=explode(',', $g_a_arr);
            foreach($shopcat as $key=>$cgoods){
                if(!in_array($key, $g_a_arr)){
                    $newcart[$key] = $cgoods;
                }
            }
            if(empty($newcart)){
                 M('car_session')->where($map)->delete();
            }else{
                $isCarExists=M('car_session')->where($map)->save(array('car_session'=>serialize($newcart)));
            }
            $createTime=date('Y-m-d H:i:s');
            //生成付款订单号
            $payId=M('orders_payid')->add(array('createTime'=>$createTime));
            if($payId){
                foreach ($ordersInfo['orderIds'] as $k=>$v){
                    M('orders_payid')->add(array('pid'=>$payId,'createTime'=>$createTime,'orderId'=>$v));
                }
            }
           session('sortingOrder',null);
           session('payid',$payId);
           $this->ajaxReturn(array('status'=>1,'msg'=>'订单提交成功','payid'=>$payId));
        }
    }
    //待发货
    public function waitDeliver(){
        $map['o.isClosed']=0;
        $map['o.orderFlag']=1;
        $map['o.userId']=session('oto_userId');
        $map['o.orderStatus']=array('between',"0,2");
        $map['orderType']=array('neq',4);
        //$map['_string']="o.isPay=1 or o.payType=0";
        $field="o.isPay,o.orderStatus,o.orderId,o.orderNo,o.areaId1,o.areaId2,o.areaId3,o.shopId,o.deliverMoney,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg";
        $waitDeliver=M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId')->where($map)->field($field)->select();
        $goodsDB=M('order_goods');
        $areaDB=M('areas');
        foreach ($waitDeliver as $k=>$v){
            $waitDeliver[$k]['goods']=$goodsDB->where(array('orderId'=>$v['orderId']))->select();
        }
        $this->waitDeliver=$waitDeliver;
        $this->display();
    }
    //待收货
    public function waitReceiving(){
        $map['isRefund']=0;
        $map['isClosed']=0;
        $map['orderFlag']=1;
        $map['orderType']=array('neq',4);
        $map['userId']=session('oto_userId');
        //$map['_string']="isPay=1 or payType=0";
        //待收货
        $map['orderStatus']=3;
        $field="o.createTime,o.orderId,o.orderNo,o.areaId1,o.areaId2,o.areaId3,o.shopId,o.deliverMoney,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg,oe.trackNumber,ex.pinyin,ex.expressCompany";
        $waitReceiving=M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId left join oto_order_express as oe on oe.orderId=o.orderId left join oto_express as ex on ex.id=oe.exId')->where($map)->field($field)->select();
        $goodsDB=M('order_goods');
        foreach ($waitReceiving as $k=>$v){
            $waitReceiving[$k]['ntime']=$v['createTime'];
            if($v['trackNumber']){
                $expressInfo=$this->checkExpress($v['pinyin'],$v['trackNumber']);
                if($expressInfo['data'][0]){
                    $waitReceiving[$k]['first']=$expressInfo['data'][0]['context'];
                    $waitReceiving[$k]['ntime']=$expressInfo['data'][0]['time'];
                }else{
                    $waitReceiving[$k]['first']='等待快递收件';
                }
            }else{
                $waitReceiving[$k]['first']='等待发货';
            }
        }
        $areaDB=M('areas');
        foreach ($waitReceiving as $k=>$v){
            $waitReceiving[$k]['goods']=$goodsDB->where(array('orderId'=>$v['orderId']))->select();
        }
        $this->waitReceiving=$waitReceiving;
        $this->display();
    }
    //待付款
    public function waitPay(){
        //待付款
        $map['o.isClosed']=0;
        $map['o.orderFlag']=1;
        //$map['o.isPay']=0;
        $map['o.orderStatus']=-2;
        $map['orderType']=array('neq',4);
        $map['o.userId']=session('oto_userId');
        $field="o.orderId,o.orderNo,o.areaId1,o.areaId2,o.areaId3,o.shopId,o.deliverMoney,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg";
        $waitPay=M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId')->where($map)->order('o.orderId DESC')->field($field)->select();
<<<<<<< .mine
        
        
||||||| .r907
=======
        
     
>>>>>>> .r912
        $goodsDB=M('order_goods');
        $areaDB=M('areas');
        foreach ($waitPay as $k=>$v){
            $waitPay[$k]['goods']=$goodsDB->where(array('orderId'=>$v['orderId']))->select();
        }
        $this->waitPay=$waitPay;
        $this->display();
    }
    //评价中心
    public function evaluate(){
        //签收7天内可评价
        $map['isRefund']=0;
        $map['isClosed']=0;
        $map['orderFlag']=1;
       // $map['_string']="isPay=1 or payType=0";
        $map['orderStatus']=4;
        $map['orderType']=array('neq',4);
        $map['userId']=session('oto_userId');
        //$map['_string']="date_sub(curdate(), INTERVAL 7 DAY) <= date(`signTime`)";
        $field="o.orderId,o.orderNo,o.areaId1,o.areaId2,o.areaId3,o.shopId,o.deliverMoney,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg";
        $evaluate=M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId')->where($map)->field($field)->select();
        $goodsDB=M('order_goods');
        $areaDB=M('areas');
        foreach ($evaluate as $k=>$v){
            $evaluate[$k]['goods']=$goodsDB->where(array('orderId'=>$v['orderId']))->select();
        }
        $this->evaluate=$evaluate;
        $this->display();
    }
    
    //全部订单
    public function orders(){
        parent::isLogin();
        $field="o.isSelf,o.isPay,o.needPay,o.orderId,o.areaId1,o.areaId2,o.areaId3,o.communityId,o.deliverMoney,o.orderStatus,o.isRefund,o.isClosed,s.shopName,s.shopId,s.shopImg";
        $orderInfo=M('orders')->field($field)->join('as o left join oto_shops as s on o.shopId=s.shopId')->where(array('o.userId'=>session('oto_userId'),'o.orderFlag'=>1))->order('o.orderId DESC')->select();
        $goodsDB=M('order_goods');
        $refundDB=M('refund');
        foreach ($orderInfo as $k=>$v){
            $orderInfo[$k]['goods']=$goodsDB->where(array('orderId'=>$v['orderId']))->select();
            if($v['status']==4){
                //检查订单是否评价过
                $isPJ['o.isClosed']=0;
                $isPJ['o.orderFlag']=1;
                $isPJ['o.orderStatus']=4;
                $isPJ['o.orderId']=$v['orderId'];
                $isPJ['_string']="date_sub(curdate(), INTERVAL 7 DAY) <= date(`signTime`)";
                $isPJ=M('orders')->where($isPJ)->join('as o join oto_share as s on o.orderId=s.orderId')->find();
                if($isPJ){
                    $orderInfo[$k]['isPJ']=1;
                    //追加评价？
                    $addTo=M('share')->where(array('goodsId'=>$isPJ['goodsId'],'orderId'=>$isPJ['orderId']))->find();
                    if($addTo){
                        $orderInfo[$k]['pj_addTo']=1;//已追加
                    }else{
                        $orderInfo[$k]['pj_addTo']=0;//没追加
                    }
                }else{
                    if(!$isPJ['isRefund']||$isPJ['isRefund']>0){//退款的订单不能评价
                        $orderInfo[$k]['isPJ']=1;
                    }else{
                        $orderInfo[$k]['isPJ']=0;
                    }
                }
            }
        }
        $this->info=$orderInfo;
        $this->display();
    }
    
   //订单详情
   public function orderDetail(){
       parent::isLogin();
       $id=I('id');
       if(!$id){
           $this->redirect(U('Wx/Index/index','','',0));
           return;
       }
       $map['orderId']=$id;
       $field="o.orderType,o.orderId,o.orderNo,o.isPay,o.isRefund,o.payType,o.orderStatus,o.areaId1,o.areaId2,o.areaId3,o.shopId,o.orderStatus,o.deliverMoney,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg,s.shopTel";
       $orderInfo=M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId')->where($map)->field($field)->select();
       
       $info=M('orders')->where(array('o.orderId'=>$id))->field('o.createTime,oe.trackNumber,ox.pinyin,ox.expressCompany')->join('as o left join oto_order_express as oe on oe.orderId=o.orderId left join oto_express as ox on ox.id=oe.exId')->find();
       $this->info=$info;
       $expressInfo['data'][0]['time']=$info['createTime'];
       $expressInfo['data'][0]['context']="等待配送";
       if($info['trackNumber']){
           $expressInfo=$this->checkExpress($info['pinyin'],$info['trackNumber']);
       }
       $this->assign('expressInfo',$expressInfo);
       
       $goodsDB=M('order_goods');
       $areaDB=M('areas');
       foreach ($orderInfo as $k=>$v){
           $orderInfo[$k]['goods']=$goodsDB->where(array('orderId'=>$v['orderId']))->select();
           $orderInfo[$k]['province']=$areaDB->where(array('areaId'=>$v['areaId1']))->getField('areaName');
           $orderInfo[$k]['city']=$areaDB->where(array('areaId'=>$v['areaId2']))->getField('areaName');
           $orderInfo[$k]['area']=$areaDB->where(array('areaId'=>$v['areaId3']))->getField('areaName');
       }
       //检查订单是否评价过
       $isPJ['o.isRefund']=0;
       $isPJ['o.isClosed']=0;
       $isPJ['o.orderFlag']=1;
       $isPJ['o.isPay']=1;
       $isPJ['o.orderStatus']=4;
       $isPJ['_string']="date_sub(curdate(), INTERVAL 7 DAY) <= date(`signTime`)";
       $isPJ=M('orders')->where($isPJ)->join('as o join oto_share as s on o.orderId=s.orderId')->find();
       if($isPJ){
           $orderInfo[0]['isPJ']=1;
       }else{
           if(!$isPJ['isRefund']||$isPJ['isRefund']>0){//退款的订单不能评价
               $orderInfo[0]['isPJ']=1;
           }else{
               $orderInfo[0]['isPJ']=0;
           }
       }
       $this->orderInfo=$orderInfo;
       $this->display();
   }
   
   
   //取消订单
   public function cancelOrder(){
      if(IS_AJAX){
          if(!session('oto_userId')){
              $this->ajaxReturn(array('status'=>-3));
              return;
          }
          $id=I('id');
          if(!$id){
              $this->ajaxReturn(array('status'=>-1));
          }
          $isChangeStatus=M('orders')->where(array('orderId'=>$id))->getField('orderStatus');
          if($isChangeStatus!=-2){
              $this->ajaxReturn(array('status'=>-1));
              return;
          }
          $res=M('orders')->where(array('orderId'=>$id))->setField(array('orderStatus'=>-1));
          if($res){
              $data = array();
              $data["orderId"] = $id;
              $data["logContent"] = "取消订单成功！";
              $data["logUserId"] = session('oto_userId');
              $data["logType"] = 0;
              $data["logTime"] = date('Y-m-d H:i:s');
              $mlogo = M('log_orders');
              $mlogo->add($data);
              $this->ajaxReturn(array('status'=>0));
          }else{
              $this->ajaxReturn(array('status'=>-1));
          }
      }
   }
   
   //退款处理页面
   public function refund(){
       $id=I('id');
       if(!$id){
           $this->redirect(U('Wx/Index/index','','',0));
           return;
       }
       $field="orderId,needPay,deliverMoney";
       $map['payType']=array('gt',0);
       $map['isPay']=1;
       $map['isRefund']=0;
       $map['userId']=session('oto_userId');
       $orderInfo=M('orders')->where(array('orderId'=>$id))->field($field)->find();
       if(!$orderInfo){
           $this->redirect(U('Wx/Index/index','','',0));
           return;
       }
       $this->orderInfo=$orderInfo;
       $this->display();
   }
   
   //退款列表
   public function refundList(){
       $map['isClosed']=0;
       $map['orderFlag']=1;
       $map['o.userId']=session('oto_userId');
       $map['orderStatus']=array('in','-6,-7,-3');
       $field="o.orderId,o.orderNo,o.areaId1,o.isRefund,o.areaId2,o.areaId3,o.shopId,o.deliverMoney,o.payType,o.isSelf,o.deliverType,o.userName,o.userAddress,o.userPhone,o.needPay,s.shopName,s.shopImg,s.shopTel,r.money as refundMoney";
       $refund=M('orders')->join('as o left join oto_shops  as s on o.shopId=s.shopId left join oto_refund as r on r.orderId=o.orderId')
       ->where($map)->field($field)->select();
       $goodsDB=M('order_goods');
       $areaDB=M('areas');
       foreach ($refund as $k=>$v){
           $refund[$k]['goods']=$goodsDB->where(array('orderId'=>$v['orderId']))->select();
       }
       $this->refund=$refund;
       $this->display();
   }
   
   //退款详情
   public function refundDetail(){
       $id=I('id');
       if(!$id){
           $this->redirect(U('Wx/Index/index','','',0));
           return;
       }
       $field="o.orderId,o.isRefund,s.shopName,r.*";
       $info=M('orders')->where(array('o.orderId'=>$id))->field($field)->join('as o join oto_refund as r on o.orderId=r.orderId left join oto_shops as s on s.shopId=o.shopId')->find();
       $this->info=$info;
       $this->display();
   }
   
   //退款申请
   public function applyRefund(){
       $id=I('id');
       $money=I('money');
       $resion=I('resion');
       $explain=I('explain');
       $img=I('img');
       if(!is_numeric($money)||$money<=0){
           //非法
           $this->ajaxReturn(array('status'=>-5));
           return;
       }
       if(!session('oto_userId')){
           $this->ajaxReturn(array('status'=>-3));
           return ;
       }
       $isRefund=M('orders')->where(array('orderId'=>$id))->find();
       if($isRefund){
           if($isRefund['isRefund']==1||$isRefund['orderStatus']<0){
               //已经申请过
               $this->ajaxReturn(array('status'=>-2));
               return;
           }
       }else{
           //不存在此订单
           $this->ajaxReturn(array('status'=>-4));
           return;
       }
       $data['orderId']=$id;
       $data['explain']=$explain;
       //$data['money']=$money;
       $data['money']=$isRefund['needPay'];
       $data['reason']=$resion;
       $data['type']=2;
       $data['time']=time();
       $data['images']=$img;
       $data['shopid']=$isRefund['shopId'];
       $data['refundNo']=time();
       $data['userid']=session('oto_userId');
       M()->startTrans();
       $db=M('refund');
       if($db->create($data)){
           $A=$db->add();
           $B=M('orders')->where(array('orderId'=>$id))->setField(array('isRefund'=>1));
           $orderStatus=-6;
           if($isRefund['orderStatus']==0){
               $orderStatus=-1;
               //商家不接单，客户已付款取消订单，直接退款到余额,并做日志记录
               $logData["orderId"] = $isRefund['orderId'];
               $logData["logContent"] = '商家没接单，客户取消订单，退款到余额';
               $logData["logUserId"] = session('oto_userId');
               $logData["logType"] = 0;
               $logData["logTime"] = date('Y-m-d H:i:s');
               $F=M('log_orders')->add($logData);
               $userMoney=M('users')->where(array('userId'=>session('oto_userId')))->getField('userMoney');
               
               $C=M('orders')->where(array('orderId'=>$id))->setField(array('orderStatus'=>$orderStatus));
               //退款到余额
               $D=M('users')->where(array('userId'=>session('oto_userId')))->setInc('userMoney',$isRefund['needPay']);
               $userMoney=$userMoney+$isRefund['needPay'];
               $E=$this->moneyRecord(2,$isRefund['needPay'],$isRefund['orderId'],1,session('oto_userId'),$userMoney,'商家不接单，取消订单',0);
               if($A&&$B&&$C&&$D&&$E){
                   M()->commit();
                   $this->ajaxReturn(array('status'=>-0));
                   return;
               }else{
                   M()->rollback();
                   $this->ajaxReturn(array('status'=>-1));
                   return;
               }
               
           }else if($isRefund['orderStatus']==1){
               $orderStatus=-6;
           }else if($isRefund['orderStatus']==2){
               $orderStatus=-7;
           }
           $C=M('orders')->where(array('orderId'=>$id))->setField(array('orderStatus'=>$orderStatus));
           if($A&&$B&&$C){
               M()->commit();
               $this->ajaxReturn(array('status'=>-0));
           }else{
               M()->rollback();
               $this->ajaxReturn(array('status'=>-1));
           }
       }else{
           $this->ajaxReturn(array('status'=>-1));
       }
       
  
   }
   

   
   //上传退款的三张图片
   public function refundUploadImg(){
       //上传头像
           if(!session('oto_userId')){
               echo "文件上传失败";
               return;
           }
           import('ORG.Net.UploadFile');
           $upload = new \UploadFile();
           $upload->autoSub = true;
           $upload->subType = 'custom';
           $data=date('Y-m',time());
           if ($upload->upload('./upload/refund/'.$data.'/')){
               $info = $upload->getUploadFileInfo();
           }
           $file_newname = $info['0']['savename'];
           $MAX_SIZE = 20000000;
           if($info['0']['type'] !='image/jpeg' && $info['0']['type'] !='image/jpg' && $info['0']['type'] !='image/pjpeg' && $info['0']['type'] != 'image/png' && $info['0']['type'] != 'image/x-png'){
               echo "2";exit;
           }
           if($info['0']['size']>$MAX_SIZE)
               echo "上传的文件大小超过了规定大小";
            
           if($info['0']['size'] == 0)
               echo "请选择上传的文件";
           switch($info['0']['error'])
           {
               case 0:
                   echo 'upload/users/'.$data.'/'.$file_newname;
                   break;
               case 1:
                   echo "上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值";
                   break;
               case 2:
                   echo "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
                   break;
               case 3:
                   echo "文件只有部分被上传";
                   break;
               case 4:
                   echo "没有文件被上传";
                   break;
       }
   }
   
   public  function delOrder(){
       if(IS_AJAX){
           $oid=I('oid');
           if(!$oid){
               //非法操作
               $this->ajaxReturn(array('status'=>-2));
               return;
           }
           $orderInfo=M('orders')->where(array('orderId'=>$oid))->find();
           if(!$orderInfo){
               //非法操作
               $this->ajaxReturn(array('status'=>-2));
               return;
           }
           if($orderInfo['orderFlag']==0){
               //订单已经删除
               $this->ajaxReturn(array('status'=>-3));
               return;
           }
           //更新商家金额
           $A=M('orders')->where(array('orderId'=>$orderInfo['orderId'],'userId'=>session('oto_userId')))->setField('orderFlag',0);
           if($A){
               $this->ajaxReturn(array('status'=>0));
           }else{
               $this->ajaxReturn(array('status'=>-1));
           }
       }
   }
   
   //确认收货
   public function confirmOrder(){
       if(IS_AJAX){
           $oid=I('oid');
           if(!$oid){
               //非法操作
               $this->ajaxReturn(array('status'=>-2));
               return;
           }
           $orderInfo=M('orders')->where(array('orderId'=>$oid))->find();
           if(!$orderInfo){
                 //非法操作
               $this->ajaxReturn(array('status'=>-2));
               return;
           }
           if($orderInfo['orderStatus']==4){
               //订单已经是收货
               $this->ajaxReturn(array('status'=>-3));
               return;
           }
           //更新商家金额
           M()->startTrans();
           $A=M('shops')->where(array('shopId'=>$orderInfo['shopId']))->setInc('bizMoney',$orderInfo['needPay']);
           
           //商家收入记录
           $data['orderId']=$oid;
           $data['money']=$orderInfo['needPay'];
           $data['time']=date('Y-m-d H:i:s',time());
           $data['shopId']=$orderInfo['shopId'];
           $B=M('biz_income')->add($data);
           
           $C=$this->scoreRecord(1,$orderInfo['needPay'],$orderInfo['orderNo'],1,$orderInfo['userId']);
           $D=M('orders')->where(array('orderId'=>$orderInfo['orderId'],'userId'=>$orderInfo['userId']))->setField(array('orderStatus'=>4,'signTime'=>date("Y-m-d H:i:s"),'isConfirm'=>1));
           if($A&&$B&&$C&&$D){
               M()->commit();
               $this->ajaxReturn(array('status'=>0));
           }else{
               M()->rollback();
               $this->ajaxReturn(array('status'=>-1));
           }
       }

   }
   
   // 创建 去付款，代付，批量付
   public function caretePayInfo(){
       $backUrl=$_SERVER['HTTP_REFERER'];
       $ids=I('ids');
       $orderId=explode(',', $ids);
       if(!$orderId){
           $this->redirect(U($backUrl,'','',0));
           return;
       }
       //判断订单状态
       $ispayInfo=M('orders')->where(array('orderId'=>array('in',$ids)))->field('isPay,orderNo')->select();
       $ispay=true;
       foreach($ispayInfo as $k=>$v){
           if($v['isPay']==1){
               $ispay=false;
           }
       }
       //已经付过
       if(!$ispay){
           $this->redirect(U('Orders/orders'),'','',0);
           return;
       }
       
       $pMap['orderId']=0;
       $pMap['pid']=0;
       $pMap['createTime']=date('Y-m-d H:i:s',time());
       M()->startTrans();
       $pDB=M('orders_payid');
       $pid=$pDB->add($pMap);
       $sonNum=0;
       foreach ($orderId as $k=>$v){
           $sMap['orderId']=$v;
           $sMap['pid']=$pid;
           $sMap['createTime']=date('Y-m-d H:i:s',time());
           $sRes=$pDB->add($sMap);
           if($sRes){
               $sonNum++;
           }
       }
       if(count($orderId)==$sonNum&&$pid){
           M()->commit();
           session('payid',$pid);//付款ID
           $this->redirect(U('Confirm/onlinkPay',array('payid'=>$pid),'',0));
       }else{
           M()->rollback();
           $this->redirect(U($backUrl,'','',0));
       }
       
   }
   
   
   
   // 积分操作记录
   /**
    * 构造函数
    * @param $type 1购物，2取消订单，3充值，4订单无效，5活动,6评价订单
    * @param $score 积分
    * @param $shopid 店铺ID
    * @param $orderid 订单ID或者充值ID
    * @param $IncDec 积分变动0为减，1加
    * @param $userid 用户ID
    * @param $totalscore 用户剩余总积分
    */
   public function scoreRecord($type = '', $payMoney=0, $orderNo = '', $IncDec = '', $userid = 0) {
       $score=floor($payMoney);
       if($score<=0){
           return;
       }
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
   
   public function expressDetail(){
       $id=I('id');
       $info=M('orders')->where(array('o.orderId'=>$id))->field('o.createTime,oe.trackNumber,ox.pinyin,ox.expressCompany')->join('as o left join oto_order_express as oe on oe.orderId=o.orderId left join oto_express as ox on ox.id=oe.exId')->find();
       $this->info=$info;
       $expressInfo['data'][0]['time']=$info['createTime'];
       $expressInfo['data'][0]['context']="等待发货";
       if($info['trackNumber']){
         $this->expressInfo=$this->checkExpress($info['pinyin'],$info['trackNumber']);
       }
       $this->display();
   }
   
   
}