<?php
namespace Wx\Model;
use Wx\Controller\BaseController;
/**
 * 商品服务类
 */
class GoodsModel extends BaseModel {
	
	/**
	 * 商品列表
	 */
	public function getGoodsList($obj){
		$areaId2 = $obj["areaId2"];
		$areaId3 = $obj["areaId3"];
		$communityId = I("communityId");
		$c1Id = (int)I("c1Id",0);
		$c2Id = (int)I("c2Id");
		$c3Id = (int)I("c3Id");
		$pcurr = (int)I("pcurr");
		$msort = (int)I("msort",1);//排序标识
		$prices = I("prices");
		if($prices != ""){
			$pricelist = explode("_",$prices);
		}
		$brandId = I("brandId",0);
		
		$keyWords = urldecode(I("keyWords"));
		$words = array();
		if($keyWords!=""){
			$words = explode(" ",$keyWords);
		}
		/*
		$sql = "SELECT  g.goodsId,goodsSn,goodsName,goodsThums,goodsStock,g.saleCount,p.shopId,marketPrice,shopPrice,ga.id goodsAttrId 
				FROM __PREFIX__goods g left join __PREFIX__goods_attributes ga on g.goodsId=ga.goodsId and ga.isRecomm=1, __PREFIX__shops p ";*/
		//为了只对应一个goods记录，修改sql为
		$sql = "SELECT  g.goodsId,goodsSn,goodsName,goodsThums,goodsStock,g.saleCount,p.shopId,marketPrice,shopPrice 
				FROM __PREFIX__goods g, __PREFIX__shops p ";		
	    if($communityId>0){
			$sql .=" , __PREFIX__shops_communitys sc ";
		}
		
		if($brandId>0){
			$sql .=" , __PREFIX__brands bd ";
		}
		$sql .= "WHERE p.areaId2 = $areaId2 AND g.shopId = p.shopId AND  g.goodsStatus=1 AND g.goodsFlag = 1 and g.isSale=1 ";
		if($communityId>0){
			$sql .= " AND sc.shopId=p.shopId AND sc.communityId = $communityId ";
		}
		if($brandId>0){
			$sql .=" AND bd.brandId=g.brandId AND g.brandId = $brandId ";
		}
		if($c1Id>0){
			$sql .= " AND g.goodsCatId1 = $c1Id";
		}
		if($c2Id>0){
			$sql .= " AND g.goodsCatId2 = $c2Id";
		}
		if($c3Id>0){
			$sql .= " AND g.goodsCatId3 = $c3Id";
		}
		
		if($areaId3>0){
			$sql .= " AND p.areaId3 = $areaId3";
		}
		if(!empty($words)){
			$sarr = array();
			foreach ($words as $key => $word) {
				if($word!=""){
					$sarr[] = "g.goodsName LIKE '%$word%'";
				}
			}
			$sql .= " AND (".implode(" or ", $sarr).")";
		}
		$glist = $this->query($sql);
		$shops = array();
		$maxPrice = 0;
		for($i=0;$i<count($glist);$i++){
			$goods = $glist[$i];
			if($goods["shopPrice"]>$maxPrice){
				$maxPrice = $goods["shopPrice"];
			}
		}
	    if($prices != "" && $pricelist[0]>=0 && $pricelist[1]>=0){
			$sql .= " AND (g.shopPrice BETWEEN  ".(int)$pricelist[0]." AND ".(int)$pricelist[1].") ";
		}
	   
		if($msort==1){//综合
			$sql .= " ORDER BY g.saleCount DESC ";
		}else if($msort==6){//人气
			$sql .= " ORDER BY g.saleCount DESC ";
		}else if($msort==7){//销量
			$sql .= " ORDER BY g.saleCount DESC ";
		}else if($msort==8){//价格
			$sql .= " ORDER BY g.shopPrice ASC ";
		}else if($msort==9){//价格
			$sql .= " ORDER BY g.shopPrice DESC ";
		}else if($msort==10){//好评
				
		}else if($msort==11){//上架时间
			$sql .= " ORDER BY g.saleTime DESC ";
		}
		$pages = $this->pageQuery($sql,$pcurr,30);
		$goodsAttrModel = M('GoodsAttributes');
		$goodsIsRecomm = $goodsAttrModel->field('goodsId')->where('isRecomm = 1')->select();
		foreach($pages['root'] as $k=>$v){
			foreach($goodsIsRecomm as $vs){
				if($v['goodsId']==$vs['goodsId']){
					$pages['root'][$k]['isRecomm'] = 1;
				}
			}
		}
		$rs["maxPrice"] = $maxPrice;
		$brands = array();
		$sql = "SELECT b.brandId, b.brandName FROM __PREFIX__brands b, __PREFIX__goods_cat_brands cb WHERE b.brandId = cb.brandId AND b.brandFlag=1 ";
		if($c1Id>0){
			$sql .= " AND cb.catId = $c1Id";
		}
		$sql .= " GROUP BY b.brandId";
		$blist = $this->query($sql);
		for($i=0;$i<count($blist);$i++){
			$brand = $blist[$i];
			$brands[$brand["brandId"]] = array('brandId'=>$brand["brandId"],'brandName'=>$brand["brandName"]);
		}
		$rs["brands"] = $brands;
		$rs["pages"] = $pages;
		$gcats["goodsCatId1"] = $c1Id;
		$gcats["goodsCatId2"] = $c2Id;
		$gcats["goodsCatId3"] = $c3Id;
		$rs["goodsNav"] = self::getGoodsNav($gcats);
		return $rs;
	}
	
	
	/**
	 * 商品列表
	 */
	public function getMaxPrice($obj){
		$areaId2 = $obj["areaId2"];

		$c1Id = (int)I("c1Id");
		$c2Id = (int)I("c2Id");
		$c3Id = (int)I("c3Id");
		
		$keyWords = urldecode(I("keyWords"));
		$words = array();
		if($keyWords!=""){
			$words = explode(" ",$keyWords);
		}
		
		$sql = "SELECT bd.brandId,bd.brandName, goodsId,goodsSn,goodsName,goodsThums,g.saleCount,p.shopId,marketPrice,shopPrice,p.shopName 
				FROM __PREFIX__goods g , __PREFIX__brands bd, __PREFIX__shops p ";
		$sql .= "WHERE p.areaId2 = $areaId2 AND g.shopId = p.shopId AND bd.brandId=g.brandId AND  g.goodsStatus=1 AND g.goodsFlag = 1";
		
		if($c1Id>0){
			$sql .= " AND g.goodsCatId1 = $c1Id";
		}
		if($c2Id>0){
			$sql .= " AND g.goodsCatId2 = $c2Id";
		}
		if($c3Id>0){
			$sql .= " AND g.goodsCatId3 = $c3Id";
		}
		if(!empty($words)){
			$sarr = array();
			foreach ($words as $key => $word) {
				if($word!=""){
					$sarr[] = "g.goodsName LIKE '%$word%'";
				}
			}
			$sql .= " AND (".implode(" or ", $sarr).")";
		}
		$sql .= " ORDER BY g.saleCount DESC";
		$glist = $this->query($sql);
		
		$maxPrice = 0;
		for($i=0;$i<count($glist);$i++){
			$goods = $glist[$i];
			if($goods["shopPrice"]>$maxPrice){
				$maxPrice = $goods["shopPrice"];
			}
		}
		return $maxPrice;
	}
	

