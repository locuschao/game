<?php
namespace Wx\Model;
use Think\Model;
use Wx\Controller\BaseController;
/**
 * 购物车服务类
 */
class CartModel extends Model {
	protected $tableName = 'goods'; 
	
	/**
	 * 添加[正常]商品到购物车
	 */
	public function addToCart(){
		//判断一下该商品是否正常出售
		$goodsInfo = self::getGoodsInfo((int)I("goodsId"),I("goodsAttrId"),(int)I("isSeckill",0),(int)I("isGroup",0));
		$id = I("goodsAttrId");
		$isseckill=I('isSeckill',0);
		$isgroup=I('isGroup',0);
		if($goodsInfo['goodsId']=='')return array();
		$goodsInfo["cnt"] = ((int)I("gcount")>0)?(int)I("gcount"):1;
		$goodsInfo["ischk"] = 1;
		$cartgoods = array();
		$totalMoney = 0;
		
		$map['userId']= session('oto_userId');
		$isCarExists=M('car_session')->where($map)->field('car_session,userId')->find();
		
		$WST_CART=unserialize($isCarExists['car_session']);
		
		if($isCarExists&&empty($WST_CART)){
		    //数据库存在空数据
		    M('car_session')->where(array('userId'=>session('oto_userId')))->delete();
		}
		
		//$WST_CART = session("WST_CART");
		//如果购物车为空则放入购物车中
		if(empty($WST_CART)){
			$shopcat = array();
			$goodsAttrId = '';
			$price = 0.0;
			foreach($goodsInfo as $k=>$v){
				if(is_numeric($k)) {
				    //拼接属性ID
					$goodsAttrId .= $goodsInfo[$k]['goodsAttrId'].'_';
					//属性为属性价格之和
					$price +=  $goodsInfo[$k]['shopPrice'];
				}
			}
			//团购商品不改变价格
			if($isgroup==1){
				if($price != 0){
					$goodsInfo['shopPrice'] = $goodsInfo['groupPrice'];
				}
			}
			//秒杀商品不改变价格
			if($isseckill==1){
				if($price != 0){
					$goodsInfo['shopPrice'] = $goodsInfo['seckillPrice'];
				}
			}
			
			$goodsAttrId = trim($goodsAttrId,'_');
			//按升序排序
			$temp=explode('_', $goodsAttrId);
			asort($temp);
			$goodsAttrId=implode('_', $temp);
			//如果没有属性，默认为0
			if(empty($goodsAttrId)){
			    $goodsAttrId=0;
			}
			//商品ID_属性ID_ 秒杀_团购
			$shopcat[$goodsInfo["goodsId"]."_".$goodsAttrId."_".$isseckill."_".$isgroup] = $goodsInfo;
			/* $totalMoney += $goodsInfo["cnt"]*$goodsInfo["shopPrice"];
			$cartgoods[] = $goodsInfo; */
			//保存数据 
		    $saveData['car_session']=serialize($shopcat);
		    $saveData['userId']=session('oto_userId');
		    $isCarExists=M('car_session')->add($saveData);
		}else{
			//如果购物车不为空则要看下该商品是否原来就存在了。
			$shopcat = $WST_CART;
			$goodsAttrId = '';
			
			//属性ID拼接
			foreach($goodsInfo as $k=>$v){
				if(is_numeric($k)) {
					$goodsAttrId .= $goodsInfo[$k]['goodsAttrId'].'_';
				}
			}
			//按升序排序属性
			$goodsAttrId = trim($goodsAttrId,'_');
			$temp=explode('_', $goodsAttrId);
			asort($temp);
			$goodsAttrId=implode('_', $temp);
			if(empty($goodsAttrId)){
			    $goodsAttrId=0;
			}
			
			//如果已经存在则要把数量相加
			if(!empty($shopcat[$goodsInfo['goodsId']."_".$goodsAttrId."_".$isseckill.'_'.$isgroup])){
			    
				$goodsInfo["cnt"]=$WST_CART[$goodsInfo["goodsId"]."_".$goodsAttrId."_".$isseckill.'_'.$isgroup]["cnt"]+$goodsInfo["cnt"];
			}
			
			$shopcat[$goodsInfo["goodsId"]."_".$goodsAttrId."_".$isseckill.'_'.$isgroup] = $goodsInfo;
            //重新把购物车内的数据拿到外边
		/* 	foreach($shopcat as $key=>$cgoods){	
				$totalMoney += $cgoods["cnt"]*$cgoods["shopPrice"];
				$cartgoods[] = $cgoods;
			} */
			 $isCarExists=M('car_session')->where($map)->save(array('car_session'=>serialize($shopcat)));
			//session("WST_CART",$shopcat);
		}
		return $id;
	}
	/**
	 * 获取商品信息
	 */
	public function getGoodsInfo($goodsId,$goodsAttrId = 0,$isSeckill,$isGroup){
		$sql = "SELECT g.attrCatId,g.goodsId,g.isGroup,g.goodsSn,g.goodsName,g.goodsThums,g.shopId,g.marketPrice,g.shopPrice,g.goodsStock,g.bookQuantity,g.isBook,sp.shopName,g.isSeckill 
				FROM __PREFIX__goods g ,__PREFIX__shops sp WHERE g.shopId=sp.shopId AND goodsFlag=1 and isSale=1 and goodsStatus=1 and g.goodsId = $goodsId ";
		$goodslist = $this->query($sql);
		//如果选择的是团购商品
		if($isGroup==1){
			$goodsGroupModel = M('GoodsGroup');
			$group = $goodsGroupModel->where("goodsId = $goodsId")->find();
			//加入团购信息
			$goodslist[0] = array_merge($goodslist[0],$group);
			//把商品价变为团购价
			$goodslist[0]['shopPrice'] = $group['groupPrice'];
			$goodslist[0]['isGroup'] = 1;//团购标志
		}else{
		    $goodslist[0]['isGroup'] = 0;//团购标志
		}
		
		//如果选择的是秒杀商品
		if($isSeckill==1){
		    $seckillModel = M('goods_seckill');
		    $secKill = $seckillModel->where("goodsId = $goodsId")->find();
		    //加入团购信息
		    $goodslist[0] = array_merge($goodslist[0],$secKill);
		    //把商品价变为团购价
		    $goodslist[0]['shopPrice'] = $secKill['seckillPrice'];
		    $goodslist[0]['isSeckill'] = 1;//团购标志
		}else{
		    $goodslist[0]['isSeckill'] = 0;//团购标志
		}
		//如果商品有价格属性的话则获取其价格属性
		if(!empty($goodslist) && $goodslist[0]['attrCatId']>0){
			$sql = "select ga.id,ga.attrPrice,ga.attrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
			        where a.attrId=ga.attrId and a.catId=".$goodslist[0]['attrCatId']." and a.isPriceAttr=1 
			        and ga.goodsId=".$goodslist[0]['goodsId']." and id in(".$goodsAttrId.")";
			$priceAttrs = $this->query($sql);
			if(!empty($priceAttrs)){
				foreach($priceAttrs as $k=>$v){
					$goodslist[0][$k]['attrId'] = $v['attrId'];
					$goodslist[0][$k]['goodsAttrId'] = $v['id'];
					$goodslist[0][$k]['attrName'] = $v['attrName'];
					$goodslist[0][$k]['attrVal'] = $v['attrVal'];
					$goodslist[0][$k]['shopPrice'] = $v['attrPrice'];
					$goodslist[0][$k]['goodsStock'] = $v['attrStock'];
				}
			}
		}
		return $goodslist[0];
	}
	
