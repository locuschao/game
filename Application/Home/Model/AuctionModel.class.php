<?php
namespace Home\Model;

/**
 * 拍卖商品模型
 */
class AuctionModel extends BaseModel
{

    /**
     * 拍卖商品列表
     */
    public function getGoodsList($obj, $type, $time)
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
				FROM __PREFIX__goods g, __PREFIX__goods_auction act,__PREFIX__shops p ";
        if ($communityId > 0) {
            $sql .= " , __PREFIX__shops_communitys sc ";
        }
        
        if ($brandId > 0) {
            $sql .= " , __PREFIX__brands bd ";
        }
        if ($type == 1) {
            $sql .= "WHERE p.areaId2 = $areaId2 AND g.shopId = p.shopId AND  g.goodsStatus=1 AND g.goodsId = act.goodsId and act.goodsAuctionStatus=1 and act.isPay=1 and act.auctionStartTime<" . $time . " and act.auctionEndTime>" . $time . " AND g.goodsFlag = 1 and g.isAuction=1";
        } elseif ($type == 2) {
            $sql .= "WHERE p.areaId2 = $areaId2 AND g.shopId = p.shopId AND  g.goodsStatus=1 AND g.goodsId = act.goodsId and act.goodsAuctionStatus=1 and act.isPay=1 and act.auctionStartTime>" . $time . " and act.auctionEndTime>" . $time . " AND g.goodsFlag = 1 and g.isAuction=1";
        } else {
            $sql .= "WHERE p.areaId2 = $areaId2 AND g.shopId = p.shopId AND  g.goodsStatus=1 AND g.goodsId = act.goodsId and act.goodsAuctionStatus=1 and act.isPay=1 and  act.auctionEndTime<" . $time . " AND g.goodsFlag = 1 and g.isAuction=1";
        }
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
        
        if ($type == 1) {
            $sql .= " ORDER BY act.auctionStartTime desc ";
        } elseif ($type == 2) {
            $sql .= " ORDER BY act.auctionStartTime asc ";
        } else {
            $sql .= " ORDER BY act.auctionEndTime desc ";
        }
        
        $sql .= ' LIMIT 0,5';
        $pages = $this->query($sql);
        // 判断是否有价格属性
        $goodsAttrModel = M('GoodsAttributes');
        // 加入拍卖信息
        $goodsGroupModel = M('GoodsAuction');
        $goodsIsRecomm = $goodsAttrModel->field('goodsId')
            ->where('isRecomm = 1')
            ->select();
        foreach ($pages as $k => $v) {
            foreach ($goodsIsRecomm as $vs) {
                if ($v['goodsId'] == $vs['goodsId']) {
                    $pages[$k]['isRecomm'] = 1;
                }
            }
            $group = $goodsGroupModel->where('goodsId=' . $v['goodsId'] . ' and shopId=' . $v['shopId'])->find();
            $pages[$k] = array_merge($pages[$k], $group);
            // //折扣
            // $pages['root'][$k]['discount'] = round($group['groupPrice']/$v['marketPrice']*10,1);
            // //差价
            // $pages['root'][$k]['disparity'] = $v['marketPrice']-$group['groupPrice'];
            // $pages['root'][$k]['shopPrice'] = $group['groupPrice'];
            // //倒计时时间
            // $endsTime = $group['endTime'];
            // $pages['root'][$k]['endsTime'] = date('Y/m/d H:i:s',$endsTime);
            // //时间戳转化为日期
            // $pages['root'][$k]['startTime'] = date("m月d日",$group['startTime']);
            // $pages['root'][$k]['endTime'] = date("m月d日",$group['endTime']);
        }
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

