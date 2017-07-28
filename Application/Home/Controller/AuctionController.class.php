<?php
namespace Home\Controller;

/**
 * 拍卖商品控制器
 */
class AuctionController extends BaseController
{

    /**
     * 进行中的拍卖 下一页
     */
    public function getMoreNowAct()
    {
        $num = I('num');
        $brandId = I('skBrandId', 0);
        $price = I('skPrice');
        $skAreaId3 = I('skAreaId3', 0);
        $skAreaId2 = I('skAreaId2', 0);
        if ($price != "_") {
            $pricelist = explode("_", $price);
        }
        $time = time();
        $m = M("GoodsAuction");
        $sql = 'SELECT * FROM oto_goods_auction act INNER JOIN oto_goods g on g.goodsId = act.goodsId  INNER JOIN oto_shops p on p.shopId=g.shopId WHERE ( act.auctionStartTime<' . $time . ' and act.goodsAuctionStatus=1 and act.auctionEndTime>' . $time . ' AND g.goodsFlag = 1 and act.isPay=1 and g.isAuction=1 AND  g.goodsStatus=1)';
        if ($brandId != 0) {
            $sql .= ' and (g.brandId=' . $brandId . ')';
        }
        if ($price != '_' && $pricelist[1] > 0) {
            $sql .= " AND (g.shopPrice BETWEEN  " . (int) $pricelist[0] . " AND " . (int) $pricelist[1] . ") ";
        }
        if ($skAreaId2 > 0) {
            $sql .= 'AND (p.areaId2=' . $skAreaId2 . ')';
        }
        if ($skAreaId3 > 0) {
            $sql .= 'AND (p.areaId3=' . $skAreaId3 . ')';
        }
        $sql .= "ORDER BY act.auctionStartTime desc  LIMIT " . (($num - 1) * 5) . ",5";
        $seckillResult = $m->query($sql);
        // $seckillResult=$m->table('oto_goods_seckill s')->join('oto_goods g on g.goodsId = s.goodsId')->where('s.seckillStartTime<'.$time.' and s.goodsSeckillStatus=1 and s.seckillEndTime>'.$time)->order('s.seckillStartTime desc')->page($num,5)->select();
        
        foreach ($seckillResult as $k => $v) {
            $seckillResult[$k]['auctionEndTime'] = date("Y-m-d H:i:s", $v['auctionEndTime']);
        }
        $this->ajaxReturn($seckillResult, 'json');
    }

    /**
     * 进行中的拍卖 下一页
     */
    public function getMoreEndAct()
    {
        $num = I('num');
        $brandId = I('skBrandId', 0);
        $price = I('skPrice');
        $skAreaId3 = I('skAreaId3', 0);
        $skAreaId2 = I('skAreaId2', 0);
        if ($price != "_") {
            $pricelist = explode("_", $price);
        }
        $time = time();
        $m = M("GoodsAuction");
        $sql = 'SELECT * FROM oto_goods_auction act INNER JOIN oto_goods g on g.goodsId = act.goodsId  INNER JOIN oto_shops p on p.shopId=g.shopId WHERE (act.goodsAuctionStatus=1 and act.auctionEndTime<' . $time . ' AND g.goodsFlag = 1 and act.isPay=1 and g.isAuction=1 AND  g.goodsStatus=1)';
        if ($brandId != 0) {
            $sql .= ' and (g.brandId=' . $brandId . ')';
        }
        if ($price != '_' && $pricelist[1] > 0) {
            $sql .= " AND (g.shopPrice BETWEEN  " . (int) $pricelist[0] . " AND " . (int) $pricelist[1] . ") ";
        }
        if ($skAreaId2 > 0) {
            $sql .= 'AND (p.areaId2=' . $skAreaId2 . ')';
        }
        if ($skAreaId3 > 0) {
            $sql .= 'AND (p.areaId3=' . $skAreaId3 . ')';
        }
        $sql .= "ORDER BY act.auctionStartTime desc  LIMIT " . (($num - 1) * 5) . ",5";
        $seckillResult = $m->query($sql);
        // $seckillResult=$m->table('oto_goods_seckill s')->join('oto_goods g on g.goodsId = s.goodsId')->where('s.seckillStartTime<'.$time.' and s.goodsSeckillStatus=1 and s.seckillEndTime>'.$time)->order('s.seckillStartTime desc')->page($num,5)->select();
        
        foreach ($seckillResult as $k => $v) {
            $seckillResult[$k]['auctionEndTime'] = date("Y-m-d H:i:s", $v['auctionEndTime']);
        }
        $this->ajaxReturn($seckillResult, 'json');
    }

