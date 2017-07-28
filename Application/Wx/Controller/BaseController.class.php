<?php
namespace Wx\Controller;
use Think\Controller;
use Think\Model;

class BaseController extends Controller
{
    public function _initialize(){
       //session('oto_userId',50);
    }
    public function isLogin(){
        if(!session('oto_userId')){
            $this->redirect(U('Wx/Login/login','','',0));
        }
    }

//普通订单，团购订单，秒杀订单 分单操作
public function sortingOrder(&$cart,$coupon=0){
    $arr=array();
    $totalNum=0;
    $totalMoney=0;
    //按店铺团购，秒杀，普通 分单
    foreach ($cart['cartgoods'] as $k=>$cartgoods){
        //$k店铺ID
        foreach ($cartgoods as $kk=>$shopgoods){
            $totalCnt_A=0;  //普通 分单的商品数
            $totalCnt_B=0;  //团购分单的商品数
            $totalCnt_C=0;  //秒杀分单的商品数
            $sortingMoney_A=0;//普通订单总金额
            $sortingMoney_B=0;//团购
            $sortingMoney_C=0;//秒杀
            //$kk->shopgoods
            foreach ($shopgoods as $sk=>$goods){
                $totalNum++;
                if($goods['isGroup']==0&&$goods['isSeckill']==0){
                    //普通 订单
                    $arr['cartgoods'][$k.'_A']['shopgoods'][]=$goods;
                    $totalCnt_A=++$totalCnt_A;
                    $singelMoney=0;
                    //商品属性价格
                    foreach ($goods as $sk=>$sv){
                        if(is_numeric($sk)){
                            $singelMoney+=$sv['shopPrice'];
                        }
                    }
                    //没有属性则使用商品价格
                    if($singelMoney==0){
                        $sortingMoney_A+=$goods['cnt']*$goods['shopPrice'];
                    }else{
                        $sortingMoney_A+=$goods['cnt']*$singelMoney;
                    }
                    $arr['cartgoods'][$k.'_A']['deliveryFreeMoney']=$cart['cartgoods'][$k]['deliveryFreeMoney'];
                    $arr['cartgoods'][$k.'_A']['deliveryMoney']=$cart['cartgoods'][$k]['deliveryMoney'];
                    $arr['cartgoods'][$k.'_A']['deliveryStartMoney']=$cart['cartgoods'][$k]['deliveryStartMoney'];
                    $arr['cartgoods'][$k.'_A']['totalCnt']=$totalCnt_A;
                    $arr['cartgoods'][$k.'_A']['totalMoney']=$sortingMoney_A;
                    
                    //优惠卷列表
                   if($coupon){
                        $obj['shopId']=$goods['shopId'];
                        $obj['goodsId']=$goods['goodsId'];
                        $obj['money']=$sortingMoney_A;
                        $arr['cartgoods'][$k.'_A']['couponList']=$this->couponList($obj);
                    } 
                    
                    if($sortingMoney_A>$cart['cartgoods'][$k]['deliveryFreeMoney']){
                        $arr['cartgoods'][$k.'_A']['postage']=0;
                    }else{
                        $arr['cartgoods'][$k.'_A']['postage']=$cart['cartgoods'][$k]['deliveryMoney'];
                    }
                    if($sortingMoney_A>$cart['cartgoods'][$k]['deliveryStartMoney']){
                        $arr['cartgoods'][$k.'_A']['deliveryStar']=1;
                    }else{
                        $arr['cartgoods'][$k.'_A']['deliveryStar']=0;
                    }
                     
                }else if($goods['isGroup']==1){//秒杀
                    $arr['cartgoods'][$k.'_B']['shopgoods'][]=$goods;
                    //统计总数总价
                    $totalCnt_B=++$totalCnt_B;
                    $sortingMoney_B+=$goods['cnt']*$goods['shopPrice'];
                    $arr['cartgoods'][$k.'_B']['deliveryFreeMoney']=$cart['cartgoods'][$k]['deliveryFreeMoney'];
                    $arr['cartgoods'][$k.'_B']['deliveryMoney']=$cart['cartgoods'][$k]['deliveryMoney'];
                    $arr['cartgoods'][$k.'_B']['deliveryStartMoney']=$cart['cartgoods'][$k]['deliveryStartMoney'];
                    $arr['cartgoods'][$k.'_B']['totalCnt']=$totalCnt_B;
                    $arr['cartgoods'][$k.'_B']['totalMoney']=$sortingMoney_B;
                    if($sortingMoney_B>$cart['cartgoods'][$k]['deliveryFreeMoney']){
                        $arr['cartgoods'][$k.'_B']['postage']=0;
                    }else{
                        $arr['cartgoods'][$k.'_B']['postage']=$cart['cartgoods'][$k]['deliveryMoney'];
                    }
                    if($sortingMoney_B>$cart['cartgoods'][$k]['deliveryStartMoney']){
                        $arr['cartgoods'][$k.'_B']['deliveryStar']=1;
                    }else{
                        $arr['cartgoods'][$k.'_B']['deliveryStar']=0;
                    }
                }else if($goods['isSeckill']==1){
                    $arr['cartgoods'][$k.'_C']['shopgoods'][]=$goods;
                    //统计总数总价
                    $totalCnt_C=++$totalCnt_C;
                    $sortingMoney_C+=$goods['cnt']*$goods['shopPrice'];
                    $arr['cartgoods'][$k.'_C']['deliveryFreeMoney']=$cart['cartgoods'][$k]['deliveryFreeMoney'];
                    $arr['cartgoods'][$k.'_C']['deliveryMoney']=$cart['cartgoods'][$k]['deliveryMoney'];
                    $arr['cartgoods'][$k.'_C']['deliveryStartMoney']=$cart['cartgoods'][$k]['deliveryStartMoney'];
                    $arr['cartgoods'][$k.'_C']['totalCnt']=$totalCnt_C;
                    $arr['cartgoods'][$k.'_C']['totalMoney']=$sortingMoney_C;
                    if($sortingMoney_B>$cart['cartgoods'][$k]['deliveryFreeMoney']){
                        $arr['cartgoods'][$k.'_C']['postage']=0;
                    }else{
                        $arr['cartgoods'][$k.'_C']['postage']=$cart['cartgoods'][$k]['deliveryMoney'];
                    }
                    if($sortingMoney_C>$cart['cartgoods'][$k]['deliveryStartMoney']){
                        $arr['cartgoods'][$k.'_C']['deliveryStar']=1;
                    }else{
                        $arr['cartgoods'][$k.'_C']['deliveryStar']=0;
                    }
                }
            }
        }
    }
    //总价
    foreach ($arr['cartgoods'] as $cartgoods){
        $totalMoney+=$cartgoods['totalMoney'];
    }
    $arr['totalMoney']=$totalMoney;
    $arr['totalNum']=$totalNum;
    return $arr;
}

//取优惠卷
// $obj数组，商品ID，商店ID
public function couponList($obj){
    $userid=session('oto_userId');
    $goodsId=$obj['goodsId'];
    $shopId=$obj['shopId'];
    $moeny=$obj['money'];
    //店铺分类 
    $catInfo=M('goods')->where(array('goodsId'=>$goodsId))->field('goodsCatId1,goodsCatId2,brandId')->find();
    $catId1=$catInfo['goodsCatId1'];
    $catId2=$catInfo['goodsCatId2'];
    $brandId=$catInfo['brandId'];
    
    $sql=<<<Eof
    SELECT
    yh.name,
    yu.youhui_id,
	yu.surplus,
	yu.user_id,
	yh.youhui_scope,
	yh.youhui_type,
	yh.good_id,
	yh.supplier_id,
	yh.brand_id,
	yh.shop_cat_id,
	yh.breaks_menoy,
	yh.deal_cate_id,
    yh.shop_cat_type
FROM
	oto_youhui_user_link AS yu
JOIN oto_youhui AS yh ON yh.id = yu.youhui_id
WHERE
	yu.user_id = $userid 
AND yu.surplus>0 
AND yh.is_effect = 1
AND UNIX_TIMESTAMP(NOW()) BETWEEN yh.begin_time
AND yh.end_time 
AND (yh.good_id=$goodsId or yh.supplier_id=$shopId or yh.shop_cat_id=$catId1 or yh.shop_cat_id=$catId2 or yh.brand_id=$brandId)
Eof;
$info=M()->query($sql);
foreach ($info as $k=>$v){
    if($v['youhui_type']==0){
        $info[$k]['couponMoney']=$v['breaks_menoy'];
    }else if($v['youhui_type']==1){
        $info[$k]['couponMoney']=$moeny-($moeny*$v['breaks_menoy'])/10;
    }
}

return $info;
  
}


//检查商品信息
public function checkStotck(&$cartInfo){
    $arr=array();
    foreach($cartInfo['cartgoods'] as $sk=>$shopOrder){
        $orderType=explode('_', $sk);
        if($orderType[1]=='A'){
            foreach ($shopOrder as $gk=>$gv){
                foreach ($gv as $gg=>$goods){
                    $attr='';
                    foreach ($goods as $attrKey=>$attrVal){
                        if(is_numeric($attrKey)){
                            $attr.=$attrVal['goodsAttrId'].'_';
                        }
                    }
                    $attr=trim($attr,'_');
                    if($attr&&$goods['goodsId']){
                        $arr[$sk.'_'.$goods['goodsId'].'_'.$attr.'_0_0']=$this->plainGoods($goods['goodsId'], $goods['cnt'],$attr);
                    }else if(!$attr&&$goods['goodsId']){
                        $arr[$sk.'_'.$goods['goodsId'].'_0_0_0']=$this->plainGoods($goods['goodsId'], $goods['cnt']);
                    }
                }
            }
        }else if($orderType[1]=='B'){//团
            foreach ($shopOrder as $gk=>$gv){
                foreach ($gv as $gg=>$goods){
                    $attr='';
                    foreach ($goods as $attrKey=>$attrVal){
                        if(is_numeric($attrKey)){
                            $attr.=$attrVal['goodsAttrId'].'_';
                        }
                    }
                    $attr=trim($attr,'_');
                    if($attr&&$goods['goodsId']){
                        $arr[$sk.'_'.$goods['goodsId'].'_'.$attr.'_0_1']=$this->groupStock($goods['goodsId'],$goods['cnt']);
                    }else if(!$arr&&$goods['goodsId']){
                        $arr[$sk.'_'.$goods['goodsId'].'_0_0_1']=$this->groupStock($goods['goodsId'],$goods['cnt']);
                    }
                }
            }

        }else if($orderType[1]=='C'){//秒
            foreach ($shopOrder as $gk=>$gv){
                foreach ($gv as $gg=>$goods){
                    $attr='';
                    foreach ($goods as $attrKey=>$attrVal){
                        if(is_numeric($attrKey)){
                            $attr.=$attrVal['goodsAttrId'].'_';
                        }
                    }
                    $attr=trim($attr,'_');
                    if($attr&&$goods['goodsId']){
                        $arr[$sk.'_'.$goods['goodsId'].'_'.$attr.'_1_0']=$this->secKillStock($goods['goodsId'],$goods['cnt']);
                    }else if(!$arr&&$goods['goodsId']){
                        $arr[$sk.'_'.$goods['goodsId'].'_0_1_0']=$this->secKillStock($goods['goodsId'],$goods['cnt']);
                    }
                }
            }
        }
    }
  
    return $arr;
}
//普通商品,$goodsId商品ID，$num购买数据
public function plainGoods($goodsId,$num,$attr=0){
    $map['goodsId']=$goodsId;
    $field="isSale,goodsName,shopPrice,goodsStock,goodsStatus,goodsFlag";
    $info=M('goods')->field($field)->where($map)->find();
    if($attr){
        $attrid=explode('_', $attr);
        $attrid=(int)$attrid[0];
        $stock=M('goods_attributes')->where(array('id'=>$attrid))->getField('attrStock');
        $info['goodsStock']=$stock;
    }
    if($info['goodsFlag']==1&&$info['goodsStatus']==1){
        $info['isSale']=1;//正常销售
    }else{
        $info['isSale']=0;//禁售
    }
    if($info['isSale']){
        if($num>$info['goodsStock']){
            $info['isSale']=0;//库存不足
        }else{
            $info['isSale']=1;
        }
    }
    return $info;
}

//团购数量及状态
public function groupStock($goodsId,$num){
    $map['goodsId']=$goodsId;
    $field="isSale,goodsName,shopPrice,goodsStock,goodsStatus,goodsFlag";
    $info=M('goods')->field($field)->where($map)->find();
    $groupInfo=M('goods_group')->where(array('goodsId'=>$goodsId))->order('id DESC')->find();
    $info['shopPrice']=$groupInfo['groupPrice'];
    $sql="SELECT
    COUNT(*) AS tp_count
    FROM
    `oto_order_goods` AS og join oto_orders as odr on og.orderId=odr.orderId and odr.orderType=3 join oto_goods_group as gg  on og.goodsId=gg.goodsId  and gg.groupStatus=1
    WHERE
    (og.goodsId = $goodsId)
    LIMIT 1";
    $group=M()->query($sql);
    $groupStock=$group[0]['tp_count'];
    $info['goodsStock']=$groupInfo['groupMaxCount']-$groupStock;
    $time=time();
    if($groupInfo['goodsGroupStatus']==1&&$groupInfo['groupStatus']==1&&$time>$groupInfo['startTime']&&$time<$groupInfo['endTime']){
        $info['isSale']=1;
    }else{
        $info['isSale']=0;//团购状态正常
    }
    if($info['isSale']){
        if($num>$info['goodsStock']){
            $info['isSale']=0;//库存不足
        }else{
            $info['isSale']=1;
        }
    }
    return $info;
}
//秒杀库存
public function secKillStock($goodsId,$num){
    $map['goodsId']=$goodsId;
    $field="isSale,goodsName,shopPrice,goodsStock,goodsStatus,goodsFlag";
    $info=M('goods')->field($field)->where($map)->find();
    $secKillInfo=M('goods_seckill')->where(array('goodsId'=>$goodsId))->order('id DESC')->find();
    $info['shopPrice']=$secKillInfo['seckillPrice'];
    $info['seckillMaxCount']=$secKillInfo['seckillMaxCount'];
    $sql="SSELECT
    COUNT(*) AS tp_count
    FROM
    `oto_order_goods` AS og
    JOIN oto_orders AS odr ON og.orderId = odr.orderId
    AND odr.orderType = 2
    JOIN oto_goods_seckill AS gg ON og.goodsId = gg.goodsId
    AND gg.seckillStatus = 1
    WHERE
    (og.goodsId = $goodsId)
    LIMIT 1";
    $group=M()->query($sql);
    $groupStock=$group[0]['tp_count'];
    $info['goodsStock']=$secKillInfo['seckillMaxCount']-$groupStock;
    $info['goodsStock']=M('goods')->where($map)->getField('goodsStock');
    $time=time();
    if($secKillInfo['seckillStatus']==1&&$secKillInfo['goodsSeckillStatus']==1&&$time>$secKillInfo['seckillStartTime']&&$time<$secKillInfo['seckillEndTime']){
        $info['isSale']=1;//正常销售
    }else{
        $info['isSale']=0;//禁售
    }
    if($info['isSale']){
        if($num>$info['goodsStock']){
            $info['isSale']=0;//库存不足
        }else{
            $info['isSale']=1;
        }
    }
    return $info;
}

// 金额操作记录
/**
 * 构造函数
 * @param $type 操作类型,1下单，2取消订单，3充值，4提现,5订单无效
 * @param $money 金额
 * @param $orderNo 订单编号或者充值ID
 * @param $IncDec 余额变动 0为减，1加
 * @param $userid 用户ID
 * @param $balance 余额
 * @param $remark 其它备注信息
 */
public function moneyRecord($type = '', $money = 0, $orderNo = '', $IncDec = '', $userid = 0, $balance = 0,$remark='',$payWay=0) {
    $db = M ( 'money_record' );
    $data ['type'] = $type;
    $data ['money'] = $money;
    $data ['time'] = time ();
    $data ['ip'] = get_client_ip ();
    $data ['orderNo'] = $orderNo;
    $data ['IncDec'] = $IncDec;
    $data ['userid'] = $userid;
    $data ['balance'] = $balance;
    $data ['remark'] = $remark;
    $data ['payWay'] = $payWay;
    $res = $db->add ( $data );
    return $res;
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
// 支付记录
/**
 * 构造函数
 * @param $payType 支付类型 0余额支付，1支付宝，2微信
 * @param $orderNo 订单编号
 * @param $payTime 付款时间
 * @param $out_trade_no 第三方返回的流水号
 * @param $payMoney 金额
 * @param $userId 用户ID
 * @param $type 0订单，1充值
 * @param $notify_id 通知ID
 * @param $notify_time 通知时间
 * @param $buyer_email 支付宝帐号或者微信OPENID
 * */
public function payRecord($payType=0,$orderNo,$payTime,$out_trade_no,$payMoney,$userId,$type=0,$notify_id,$notify_time,$buyer_email){
    $data['payType']=$payType;//支付类型 0余额支付，1支付宝，2微信
    $data['orderNo']=$orderNo;
    $data['payTime']=$payTime;
    $data['out_trade_no']=$out_trade_no;
    $data['payMoney']=$payMoney;
    $data['userId']=$userId;
    $data['type']=$type;
    $data['notify_id']=$notify_id;
    $data['notify_time']=$notify_time;
    $data['buyer_email']=$buyer_email;
    $db=M('pay_record');
    $res=$db->add($data);
    return $res;
}
/**
 * 构造函数
 * @param $type 快递类型
 * @param $num 快递单号
 * 
 **/
public  function checkExpress($type='shentong',$num='229855869255'){
    $ch = curl_init("http://www.kuaidi100.com/query?type=$type&postid=$num") ;
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
    $output = curl_exec($ch) ;
    curl_close($ch);
    $info=json_decode($output,true);
    return $info;
    //返回格式如下
    /* [message] => ok
    [nu] => 229855869255
    [ischeck] => 1
    [com] => shentong
    [status] => 200
    [condition] => F00
    [state] => 3
    [data] => Array
    (
        [0] => Array
        (
            [time] => 2016-04-05 18:13:35
            [context] => 已签收,签收人是: 本人
            [ftime] => 2016-04-05 18:13:35
        )
    
        [1] => Array
        (
            [time] => 2016-04-05 13:40:34
            [context] => 上海浦东金桥公司(021-61561111) 的派件员 龚安 正在派件
            [ftime] => 2016-04-05 13:40:34
        )
    ) */
}




}