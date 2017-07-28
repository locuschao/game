<?php
namespace Wx\Controller;

use Think\Controller;
use Think\Model;
class IndexController extends BaseController {
    public  $ip;
    public  $cityInfo;
    public  $userid;
    public function _initialize(){
       parent::_initialize();
      @header('Content-type: text/html;charset=UTF-8');
      $this->ip='219.137.150.255 ';
//       $this->ip=get_client_ip();
      $this->cityInfo=GetIpLookup($this->ip);
      if(session('oto_userId')){
          $this->userid=session('oto_userId');
      }
    }
<<<<<<< .mine
    public function pays(){
        echo 2222;return;
        $this->display();
    }
||||||| .r788
=======
    public function pay(){
        echo 2222;return;
        $this->display();
    }
>>>>>>> .r852
    public function test(){
        $a=array();
        $c=unserialize('a:2:{s:18:"18_320_321_324_0_0";a:19:{s:9:"attrCatId";s:1:"7";s:7:"goodsId";s:2:"18";s:7:"isGroup";s:1:"0";s:7:"goodsSn";s:5:"SN123";s:9:"goodsName";s:6:"龙眼";s:10:"goodsThums";s:44:"Upload/goods/2016-03/56f38e1aadab5_thumb.png";s:6:"shopId";s:2:"40";s:11:"marketPrice";s:4:"19.0";s:9:"shopPrice";s:3:"6.0";s:10:"goodsStock";s:2:"11";s:12:"bookQuantity";s:1:"0";s:6:"isBook";s:1:"0";s:8:"shopName";s:6:"分享";s:9:"isSeckill";s:1:"0";i:0;a:6:{s:6:"attrId";s:2:"18";s:11:"goodsAttrId";s:3:"324";s:8:"attrName";s:5:"O-古";s:7:"attrVal";s:4:"nice";s:9:"shopPrice";s:3:"3.5";s:10:"goodsStock";s:2:"17";}i:1;a:6:{s:6:"attrId";s:2:"17";s:11:"goodsAttrId";s:3:"321";s:8:"attrName";s:5:"O-旧";s:7:"attrVal";s:3:"two";s:9:"shopPrice";s:3:"2.0";s:10:"goodsStock";s:2:"11";}i:2;a:6:{s:6:"attrId";s:2:"16";s:11:"goodsAttrId";s:3:"320";s:8:"attrName";s:5:"O-新";s:7:"attrVal";s:3:"one";s:9:"shopPrice";s:3:"1.0";s:10:"goodsStock";s:2:"11";}s:3:"cnt";i:4;s:5:"ischk";i:1;}s:10:"15_276_0_0";a:17:{s:9:"attrCatId";s:1:"6";s:7:"goodsId";s:2:"15";s:7:"isGroup";s:1:"0";s:7:"goodsSn";s:22:"购物车测试商品1";s:9:"goodsName";s:22:"购物车测试商品1";s:10:"goodsThums";s:44:"Upload/goods/2016-03/56f38c93c7f30_thumb.png";s:6:"shopId";s:2:"40";s:11:"marketPrice";s:4:"12.0";s:9:"shopPrice";s:4:"11.0";s:10:"goodsStock";s:2:"20";s:12:"bookQuantity";s:1:"0";s:6:"isBook";s:1:"0";s:8:"shopName";s:6:"分享";s:9:"isSeckill";s:1:"0";i:0;a:6:{s:6:"attrId";s:2:"13";s:11:"goodsAttrId";s:3:"276";s:8:"attrName";s:7:"类型1";s:7:"attrVal";s:6:"红色";s:9:"shopPrice";s:4:"11.0";s:10:"goodsStock";s:1:"0";}s:3:"cnt";i:1;s:5:"ischk";i:1;}}');
        p($c);return;
        $d=unserialize($c);
        if($d){
            echo 1;
        }else{
            echo 2;
        }
    }
    