	/**
	 * 查询商品信息
	 */
	public function getGoodsDetails($obj){		
		$goodsId = $obj["goodsId"];
		$sql = "SELECT sc.catName,sc2.catName as pCatName, g.*,shop.shopName,shop.deliveryType,ga.id goodsAttrId,ga.attrPrice,ga.attrStock,
				shop.shopAtive,shop.shopTel,shop.shopAddress,shop.deliveryTime,shop.isInvoice, shop.deliveryStartMoney,g.goodsStock,shop.deliveryFreeMoney,shop.qqNo,
				shop.deliveryMoney ,g.goodsSn,shop.serviceStartTime,shop.serviceEndTime FROM __PREFIX__goods g left join __PREFIX__goods_attributes ga on g.goodsId=ga.goodsId and ga.isRecomm=1, __PREFIX__shops shop, __PREFIX__shops_cats sc 
				LEFT JOIN __PREFIX__shops_cats sc2 ON sc.parentId = sc2.catId
				WHERE g.goodsId = $goodsId AND shop.shopId=sc.shopId AND sc.catId=g.shopCatId1 AND g.shopId = shop.shopId AND g.goodsFlag = 1 ";		
		$rs = $this->query($sql);
		if(!empty($rs) && $rs[0]['goodsAttrId']>0){
			$rs[0]['shopPrice'] = $rs[0]['attrPrice'];
			$rs[0]['goodsStock'] = $rs[0]['attrStock'];
		}
		return $rs[0];
	}
	