	/**
	 * 获取购物车信息
	 */
	public function getCartInfo(){
		$totalMoney = 0;
		$map['userId']= session('oto_userId');
		$isCarExists=M('car_session')->where($map)->field('car_session,userId')->find();
		$dbCarInfo=unserialize($isCarExists['car_session']);
		$shopcart = $dbCarInfo?$dbCarInfo:array();
		$cartgoods = array();
		foreach($shopcart as $goodsId=>$cgoods){
			$temp = explode('_',$goodsId);
			//去除末尾团购标志
			$isgroup=array_pop($temp);
			//去除秒杀标志
			$isseckill=array_pop($temp);
			
			$goodsAttrId = array();
			foreach($temp as $k=>$v) {
				if ($k == 0) {
					$goodsId = (int)$v;
				} else {
					$goodsAttrId[] = (int)$v;
				}
			}
			$goodsAttrId = implode(',',$goodsAttrId);
			$sql = "SELECT  g.goodsThums,g.goodsId,g.isGroup,g.isSeckill,g.shopPrice,g.isBook,g.goodsName,g.shopId,g.goodsStock,g.shopPrice,shop.shopName,shop.qqNo,shop.deliveryType,shop.shopAtive,
					shop.shopTel,shop.shopAddress,shop.deliveryTime,shop.isInvoice, shop.deliveryStartMoney,shop.shopImg,
					shop.deliveryFreeMoney,shop.deliveryMoney ,g.goodsSn,shop.serviceStartTime,shop.serviceEndTime
					FROM __PREFIX__goods g, __PREFIX__shops shop
					WHERE g.goodsId = $goodsId AND g.shopId = shop.shopId AND g.goodsFlag = 1 and g.isSale=1 and g.goodsStatus=1 ";
			$goods = self::queryRow($sql);
		    //如果商品有价格属性的话则获取其价格属性
		    if(!empty($goods) && $cgoods['attrCatId']>0){
			    $sql = "select ga.id,ga.attrPrice,ga.attrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
			             where a.attrId=ga.attrId and a.catId=".$cgoods['attrCatId']." and a.isPriceAttr=1
			             and ga.goodsId=".$goodsId." and id in(".$goodsAttrId.") order by ga.id asc";
				$priceAttrs = $this->query($sql);
				if(!empty($priceAttrs)){
					foreach($priceAttrs as $k=>$v){
						$goods[$k]['goodsAttrId'] = $v['id'];
						$goods[$k]['attrName'] = $v['attrName'];
						$goods[$k]['attrVal'] =$v['attrVal'];
						$goods[$k]['shopPrice'] = $v['attrPrice'];
						$goods[$k]['goodsStock'] = $v['attrStock'];
					}
				}
			}
			
			//如果是团购商品
			$base=new BaseController();
			if($isgroup){
			    $goodsGroupModel = M('GoodsGroup');
			    $group = $goodsGroupModel->where("goodsId = $goodsId")->find();
			    //加入团购信息
			    $goods = array_merge($goods,$group);
			    $groupInfo=$base->groupStock($goodsId, 1);
			    $goods['goodsStock']=$groupInfo['goodsStock'];
			    //把商品价变为团购价
			    $goods['shopPrice'] = $group['groupPrice'];
			    $goods['isGroup'] = 1;
			    $time=time();
			    if($group['goodsGroupStatus']==1&&$group['groupStatus']==1&&$group['startTime']&&$time>$group['startTime']&&$time<$group['endTime']){
			        //是否还可以团购
			        $goods['groupStatus'] = 1;
			    }else{
			        $goods['groupStatus'] = 0;
			    }
			}else{
			    $goods['isGroup'] = 0;
			}
			//如果是秒杀商品
			if($isseckill){
			    $secmillDB = M('goods_seckill');
			    $secKill = $secmillDB->where("goodsId = $goodsId")->find();
			    //加入团购信息
			    $goods = array_merge($goods,$secKill);
			    $secKillInfo=$base->secKillStock($goodsId, 1);
			    $goods['goodsStock']=$secKillInfo['goodsStock'];
			    //把商品价变为团购价
			    $goods['shopPrice'] = $secKill['seckillPrice'];
			    $time=time();
			    if($secKill['goodsSeckillStatus']==1&&$secKill['seckillStatus']==1&&$time>$secKill['seckillStartTime']&&$time<$secKill['seckillEndTime']){
			        $goods['seckillStatus'] = 1;
			    }else{
			        $goods['seckillStatus'] = 0;
			    }
			    $goods['isSeckill'] = 1;
			}else{
			    $goods['isSeckill'] = 0;
			}

			if($goods["isBook"]==1){
				$goods["goodsStock"] = $goods["goodsStock"]+$goods["bookQuantity"];
			}
			$goods["goodsAttrId"] = str_replace(',', '_', $goodsAttrId);
			$goods["cnt"] = $cgoods["cnt"];
			$goods["ischk"] = $cgoods["ischk"];
			$totalCnt += $cgoods["cnt"];
			$price = 0;
			foreach($goods as $k=>$v){
				if(is_numeric($k)){
					$price += $v["shopPrice"];
				}
			}
			if($price!=0 && $goods['isGroup']!=1&& $goods['isSeckill']!=1){
				$shopPrice = $price;
			}else{
				$shopPrice = $cgoods["shopPrice"];
			}
			$cartgoods[$goods["shopId"]]["shopgoods"][] = $goods;
			$cartgoods[$goods["shopId"]]["deliveryFreeMoney"] = $goods["deliveryFreeMoney"];//店铺免运费最低金额
			$cartgoods[$goods["shopId"]]["deliveryMoney"] = $goods["deliveryMoney"];//店铺配送费
			$cartgoods[$goods["shopId"]]["deliveryStartMoney"] = $goods["deliveryStartMoney"];//店铺配送费
			$cartgoods[$goods["shopId"]]["totalCnt"] = $cartgoods[$goods["shopId"]]["totalCnt"]+$cgoods["cnt"];
			$cartgoods[$goods["shopId"]]["totalMoney"] = $cartgoods[$goods["shopId"]]["totalMoney"]+($goods["cnt"]*$shopPrice);
			$totalMoney += $goods["cnt"]*$shopPrice;
		}

		$cartInfo = array();
		$cartInfo["totalMoney"] = $totalMoney;
		$cartInfo["cartgoods"] = $cartgoods;
		return $cartInfo;
		
	}
   /**
    * 选中的购物车商品库存问题
    */
	public  function checkSelectGoodsStock(){
	    //商品id_数量_属性id
            $get=I('goodsId_num_attrId');
            $arr=explode(',', $get);
            foreach ($arr as $k=>$v){
                $temp = explode('_',$v);
                //价格属性逻辑上数量是一样的，所以此处只查一个属性的数量即可
                $goodsId = (int)$temp[0];
                $isGroup=array_pop($temp);//团购
                $isSeckill=array_pop($temp);//秒杀
                $goodsAttrId = (int)$temp[2];
                $obj = array();
                $obj["goodsId"] = $goodsId;
                $obj["goodsAttrId"] = $goodsAttrId;
                $obj["isGroup"] = $isGroup;
                $obj["isSeckill"] = $isSeckill;
                $goods = $this->getGoodsStock($obj);
                if($goods["isBook"]==1){
                    $goods["goodsStock"] = $goods["goodsStock"]+$goods["bookQuantity"];
                }
                $goods["cnt"] =(int)$temp[1]; //传过来的产品数量
                unset($temp[0]);
                unset($temp[1]);
                $goods["attrId"] =implode('_', $temp); 
                $goods["stockStatus"] = ($goods["goodsStock"]>=$goods["cnt"])?1:0;
                $cartgoods[] = $goods;
            }
			return $cartgoods;
	}
	