	public function index(){

	    $db = M ( 'ads' );
		$date = date( 'Y-m-d', time ());
		
		$where ['adPositionId'] = '-4';
		$where ['_string'] = " '$date' between  adStartDate  and  adEndDate";
		//图片轮播
		$adInfo = $db->where ( $where )->field ( 'adId,adfile,adurl' )->order ( 'adSort ASC' )->select ();
		$this->assign('adInfo',$adInfo);
		
		//首页头条
		$where ['adPositionId'] = '2';
		$indexHotAd=$db->where($where)->field('adName,adurl')->order('adId DESC')->find();
		$this->assign('indexHotAd',$indexHotAd);
		
	
		//一条广告
		$where ['adPositionId'] = '5';
		$indexImg=$db->where($where)->field('adFile,adurl')->order('adId DESC')->find();
		$this->assign('indexImg',$indexImg);
		//精品
		$this->assign('isBest',$this->boutique('isBest'));
		//新品
		$this->assign('isNew',$this->boutique('isNew'));
		//热销
		$this->assign('isHot',$this->boutique('isHot'));
	/* 	echo strtotime('2016-4-29 16:00:00');
		p( strtotime('2016-4-29 20:00:00'));return; */
		//用户信息
		$userScore=M('users')->where(array('userId'=>session('oto_userId')))->getField('userScore');
		$this->userScore=$userScore;
		
		//积分商品列表
		$smap['goodsStock']=array('gt',0);
		$smap['goodsFlag']=1;
		$scoreGoods=M('integral')->where($smap)->select();
		$this->scoreGoodsInfo=$scoreGoods;
		
		
		//购物车信息
		$m = D('Wx/Cart');
		$cartInfo = $m->getCartInfo();
	
		$sortingOrder=$this->sortingOrder($cartInfo);
       
		$this->assign('cartInfo',$sortingOrder);
		
		//购物车总数
		$this->assign('goodsCnt',$this->getCartGoodCnt());
		
		$this->assign('cityInfo',$this->cityInfo);
		
		//秒杀
		$this->secKill();
		
		//猜你喜欢
		$this->guess();
		
		//个人中心退款中，待发货，待评价等订单的统计信息
		$this->assign('stats',$this->stats());
		//店铺分类
		$cat=M('goods_cats')->where(array('isShow'=>1,'catFlag'=>1))->order('catSort ASC')->select();
		$res=$this->arrayPidProcess($cat);
		$this->assign('cate',$res);
		$this->uinfo=M('users')->where(array('userId'=>session('oto_userId')))->field('userPhone,userPhoto')->find();
		//p($res);return;
		$this->display();
	}

//猜你喜欢
public function guess(){
    
    $field="g.goodsId,g.goodsName,g.goodsThums,g.shopPrice,g.shopId";
    $where['g.goodsFlag']=1;
    $where['g.goodsStatus']=1;
    $where['s.shopStatus']=1;
    $where['s.shopAtive']=1;
    $where['s.shopFlag']=1;
    $h=date('H',time());
    $where['_string']=" s.serviceStartTime<= $h && $h <= s.serviceEndTime ";
    $goodsInfo=M('goods')->field($field)->where($where)->limit(3)->join("as g left join oto_shops as s on g.shopId=s.shopId ")->order('RAND()')->select();
    if(IS_AJAX){
    $this->ajaxReturn($goodsInfo);
    }else{
        $this->guessInfo=$goodsInfo;
    }
}

	
//当前时间段秒杀活动
public  function secKill(){
    $page=I('page',0);
    $time=time();
    $step=3;
    $limit=$step*$page;
    $field="g.goodsId,g.goodsName,g.goodsThums,k.seckillPrice";
    $map['goodsSeckillStatus']=1;
    $map['seckillStatus']=1;
    $map['_string']="$time between k.seckillStartTime and k.seckillEndTime";
    $info=M('goods_seckill')->join('as k join oto_goods as g on g.goodsId=k.goodsId')->limit($limit,$step)->where($map)->select();
    if(IS_AJAX){
        $this->ajaxReturn($info);
    }else{
        $this->secKillInfo=$info;
    }
    
}
	
	
	
//统计退款，待付款订单数据 
public function stats(){
    $info=array();
    //待付款
    $map['isRefund']=0;
    $map['isClosed']=0;
    $map['orderFlag']=1;
    $map['isPay']=0;
    $map['orderStatus']=-2;
    $waitPay=M('orders')->where($map)->count();
    $info['waitPay']=$waitPay;
    
    //待发货
    $map['orderStatus']=array('between','0,2');
    unset($map['payType']);
    unset($map['isPay']);
    $map['_string']="isPay=1 or payType=0";
    $deliver=M('orders')->where($map)->count();
    $info['waitDeliver']=$deliver;
    
    //待收货
    $map['orderStatus']=3;
    $receiving=M('orders')->where($map)->count();
    $info['waitReceiving']=$receiving;
    
    //待评价
    //AND ( date_sub(curdate(), INTERVAL 7 DAY) <= date(`signTime`) )
    $sql = 'SELECT o.orderId, o.orderNo, o.areaId1, o.areaId2, o.areaId3, o.shopId, o.deliverMoney, o.payType, o.isSelf, o.deliverType, o.userName, o.userAddress, o.userPhone, o.needPay, s.shopName, s.shopImg FROM `oto_orders` as o left join oto_shops as s on o.shopId=s.shopId WHERE (`isRefund` = 0) AND (`isClosed` = 0) AND (`orderFlag` = 1) AND (`orderStatus` = 4)  AND orderId NOT IN ( SELECT orderId FROM oto_goods_appraises WHERE orderId = o.orderId AND goodsId  IN ( SELECT goodsId FROM oto_order_goods WHERE orderId = o.orderId ) ) and o.userId='.session('oto_userId') ;
    $evaluate = M()->query($sql);
    $total=0;
    $goodsDB = M('order_goods');
    foreach ($evaluate as $k => $v) {
        $evaluate[$k]['goods'] = $goodsDB->where(array(
            'orderId' => $v['orderId']
        ))->select();
        $total+=count($evaluate[$k]['goods']);
    }
    $this->needEvaluate=$total;
    $info['waitEvaluate']=$total;
    
    //售后
    unset($map['_string']);
    $map['orderStatus']=array('in',"-6,-7,-3");
    $refund=M('orders')->where($map)->count();
    $info['waitRefund']=$refund;
    return $info;
}
	
//递归
function arrayPidProcess($data,$res=array(),$pid='0'){
    foreach ($data as $k => $v){
        if($v['parentId']==$pid&&$v['isShow']==1&&$v['catFlag']==1){
            $res[$v['catId']]['info']=$v;
            $res[$v['catId']]['child']=$this->arrayPidProcess($data,array(),$v['catId']);
        }
    }
    return $res;
 }
	