	/**
	 * 获取商品信息-购物车/核对订单用
	 */
    public function getGoodsForCheck($obj){		
		$goodsId = (int)$obj["goodsId"];
		$goodsAttrId = $obj["goodsAttrId"];
		$temp=explode(',', $goodsAttrId);
		$isGroup=array_pop($temp);
		$isSeckill=array_pop($temp);
		$goodsAttrId=implode(',', $temp);
		$sql = "SELECT sc.catName,sc2.catName as pCatName, g.attrCatId,g.goodsThums,g.goodsId,g.goodsName,g.shopPrice,g.goodsStock
				,g.shopId,shop.shopName,shop.qqNo,shop.deliveryType,shop.shopAtive,shop.shopTel,shop.shopAddress,shop.deliveryTime,shop.isInvoice, 
				shop.shopImg,shop.deliveryStartMoney,g.goodsStock,shop.deliveryFreeMoney,shop.deliveryMoney ,g.goodsSn,shop.serviceStartTime startTime,shop.serviceEndTime endTime
				FROM __PREFIX__goods g, __PREFIX__shops shop, __PREFIX__shops_cats sc 
				LEFT JOIN __PREFIX__shops_cats sc2 ON sc.parentId = sc2.catId
				WHERE g.goodsId = $goodsId AND shop.shopId=sc.shopId AND sc.catId=g.shopCatId1 AND g.shopId = shop.shopId AND g.goodsFlag = 1 ";		
		$rs = $this->query($sql);
		if(!empty($rs) && $rs[0]['attrCatId']>0){
			$sql = "select ga.id,ga.attrPrice,ga.attrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
			        where a.attrId=ga.attrId and a.catId=".$rs[0]['attrCatId']." 
			        and ga.goodsId=".$rs[0]['goodsId']." and id in(".$goodsAttrId.") order by ga.id asc";
			$priceAttrs = $this->query($sql);
			if(!empty($priceAttrs)){
				foreach($priceAttrs as $k=>$v){
					$rs[0][$k]['attrId'] = $v['attrId'];
					$rs[0][$k]['goodsAttrId'] = $v['id'];
					$rs[0][$k]['attrName'] = $v['attrName'];
					$rs[0][$k]['attrVal'] = $v['attrVal'];
					$rs[0][$k]['shopPrice'] = $v['attrPrice'];
					$rs[0][$k]['goodsStock'] = $v['attrStock'];
				}
			}
		}
		$goods=$rs[0];
          //如果是团购商品
			if($isGroup){
				$goodsGroupModel = M('GoodsGroup');
		        $groupMap['goodsId']=$goodsId;
/* 				$groupMap['goodsGroupStatus']=1;
				$groupMap['groupStatus']=1;
				$groupMap['_string']="unix_timestamp(now()) BETWEEN startTime and endTime"; */
				$group = $goodsGroupModel->where($groupMap)->find();
				//加入团购信息
				$goods = array_merge($rs[0],$group);
				//把商品价变为团购价
				$time=time();
				$goods['shopPrice'] = $group['groupPrice'];
				if($group['goodsGroupStatus']==1&&$group['groupStatus']==1&&$group['startTime']&&$time>$group['startTime']&&$time<$group['endTime']){
				    //是否还可以团购
				    $goods['groupStatus'] = 1;
				}else{
				    $goods['groupStatus'] = 0;
				}
				$goods['isGroup'] = 1;
			}else{
			    //不是团购
			    $goods['isGroup'] = 0;
			}
			//如果是秒杀商品
			if($isSeckill){
				$secmillDB = M('goods_seckill');
				$seckillMap['goodsId']=$goodsId;
				/* $seckillMap['goodsSeckillStatus']=1;
				$seckillMap['seckillStatus']=1;
				$seckillMap['_string']="unix_timestamp(now()) BETWEEN seckillStartTime and seckillEndTime"; */
				$secKill = $secmillDB->where("goodsId = $goodsId")->find();
				//加入团购信息
				$goods = array_merge($rs[0],$secKill);
				//把商品价变为团购价
				$goods['shopPrice'] = $secKill['seckillPrice'];
				//商品是否还可以秒杀
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
		return $goods;
	}
	/**
	 * 获取商品的属性
	 */
	public function getAttrs($obj){
		$id = (int)$obj["goodsId"];
		$shopId = (int)$obj["shopId"];
		$attrCatId = (int)$obj["attrCatId"];
		$goods = array();
		//获取规格属性
		$sql = "select ga.id,ga.attrVal,ga.attrPrice,ga.attrStock,ga.isRecomm,a.attrId,a.attrName,a.isPriceAttr
		            from __PREFIX__attributes a 
		            left join __PREFIX__goods_attributes ga on ga.attrId=a.attrId and ga.goodsId=".$id." where  
					a.attrFlag=1 and a.catId=".$attrCatId." and a.shopId=".$shopId." order by a.attrSort, a.attrId,ga.id";
		$attrRs = $this->query($sql);
		if(!empty($attrRs)){
			$priceAttr = array();
			$attrs = array();
			$attrId = array();
			foreach ($attrRs as $key =>$v){
				if($v['isPriceAttr']==1){	
				$attrId[$v['attrId']]['name'] = $v['attrName'];				
					if(in_array($v['attrId'],$attrId)){
						$attrId[$v['attrId']][] = $v;

					}else{
						$attrId[$v['attrId']][] = $v;
					}
					
				}else{
					$v['attrContent'] = $v['attrVal'];
					$attrs[] = $v;
				}
			}
			$goods['priceAttrs'] = $attrId;
			$goods['attrs'] = $attrs;

		}
		return $goods;
	}
	/**
	 * 获取商品相册
	 */
	public function getGoodsImgs(){
		$goodsId = (int)I("goodsId");
		$sql = "SELECT img.* FROM __PREFIX__goods_gallerys img WHERE img.goodsId = $goodsId ";		
		$rs = $this->query($sql);
		return $rs;
	}
	
	
	/**
	 * 获取关联商品
	 */
	public function getRelatedGoods(){
		$goodsId = (int)I("goodsId");
		$sql = "SELECT g.* FROM __PREFIX__goods g, ".DB_PRE."goods_relateds gr WHERE g.goodsId = gr.relatedGoodsId AND g.goodsStock>0 AND g.goodsStatus = 1 AND gr.goodsId =$goodsId";
		$rs = $this->query($sql);
		return $rs;
		
	}
	
	/**
	 * 获取上架中的商品
	 */
	public function queryOnSaleByPage(){
		$shopId=(int)session('WST_USER.shopId');
		$shopCatId1 = (int)I('shopCatId1',0);
		$shopCatId2 = (int)I('shopCatId2',0);
		$goodsName = I('goodsName');
		$sql = "select g.goodsId,g.goodsSn,g.goodsName,g.goodsImg,g.goodsThums,g.shopPrice,g.goodsStock,g.saleCount,g.isSale,g.isRecomm,g.isHot,g.isBest,g.isNew from __PREFIX__goods g
				
				where g.goodsFlag=1 
		     and g.shopId=".$shopId." and g.goodsStatus=1 and g.isSale=1 ";
		if($shopCatId1>0)$sql.=" and g.shopCatId1=".$shopCatId1;
		if($shopCatId2>0)$sql.=" and g.shopCatId2=".$shopCatId2;
		if($goodsName!='')$sql.=" and (g.goodsName like '%".$goodsName."%' or g.goodsSn like '%".$goodsName."%') ";
		$sql.=" order by g.goodsId desc";
		
		return $this->pageQuery($sql);
	}
    /**
	 * 获取下架的商品
	 */
	public function queryUnSaleByPage(){
		$shopId=(int)session('WST_USER.shopId');
		$shopCatId1 = (int)I('shopCatId1',0);
		$shopCatId2 = (int)I('shopCatId2',0);
		$goodsName = I('goodsName');
		$sql = "select g.goodsId,g.goodsSn,g.goodsName,g.goodsImg,g.goodsThums,g.shopPrice,g.goodsStock,g.saleCount,g.isSale,g.isRecomm,g.isHot,g.isBest,g.isNew,ga.isRecomm as attIsRecomm from __PREFIX__goods  g
				left join __PREFIX__goods_attributes ga on g.goodsId = ga.goodsId and ga.isRecomm = 1
				where g.goodsFlag=1 
		      and g.shopId=".$shopId." and g.isSale=0 ";
		if($shopCatId1>0)$sql.=" and g.shopCatId1=".$shopCatId1;
		if($shopCatId2>0)$sql.=" and g.shopCatId2=".$shopCatId2;
		if($goodsName!='')$sql.=" and (g.goodsName like '%".$goodsName."%' or g.goodsSn like '%".$goodsName."%') ";
		$sql.=" order by g.goodsId desc";
		return $this->pageQuery($sql);
	}
    /**
	 * 获取审核中的商品
	 */
	public function queryPenddingByPage(){
		$shopId=(int)session('WST_USER.shopId');
		$shopCatId1 = (int)I('shopCatId1',0);
		$shopCatId2 = (int)I('shopCatId2',0);
		$goodsName = I('goodsName');
		$sql = "select g.goodsId,g.goodsSn,g.goodsName,g.goodsImg,g.goodsThums,g.shopPrice,g.goodsStock,g.saleCount,g.isSale,g.isRecomm,g.isHot,g.isBest,g.isNew,ga.isRecomm as attIsRecomm from __PREFIX__goods g
				left join __PREFIX__goods_attributes ga on g.goodsId = ga.goodsId and ga.isRecomm = 1
				where g.goodsFlag=1 
		     and g.shopId=".$shopId." and g.goodsStatus=0 and isSale=1 ";
		if($shopCatId1>0)$sql.=" and g.shopCatId1=".$shopCatId1;
		if($shopCatId2>0)$sql.=" and g.shopCatId2=".$shopCatId2;
		if($goodsName!='')$sql.=" and (g.goodsName like '%".$goodsName."%' or g.goodsSn like '%".$goodsName."%') ";
		$sql.=" order by g.goodsId desc";
		return $this->pageQuery($sql);
	}
	/**
	 * 新增商品
	 */
	public function insert(){
	 	$rd = array('status'=>-1);
	 	//查询商家状态
		$sql = "select shopStatus from __PREFIX__shops where shopFlag = 1 and shopId=".(int)session('WST_USER.shopId');
		$shopStatus = $this->query($sql);
		if(empty($shopStatus)){
			$rd['status'] = -2;
			return $rd;
		}
	    
		$data = array();
		$data["goodsSn"] = I("goodsSn");
		$data["goodsName"] = I("goodsName");
		$data["goodsImg"] = I("goodsImg");
		$data["goodsThums"] = I("goodsThumbs");
		$data["shopId"] = session('WST_USER.shopId');
		$data["marketPrice"] = (float)I("marketPrice");
		$data["shopPrice"] = (float)I("shopPrice");
		$data["goodsStock"] = (int)I("goodsStock");
		$data["isBook"] = (int)I("isBook");
		$data["bookQuantity"] = (int)I("bookQuantity");
		$data["warnStock"] = (int)I("warnStock");
		$data["goodsUnit"] = I("goodsUnit");
		$data["isBest"] = (int)I("isBest");
		$data["isRecomm"] = (int)I("isRecomm");
		$data["isNew"] = (int)I("isNew");
		$data["isHot"] = (int)I("isHot");
	    //如果商家状态不是已审核则所有商品只能在仓库中
	    if($shopStatus[0]['shopStatus']==1){
			$data["isSale"] = (int)I("isSale");
		}else{
			$data["isSale"] = 0;
		}
		$data["goodsCatId1"] = (int)I("goodsCatId1");
		$data["goodsCatId2"] = (int)I("goodsCatId2");
		$data["goodsCatId3"] = (int)I("goodsCatId3");
		$data["shopCatId1"] = (int)I("shopCatId1");
		$data["shopCatId2"] = (int)I("shopCatId2");
		$data["goodsDesc"] = I("goodsDesc");
		$data["attrCatId"] = (int)I("attrCatId");
		$data["isShopRecomm"] = 0;
		$data["isIndexRecomm"] = 0;
		$data["isActivityRecomm"] = 0;
		$data["isInnerRecomm"] = 0;
		$data["goodsStatus"] = ($GLOBALS['CONFIG']['isGoodsVerify']==1)?0:1;
		$data["goodsFlag"] = 1;
		$data["createTime"] = date('Y-m-d H:i:s');
		$data['isGroup'] = (int)I("isGroup");
		if($this->checkEmpty($data,true)){
			$data["brandId"] = (int)I("brandId");
			$data["goodsSpec"] = I("goodsSpec");
			$data["goodsKeywords"] = I("goodsKeywords");
			$m = M('goods');
			$goodsId = $m->add($data);
			if(false !== $goodsId){
				if($shopStatus[0]['shopStatus']==1){
				    $rd['status']= 1;
				}else{
				    $rd['status'] = -3;
				}
				//规格属性
				if($data["attrCatId"]>0){
					$m = M('goods_attributes');
					//获取商品类型属性
					$sql = "select attrId,attrName,isPriceAttr from __PREFIX__attributes where attrFlag=1 
					       and catId=".$data["attrCatId"]." and shopId=".session('WST_USER.shopId');
					$attrRs = $m->query($sql);
					if(!empty($attrRs)){
						$priceAttrId = array();
						foreach ($attrRs as $key =>$v){
							if($v['isPriceAttr']==1){
								$priceAttrId[] = $v['attrId'];
								continue;
							}else{
								$attr = array();
								$attr['shopId'] = session('WST_USER.shopId');
								$attr['goodsId'] = $goodsId;
								$attr['attrId'] = $v['attrId'];
								$attr['attrVal'] = I('attr_name_'.$v['attrId']);
								$m->add($attr);
							}
						}
						if($priceAttrId>0){
							foreach($priceAttrId as $v){
							$no = (int)I('goodsPriceNo');
							$no = $no>50?50:$no;
							$totalStock = 0;
							for ($i=0;$i<=$no;$i++){
								$name = trim(I('price_name_'.$v."_".$i));
								if($name=='')continue;
								$attr = array();
								$attr['shopId'] = session('WST_USER.shopId');
								$attr['goodsId'] = $goodsId;
								$attr['attrId'] = $v;
								$attr['attrVal'] = $name;
								$attr['attrPrice'] = (float)I('price_price_'.$v."_".$i);
								$attr['isRecomm'] = (int)I('price_isRecomm_'.$v."_".$i);
								$attr['attrStock'] = (int)I('price_stock_'.$v."_".$i);
								$totalStock = $totalStock + (int)$attr['attrStock'];
								$m->add($attr);
							}
							//更新商品总库存
							/*
							$sql = "update __PREFIX__goods set goodsStock=".$totalStock." where goodsId=".$goodsId;
							$m->execute($sql);*/
						}
						}
					}
				}
				//保存相册
				$gallery = I("gallery");
				if($gallery!=''){
					$str = explode(',',$gallery);
					foreach ($str as $k => $v){
						if($v=='')continue;
						$str1 = explode('@',$v);
						$data = array();
						$data['shopId'] = session('WST_USER.shopId');
						$data['goodsId'] = $goodsId;
						$data['goodsImg'] = $str1[0];
						$data['goodsThumbs'] = $str1[1];
						$m = M('goods_gallerys');
						$m->add($data);
					}
				}
				//保存团购信息
				$goodsGroupModel  = M('GoodsGroup');
				$shopId = session('WST_USER.shopId');
				$goodsId = $goodsId;
				$groupPrice = (int)I('groupPrice');
				$groupMaxCount = (int)I('groupMaxCount');
				$groupMinCount = (int)I('groupMinCount');
				$startTime = I('startTime');
				$endTime = I('endTime');
				$groupDesc = I('groupDesc');
				$sql = "insert into __PREFIX__goods_group (shopId,goodsId,groupPrice,groupMaxCount,groupMinCount,
 startTime,endTime,groupDesc,goodsGroupStatus,groupStatus) values($shopId,$goodsId,$groupPrice,$groupMaxCount,
$groupMinCount,unix_timestamp('$startTime'),unix_timestamp('$endTime'),'$groupDesc',0,0)";
				$goodsGroupModel->query($sql);
			}
		}
		return $rd;
	} 
	 