    public function getMoreWaitAct()
    {
        $num = I('num');
        $brandId = I('skBrandId', 0);
        $price = I('skPrice');
        $skAreaId3 = I('skAreaId3', 0);
        $skAreaId2 = I('skAreaId2', 0);
        if ($price != "_") {
            $pricelist = explode("_", $price);
        }
        $time = time();
        $m = M("GoodsAuction");
        $sql = 'SELECT * FROM oto_goods_auction act INNER JOIN oto_goods g on g.goodsId = act.goodsId  INNER JOIN oto_shops p on p.shopId=g.shopId WHERE ( act.auctionStartTime>' . $time . ' and act.goodsAuctionStatus=1 and act.auctionEndTime>' . $time . ' AND g.goodsFlag = 1 and act.isPay=1 and g.isAuction=1 AND  g.goodsStatus=1)';
        if ($brandId != 0) {
            $sql .= ' and (g.brandId=' . $brandId . ')';
        }
        if ($price != '_' && $pricelist[1] > 0) {
            $sql .= " AND (g.shopPrice BETWEEN  " . (int) $pricelist[0] . " AND " . (int) $pricelist[1] . ") ";
        }
        if ($skAreaId2 > 0) {
            $sql .= 'AND (p.areaId2=' . $skAreaId2 . ')';
        }
        if ($skAreaId3 > 0) {
            $sql .= 'AND (p.areaId3=' . $skAreaId3 . ')';
        }
        $sql .= "ORDER BY act.auctionStartTime asc  LIMIT " . (($num - 1) * 5) . ",5";
        $seckillResult = $m->query($sql);
        // $seckillResult=$m->table('oto_goods_seckill s')->join('oto_goods g on g.goodsId = s.goodsId')->where('s.seckillStartTime<'.$time.' and s.goodsSeckillStatus=1 and s.seckillEndTime>'.$time)->order('s.seckillStartTime desc')->page($num,5)->select();
        
        foreach ($seckillResult as $k => $v) {
            $seckillResult[$k]['auctionStartTime'] = date("Y-m-d H:i:s", $v['auctionStartTime']);
        }
        $this->ajaxReturn($seckillResult, 'json');
    }