	//团购
	public function group(){
	    $this->display();
	}
	//超市
	public function supermarket(){
	    $parentCatid=I('parentId',334);
	    //$parentCatid=334;//超市
	    //二级分类
	    $sonCat=M('goods_cats')->where(array('parentId'=>$parentCatid,'catFlag'=>1))->field('catId,catName')->order('catSort ASC')->select();
	    $this->assign('cat',$sonCat);
	    //精品推荐
	    $baseMap['goodsCatId1']=$parentCatid;
	    $baseMap['goodsStatus']=1;
	    $baseMap['isGroup']=0;
	    $baseMap['isBest']=1;
	    $baseMap['goodsFlag']=1;
	    $baseMap['goodsStock']=array('gt',0);
	    $field="goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
	    $goods=M('goods')->where($baseMap)->field($field)->limit(3)->select();
	    //默认第一个三级显示
	    unset($baseMap['isBest']);
	    $secondCat=M('goods_cats')->where(array('parentId'=>$sonCat[0]['catId'],'catFlag'=>1))->field('catId,catName')->order('catSort ASC')->select();
	    $baseMap['goodsCatId2']=$sonCat[0]['catId'];
	    $goodsHot=M('goods')->where($baseMap)->field($field)->limit(8)->select();
	       
	    $this->cateName=M('goods_cats')->where(array('catId'=>$parentCatid))->getField('catName');
	    $this->assign('secondCat',$secondCat);
	    $this->assign('baseGoods',$goods);
	    $this->assign('hotGoods',$goodsHot);
	    $this->display();
	}
	//加载三级菜单
	public  function thirdCat(){
	    if(IS_AJAX){
    	    $secondCatId=I('id');
    	    $secondCat=M('goods_cats')->where(array('parentId'=>$secondCatId,'catFlag'=>1))->field('catId,catName')->order('catSort ASC')->select();
	        //默认加载第一个分类的内容
    	    $baseMap['goodsCatId2']=$secondCatId;
    	    $baseMap['goodsStatus']=1;
    	    $baseMap['isGroup']=0;
    	    $baseMap['isBest']=1;
    	    $baseMap['goodsFlag']=1;
    	    $baseMap['goodsStock']=array('gt',0);
    	    $field="goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
    	    $goods=M('goods')->where($baseMap)->field($field)->limit(8)->select();
    	    foreach ($goods as $k=>$v){
    	        $goods[$k]['url']=U('goodsDetail',array('id'=>$v['goodsId']));
    	    }
    	    $res=array();
    	    $res['goods']=$goods;
    	    $res['cat']=$secondCat;
    	    $this->ajaxReturn($res);
	    }
	  }
	  