	/**
	 * 编辑商品信息
	 */
	public function edit(){
		$rd = array('status'=>-1);
	 	$goodsId = (int)I("id",0);
	 	$shopId = (int)session('WST_USER.shopId');
	    //查询商家状态
		$sql = "select shopStatus from __PREFIX__shops where shopFlag = 1 and shopId=".$shopId;
		$shopStatus = $this->query($sql);
		if(empty($shopStatus)){
			$rd['status'] = -2;
			return $rd;
		}
	 	//加载商品信息
	 	$m = M('goods');
	 	$goods = $m->where('goodsId='.$goodsId." and shopId=".$shopId)->find();
	 	if(empty($goods))return array();
		$data = array();
		
		$data["goodsSn"] = I("goodsSn");
		$data["goodsName"] = I("goodsName");
		$data["goodsImg"] = I("goodsImg");
		$data["goodsThums"] = I("goodsThumbs");
		$data["marketPrice"] = (float)I("marketPrice");
		$data["shopPrice"] = (float)I("shopPrice");
		$data["goodsStock"] = (int)I("goodsStock");
		$data["isBook"] = (int)I("isBook");
		$data["bookQuantity"] = (int)I("bookQuantity");
		$data["warnStock"] = (int)I("warnStock");
		$data["goodsUnit"] = I("goodsUnit");
		$data["isBest"] = (int)I("isBest");
		$data["isRecomm"] = (int)I("isRecomm");
		$data["isNew"] = (int)I("isNew");
		$data["isHot"] = (int)I("isHot");
		
	    //如果商家状态不是已审核则所有商品只能在仓库中
	    if($shopStatus[0]['shopStatus']==1){
			$data["isSale"] = (int)I("isSale");
		}else{
			$data["isSale"] = 0;
		}
		$data["goodsCatId1"] = (int)I("goodsCatId1");
		$data["goodsCatId2"] = (int)I("goodsCatId2");
		$data["goodsCatId3"] = (int)I("goodsCatId3");
		$data["shopCatId1"] = (int)I("shopCatId1");
		$data["shopCatId2"] = (int)I("shopCatId2");
		$data["goodsDesc"] = I("goodsDesc");
		$data["goodsStatus"] = ($GLOBALS['CONFIG']['isGoodsVerify']['fieldValue']==1)?0:1;
		$data["attrCatId"] = (int)I("attrCatId");
		if($this->checkEmpty($data,true)){
			$data["goodsKeywords"] =  I("goodsKeywords");
			$data["brandId"] = (int)I("brandId");
			$data["goodsSpec"] = I("goodsSpec");
			
			
			$rs = $m->where('goodsId='.$goods['goodsId'])->save($data);
			if(false !== $rs){
				if($shopStatus[0]['shopStatus']==1){
				    $rd['status']= 1;
				}else{
					$rd['status']= -3;
				}
			    //规格属性
				if($data["attrCatId"]>0){
					$m = M('goods_attributes');
					//删除属性记录
					$m->query("delete from __PREFIX__goods_attributes where goodsId=".$goodsId);
					//获取商品类型属性
					$sql = "select attrId,attrName,isPriceAttr from __PREFIX__attributes where attrFlag=1 
					       and catId=".$data["attrCatId"]." and shopId=".session('WST_USER.shopId');
					$attrRs = $m->query($sql);
					if(!empty($attrRs)){
						$priceAttrId = array();
						foreach ($attrRs as $key =>$v){
							if($v['isPriceAttr']==1){
								$priceAttrId[] = $v['attrId'];
								continue;
							}else{
								$attr = array();
								$attr['shopId'] = session('WST_USER.shopId');
								$attr['goodsId'] = $goodsId;
								$attr['attrId'] = $v['attrId'];
								$attr['attrVal'] = I('attr_name_'.$v['attrId']);
								$m->add($attr);
							}
						}
						if($priceAttrId>0){
							foreach($priceAttrId as $v){
							$no = (int)I('goodsPriceNo');
							$no = $no>50?50:$no;
							$totalStock = 0;
							for ($i=0;$i<=$no;$i++){
								$name = trim(I('price_name_'.$v."_".$i));
								if($name=='')continue;
								$attr = array();
								$attr['shopId'] = session('WST_USER.shopId');
								$attr['goodsId'] = $goodsId;
								$attr['attrId'] = $v;
								$attr['attrVal'] = $name;
								$attr['attrPrice'] = (float)I('price_price_'.$v."_".$i);
								$attr['isRecomm'] = (int)I('price_isRecomm_'.$v."_".$i);
								if($attr['isRecomm']==1){
									$recommPrice = $attr['attrPrice'];
								}
								$attr['attrStock'] = (int)I('price_stock_'.$v."_".$i);
								$totalStock = $totalStock + (int)$attr['attrStock'];
								$m->add($attr);
							}
							//更新商品总库存
							/*$sql = "update __PREFIX__goods set goodsStock=".$totalStock;
							if($recommPrice>0){
								$sql .= ",shopPrice=".$recommPrice;
							}
							$sql .= " where goodsId=".$goodsId;
							$m->execute($sql);*/
						}
						}
					}
				}
				
			    //保存相册
				$gallery = I("gallery");
				if($gallery!=''){
					$str = explode(',',$gallery);
					$m = M('goods_gallerys');
					//删除相册信息
					$m->where('goodsId='.$goods['goodsId'])->delete();
					//保存相册信息
					foreach ($str as $k => $v){
						if($v=='')continue;
						$str1 = explode('@',$v);
						$data = array();
						$data['shopId'] = $goods['shopId'];
						$data['goodsId'] = $goods['goodsId'];
						$data['goodsImg'] = $str1[0];
						$data['goodsThumbs'] = $str1[1];
						$m->add($data);
					}
				}
				//保存团购信息
				$goodsGroupModel  = M('GoodsGroup');
				$data = array();
				$shopId = session('WST_USER.shopId');
				$goodsId = $goodsId;
				$groupPrice = (int)I('groupPrice');
				$groupMaxCount = (int)I('groupMaxCount');
				$groupMinCount = (int)I('groupMinCount');
				$startTime = I('startTime');
				$endTime = I('endTime');
				$groupDesc = I('groupDesc');
				$sql = "update __PREFIX__goods_group set groupPrice=$groupPrice,groupMaxCount=$groupMaxCount,
groupMinCount=$groupMinCount,startTime=unix_timestamp('$startTime'),endTime=unix_timestamp('$endTime'),groupDesc='$groupDesc'
where goodsId=$goodsId and shopId=$shopId";
				$goodsGroupModel->query($sql);
			}
		}
		return $rd;
	}
	/**
	 * 获取商品信息
	 */
	 public function get(){
	 	$m = M('goods');
	 	$id = (int)I('id',0);
	 	$shopId = (int)session('WST_USER.shopId');
		$goods = $m->where("goodsId=".$id." and shopId=".$shopId)->find();
		if(empty($goods))return array();
		$m = M('goods_gallerys');
		$goods['gallery'] = $m->where('goodsId='.$id)->select();
		//获取规格属性
		$sql = "select ga.id,ga.attrVal,ga.attrPrice,ga.attrStock,ga.isRecomm,a.attrId,a.attrName,a.isPriceAttr,a.attrType,a.attrContent
		            ,ga.isRecomm from __PREFIX__attributes a 
		            left join __PREFIX__goods_attributes ga on ga.attrId=a.attrId and ga.goodsId=".$id." where  
					a.attrFlag=1 and a.catId=".$goods['attrCatId']." and a.shopId=".$shopId." ORDER BY ga.id asc";
		$attrRs = $m->query($sql);
		if(!empty($attrRs)){
			$priceAttr = array();
			$attrs = array();
			foreach ($attrRs as $key =>$v){
				if($v['isPriceAttr']==1){
					$goods['priceAttrId'] = $v['attrId'];
					$goods['priceAttrName'] = $v['attrName'];
					$priceAttr[$v['attrId']][] = $v;
				}else{
					//分解下拉和多选的选项
					if($v['attrType']==1 || $v['attrType']==2){
						$v['opts']['txt'] = explode(',',$v['attrContent']);
						if($v['attrType']==1){
							$vs = explode(',',$v['attrVal']);
							//保存多选的值
							foreach ($vs as $vv){
								$v['opts']['val'][$vv] = 1;
							}
						}
					}
					$attrs[] = $v;
				}
			}
			$goods['priceAttrs'] = $priceAttr;
			//价格属性总个数
			$priceAttrCount = 0;
			foreach($priceAttr as $v){$priceAttrCount += count($v);}
			$goods['priceAttrCount'] = $priceAttrCount;
			//价格属性个数
			$goods['priceAttrCounts'] = count($priceAttr);
			$goods['attrs'] = $attrs;
		}
		 //团购信息
		 if($goods['isGroup']==1){
			 $goodsGroupModel = M('GoodsGroup');
			 $group = $goodsGroupModel->where('goodsId ='.$goods['goodsId'])->find();
			 $startTime = $group['startTime'];
			 $group['startTime'] = array();
			 $group['startTime']['year'] = date('Y-m-d',$startTime);
			 $group['startTime']['H'] = date('H',$startTime);
			 $group['startTime']['i'] = date('i',$startTime);
			 $group['startTime']['s'] = date('s',$startTime);
			 $endTime = $group['endTime'];
			 $group['endTime'] = array();
			 $group['endTime']['year'] = date('Y-m-d ',$endTime);
			 $group['endTime']['H'] = date('H',$endTime);
			 $group['endTime']['i'] = date('i',$endTime);
			 $group['endTime']['s'] = date('s',$endTime);
			 foreach($group as $k=>$v){
				 $goods[$k] = $v;
			 }
		 }
		return $goods;
	 }
	 /**
	  * 删除商品
	  */
	 public function del(){
	 	$rd = array('status'=>-1);
	 	$m = M('goods');
	 	$shopId = (int)session('WST_USER.shopId');
	 	$data = array();
		$data["goodsFlag"] = -1;
	 	$rs = $m->where("shopId=".$shopId." and goodsId=".I('id'))->save($data);
	    if(false !== $rs){
			$rd['status']= 1;
		}
		return $rd;
	 }
	 