	/**
	 * 检测购物车中商品库存
	 */
	public function checkCatGoodsStock(){
	    $map['userId']= session('oto_userId');
	    $isCarExists=M('car_session')->where($map)->field('car_session,userId')->find();
	    $WST_CART=unserialize($isCarExists['car_session']);
		$shopcart = $WST_CART?$WST_CART:array();	
		foreach($shopcart as $key=>$cgoods){
			$temp = explode('_',$key);
			//价格属性逻辑上数量是一样的，所以此处只查一个属性的数量即可
			$goodsId = (int)$temp[0];
			$isGroup=array_pop($temp);//团购
			$isSeckill=array_pop($temp);//秒杀
			$goodsAttrId = (int)$temp[1];
			$obj = array();
			$obj["goodsId"] = $goodsId;
			$obj["goodsAttrId"] = $goodsAttrId;
			$obj["isGroup"] = $isGroup;
			$obj["isSeckill"] = $isSeckill;
			$goods = $this->getGoodsStock($obj);
			if($goods["isBook"]==1){
				$goods["goodsStock"] = $goods["goodsStock"]+$goods["bookQuantity"];
			}
			$goods["cnt"] = $cgoods["cnt"];
			$goods["stockStatus"] = ($goods["goodsStock"]>=$goods["cnt"])?1:0;		
			$cartgoods[] = $goods;
		}
		return $cartgoods;
	}
	
