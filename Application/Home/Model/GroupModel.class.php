<?php
namespace Home\Model;

/**
 * 团购商品模型
 */
class GroupModel extends BaseModel
{

    /**
     * 团购商品列表
     */
    public function getGoodsList($obj)
    {
        $areaId2 = $obj["areaId2"];
        $areaId3 = $obj["areaId3"];
        $communityId = I("communityId");
        $c1Id = (int) I("c1Id", 0);
        $c2Id = (int) I("c2Id");
        $c3Id = (int) I("c3Id");
        $pcurr = (int) I("pcurr");
        $msort = (int) I("msort", 1); // 排序标志
        $prices = I("prices");
        if ($prices != "") {
            $pricelist = explode("_", $prices);
        }
        $brandId = I("brandId", 0);
        
        $keyWords = urldecode(I("keyWords"));
        $words = array();
        if ($keyWords != "") {
            $words = explode(" ", $keyWords);
        }
        /*
         * $sql = "SELECT g.goodsId,goodsSn,goodsName,goodsThums,goodsStock,g.saleCount,p.shopId,marketPrice,shopPrice,ga.id goodsAttrId
         * FROM __PREFIX__goods g left join __PREFIX__goods_attributes ga on g.goodsId=ga.goodsId and ga.isRecomm=1, __PREFIX__shops p ";
         */
        // 为了只对应一个goods记录，修改sql为
        $sql = "SELECT  p.shopName,g.goodsId,goodsSn,goodsName,goodsImg,goodsThums,goodsStock,g.saleCount,p.shopId,marketPrice,shopPrice
				FROM __PREFIX__goods g, __PREFIX__shops p ";
        if ($communityId > 0) {
            $sql .= " , __PREFIX__shops_communitys sc ";
        }
        
        if ($brandId > 0) {
            $sql .= " , __PREFIX__brands bd ";
        }
        $sql .= "WHERE p.areaId2 = $areaId2 AND g.shopId = p.shopId AND  g.goodsStatus=1 AND g.goodsFlag = 1 and g.isSale=1 and g.isGroup=1";
        if ($communityId > 0) {
            $sql .= " AND sc.shopId=p.shopId AND sc.communityId = $communityId ";
        }
        if ($brandId > 0) {
            $sql .= " AND bd.brandId=g.brandId AND g.brandId = $brandId ";
        }
        if ($c1Id > 0) {
            $sql .= " AND g.goodsCatId1 = $c1Id";
        }
        if ($c2Id > 0) {
            $sql .= " AND g.goodsCatId2 = $c2Id";
        }
        if ($c3Id > 0) {
            $sql .= " AND g.goodsCatId3 = $c3Id";
        }
        
        if ($areaId3 > 0) {
            $sql .= " AND p.areaId3 = $areaId3";
        }
        if (! empty($words)) {
            $sarr = array();
            foreach ($words as $key => $word) {
                if ($word != "") {
                    $sarr[] = "g.goodsName LIKE '%$word%'";
                }
            }
            $sql .= " AND (" . implode(" or ", $sarr) . ")";
        }
        $glist = $this->query($sql);
        $shops = array();
        $maxPrice = 0;
        for ($i = 0; $i < count($glist); $i ++) {
            $goods = $glist[$i];
            if ($goods["shopPrice"] > $maxPrice) {
                $maxPrice = $goods["shopPrice"];
            }
        }
        if ($prices != "" && $pricelist[0] >= 0 && $pricelist[1] >= 0) {
            $sql .= " AND (g.shopPrice BETWEEN  " . (int) $pricelist[0] . " AND " . (int) $pricelist[1] . ") ";
        }
        
        if ($msort == 1) { // 综合
            $sql .= " ORDER BY g.saleCount DESC ";
        } else 
            if ($msort == 6) { // 人气
                $sql .= " ORDER BY g.saleCount DESC ";
            } else 
                if ($msort == 7) { // 销量
                    $sql .= " ORDER BY g.saleCount DESC ";
                } else 
                    if ($msort == 8) { // 价格
                        $sql .= " ORDER BY g.shopPrice ASC ";
                    } else 
                        if ($msort == 9) { // 价格
                            $sql .= " ORDER BY g.shopPrice DESC ";
                        } else 
                            if ($msort == 10) { // 好评
                            } else 
                                if ($msort == 11) { // 上架时间
                                    $sql .= " ORDER BY g.saleTime DESC ";
                                }
        $pages = $this->pageQuery($sql, $pcurr, 30);
        // 判断是否有价格属性
        $goodsAttrModel = M('GoodsAttributes');
        // 加入团购信息
        $goodsGroupModel = M('GoodsGroup');
        $goodsIsRecomm = $goodsAttrModel->field('goodsId')
            ->where('isRecomm = 1')
            ->select();
        $arr = array();
        $total = 0;
        foreach ($pages['root'] as $k => $v) {
            foreach ($goodsIsRecomm as $vs) {
                if ($v['goodsId'] == $vs['goodsId']) {
                    $v['isRecomm'] = 1;
                }
            }
            // 团购开始时间必须小于当前时间,只显示进行中的团购活动
            $group = $goodsGroupModel->where('goodsId=' . $v['goodsId'] . ' and shopId=' . $v['shopId'] . ' and goodsGroupStatus=1 and startTime<unix_timestamp(now()) and endTime>unix_timestamp(now())')->find();
            if ($group) {
                // 折扣
                $v['discount'] = round($group['groupPrice'] / $v['marketPrice'] * 10, 1);
                // 差价
                $v['disparity'] = $v['marketPrice'] - $group['groupPrice'];
                $v['shopPrice'] = $group['groupPrice'];
                // 倒计时时间
                $endsTime = $group['endTime'];
                $v['endsTime'] = date('Y/m/d H:i:s', $endsTime);
                // 时间戳转化为日期
                $v['startTime'] = date("m月d日", $group['startTime']);
                $v['endTime'] = date("m月d日", $group['endTime']);
                // 团购订单商品信息
                $sql = "select og.* from __PREFIX__order_goods og left join __PREFIX__orders o on o.orderId=og.orderId where o.orderStatus in(0,1,2,3,4) and og.goodsGroupId = " . $group['id'];
                $orderGoods = $this->query($sql);
                $totalNums = 0;
                foreach ($orderGoods as $ks => $vs) {
                    $totalNums += $vs['goodsNums'];
                }
                // 团购订单数量
                $v['totalNums'] = $totalNums;
                $arr[] = array_merge($v, $group);
                $total ++;
            }
        }
        $pages['root'] = $arr;
        $pages['total'] = $total;
        $rs["maxPrice"] = $maxPrice;
        $brands = array();
        $sql = "SELECT b.brandId, b.brandName,b.brandIco FROM __PREFIX__brands b, __PREFIX__goods_cat_brands cb WHERE b.brandId = cb.brandId AND b.brandFlag=1 ";
        if ($c1Id > 0) {
            $sql .= " AND cb.catId = $c1Id";
        }
        $sql .= " GROUP BY b.brandId";
        $blist = $this->query($sql);
        for ($i = 0; $i < count($blist); $i ++) {
            $brand = $blist[$i];
            $brands[$brand["brandId"]] = array(
                'brandId' => $brand["brandId"],
                'brandName' => $brand["brandName"],
                'brandIco' => $brand['brandIco']
            );
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
    public function getMaxPrice($obj)
    {
        $areaId2 = $obj["areaId2"];
        
        $c1Id = (int) I("c1Id");
        $c2Id = (int) I("c2Id");
        $c3Id = (int) I("c3Id");
        
        $keyWords = urldecode(I("keyWords"));
        $words = array();
        if ($keyWords != "") {
            $words = explode(" ", $keyWords);
        }
        
        $sql = "SELECT  g.goodsId,goodsSn,goodsName,goodsThums,g.saleCount,p.shopId,marketPrice,shopPrice,p.shopName
				FROM __PREFIX__goods g , __PREFIX__shops p , __PREFIX__goods_group gp ";
        $sql .= "WHERE p.areaId2 = $areaId2 AND g.shopId = p.shopId  AND g.goodsId = gp.goodsId AND g.goodsStatus=1 AND g.goodsFlag = 1 AND gp.goodsGroupStatus = 1 AND gp.groupStatus = 1";
        
        if ($c1Id > 0) {
            $sql .= " AND g.goodsCatId1 = $c1Id";
        }
        if ($c2Id > 0) {
            $sql .= " AND g.goodsCatId2 = $c2Id";
        }
        if ($c3Id > 0) {
            $sql .= " AND g.goodsCatId3 = $c3Id";
        }
        if (! empty($words)) {
            $sarr = array();
            foreach ($words as $key => $word) {
                if ($word != "") {
                    $sarr[] = "g.goodsName LIKE '%$word%'";
                }
            }
            $sql .= " AND (" . implode(" or ", $sarr) . ")";
        }
        $sql .= " ORDER BY g.saleCount DESC";
        $glist = $this->query($sql);
        $maxPrice = 0;
        for ($i = 0; $i < count($glist); $i ++) {
            $goods = $glist[$i];
            if ($goods["shopPrice"] > $maxPrice) {
                $maxPrice = $goods["shopPrice"];
            }
        }
        
        return $maxPrice;
    }

    /**
     * 获取商品类别导航
     */
    public function getGoodsNav($obj = array())
    {
        $goodsId = (int) I("goodsId");
        if ($goodsId > 0) {
            $sql = "SELECT goodsCatId1,goodsCatId2,goodsCatId3 FROM __PREFIX__goods WHERE goodsId = $goodsId";
            $rs = $this->queryRow($sql);
        } else {
            $rs = $obj;
        }
        $gclist = M('goods_cats')->cache('WST_CACHE_GOODS_CAT_URL', 31536000)
            ->where('isShow = 1')
            ->field('catId,catName')
            ->order('catId')
            ->select();
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
     * 查询商品信息
     */
    public function getGoodsDetails($obj)
    {
        $goodsId = $obj["goodsId"];
        $sql = "SELECT sc.catName,sc2.catName as pCatName, g.*,shop.shopName,shop.deliveryType,ga.id goodsAttrId,ga.attrPrice,ga.attrStock,
				shop.shopAtive,shop.shopTel,shop.shopAddress,shop.deliveryTime,shop.isInvoice, shop.deliveryStartMoney,g.goodsStock,shop.deliveryFreeMoney,shop.qqNo,
				shop.deliveryMoney ,g.goodsSn,shop.serviceStartTime,shop.serviceEndTime FROM __PREFIX__goods g left join __PREFIX__goods_attributes ga on g.goodsId=ga.goodsId and ga.isRecomm=1, __PREFIX__shops shop, __PREFIX__shops_cats sc
				LEFT JOIN __PREFIX__shops_cats sc2 ON sc.parentId = sc2.catId
				WHERE g.goodsId = $goodsId AND shop.shopId=sc.shopId AND sc.catId=g.shopCatId1 AND g.isGroup=1 AND g.shopId = shop.shopId AND g.goodsFlag = 1 ";
        $rs = $this->query($sql);
        if (! empty($rs) && $rs[0]['goodsAttrId'] > 0) {
            $rs[0]['shopPrice'] = $rs[0]['attrPrice'];
            $rs[0]['goodsStock'] = $rs[0]['attrStock'];
        }
        // 加入团购信息
        $goodsGroupModel = M('GoodsGroup');
        $orderModel = M('Orders');
        $group = $goodsGroupModel->where('id =' . $obj['id'])->find();
        $rs[0] = array_merge($rs[0], $group);
        // 折扣
        $rs[0]['discount'] = round($rs[0]['groupPrice'] / $rs[0]['marketPrice'] * 10, 1);
        // 差价
        $rs[0]['disparity'] = $rs[0]['marketPrice'] - $group['groupPrice'];
        // 团购订单商品信息
        $ordersGoodsModel = M('OrderGoods');
        $orderGoods = $ordersGoodsModel->where('goodsGroupId = ' . $group['id'])->select();
        if ($orderGoods) {
            foreach ($orderGoods as $k => $v) {
                $orders = $orderModel->where('orderId = ' . $v['orderId'] . ' and orderStatus in(0,1,2,3,4)')->find();
                if (! $orders) {
                    unset($orderGoods[$k]);
                }
            }
        }
        $totalNums = 0;
        foreach ($orderGoods as $ks => $vs) {
            $totalNums += $vs['goodsNums'];
        }
        // 团购订单数量
        $rs[0]['totalNums'] = $totalNums;
        return $rs[0];
    }

    /**
     * 获取商品相册
     */
    public function getGoodsImgs()
    {
        $goodsId = (int) I("goodsId");
        
        $sql = "SELECT img.* FROM __PREFIX__goods_gallerys img WHERE img.goodsId = $goodsId ";
        $rs = $this->query($sql);
        return $rs;
    }

    /**
     * 获取关联商品
     */
    public function getRelatedGoods()
    {
        $goodsId = (int) I("goodsId");
        $sql = "SELECT g.* FROM __PREFIX__goods g, " . DB_PRE . "goods_relateds gr WHERE g.goodsId = gr.relatedGoodsId AND g.goodsStock>0 AND g.goodsStatus = 1 AND gr.goodsId =$goodsId";
        $rs = $this->query($sql);
        return $rs;
    }

    /**
     * 获取商品的属性
     */
    public function getAttrs($obj)
    {
        $id = (int) $obj["goodsId"];
        $shopId = (int) $obj["shopId"];
        $attrCatId = (int) $obj["attrCatId"];
        $goods = array();
        // 获取商品的属性
        $sql = "select ga.id,ga.attrVal,ga.attrPrice,ga.attrStock,ga.isRecomm,a.attrId,a.attrName,a.isPriceAttr
		            from __PREFIX__attributes a
		            left join __PREFIX__goods_attributes ga on ga.attrId=a.attrId and ga.goodsId=" . $id . " where
					a.attrFlag=1 and a.catId=" . $attrCatId . " and a.shopId=" . $shopId . " order by a.attrSort, a.attrId,ga.id";
        $attrRs = $this->query($sql);
        if (! empty($attrRs)) {
            $priceAttr = array();
            $attrs = array();
            $attrId = array();
            foreach ($attrRs as $key => $v) {
                if ($v['isPriceAttr'] == 1) {
                    $attrId[$v['attrId']]['name'] = $v['attrName'];
                    if (in_array($v['attrId'], $attrId)) {
                        $attrId[$v['attrId']][] = $v;
                    } else {
                        $attrId[$v['attrId']][] = $v;
                    }
                } else {
                    $v['attrContent'] = $v['attrVal'];
                    $attrs[] = $v;
                }
            }
            $goods['priceAttrs'] = $attrId;
            $goods['attrs'] = $attrs;
        }
        return $goods;
    }
}