	 /**
	  * 批量删除商品
	  */
	 public function batchDel(){
	 	$rd = array('status'=>-1);
	 	$m = M('goods');
	 	$shopId = (int)session('WST_USER.shopId');
	 	$data = array();
		$data["goodsFlag"] = -1;
	 	$rs = $m->where("shopId=".$shopId." and goodsId in(".I('ids').")")->save($data);
	    if(false !== $rs){
			$rd['status']= 1;
		}
		return $rd;
	 }
	 /**
	  * 批量修改商品状态
	  */
	 public function goodsSet(){
	 	$rd = array('status'=>-1);
	 	$code = I('code');
	 	$codeArr = array('isBest','isNew','isHot','isRecomm');
	 	if(in_array($code,$codeArr)){
		 	$m = M('goods');
		 	$shopId = (int)session('WST_USER.shopId');
		 	$data = array();
			$data[$code] = 1;
		 	$rs = $m->where("shopId=".$shopId." and goodsId in(".I('ids').")")->save($data);
		    if(false !== $rs){
				$rd['status']= 1;
			}
	 	}
		return $rd;
	 }
     /**
	  * 批量上架/下架商品
	  */
	 public function sale(){
	 	$rd = array('status'=>-1);
	 	$m = M('goods');
	 	$isSale = (int)I('isSale');
	 	$shopId = (int)session('WST_USER.shopId');
	 	$ids = I('ids');
	 	if($isSale==1){
	 		//核对店铺状态
	 		$sql = "select shopStatus from __PREFIX__shops where shopId=".$shopId;
	 		$shopRs = $m->query($sql);
	 		if($shopRs[0]['shopStatus']!=1){
	 			$rd['status']= -3;
	 			return $rd;
	 		}
	 		//核对商品是否符合上架的条件
	 		$sql = "select g.goodsId from __PREFIX__goods g,__PREFIX__shops_cats sc2,__PREFIX__goods_cats gc3 
	 		   where g.shopCatId2=sc2.catId and sc2.catFlag=1 and sc2.isShow=1 and g.goodsCatId3=gc3.catId and gc3.catFlag=1 and gc3.isShow=1
	 		   and g.goodsId in(".$ids.")";
	 		$goodsRs = $m->query($sql);
	 		if(count($goodsRs)>0){
	 			$rd['num'] = 0;
	 			foreach ($goodsRs as $key =>$v){
			 		//商品上架操作
				 	$data = array();
					$data["isSale"] = 1;
				 	$rs = $m->where("shopId=".$shopId." and goodsId =".$v['goodsId'])->save($data);
				    if(false !== $rs){
						$rd['num']++;
					}
	 			}
	 			$rd['status'] = (count(explode(',',$ids))==$rd['num'])?1:2;
	 		}else{
	 			$rd['status']= -2;
	 		}
	 	}else{
		 	//商品下架操作
		 	$data = array();
			$data["isSale"] = 0;
		 	$rs = $m->where("shopId=".$shopId." and goodsId in(".$ids.")")->save($data);
		    if(false !== $rs){
				$rd['status']= 1;
			}
	 	}
	 	
		return $rd;
	 }
	 