    public function index()
    {
        $mgoods = D('Home/Auction');
        $mareas = D('Home/Areas');
        $mcommunitys = D('Home/Communitys');
        // ��ȡĬ�ϳ��м�����
        $areaId2 = $this->getDefaultCity();
        $districts = $mareas->getDistricts($areaId2);
        // ��ȡ����
        $areaId3 = (int) I("areaId3");
        $communitys = array();
        if ($areaId3 > 0) {
            $communitys = $mcommunitys->getByDistrict($areaId3);
        }
        $this->assign('communitys', $communitys);
        
        // ��ȡ��Ʒ�б�
        $obj["areaId2"] = $areaId2;
        $obj["areaId3"] = $areaId3;
        // 1 表示已开始 2表示未开始 3已结束
        $rslist = $mgoods->getGoodsList($obj, 1, time());
        $rslist2 = $mgoods->getGoodsList($obj, 2, time());
        $rslist3 = $mgoods->getGoodsList($obj, 3, time());
        // 注入即将开始、等待开始的秒杀商品数据
        $this->assign('actNowData', $rslist['pages']);
        $this->assign('actWaitData', $rslist2['pages']);
        $this->assign('actEndData', $rslist3['pages']);
        // print_r($rslist['pages']);
        $brands = $rslist["brands"];
        $pages = $rslist["pages"];
        $goodsNav = $rslist["goodsNav"];
        $this->assign('goodsList', $rslist);
        // ��̬���ּ۸�����
        $maxPrice = $mgoods->getMaxPrice($obj);
        $minPrice = 0;
        $pavg5 = ($maxPrice / 5);
        $prices = array();
        $price_grade = 0.0001;
        for ($i = - 2; $i <= log10($maxPrice); $i ++) {
            $price_grade *= 10;
        }
        // ������
        $span = ceil(($maxPrice - $minPrice) / 8 / $price_grade) * $price_grade;
        if ($span == 0) {
            $span = $price_grade;
        }
        for ($i = 1; $i <= 8; $i ++) {
            $prices[($i - 1) * $span . "_" . ($span * $i)] = ($i - 1) * $span . "-" . ($span * $i);
            if (($span * $i) > $maxPrice)
                break;
        }
        if (count($prices) < 5) {
            $prices = array();
            $prices["0_100"] = "0-100";
            $prices["100_200"] = "100-200";
            $prices["200_300"] = "200-300";
            $prices["300_400"] = "300-400";
            $prices["400_500"] = "400-500";
        }
        $this->assign('c1Id', I("c1Id"));
        $this->assign('c2Id', I("c2Id"));
        $this->assign('c3Id', I("c3Id"));
        $this->assign('bs', I("bs", 0));
        $this->assign('msort', I("msort", 0));
        $this->assign('sj', I("sj", 0));
        $this->assign('stime', I("stime")); // �ϼܿ�ʼʱ��
        $this->assign('etime', I("etime")); // �ϼܽ���ʱ��
        
        $this->assign('areaId3', I("areaId3", 0));
        $this->assign('communityId', I("communityId", 0));
        
        $pricelist = explode("_", I("prices"));
        $this->assign('sprice', $pricelist[0]);
        $this->assign('eprice', $pricelist[1]);
        
        $this->assign('brandId', I("brandId", 0));
        $this->assign('keyWords', urldecode(I("keyWords")));
        $this->assign('brands', $brands);
        $this->assign('goodsNav', $goodsNav);
        $this->assign('pages', $pages);
        $this->assign('prices', $prices);
        $priceId = $prices[I("prices")];
        $this->assign('priceId', (strlen($priceId) > 1) ? I("prices") : '');
        $this->assign('districts', $districts);
        $this->display('goods_list');
    }