	 //加载三级分类下的内容
	  //加载三级菜单
	  public  function thirdCatGoodsInfo(){
	      if(IS_AJAX){
	          $page=I('page');
	          $catid=I('id');
	          //默认加载第一个分类的内容
	          $baseMap['goodsCatId3']=$catid;
	          $baseMap['goodsStatus']=1;
	          $baseMap['isGroup']=0;
	          $baseMap['isBest']=1;
	          $baseMap['goodsFlag']=1;
	          $baseMap['goodsStock']=array('gt',0);
	          $field="goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
	          $limit=8;
	          $start=$page*$limit;
	          $goods=M('goods')->where($baseMap)->field($field)->limit($start,$limit)->select();
	          foreach ($goods as $k=>$v){
	              $goods[$k]['url']=U('goodsDetail',array('id'=>$v['goodsId']));
	          }
	          $this->ajaxReturn($goods);
	      }
	  }
	//加载更多
	public  function moreSupermarket(){
	        $parentCatid=I('parentId',334);
	        //$parentCatid=334;//超市
	        $page=I('page');
	        //精品推荐
	        $catid2=I('catid2');
	        $catid3=I('catid3');
	        if($catid3){
	            $baseMap['goodsCatId3']=$catid3;
	        }else if($catid2){
	            $baseMap['goodsCatId2']=$catid2;
	        }else{
	            $baseMap['goodsCatId1']=$parentCatid;
	        }
	        $baseMap['goodsStatus']=1;
	        $baseMap['isGroup']=0;
	        //$baseMap['isBest']=1;
	        $baseMap['goodsFlag']=1;
	        $baseMap['goodsStock']=array('gt',0);
	        $step=8;
	        $limit=$step*$page;
	        $field="goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
	        $goods=M('goods')->where($baseMap)->field($field)->limit($limit,$step)->select();
	        foreach ($goods as $k=>$v){
	            $goods[$k]['url']=U('goodsDetail',array('id'=>$v['goodsId']));
	        }
	        $this->ajaxReturn($goods);
	}
	//加载更多
	public  function switchMoreSupermarket(){
	   // $parentCatid=334;//超市
	    $parentCatid=I('parentId',334);
	    $page=I('page');
	    //精品推荐
	    $baseMap['goodsCatId1']=$parentCatid;
	    $baseMap['goodsStatus']=1;
	    $baseMap['isGroup']=0;
	    $baseMap['isBest']=1;
	    $baseMap['goodsFlag']=1;
	    $baseMap['goodsStock']=array('gt',0);
	    $step=3;
	    $limit=$step*$page;
	    $field="goodsId,goodsName,marketPrice,shopPrice,goodsStock,goodsThums";
	    $goods=M('goods')->where($baseMap)->field($field)->limit($limit,$step)->select();
	    foreach ($goods as $k=>$v){
	        $goods[$k]['url']=U('goodsDetail',array('id'=>$v['goodsId']));
	    }
	    $this->ajaxReturn($goods);
	}
	//订单详情
	public  function goodsDetail(){
	    $id=I('get.id');
	    $isgroup=I('get.group');
	    $isSecKill=I('get.secKill');
	    //判断此团购是否有效
	    if($isgroup){
	        $sql="select id,groupPrice from oto_goods_group WHERE unix_timestamp(now()) BETWEEN startTime AND endTime and goodsGroupStatus=1 and groupStatus=1 and goodsId=".$id;
	        $group=M()->query($sql);
	        if($group){
	            $this->isgroup=1;
	        }else{
	            $this->isgroup=0;
	        }
	    }else{
	        $this->isgroup=0;
	    }
	    //判断此秒杀是否有效
	    if($isSecKill){
	        $sql="select id,seckillPrice,seckillMaxCount from oto_goods_seckill WHERE unix_timestamp(now()) BETWEEN  seckillStartTime AND seckillEndTime and goodsSeckillStatus=1 and seckillStatus=1 and goodsId= ".$id;
	        $seckill=M()->query($sql);
	        if($seckill){
	            $this->isSeckill=1;
	        }else{
	            $this->isSeckill=0;
	        }
	    }else{
	        $this->isSeckill=0;
	    }
	    $filed="g.goodsSn,g.marketPrice,g.goodsName,g.marketPrice,g.shopPrice,g.goodsStock,g.goodsUnit,g.goodsDesc,g.goodsId,g.goodsThums";
	    $map['g.goodsFlag']=1;
	    $map['g.goodsId']=$id;
	    $map['g.goodsStatus']=1;
	    $goodsInfo=M('goods as g')->field($filed)->where($map)->find();
	    $goodsInfo['goodsDesc']=html_entity_decode($goodsInfo['goodsDesc']);
	    //如果是团购，则使用团购价
	    if($isgroup&&$group[0]['seckillPrice']){
	        $goodsInfo['shopPrice']=$group[0]['seckillPrice'];
	    }
	    //如果是秒杀，则使用秒杀价
	    if($isSecKill&&$seckill[0]['seckillPrice']){
	        $goodsInfo['shopPrice']=$seckill[0]['seckillPrice'];
	        $goodsInfo['maxNum']=$seckill[0]['seckillMaxCount'];
	    }
	    //图片轮播 
	    $scrollImg=M('goods_gallerys')->where(array('goodsId'=>$id))->field('goodsImg')->select();
	    //是否关注
	    $isAttentition=M('favorites')->where(array('targetId'=>$id,'favoriteType'=>0))->find();
	    if($isAttentition){
	        $goodsInfo['attentition']=1;
	    }else{
	        $goodsInfo['attentition']=0;
	    }
	    //商品属性
	    $attrMap['g.goodsId']=$id;
	    $attrMap['a.isPriceAttr']=0;
	    $attrMap['a.attrFlag']=1;
	    $attrField="g.goodsId,g.attrVal,a.attrName";
	    $attr=M('goods_attributes')->where($attrMap)->field($attrField)->join('as g left join oto_attributes as a on a.attrId=g.attrId')->select();
	    $this->assign('attr',$attr);
	
	    //用户默认地址
	    $userAddr=M('user_address')->where(array('userId'=>session('oto_userId'),'isDefault'=>1,'addressFlag'=>1))->find();
	    $areaDb=M('areas');
	    $userAddr['province']=$areaDb->where(array('areaId'=>$userAddr['areaId1']))->getField('areaName');
	    $userAddr['city']=$areaDb->where(array('areaId'=>$userAddr['areaId2']))->getField('areaName');
	    $userAddr['area']=$areaDb->where(array('areaId'=>$userAddr['areaId3']))->getField('areaName');
	    $userAddr['communitys']=M('communitys')->where(array('communityId'=>$userAddr['communityId']))->getField('communityName');
	    $this->userAddr=$userAddr;
	    
	    
	    //月销量
	    $m_map['g.goodsId']=$id;
	    $m_map['o.isPay']=1;
	    $m_time=time();
	    $m_map['_string']="(paytime-$m_time)<2592000";
	    $monthCount=M('order_goods')->where($m_map)->join('as g left join oto_orders as o on g.orderId=o.orderId')->count();
	    $goodsInfo['monthCount']=$monthCount;
	    
	    //销售属性 如：颜色尺码 ，不同价格
	    $saleAttr['g.goodsId']=$id;
	    $saleAttr['a.isPriceAttr']=1;//价格属性
	    $saleAttr['a.attrFlag']=1;
	    $saleAttrField="g.goodsId,g.attrId,g.attrPrice,g.attrStock,g.attrVal,g.id";
	    $saleAttrInfo=M('goods_attributes')->where($saleAttr)->field($saleAttrField)->join('as g left join oto_attributes as a on a.attrId=g.attrId')->select();
	    if($saleAttrInfo){
	        $goodsTotal=0;
	        foreach ($saleAttrInfo as $k=>$v){
	            $goodsTotal+=$v['attrStock'];
	        }
	        //库存为所有属性总和
	        $goodsInfo['goodsStock']=$goodsTotal;
	    }
	    $parentAttrField="a.shopId,a.catId,a.attrName,a.attrId";
	    $parentAttrInfo=M('goods_attributes')->where($saleAttr)->field($parentAttrField)->join('as g left join oto_attributes as a on a.attrId=g.attrId')->group('attrId')->select();
	    foreach ($parentAttrInfo as $k=>$v){
	       foreach ($saleAttrInfo as $kk=>$vv){
	           if($v['attrId']==$vv['attrId']){
	               $parentAttrInfo[$k]['child'][]=$vv;
	           }
	       }
	    }
// 	    p($parentAttrInfo);return;
	    $this->assign('saleAttr',$parentAttrInfo);
	    
	    //评价晒单
	    $share_db=M('share');
	    $shareMap['goodsId']=$id;
	    $shareField="u.userId,u.userName,s.goodsId,s.shareTitle,s.shareContent,s.shareImg1,s.shareImg2,s.shareImg3,s.shareImg4,s.shareTime,s.star,(4-s.star) as star_";
	    $countShare=$share_db->where($shareMap)->count();
	    $avgShare=$share_db->where($shareMap)->avg('star');
	    $avgShare=($avgShare/5)*100;
	    $shareInfo=$share_db->where($shareMap)->field($shareField)->join('as s left join oto_users as u on u.userId=s.userId')->limit(5)->select();
	    $this->assign('shareCount',array('total'=>$countShare,'star'=>$avgShare));
	    $this->assign('shareInfo',$shareInfo);
	    $this->assign('cityInfo',$this->cityInfo);
	    $this->assign('scrollImg',$scrollImg);
	    $this->assign('goodsInfo',$goodsInfo);
	    $this->assign('goodsCnt',$this->getCartGoodCnt());
	    
	    
        //购物车是否已经有此产品
        $carInfo=M('car_session')->where(array('userId'=>session('oto_userId')))->getField('car_session');
        $carInfo=unserialize($carInfo);
        $select=array();
        foreach ($carInfo as $k=>$v){
            $attr='';
            if($id==$v['goodsId']){
                foreach ($v as $kk=>$vv){
                    if(is_numeric($kk)){
                       $attr.=$vv['attrName'].':'.$vv['attrVal'].' '.',';
                    }
                }
                $attr=trim($attr,',');
                $select[]=array('goodsName'=>$v['goodsName'],'goodsAttr'=>$attr);
            }
        }
	    $this->select=$select;
	    
	    
	    $this->display();
	}