	/**
	 * 获取店铺商品列表
	 */
	public function getShopsGoods($shopId = 0){
		
		$shopId = ($shopId>0)?$shopId:(int)I("shopId");
		$ct1 = (int)I("ct1");
		$ct2 = (int)I("ct2");
		$msort = (int)I("msort");//排序標識		
		
		$sprice = I("sprice");//开始价格
		$eprice = I("eprice");//结束价格
		//$goodsName = I("goodsName");//搜索店鋪名
		$goodsName = urldecode(I("goodsName"));//搜索店鋪名
		$words = array();
		if($goodsName!=""){
			$words = explode(" ",$goodsName);
		}
		/*$sql = "SELECT sp.shopName, g.saleCount totalnum, sp.shopId ,g.goodsStock, g.goodsId , g.goodsName,g.goodsImg, g.goodsThums,g.shopPrice,g.marketPrice, g.goodsSn,ga.id goodsAttrId 
						FROM __PREFIX__goods g left join __PREFIX__goods_attributes ga on g.goodsId = ga.goodsId and ga.isRecomm=1,__PREFIX__shops sp 
						WHERE g.shopId = sp.shopId AND sp.shopFlag=1 AND sp.shopStatus=1 AND g.goodsFlag = 1 AND g.isSale = 1 AND g.goodsStatus = 1 AND g.shopId = $shopId";*/
		//商品列表无需显示属性，修改sql为：
		$sql = "SELECT sp.shopName, g.saleCount totalnum, sp.shopId ,g.goodsStock, g.goodsId , g.goodsName,g.goodsImg, g.goodsThums,g.shopPrice,g.marketPrice, g.goodsSn
						FROM __PREFIX__goods g ,__PREFIX__shops sp 
						WHERE g.shopId = sp.shopId AND sp.shopFlag=1 AND sp.shopStatus=1 AND g.goodsFlag = 1 AND g.isSale = 1 AND g.goodsStatus = 1 AND g.shopId = $shopId";
		if($ct1>0){
			$sql .= " AND g.shopCatId1 = $ct1 ";
		}
		if($ct2>0){
			$sql .= " AND g.shopCatId2 = $ct2 ";
		}
		if($sprice!=""){
			$sql .= " AND g.shopPrice >= '$sprice' ";
		}
		if($eprice!=""){
			$sql .= " AND g.shopPrice <= '$eprice' ";
		}

		if(!empty($words)){
			$sarr = array();
			foreach ($words as $key => $word) {
				if($word!=""){
					$sarr[] = "g.goodsName LIKE '%$word%'";
				}
			}
			$sql .= " AND (".implode(" or ", $sarr).")";
		}
		
		if($msort==1){//综合
			$sql .= " ORDER BY g.saleCount DESC ";
		}else if($msort==2){//人气
			$sql .= " ORDER BY g.saleCount DESC ";
		}else if($msort==3){//销量
			$sql .= " ORDER BY g.saleCount DESC ";
		}else if($msort==4){//价格
			$sql .= " ORDER BY g.shopPrice ASC ";
		}else if($msort==5){//价格
			$sql .= " ORDER BY g.shopPrice DESC ";
		}else if($msort==6){//好评
			
		}else if($msort==7){//上架时间
			$sql .= " ORDER BY g.saleTime DESC ";
		}
		$rs = $this->pageQuery($sql,I('p'),30);
		$goodsAttrModel = M('GoodsAttributes');
		$goodsIsRecomm = $goodsAttrModel->field('goodsId')->where('isRecomm = 1')->select();
		foreach($rs['root'] as $k=>$v){
			foreach($goodsIsRecomm as $vs){
				if($v['goodsId']==$vs['goodsId']){
					$rs['root'][$k]['isRecomm'] = 1;
				}
			}
		}
		return $rs;
		
	}
	
	
	/**
	 * 获取店铺商品列表
	 */
	public function getHotGoods($shopId){
		$hotgoods = S("WST_CACHE_HOT_GOODS_".$shopId);
		if(!$hotgoods){
			//热销排名
			$sql = "SELECT sp.shopName, g.saleCount totalnum, sp.shopId , g.goodsId , g.goodsName,g.goodsImg, g.goodsThums,g.shopPrice,g.marketPrice, g.goodsSn 
							FROM __PREFIX__goods g,__PREFIX__shops sp 
							WHERE g.shopId = sp.shopId AND g.goodsFlag = 1 AND sp.shopFlag=1 AND sp.shopStatus=1 AND g.isSale = 1 AND g.goodsStatus = 1 AND sp.shopId = $shopId
							ORDER BY g.saleCount desc limit 5";	
			$hotgoods = $this->query($sql);
			S("WST_CACHE_HOT_GOODS_".$shopId,$hotgoods,86400);
		}
		return  $hotgoods;
	}
	
