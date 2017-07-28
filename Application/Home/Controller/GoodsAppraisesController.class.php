<?php
namespace Home\Controller;

/**
 * 商品评价控制器
 */
class GoodsAppraisesController extends BaseController
{

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isShopLogin();
        $USER = session("WST_USER");
        // 获取商家商品分类
        $m = D('Home/ShopsCats');
        $this->assign('shopCatsList', $m->queryByList($USER['shopId'], 0));
        $m = D('Home/Goods_appraises');
        $page = $m->queryByPage($USER['shopId']);
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign("shopCatId2", I('shopCatId2'));
        $this->assign("shopCatId1", I('shopCatId1'));
        $this->assign("goodsName", I('goodsName'));
        $this->assign("umark", "GoodsAppraises");
        $this->display("Shops/goodsappraises/list");
    }

    /**
     * 获取指定商品评价
     */
    public function getAppraise()
    {
        $this->isLogin();
        $m = D('Home/Goods_appraises');
        $appraise = $m->getAppraise();
        $this->assign('appraise', $appraise);
        
        $this->display("Shops/goodsappraises/appraise");
    }

    /**
     * ****************************************************************
     * 会员操作
     * ****************************************************************
     */
    /**
     * 订单评价
     */
    public function toAppraise()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Goods_appraises');
        $obj["userId"] = $USER['userId'];
        $obj["orderId"] = (int) I("orderId");
        $rs = $morders->getOrderAppraises($obj);
        $this->assign("orderInfo", $rs);
        $this->display("Users/orders/appraise");
    }

    /**
     * 添加评价
     */
    public function addGoodsAppraises()
    {
        $this->isUserAjaxLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Goods_appraises');
        $obj["userId"] = $USER['userId'];
        $obj["orderId"] = (int) I("orderId");
        $obj["goodsId"] = (int) I("goodsId");
        $rs = $morders->addGoodsAppraises($obj);
        $data = array();
        $data["status"] = $rs;
        $this->ajaxReturn($data);
    }

    /**
     * 获取评价
     */
    public function getAppraisesList()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        $morders = D('Home/Goods_appraises');
        $obj["userId"] = $USER['userId'];
        $this->assign("umark", "getAppraisesList");
        $appraiseList = $morders->getAppraisesList($obj);
        $this->assign("appraiseList", $appraiseList);
        $this->display("Users/orders/list_appraise_manage");
    }

    /**
     * 获取前台评价列表
     */
    public function getGoodsappraises()
    {
        $goods = D('Home/Goods_appraises');
        $goodsAppraises = $goods->getGoodsAppraises();
        $this->ajaxReturn($goodsAppraises);
    }
}
;
?>