	//加载更多评价内容
	public function moereComment(){
	    //评价晒单
	    if(IS_AJAX){
	        $page=I('page');
	        $id=I('id');
	        $share_db=M('share');
	        $shareMap['goodsId']=$id;
	        $shareField="u.userId,u.userName,s.goodsId,s.shareTitle,s.shareContent,s.shareImg1,s.shareImg2,s.shareImg3,s.shareImg4,s.shareTime,s.star,(4-s.star) as star_";
	        $countShare=$share_db->where($shareMap)->count();
	        $avgShare=$share_db->where($shareMap)->avg('star');
	        $avgShare=($avgShare/5)*100;
	        $step=5;
	        $star=$step*$page;
	        $shareInfo=$share_db->where($shareMap)->field($shareField)->join('as s left join oto_users as u on u.userId=s.userId')->limit($star,$step)->select();
	        $this->ajaxReturn($shareInfo);
	    }
	}
	
	//首页-》精品
	public function boutique($type='isBest'){
	    $type=I('type')?I('type'):$type;
	    $page=I('page')?I('page'):0;
	    $location='广州';
	    $areaId=M('areas')->where(array('areaName'=>array('like','%'.$location.'%')))->limit(1)->getField('areaId');
	    $field="g.goodsId,g.goodsName,g.goodsThums,g.shopPrice,g.shopId";
	    $where['g.goodsFlag']=1;
	    $where['g.goodsStatus']=1;
	    $where['s.shopStatus']=1;
	    $where['s.shopAtive']=1;
	    $where['s.shopFlag']=1;
	    switch($type){
	        case 'isBest' :$where['g.isBest']=1; break;
	        case 'isHot' :$where['g.isHot']=1; break;
	        case 'isNew' :$where['g.isNew']=1; break;
	    }
	    $h=date('H',time());
	    $num=3;
	    $star=$page*3;
	    $where['_string']=" s.serviceStartTime<= $h && $h <= s.serviceEndTime ";
	    //->group('g.shopId')
        $goodsInfo=M('goods')->field($field)->where($where)->limit($star,$num)->join("as g left join oto_shops as s on g.shopId=s.shopId and (s.areaId1=$areaId or s.areaId2=$areaId or s.areaId3=$areaId )")->select();
	    if(IS_AJAX){
	        $this->ajaxReturn($goodsInfo);
	    }else{
	       return $goodsInfo;
	    }
	}
	/**
	 * 根据商品ID返回店铺ID
	 */
	public  function getShopIdByGoodsId($goodsId){
	    $shopId=M('goods')->where(array('goodsId'=>$goodsId))->getField('shopId');
	    return $shopId;
	}
	/**
	 * 操作成功 status=0
	 */
	public function ajaxSuccess(){
	    $this->ajaxReturn(array('status'=>0));
	}
	/**
	 * 操作失败 status=-1
	 */
	public function ajaxFail(){
	    $this->ajaxReturn(array('status'=>-1));
	}
	/**
	 * 需要登录 status=-2
	 */
	public function ajaxNeedLogin(){
	    $this->ajaxReturn(array('status'=>-2));
	}
	/**
	 * 关注商品
	 */
	public function attenTition(){
	    if(IS_AJAX){
	        if(!$this->userid) return  $this->ajaxNeedLogin();
	        $id=I('goodsId');
	        $db=M('favorites');
	        $map['targetId']=$id;
	        $map['userId']=$this->userid;
	        $map['favoriteType']=0;
	        $isExis=$db->where($map)->find();
	        if($isExis){
	            $del=$db->where($map)->delete();
	            if($del){
	                $this->ajaxSuccess();
	            }else{
	                $this->ajaxFail();
	            }
	        }else{
	            $map['createTime']=date('Y-m-d H:i:s',time());
	            $add=$db->add($map);
	            if($add){
	                $this->ajaxSuccess();
	            }else{
	                $this->ajaxFail();
	            }
	        }
	    }
	}
	/**
	 * 加入购物车
	 * 
	 */
	public  function addCart(){
	    if(!session('oto_userId')){
	        $this->ajaxNeedLogin();
	        return;
	    }
	    $province=I('province');
	    $city=I('city');
	    $area=I('area');
	    $community=I('community');
	    if(empty($province)||empty($city)||empty($area)){
	        $this->ajaxReturn(array('status=>-5'));
	        return;
	    }
	    //配送范围判断 
	    $goodsId=I('goodsId');
	    $shopid=M('goods')->where(array('goodsId'=>$goodsId))->getField('shopId');
	    if($community){
	        $range=M('shops_communitys')->field('communityId')->where(array('shopId'=>$shopid,'communityId'=>$community))->find();
	        if(!$range){
	            //不在配送范围
	            $this->ajaxReturn(array('status'=>-6));
	            return;
	        }
	    }else if($area){
	        //区级配送范围
	        $range=M('shops_communitys')->field('areaId3')->where(array('shopId'=>$shopid,'areaId3'=>$area))->find();
	        if(!$range){
	            //不在配送范围
	            $this->ajaxReturn(array('status'=>-6));
	            return;
	        }
	    }
	    //范围判断 结束 
	    
	    //判断库存
	    $goodsId=I('goodsId');
	    $attrId=I('goodsAttrId');
	    $gcount=I('gcount');
	    $isGroup=I('isGroup');
	    $isSeckill=I('isSeckill');
	    
	    //判断商品状态
	    $stotck=M('goods')->where(array('goodsId'=>$goodsId,'goodsStatus'=>1,'goodsFlag'=>1))->find();
	    if(!$stotck){
	        $this->ajaxReturn(array('status'=>-9,'msg'=>'商品已经下架'));
	        return;
	    }
	    
	    //判断库存用
	    $map['userId']= session('oto_userId');
	    $isCarExists=M('car_session')->where($map)->field('car_session,userId')->find();
	    $WST_CART=unserialize($isCarExists['car_session']);
	    
	    $goodsInfo = D('Wx/Cart')->getGoodsInfo((int)I("goodsId"),I("goodsAttrId"),(int)I("isSeckill",0),(int)I("isGroup",0));
	    $goodsAttrId = '';
	    $price = 0.0;
	    foreach($goodsInfo as $k=>$v){
	        if(is_numeric($k)) {
	            //拼接属性ID
	            $goodsAttrId .= $goodsInfo[$k]['goodsAttrId'].'_';
	        }
	    }
	    $goodsAttrId=rtrim($goodsAttrId,'_');
	    //按升序排序
	    $temp=explode('_', $goodsAttrId);
	    asort($temp);
	    $goodsAttrId=implode('_', $temp);
	    //如果没有属性，默认为0
	    if(empty($goodsAttrId)){
	        $goodsAttrId=0;
	    }
	    $cartNum=$WST_CART[$goodsInfo["goodsId"]."_".$goodsAttrId."_".$isSeckill.'_'.$isGroup]["cnt"];
	    
	    if($isGroup){
	        //团购最大数量
	        $maxGroup=M('goods_group')->where(array('goodsId'=>$goodsId,'groupStatus'=>1))->getField('groupMaxCount');
	        if(!$maxGroup){
	            $this->ajaxReturn(array('status'=>-7,'msg'=>'团购已结束'));
	            return;
	        }
	        //所有人下的团购订单数
	       $sql="select count(og.goodsNums) as saleNum from oto_order_goods as og join oto_goods_group as gg on og.goodsId=gg.goodsId and gg.groupStatus=1 join oto_orders as od on od.orderId=og.orderId and od.orderType=3 where og.goodsId=$goodsId";
	       $saleNum=M()->query($sql);
	       $saleNum=$saleNum[0]['saleNum'];//总销量
	       
	       if($saleNum>=$maxGroup){
	           $this->ajaxReturn(array('status'=>-8,'msg'=>'团购商品已售罄'));
	           return;
	       }
	      //库存
	       $short=$maxGroup-$saleNum;
	       
	       //购物车中的商品已经大于库存
	       if($cartNum&&$cartNum>=$short){
	           $this->ajaxReturn(array('status'=>-9,'msg'=>'库存不足'));
	           return;
	       }
	       
	       if($gcount >$short){
	           $this->ajaxReturn(array('status'=>-9,'msg'=>'团购商品库存只剩下'.$short.'份'));
	           return;
	       }
	       
	    }else if($isSeckill){
	        $seckillMap['goodsId']=$goodsId;
	        $seckillMap['seckillStatus']=1;
	        $seckillMap['goodsSeckillStatus']=1;
	        $time=time();
	        $seckillMap['_string']="$time between seckillStartTime and seckillEndTime";
	        $secKill=M('goods_seckill')->where($seckillMap)->getField('seckillMaxCount');
	        if(!$secKill){
	            $this->ajaxReturn(array('status'=>-10,'msg'=>'秒杀已结束'));
	            return;
	        }
	        $secKillStock=M('goods')->where(array('goodsId'=>$goodsId))->getField('goodsStock');
	        if(!$secKillStock){
	            $this->ajaxReturn(array('status'=>-12,'msg'=>'秒杀商品已售罄'));
	            return;
	        }
	        if($gcount>$secKillStock){
	            $this->ajaxReturn(array('status'=>-13,'msg'=>'秒杀商品库存只剩下'.$secKillStock.'份'));
	            return;
	        }

	        //限购，购物车中商品大于限购或者加车数量大小限购数量
	        if($gcount>$secKill||$cartNum>=$secKill){
	            $this->ajaxReturn(array('status'=>-14,'msg'=>'限购'.$secKill.'份'));
	            return;
	        }
	    }else{
	        if($attrId){
	            $attrstotck=M('goods_attributes')->where(array('goodsId'=>$goodsId,'id'=>array('in',$attrId)))->getField('attrStock');
	            if($cartNum>=$attrstotck){
	                $this->ajaxReturn(array('status'=>-15,'msg'=>'库存不足'));
	                return;
	            }
	            
	            if($gcount>$attrstotck){
	                $this->ajaxReturn(array('status'=>-10,'msg'=>'商品库存只剩下'.$attrstotck));
	                return;
	            }
	        }else{
	            if($cartNum>=$stotck['goodsStock']){
	                $this->ajaxReturn(array('status'=>-15,'msg'=>'库存不足'));
	                return;
	            }
	            if($gcount>$stotck['goodsStock']){
	               $this->ajaxReturn(array('status'=>-10,'msg'=>'商品库存只剩下'.$stotck['goodsStock'].'份'));
	                return;
	            }
	        }
	    }
	    $m = D('Wx/Cart');
	    $res = $m->addToCart();
	    $this->ajaxReturn($res);
	}
	/**
	 * 获取购物车商品数量
	 */
	public function getCartGoodCnt(){
	    $map['userId']= session('oto_userId');
		$isCarExists=M('car_session')->where($map)->field('car_session,userId')->find();
		if($isCarExists){
		    $shopcart=unserialize($isCarExists['car_session']);
		    $goodsCut=count($shopcart);
		    if(IS_AJAX){
		        if($goodsCut){
		            $this->ajaxReturn(array('goodscnt'=>$goodsCut));
		        }else {
		            $this->ajaxReturn(array('goodscnt'=>0));
		        }
		    }else{
		        return $goodsCut;
		    }
		}else{
		    if(IS_AJAX){
		          $this->ajaxReturn(array('goodscnt'=>0));
		    }else{
		        return 0;
		    }
		}
	}
	