	/**
	 * 获取商品库存
	 */
	public function getGoodsStock($data){
	 	$goodsId = $data['goodsId'];
		$isBook = $data['isBook'];
		$isGroup=$data['isGroup'];
		$isSeckill=$data['isSeckill'];
		$goodsAttrId = $data['goodsAttrId'];
		if($isBook==1){
			$sql = "select goodsId,(goodsStock+bookQuantity) as goodsStock from __PREFIX__goods where isSale=1 and goodsFlag=1 and goodsStatus=1 and goodsId=".$goodsId;
		}else{
			$sql = "select goodsId,goodsStock,attrCatId from __PREFIX__goods where isSale=1 and goodsFlag=1 and goodsStatus=1 and goodsId=".$goodsId;
		}
	 	$goods = $this->query($sql);
	 	if($goods[0]['attrCatId']>0){
	 		$sql = "select ga.id,ga.attrStock from __PREFIX__goods_attributes ga where ga.goodsId=".$goodsId." and id=".$goodsAttrId;
			$priceAttrs = $this->query($sql);
			if(!empty($priceAttrs))$goods[0]['goodsStock'] = $priceAttrs[0]['attrStock'];
	 	}
	 	if(empty($goods)){
	 	    return array();
	 	}
	 	if($isGroup==1){
	 	    $a=new BaseController();
	 	    $group=$a->groupStock($goodsId, 1);
	 	    $goods[0]['goodsStock']=$group['goodsStock'];
	 	}
	 	if($isSeckill==1){
	 	    $a=new BaseController();
	 	    $secKill=$a->secKillStock($goodsId, 1);
	 	    $goods[0]['goodsStock']=$secKill['goodsStock'];
	 	}
	 	return $goods[0];
	 }
	 
	 
	/**
	 * 查询商品简单信息
	 */
	public function getGoodsInfo($goodsId,$goodsAttrId){		
		$sql = "SELECT g.attrCatId,g.goodsId,g.goodsName,g.goodsStock,g.bookQuantity,g.isBook,g.isSale FROM __PREFIX__goods g WHERE g.goodsId = $goodsId AND g.goodsFlag = 1 AND g.goodsStatus = 1";		
		$rs = $this->queryRow($sql);
        if(!empty($rs) && $rs['attrCatId']>0){
        	$sql = "select ga.id,ga.attrPrice,ga.attrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
			        where a.attrId=ga.attrId and a.catId=".$rs['attrCatId']." 
			        and ga.goodsId=".$rs['goodsId']." and id=".$goodsAttrId;
			$priceAttrs = $this->query($sql);
			if(!empty($priceAttrs))$rs['goodsStock'] = $priceAttrs[0]['attrStock'];
        }
		return $rs;
		
	}
	
	/**
	 * 查询商品简单信息
	 */
	public function getGoodsSimpInfo($goodsId,$goodsAttrId){
		$goodsAttrId = implode(',',$goodsAttrId);
		$sql = "SELECT g.*,sp.shopId,sp.shopName,sp.deliveryFreeMoney,sp.deliveryMoney,sp.deliveryStartMoney,sp.isInvoice,sp.serviceStartTime startTime,sp.serviceEndTime endTime,sp.deliveryType 
				FROM __PREFIX__goods g, __PREFIX__shops sp 
				WHERE g.shopId = sp.shopId AND g.goodsId = $goodsId AND g.isSale=1 AND g.goodsFlag = 1 AND g.goodsStatus = 1";
		$rs = $this->queryRow($sql);
	    if(!empty($rs) && $rs['attrCatId']>0){
        	$sql = "select ga.id,ga.attrPrice,ga.attrStock,a.attrName,ga.attrVal,ga.attrId from __PREFIX__attributes a,__PREFIX__goods_attributes ga
			        where a.attrId=ga.attrId and a.catId=".$rs['attrCatId']." 
			        and ga.goodsId=".$rs['goodsId']." and id in(".$goodsAttrId.")";
			$priceAttrs = $this->query($sql);
			if(!empty($priceAttrs)){
				foreach($priceAttrs as $k=>$v){
					$rs['attrs'][$k]['attrId'] =$v['attrId'];
					$rs['attrs'][$k]['goodsAttrId'] = $v['id'];
					$rs['attrs'][$k]['attrName'] = $v['attrName'];
					$rs['attrs'][$k]['attrVal'] = $v['attrVal'];
					$rs['attrs'][$k]['shopPrice'] = $v['attrPrice'];
					$rs['attrs'][$k]['goodsStock'] = $v['attrStock'];
				}
			}
        }
		return $rs;
		
	}
	
	
	/**
	 * 获取商品类别导航
	 */
	public function getGoodsNav($obj=array()){
		$goodsId = (int)I("goodsId");
		if($goodsId>0){
			$sql = "SELECT goodsCatId1,goodsCatId2,goodsCatId3 FROM __PREFIX__goods WHERE goodsId = $goodsId";
			$rs = $this->queryRow($sql);
		}else{
			$rs = $obj;
		}
		$gclist = M('goods_cats')->cache('WST_CACHE_GOODS_CAT_URL',31536000)->where('isShow = 1')->field('catId,catName')->order('catId')->select();
		$catslist = array();
		foreach ($gclist as $key => $gcat) {
			$catslist[$gcat["catId"]] = $gcat;
		}
		
		$data[] = $catslist[$rs["goodsCatId1"]];
		$data[] = $catslist[$rs["goodsCatId2"]];
		$data[] = $catslist[$rs["goodsCatId3"]];
		return $data;
	}
	
	/**
	 * 查询商品属性价格及库存
	 */
	public function getPriceAttrInfo(){
		$goodsId = (int)I("goodsId");
		$id =  I("id");
		foreach($id as $v){
			$v = (int)$v;
			$sql = "select id,attrPrice,attrStock from  __PREFIX__goods_attributes where goodsId=".$goodsId." and id=".$v;
			$rs[] = $this->query($sql);
		}
		return $rs;
	}
	
	/**
	 * 修改商品库存
	 */
	public function editStock(){
		$rdata= array("status"=>-1);
		$goodsId = (int)I("goodsId");
		$stock = (int)I("stock");
		$data = array();
		$data["goodsStock"] = $stock;
		
		M('goods')->where("goodsId=$goodsId")->save($data);
		$rdata["status"] = 1;
		return $rdata;
	}
	
	/**
	 * 修改商品库存,商品编号,价格
	 */
	public function editGoodsBase(){
	
		$rdata= array("status"=>-1);
		$vfield = (int)I("vfield");
		$goodsId = (int)I("goodsId");
	
		$data = array();
		if($vfield==1){//商品编号
			$data["goodsSn"] = I("vtext");
		}else if($vfield==2){//商品价格
			$data["shopPrice"] = I("vtext");
		}else if($vfield==3){//商品庫存
			$data["goodsStock"] = (int)I("vtext");
		}
	
		M('goods')->where("goodsId=$goodsId")->save($data);
		$rdata["status"] = 1;
		return $rdata;
	}
	
