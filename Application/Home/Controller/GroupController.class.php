<?php
namespace Home\Controller;

/**
 * 团购商品控制器
 */
class GroupController extends BaseController
{

    /**
     * 商品列表
     */
    public function index()
    {
        // 修改团购活动状态
        $this->changeGroupStatus();
        $mgoods = D('Home/Group');
        $mareas = D('Home/Areas');
        $mcommunitys = D('Home/Communitys');
        // 获取默认城市及县区
        $areaId2 = $this->getDefaultCity();
        $districts = $mareas->getDistricts($areaId2);
        // 获取社区
        $areaId3 = (int) I("areaId3");
        $communitys = array();
        if ($areaId3 > 0) {
            $communitys = $mcommunitys->getByDistrict($areaId3);
        }
        $this->assign('communitys', $communitys);
        
        // 获取商品列表
        $obj["areaId2"] = $areaId2;
        $obj["areaId3"] = $areaId3;
        $rslist = $mgoods->getGoodsList($obj);
        $brands = $rslist["brands"];
        $pages = $rslist["pages"];
        $goodsNav = $rslist["goodsNav"];
        $this->assign('goodsList', $rslist);
        // 动态划分价格区间
        $maxPrice = $mgoods->getMaxPrice($obj);
        $minPrice = 0;
        $pavg5 = ($maxPrice / 5);
        $prices = array();
        $price_grade = 0.0001;
        for ($i = - 2; $i <= log10($maxPrice); $i ++) {
            $price_grade *= 10;
        }
        // 区间跨度
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
        $this->assign('stime', I("stime")); // 上架开始时间
        $this->assign('etime', I("etime")); // 上架结束时间
        
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
        // 团购商品倒计时
        $endTime = $pages['root'][0]['endTime'];
        $endTime = date('Y/m/d H:i:s', $endTime);
        $this->assign('endTime', $endTime);
        $this->assign('prices', $prices);
        $priceId = $prices[I("prices")];
        $this->assign('priceId', (strlen($priceId) > 1) ? I("prices") : '');
        $this->assign('districts', $districts);
        $this->display('goods_list');
    }

    /**
     * 查询商品详情
     */
    public function getGoodsDetails()
    {
        // 修改团购活动状态
        $this->changeGroupStatus();
        $goods = D('Home/Group');
        $kcode = I("kcode");
        $scrictCode = base64_encode(md5("o2omall" . date("Y-m-d")));
        // 查询团购商品活动详情
        $goodsId = (int) I("goodsId");
        $id = (int) I("id");
        $this->assign('goodsId', $goodsId);
        $obj["goodsId"] = $goodsId;
        $obj["id"] = $id;
        $goodsDetails = $goods->getGoodsDetails($obj);
        // 团购商品倒计时
        $endTime = $goodsDetails['endTime'];
        $endTime = date('Y/m/d H:i:s', $endTime);
        $this->assign('endTime', $endTime);
        // 是否是从团购商品列表获取
        $this->assign('isGroup', I('isGroup'));
        $time = time();
        if ($kcode == $scrictCode || ($goodsDetails["groupStatus"] == 1 && $goodsDetails["startTime"] < $time && $goodsDetails["endTime"] > $time)) {
            if ($kcode == $scrictCode) { // 来自后台管理员
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
            $goodsDetails['groupDesc'] = htmlspecialchars_decode($goodsDetails['groupDesc']);
            
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
            // 获取商品属性价格并累加,商品属性库存取最小值
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
            // 获取关注信息
            $m = D('Home/Favorites');
            $this->assign("favoriteGoodsId", $m->checkFavorite($goodsId, 0));
            $m = D('Home/Favorites');
            $this->assign("favoriteShopId", $m->checkFavorite($shopId, 1));
            // 客户端二维码
            $this->assign("qrcode", base64_encode("{type:'goods',content:'" . $goodsId . "',key:'wstmall'}"));
            $this->display('goods_details');
        } else {
            $this->assign('time', $time);
            $this->assign('goodsDetails', $goodsDetails);
            $this->display('goods_notexist');
        }
    }
}