	/**
	 * 获取商品库存
	 */
	public function getGoodsStock($data){

	    $goodsId = $data['goodsId'];
	    $isBook = $data['isBook'];
	    $goodsAttrId = $data['goodsAttrId'];//属性的一个值 
	    $isGroup=$data['isGroup'];
	    $isSeckill=$data['isSeckill'];
	    
	    if($isBook==1){
	        $sql = "select goodsId,(goodsStock+bookQuantity) as goodsStock from __PREFIX__goods where isSale=1 and goodsFlag=1 and goodsStatus=1 and goodsId=".$goodsId;
	    }else{
	        $sql = "select goodsId,goodsStock,attrCatId from __PREFIX__goods where isSale=1 and goodsFlag=1 and goodsStatus=1 and goodsId=".$goodsId;
	    }
	    $goods = M()->query($sql);
	    if($goods[0]['attrCatId']>0){
	        //价格属性
	        $sql = "select ga.id,ga.attrStock from __PREFIX__goods_attributes ga where ga.goodsId=".$goodsId." and id=".$goodsAttrId;
	        $priceAttrs = $this->query($sql);
	        if(!empty($priceAttrs))$goods[0]['goodsStock'] = $priceAttrs[0]['attrStock'];
	    }
	    
	    $goods[0]['isGroup']=0;
	    $goods[0]['isSeckill']=0;
	    
	    //团购库存
	    if($isGroup==1){
	        $a=new BaseController();
	        $group=$a->groupStock($goodsId, 1);
	        $goods[0]['goodsStock']=$group['goodsStock'];
	        $goods[0]['isGroup']=1;
	    }
	    //秒杀库存
	    if($isSeckill==1){
	        $a=new BaseController();
	        $secKill=$a->secKillStock($goodsId, 1);
	        $goods[0]['goodsStock']=$secKill['goodsStock'];
	        $goods[0]['isSeckill']=1;
	        $goods[0]['seckillMaxCount']=$secKill['seckillMaxCount'];
	    }
	    if(empty($goods))return array();
	    return $goods[0];
	}
	
