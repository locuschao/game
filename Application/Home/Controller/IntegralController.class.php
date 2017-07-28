<?php
namespace Home\Controller;

/**
 * 积分商城商品控制器
 */
class IntegralController extends BaseController
{

    /**
     * 分页显示积分商品列表
     */
    public function index()
    {
        $this->isLogin();
        // 获取个人信息
        $m = D('Home/Users');
        $obj["userId"] = session('WST_USER.userId');
        $user = $m->getUserById($obj);
        $this->assign('user', $user);
        // 获取所有商品分类
        $m = D('Integral');
        $cats = $m->getCats();
        $this->assign('cats', $cats);
        $this->assign('catId', I("catId", 0));
        // 获取所有商品
        $pages = $m->queryByPage();
        $this->assign('pages', $pages);
        // 动态划分价格区间
        $maxPrice = $m->getMaxPrice($obj);
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
            $prices["0_1000"] = "0-1000";
            $prices["1000_2000"] = "1000-2000";
            $prices["2000_3000"] = "2000-3000";
            $prices["3000_4000"] = "3000-4000";
            $prices["4000_5000"] = "4000-5000";
            $prices["5000_6000"] = "5000-6000";
            $prices["6000_7000"] = "6000-7000";
            $prices["7000_8000"] = "7000-8000";
            $prices["8000_9000"] = "8000-9000";
            $prices["9000_10000"] = "9000-10000";
            $prices["10000_100000"] = "10000+";
        }
        $this->assign('c1Id', I("c1Id"));
        $this->assign('c2Id', I("c2Id"));
        $this->assign('c3Id', I("c3Id"));
        $this->assign('bs', I("bs", 0));
        $this->assign('msort', I("msort", 0));
        $this->assign('sj', I("sj", 0));
        $this->assign('stime', I("stime")); // 上架开始时间
        $this->assign('etime', I("etime")); // 上架结束时间
        
        $this->assign('communityId', I("communityId", 0));
        
        $pricelist = explode("_", I("prices"));
        $this->assign('sprice', $pricelist[0]);
        $this->assign('eprice', $pricelist[1]);
        $this->assign('keyWords', urldecode(I("keyWords")));
        $this->assign('brands', $brands);
        $this->assign('goodsNav', $goodsNav);
        
        $this->assign('prices', $prices);
        $priceId = $prices[I("prices")];
        $this->assign('priceId', (strlen($priceId) > 1) ? I("prices") : '');
        $this->display('integral/list');
        exit(0);
    }

    /*
     * 获取商品详情
     */
    public function getGoodsDetails()
    {
        $this->isLogin();
        $goodsId = (int) I('goodsId');
        // 获取指定id商品
        $goodsm = D('Integral');
        $goodsDetails = $goodsm->getGoodsDetails($goodsId);
        $goodsContent = $goodsDetails['goodsContent'];
        $goodsDetails['goodsContent'] = htmlspecialchars_decode($goodsContent);
        $this->assign('goodsDetails', $goodsDetails);
        // 获取商品相册
        $goods = D('Home/Goods');
        $this->assign("goodsImgs", $goods->getGoodsImgs());
        // 获取指定id商品分类
        $catsm = D('integral');
        $catId = $goodsDetails['goodsCatId'];
        $cat = $catsm->getCat($catId);
        $this->assign('cat', $cat);
        $this->display('integral/goods_details');
    }

    /**
     * 核对商品信息
     */
    public function checkGoodsStock()
    {
        $this->isLogin();
        $m = D('Home/Integral');
        $goods = $m->checkGoodsStock();
        $this->ajaxReturn($goods);
    }

    /**
     * 买家兑换记录
     */
    public function exchangeRecord()
    {
        $this->isLogin();
        $obj["userId"] = session('WST_USER.userId');
        $obj['lastTime'] = (int) I('lastTime', 0);
        $obj['pay'] = (int) I('pay', 0);
        $this->assign('lastTime', $obj['lastTime']);
        $this->assign('pay', $obj['pay']);
        $m = D('Home/Integral');
        $pages = $m->exchangeRecord($obj);
        $this->assign('pages', $pages);
        // 获取个人信息
        $m = D('Home/Users');
        $user = $m->getUserById($obj);
        $this->assign('user', $user);
        $this->display('integral/record');
    }
}
;
?>