	public function getKeyList(){
		$keywords = I("keywords");
		$m = M('goods');
		$sql="select DISTINCT goodsName as searchKey from __PREFIX__goods where goodsStatus=1 and goodsFlag=1 and goodsName like '%$keywords%' Order by saleCount desc, goodsName asc limit 10";
		$rs = $this->query($sql);
		return $rs;
	}
	
	
	/**
	 * 修改 推荐/精品/新品/热销/上架
	 */
	public function changSaleStatus(){
		$rdata= array("status"=>-1);
		$goodsId = (int)I("goodsId");
		$tamk = (int)I("tamk");
		$flag = (int)I("flag");
		$data = array();
		if($tamk==0){
			$tamk = 1;
		}else{
			$tamk = 0;
		}
		if($flag==1){
			$data["isRecomm"] = $tamk;
		}else if($flag==2){
			$data["isBest"] = $tamk;
		}else if($flag==3){
			$data["isNew"] = $tamk;
		}else if($flag==4){
			$data["isHot"] = $tamk;
		}else if($flag==5){
			$data["isSale"] = $tamk;
		}
	
		M('goods')->where("goodsId=$goodsId")->save($data);
		$rdata["status"] = 1;
		return $rdata;
	}
	
	/**
	 * 获取商品历史浏览记录(取最新10條)
	 */
	function getViewGoods(){
		$m = M();
		$viewGoods = cookie("viewGoods");
		$viewGoods = array_reverse($viewGoods);
		$goodIds = 0;
		if(!empty($viewGoods)){
			$goodIds = implode(",",$viewGoods);
		}
		//热销排名
		$sql = "SELECT g.saleCount totalnum, g.goodsId , g.goodsName,g.goodsImg, g.goodsThums,g.shopPrice,g.marketPrice, g.goodsSn FROM __PREFIX__goods g
				WHERE g.goodsId in ($goodIds) AND g.goodsFlag = 1 AND g.isSale = 1 AND g.goodsStatus = 1
				ORDER BY FIELD(g.goodsId,$goodIds) limit 10";
	
		$goods = $m->query($sql);
		return  $goods;
	
	}
	
	/**
	 * 上传商品数据
	 */
	public function importGoods($data){
		$objReader = WSTReadExcel($data['file']['savepath'].$data['file']['savename']);
        $objReader->setActiveSheetIndex(0); 
        $sheet = $objReader->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $cells = $sheet->getHighestColumn();
        //数据集合
        $readData = array();
        $goodsCatMap = array();
        $shopGoodsCatMap = array();
        $brandMap = array();
        $shopId = (int)session('WST_USER.shopId');
        $goodsModel = M('goods');
        $importNum = 0;
        //循环读取每个单元格的数据
        for ($row = 3; $row <= $rows; $row++){//行数是以第3行开始
            $goods = array();
            $goods['shopId'] = $shopId;
            $goods['goodsSn'] = trim($sheet->getCell("A".$row)->getValue());
            if($goods['goodsSn']=='')break;//如果某一行第一列为空则停止导入
            $goods['goodsName'] = trim($sheet->getCell("B".$row)->getValue());
            $goods['marketPrice'] = trim($sheet->getCell("C".$row)->getValue());
            $goods['shopPrice'] = trim($sheet->getCell("D".$row)->getValue());
            $goods['goodsStock'] = trim($sheet->getCell("E".$row)->getValue());
            $goods['saleCount'] = trim($sheet->getCell("F".$row)->getValue());
            $goods['goodsUnit'] = trim($sheet->getCell("G".$row)->getValue());
            $goods['goodsSpec'] = trim($sheet->getCell("H".$row)->getValue());
            $goods['goodsKeywords'] = trim($sheet->getCell("I".$row)->getValue());
            $goods['isSale'] = 0;
            $goods['isRecomm'] = (trim($sheet->getCell("J".$row)->getValue())!='')?1:0;
            $goods['isBest'] = (trim($sheet->getCell("K".$row)->getValue())!='')?1:0;
            $goods['isNew'] = (trim($sheet->getCell("L".$row)->getValue())!='')?1:0;
            $goods['isHot'] = (trim($sheet->getCell("M".$row)->getValue())!='')?1:0;
            //查询商城分类
            $goodsCat = trim($sheet->getCell("N".$row)->getValue());
            if($goodsCatMap[$goodsCat]==''){
	            $sql = "select gc1.catId catId1,gc2.catId catId2,gc3.catId catId3,gc3.catName 
	                    from __PREFIX__goods_cats gc3, __PREFIX__goods_cats gc2,__PREFIX__goods_cats gc1
	                    where gc3.parentId=gc2.catId and gc2.parentId=gc1.catId and gc3.isShow=1 and gc2.isShow=1 and gc1.isShow=1
	                    and gc3.catFlag=1 and gc2.catFlag=1 and gc1.catFlag=1 and gc3.catName='".$goodsCat."'";
	            $trs = $this->queryRow($sql);
	            if(!empty($trs)){
	            	$goodsCatMap[$trs['catName']] = $trs;
	            }
            }
            $goods['goodsCatId1'] = (int)$goodsCatMap[$goodsCat]['catId1'];
            $goods['goodsCatId2'] = (int)$goodsCatMap[$goodsCat]['catId2'];
            $goods['goodsCatId3'] = (int)$goodsCatMap[$goodsCat]['catId3'];
            //查询商城分类
            $shopGoodsCat = trim($sheet->getCell("O".$row)->getValue());
            if($shopGoodsCatMap[$shopGoodsCat]==''){
	            $sql = "select sc1.catId catId1,sc2.catId catId2,sc2.catName
	                    from __PREFIX__shops_cats sc2, __PREFIX__shops_cats sc1
	                    where sc2.parentId=sc1.catId
	                    and sc2.catFlag=1 and sc1.catFlag=1 and sc1.shopId=".$shopId." and sc2.catName='".$shopGoodsCat."'";
	            $trs = $this->queryRow($sql);
	            if(!empty($trs)){
	            	$shopGoodsCatMap[$trs['catName']] = $trs;
	            }
            }
            $goods['shopCatId1'] = (int)$shopGoodsCatMap[$shopGoodsCat]['catId1'];
            $goods['shopCatId2'] = (int)$shopGoodsCatMap[$shopGoodsCat]['catId2'];
            //查询品牌
            $brand = trim($sheet->getCell("P".$row)->getValue());
            if($brandMap[$brand]==''){
            	$sql="select brandId,brandName from __PREFIX__brands where brandName='".$brand."' and brandFlag=1";
            	$trs = $this->queryRow($sql);
	            if(!empty($trs)){
	            	$brandMap[$trs['brandName']] = $trs;
	            }
            }
            $goods['brandId'] = (int)$brandMap[$brand]['brandId'];
            $goods['goodsDesc'] = trim($sheet->getCell("Q".$row)->getValue());
            $goods['goodsStatus'] = 0;
            $goods['goodsFlag'] = 1;
            $goods['createTime'] = date('Y-m-d H:i:s');
            //$val = preg_replace('/^[(\xc2\xa0)|\s]+/', '',$val);
            $readData[] = $goods;
            $importNum++;
        }
        if(count($readData)>0)$goodsModel->addAll($readData);
        return array('status'=>1,'importNum'=>$importNum);
	}
}