	/**
	 * 删除购物车中的商品
	 */
	public function delCartGoods(){
		$goodsKey = (int)I("goodsId")."_".I("goodsAttrId");
		$shopcart = session("WST_CART")?session("WST_CART"):array();
		$map['userId']= session('oto_userId');
		$isCarExists=M('car_session')->where($map)->field('car_session,userId')->find();
		$dbCarInfo=unserialize($isCarExists['car_session']);
		$shopcart=session("WST_CART");
		$newShopcat = array();
		foreach($shopcart as $key=>$cgoods){	
			if($goodsKey != $key){
				$newShopcat[$key] = $cgoods;
			}			
		}
		if($isCarExists['userId']){
		    $isCarExists=M('car_session')->where($map)->save(array('car_session'=>serialize($newShopcat)));
		}else{
		    $saveData['car_session']=serialize($newShopcat);
		    $saveData['userId']=session('oto_userId');
		    $isCarExists=M('car_session')->add($saveData);
		}
		//session("WST_CART",$newShopcat);
		return 1;
	}
	
	/**
	 * 修改购物车中的商品数量
	 * 
	 */
	public function changeCartGoodsnum(){
	    //按升序排序属性
	    $goodsAttr=I("goodsAttrId");
	    $isGround=(int)I('isGround',0);
	    $isSeckill=(int)I('isSeckill',0);
	    $temp=explode('_', $goodsAttr);
	    asort($temp);
	    $goodsAttrId=implode('_', $temp);
		if(I("goodsAttrId")){
			$goodsKey = (int)I("goodsId")."_".$goodsAttrId.'_'.$isSeckill.'_'.$isGround;	
		}else{
			$goodsKey = (int)I("goodsId")."_".$isSeckill.'_'.$isGround;
		}
		$num = abs((int)I("num"));
		$ischk = (int)I("ischk",0);
		$map['userId']= session('oto_userId');
		$isCarExists=M('car_session')->where($map)->field('car_session,userId')->find();
		$dbCarInfo=unserialize($isCarExists['car_session']);
		$shopcart = $dbCarInfo?$dbCarInfo:array();
		$newShopcart = array();
		foreach($shopcart as $key=>$cgoods){	
			$cartgoods = $shopcart[$key];
			if($goodsKey == $key){
				$cartgoods["cnt"] = $num;
				$cartgoods["ischk"] = $ischk;
			}
			$newShopcart[$key] = $cartgoods;	
		}
		$isCarExists=M('car_session')->where($map)->save(array('car_session'=>serialize($newShopcart)));
		return 1;
	}
	
	/**
	 * 用来处理内容中为空的判断
	 */
	public function checkEmpty($data,$isDie = false){
	    foreach ($data as $key=>$v){
	        if(trim($v)==''){
	            if($isDie)die("{status:-1,'key'=>'$key'}");
	            return false;
	        }
	    }
	    return true;
	}
	
	/**
	 * 输入sql调试信息
	 */
	public function logSql($m){
	    echo $m->getLastSql();
	}
	
	
	/**
	 * 获取一行记录
	 */
	public function queryRow($sql){
	    $plist = $this->query($sql);
	    return $plist[0];
	}
	

	
}