    public function getNextJoinPrice($goodsId)
    {
        $ap = M('GoodsAuctionAddprice');
        $maxPrice = $ap->where('goodsId=' . $goodsId)->max('joinPrice');
        
        $ga = M('GoodsAuction');
        $addPrice = $ga->where('goodsId=' . $goodsId)->getField('auctionAddPrice');
        $lowPrice = $ga->where('goodsId=' . $goodsId)->getField('auctionLowPrice');
        $maxPrice = $maxPrice ? $maxPrice : $lowPrice;
        return ($maxPrice + $addPrice);
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
        
        $sql = "SELECT bd.brandId,bd.brandName, goodsId,goodsSn,goodsName,goodsThums,g.saleCount,p.shopId,marketPrice,shopPrice,p.shopName
				FROM __PREFIX__goods g , __PREFIX__brands bd, __PREFIX__shops p ";
        $sql .= "WHERE p.areaId2 = $areaId2 AND g.shopId = p.shopId AND bd.brandId=g.brandId AND  g.goodsStatus=1 AND g.goodsFlag = 1";
        
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
				WHERE g.goodsId = $goodsId AND shop.shopId=sc.shopId AND sc.catId=g.shopCatId1 AND g.isAuction=1 AND g.shopId = shop.shopId AND g.goodsFlag = 1 ";
        $rs = $this->query($sql);
        if (! empty($rs) && $rs[0]['goodsAttrId'] > 0) {
            $rs[0]['shopPrice'] = $rs[0]['attrPrice'];
            $rs[0]['goodsStock'] = $rs[0]['attrStock'];
        }
        // 加入拍卖信息，增加浏览记录
        $goodsGroupModel = M('GoodsAuction');
        $goodsGroupModel->where('goodsId=' . $rs[0]['goodsId'] . ' and shopId=' . $rs[0]['shopId'])->setInc('auctionBowse');
        $group['root'][0] = $goodsGroupModel->where('goodsId=' . $rs[0]['goodsId'] . ' and shopId=' . $rs[0]['shopId'])->find();
        $morders = D('Home/Orders');
        $temp = $morders->checkWin($group);
        $group = $temp['root'][0];
        if (! ! $group) {
            if (time() > $group['auctionStartTime']) {
                $group['auctionTime'] = date('Y m d H:i:s', $group['auctionEndTime']);
                $group['state'] = '结束';
            } else {
                $group['auctionTime'] = date('Y m d H:i:s', $group['auctionStartTime']);
                $group['state'] = '开始';
            }
        }
        // 是否参加过
        $USER = session('WST_USER');
        $userId = $USER['userId'];
        
        $ap = M('GoodsAuctionAddprice');
        if ($userId) {
            $userJoinStatus = $ap->where('goodsId=' . $goodsId . ' AND userId=' . $userId)->find();
            $group['userJoinStatus'] = $userJoinStatus;
        }
        // 最高叫价
        $joinMaxPrice = $ap->where('goodsId=' . $goodsId)->max('joinPrice');
        $group['joinMaxPrice'] = $joinMaxPrice ? $joinMaxPrice : $group['auctionLowPrice'];
        // 参加叫价
        $nowJoinPrice = $joinMaxPrice ? $joinMaxPrice : $group['auctionLowPrice'];
        $group['nowJoinPrice'] = $nowJoinPrice + $group['auctionAddPrice'];
        // 热度-参加人的人数
        $joinHotNum = array();
        $auctionNum = $ap->join('RIGHT JOIN oto_users ON oto_users.userId = oto_goods_auction_addPrice.userId')
            ->where('goodsId=' . $goodsId)
            ->field('oto_goods_auction_addPrice.userId')
            ->select();
        foreach ($auctionNum as $k => $v) {
            $joinHotNum[] = $v['userId'];
        }
        $joinHotNum = count(array_flip($joinHotNum));
        $group['joinHotNum'] = $joinHotNum;
        // 叫价记录 limit 5
        $auctionRecord = $ap->join('RIGHT JOIN oto_users ON oto_users.userId = oto_goods_auction_addPrice.userId')
            ->where('goodsId=' . $goodsId)
            ->order('actCreateTime desc')
            ->limit(5)
            ->field('actCreateTime,userName,joinPrice')
            ->select();
        foreach ($auctionRecord as $k => $v) {
            $auctionRecord[$k]['actCreateTime'] = date('Y-m-d H:i:s', $v['actCreateTime']);
        }
        $group['auctionRecord'] = $auctionRecord;
        // 成交记录
        if ($group['isDeal'] == 1) {
            $recordData = $ap->join('RIGHT JOIN oto_users ON oto_users.userId = oto_goods_auction_addPrice.userId')
                ->where('goodsId=' . $goodsId . ' and isWin=1')
                ->order('joinPrice desc')
                ->field('joinPrice,userName')
                ->select();
            $group['recordData'] = $recordData;
        }
        
        $rs[0] = array_merge($rs[0], $group);
        
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
    // 卖家放弃拍卖的申请
    public function giveUpAuction()
    {
        $id = (int) I('auctionId');
        $m = M('GoodsAuction');
        $winNum = $m->where('goodsId=' . $id)->getField('auctionWinNum');
        $num = $m->where('goodsId=' . $id)->delete();
        $g = M('goods');
        $num2 = $g->where('goodsId=' . $id)->save(array(
            'isAuction' => '0'
        ));
        $g->where('goodsId=' . $id)->setInc('goodsStock', $winNum);
        if (! ! $num && ! ! $num2) {
            return 1;
        } else {
            return 2;
        }
    }
}