    /**
     * ��ѯ��Ʒ����
     */
    public function getGoodsDetails()
    {
        // session_destroy();
        $goods = D('Home/Auction');
        $kcode = I("kcode");
        $scrictCode = base64_encode(md5("o2omall" . date("Y-m-d")));
        // ��ѯ��Ʒ����
        $goodsId = (int) I("goodsId");
        $this->assign('goodsId', $goodsId);
        $obj["goodsId"] = $goodsId;
        $goodsDetails = $goods->getGoodsDetails($obj);
        // var_dump($goodsDetails);
        // 无论上下架都可以显示
        // if($kcode==$scrictCode || ($goodsDetails["isSale"]==1 && $goodsDetails["goodsStatus"]==1)){
        if ($kcode == $scrictCode || ($goodsDetails["goodsStatus"] == 1)) {
            if ($kcode == $scrictCode) { // ���Ժ�̨����Ա
                $this->assign('comefrom', 1);
            }
            
            $shopServiceStatus = 1;
            if ($goodsDetails["shopAtive"] == 0) {
                $shopServiceStatus = 0;
            }
            $goodsDetails["serviceEndTime"] = str_replace('.5', ':30', $goodsDetails["serviceEndTime"]);
            $goodsDetails["serviceEndTime"] = str_replace('.0', ':00', $goodsDetails["serviceEndTime"]);
            $goodsDetails["serviceStartTime"] = str_replace('.5', ':30', $goodsDetails["serviceStartTime"]);
            $goodsDetails["serviceStartTime"] = str_replace('.0', ':00', $goodsDetails["serviceStartTime"]);
            $goodsDetails["shopServiceStatus"] = $shopServiceStatus;
            $goodsDetails['goodsDesc'] = htmlspecialchars_decode($goodsDetails['goodsDesc']);
            
            $areas = D('Home/Areas');
            $shopId = intval($goodsDetails["shopId"]);
            $obj["shopId"] = $shopId;
            $obj["areaId2"] = $this->getDefaultCity();
            $obj["attrCatId"] = $goodsDetails['attrCatId'];
            $shops = D('Home/Shops');
            $shopScores = $shops->getShopScores($obj);
            $this->assign("shopScores", $shopScores);
            
            $shopCity = $areas->getDistrictsByShop($obj);
            $this->assign("shopCity", $shopCity[0]);
            
            $shopCommunitys = $areas->getShopCommunitys($obj);
            $this->assign("shopCommunitys", json_encode($shopCommunitys));
            
            $this->assign("goodsImgs", $goods->getGoodsImgs());
            // $this->assign("hotgoods",$goods->getHotGoods($goodsDetails['shopId']));
            $this->assign("relatedGoods", $goods->getRelatedGoods($goodsId));
            $this->assign("goodsNav", $goods->getGoodsNav());
            // ��ȡ��Ʒ���Լ۸��ۼ�,��Ʒ���Կ��ȡ��Сֵ
            $price = 0.0;
            $stock = array();
            $goodsAttrId = array();
            foreach ($goods->getAttrs($obj) as $k => $v) {
                if ($k == 'priceAttrs') {
                    foreach ($v as $key => $value) {
                        foreach ($value as $ks => $vs) {
                            if (is_numeric($ks) && $vs['isRecomm'] == 1) {
                                $price += $vs['attrPrice'];
                                $stock[] = $vs['attrStock'];
                                $goodsAttrId[] = $vs['id'];
                            }
                        }
                    }
                }
            }
            $stocks = min($stock);
            if ($stocks != 0) {
                $goodsDetails['goodsStock'] = $stocks;
            }
            if ($price != 0) {
                $goodsDetails['shopPrice'] = $price;
            }
            
            $goodsDetails['goodsAttrId'] = $goodsAttrsId;
            $this->assign("goodsAttrs", $goods->getAttrs($obj));
            $this->assign("goodsDetails", $goodsDetails);
            $viewGoods = cookie("viewGoods");
            $sql = "select id,attrPrice,attrStock from  __PREFIX__goods_attributes where goodsId=" . $goodsId;
            if (! in_array($goodsId, $viewGoods)) {
                $viewGoods[] = $goodsId;
            }
            if (! empty($viewGoods)) {
                cookie("viewGoods", $viewGoods, 25920000);
            }
            // ��ȡ��ע��Ϣ
            $m = D('Home/Favorites');
            $this->assign("favoriteGoodsId", $m->checkFavorite($goodsId, 0));
            $m = D('Home/Favorites');
            $this->assign("favoriteShopId", $m->checkFavorite($shopId, 1));
            // �ͻ��˶�ά��
            $this->assign("qrcode", base64_encode("{type:'goods',content:'" . $goodsId . "',key:'wstmall'}"));
            $this->display('goods_details');
        } else {
            $this->display('goods_notexist');
        }
    }
    // 第一次参加需要支付保证金
    public function joinAuction()
    {
        $USER = session('WST_USER');
        if (! $USER['userId'])
            $this->ajaxReturn(2, 'json');
        $data['userId'] = $USER['userId'];
        $data['goodsId'] = I('goodsId');
        $data['shopId'] = I('shopId');
        $data['joinPrice'] = I('joinPrice');
        $data['actCreateTime'] = time();
        $auctionModel = D('Home/Auction');
        $nextJoinPrice = $auctionModel->getNextJoinPrice($data['goodsId']);
        if ($data['joinPrice'] < $nextJoinPrice)
            $this->ajaxReturn(3, 'json');
        $ap = M('GoodsAuctionAddprice');
        $temp = $ap->data($data)->add();
        if ($temp) {
            $this->ajaxReturn(1, 'json');
        } else {
            $this->ajaxReturn(0, 'json');
        }
    }
    // 再次参加不需要再次支付保证金
    public function againJoinAuction()
    {
        $USER = session('WST_USER');
        if (! $USER['userId'])
            $this->ajaxReturn(2, 'json');
        $data['userId'] = $USER['userId'];
        $data['goodsId'] = I('goodsId');
        $data['shopId'] = I('shopId');
        $data['joinPrice'] = I('joinPrice');
        $data['actCreateTime'] = time();
        $auctionModel = D('Home/Auction');
        $nextJoinPrice = $auctionModel->getNextJoinPrice($data['goodsId']);
        if ($data['joinPrice'] < $nextJoinPrice)
            $this->ajaxReturn(3, 'json');
        $ap = M('GoodsAuctionAddprice');
        $temp = $ap->data($data)->add();
        if ($temp) {
            $this->ajaxReturn(1, 'json');
        } else {
            $this->ajaxReturn(0, 'json');
        }
    }

