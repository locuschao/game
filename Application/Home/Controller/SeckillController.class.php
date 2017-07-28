<?php
namespace Home\Controller;

/**
 * ��Ʒ������
 */
class SeckillController extends BaseController
{

    /**
     * ��Ʒ�б�
     */
    public function getMoreNowSk()
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
        $m = M("GoodsSeckill");
        $sql = 'SELECT * FROM oto_goods_seckill s INNER JOIN oto_goods g on g.goodsId = s.goodsId  INNER JOIN oto_shops p on p.shopId=g.shopId WHERE ( s.seckillStartTime<' . $time . ' and s.goodsSeckillStatus=1 and s.seckillEndTime>' . $time . ' AND g.goodsFlag = 1 and g.isSale=1 AND  g.goodsStatus=1)';
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
        $sql .= "ORDER BY s.seckillStartTime desc  LIMIT " . (($num - 1) * 5) . ",5";
        $seckillResult = $m->query($sql);
        // $seckillResult=$m->table('oto_goods_seckill s')->join('oto_goods g on g.goodsId = s.goodsId')->where('s.seckillStartTime<'.$time.' and s.goodsSeckillStatus=1 and s.seckillEndTime>'.$time)->order('s.seckillStartTime desc')->page($num,5)->select();
        
        foreach ($seckillResult as $k => $v) {
            $seckillResult[$k]['seckillEndTime'] = date("Y-m-d H:i:s", $v['seckillEndTime']);
        }
        $this->ajaxReturn($seckillResult, 'json');
    }

    public function getMoreWaitSk()
    {
        // $num=I('num');
        // $time=time();
        // $m=M("GoodsSeckill");
        // $skWaitData=$m->table('oto_goods_seckill s')->join('oto_goods g on g.goodsId = s.goodsId')->where('s.seckillStartTime>'.$time.' and s.goodsSeckillStatus=1 ')->order('s.seckillStartTime asc')->page($num,5)->select();
        // foreach($skWaitData as $k=>$v){
        // $skWaitData[$k]['seckillStartTime']=date("Y-m-d H:i:s",$v['seckillStartTime']);
        // }
        // $this->ajaxReturn($skWaitData,'json');
        $num = I('num');
        $brandId = I('skBrandId', 0);
        $price = I('skPrice');
        $skAreaId3 = I('skAreaId3', 0);
        $skAreaId2 = I('skAreaId2', 0);
        if ($price != "_") {
            $pricelist = explode("_", $price);
        }
        $time = time();
        $m = M("GoodsSeckill");
        $sql = 'SELECT * FROM oto_goods_seckill s INNER JOIN oto_goods g on g.goodsId = s.goodsId  INNER JOIN oto_shops p on p.shopId=g.shopId WHERE ( s.seckillStartTime>' . $time . ' and s.goodsSeckillStatus=1 and s.seckillEndTime>' . $time . ' AND g.goodsFlag = 1 and g.isSale=1 AND  g.goodsStatus=1)';
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
        $sql .= "ORDER BY s.seckillStartTime asc  LIMIT " . (($num - 1) * 5) . ",5";
        $seckillResult = $m->query($sql);
        // $seckillResult=$m->table('oto_goods_seckill s')->join('oto_goods g on g.goodsId = s.goodsId')->where('s.seckillStartTime<'.$time.' and s.goodsSeckillStatus=1 and s.seckillEndTime>'.$time)->order('s.seckillStartTime desc')->page($num,5)->select();
        
        foreach ($seckillResult as $k => $v) {
            $seckillResult[$k]['seckillStartTime'] = date("Y-m-d H:i:s", $v['seckillStartTime']);
        }
        $this->ajaxReturn($seckillResult, 'json');
    }

    public function index()
    {
        // 注入秒杀商品
        // $time=time();
        // $this->assign('time',$time);
        // $m=M("GoodsSeckill");
        // $skNowData=$m->table('oto_goods_seckill s')->join('oto_goods g on g.goodsId = s.goodsId')->where('s.seckillStartTime<'.$time.' and s.goodsSeckillStatus=1 and s.seckillEndTime>'.$time)->order('s.seckillStartTime desc')->limit(5)->select();
        // $skWaitData=$m->table('oto_goods_seckill s')->join('oto_goods g on g.goodsId = s.goodsId')->where('s.seckillStartTime>'.$time.' and s.goodsSeckillStatus=1 ')->order('s.seckillStartTime asc')->limit(5)->select();
        // $this->assign('skNowData',$skNowData);
        // $this->assign('skWaitData',$skWaitData);
        $mgoods = D('Home/Seckill');
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
        // 1 表示已开始 2表示未开始
        $rslist = $mgoods->getGoodsList($obj, 1, time());
        $rslist2 = $mgoods->getGoodsList($obj, 2, time());
        // 注入即将开始、等待开始的秒杀商品数据
        $this->assign('skNowData', $rslist['pages']);
        $this->assign('skWaitData', $rslist2['pages']);
        
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
        $goods = D('Home/Seckill');
        $kcode = I("kcode");
        $scrictCode = base64_encode(md5("o2omall" . date("Y-m-d")));
        // ��ѯ��Ʒ����
        $goodsId = (int) I("goodsId");
        $this->assign('goodsId', $goodsId);
        $obj["goodsId"] = $goodsId;
        $goodsDetails = $goods->getGoodsDetails($obj);
        // print_r($goodsDetails);
        if ($kcode == $scrictCode || ($goodsDetails["isSale"] == 1 && $goodsDetails["goodsStatus"] == 1)) {
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
}