	/**
	 * 获取商品库存
	 *
	 */
	public function getGoodsStock(){
	    $data = array();
	    $data['goodsId'] = (int)I('goodsId');
	    $data['isBook'] = (int)I('isBook');
	    $data['goodsAttrId'] = (int)I('goodsAttrId');
	    $data['isGroup']=I('isGround',0,intval);
	    $data['isSeckill']=I('isSeckill',0,intval);
	    $goods = D('Wx/Cart');
	    $goodsStock = $goods->getGoodsStock($data);
	    echo json_encode($goodsStock);
	
	}
	
	/**
	 * 修改购物车中的商品数量
	 *
	 */
	public function changeCartGoodsNum(){
	    $m = D('Wx/Cart');
	    $res = $m->changeCartGoodsnum();echo $res;die;
	    echo "{status:1}";
	}
	
	
	/**
	 * 检测购物车中商品库存
	 *
	 */
	public function checkCartGoodsStock(){
	    $m = D('Wx/Cart');
	    $res = $m->checkCatGoodsStock();
	    echo json_encode($res);
	}
	/**
	 * 检测选中的购物车中商品库存
	 *
	 */
	public function checkSelectGoodsStock(){
	    $m = D('Wx/Cart');
	    $res = $m->checkSelectGoodsStock();
	    echo json_encode($res);
	}
	
	public  function cart(){
	    $this->display();
	}
}