    public function getNowData()
    {
        $data = array();
        $goodsId = I('goodsId');
        $ap = M('GoodsAuctionAddprice');
        $ga = M('GoodsAuction');
        $data['joinMaxPrice'] = $ap->where('goodsId=' . $goodsId)->max('joinPrice');
        if (! $data['joinMaxPrice']) {
            $ga = M('GoodsAuction');
            $temp = $ga->where('goodsId=' . $goodsId)->find();
            $data['joinMaxPrice'] = $temp['auctionLowPrice'];
        }
        // 点赞数 浏览数
        $oneData = $ga->where('goodsId=' . $goodsId)
            ->field('auctionLike,auctionBowse')
            ->find();
        $data['auctionLike'] = $oneData['auctionLike'];
        $data['auctionBowse'] = $oneData['auctionBowse'];
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
        $data['joinHotNum'] = $joinHotNum;
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
        $data['auctionRecord'] = $auctionRecord;
        
        $this->ajaxReturn($data, 'json');
    }

    public function addLikeNum()
    {
        $goodsId = I('goodsId');
        $ga = M('GoodsAuction');
        $state = $ga->where('goodsId=' . $goodsId)->setInc('auctionLike');
        if ($state) {
            $this->ajaxReturn(1, 'json');
        } else {
            $this->ajaxReturn(0, 'json');
        }
    }

    public function getsuccessRecordData()
    {
        $goodsId = I('goodsId');
        $ga = M('GoodsAuctionAddprice');
        $state = $ga->where('goodsId=' . $goodsId)->select();
        if ($state) {
            $this->ajaxReturn(1, 'json');
        } else {
            $this->ajaxReturn(0, 'json');
        }
    }
    // 卖家支付保证金 模拟
    public function payMarginMoney()
    {
        // $this->isAjaxLogin();
        // $this->checkAjaxPrivelege('splb_04');
        $m = M('GoodsAuction');
        $goodsId = I('auctionId');
        $rs = $m->where('goodsId=' . $goodsId)->save(array(
            'isPay' => 1
        ));
        if ($rs) {
            $rs = 1;
        } else {
            $rs = 0;
        }
        $this->ajaxReturn($rs);
    }
    // 卖家放弃申请拍卖
    public function giveUpAuction()
    {
        // $this->isAjaxLogin();
        // $this->checkAjaxPrivelege('splb_04');
        $m = D('Home/Auction');
        $rs = $m->giveUpAuction();
        $this->ajaxReturn($rs);
    }
}