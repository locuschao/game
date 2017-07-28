<?php
namespace Admin\Controller;

/**
 * 商品控制器
 */
class GroupController extends BaseController
{

    /**
     * 查看
     */
    public function toView()
    {
        $this->changeGroupStatus();
        $this->isLogin();
        $m = D('Admin/Group');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
        } else {
            die("商品不存在!");
        }
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->view->display('/group/view');
    }

    /**
     * 查看
     */
    public function toPenddingView()
    {
        $this->changeGroupStatus();
        $this->isLogin();
        $m = D('Admin/Group');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
            // 获取商品分类信息
            $m = D('Admin/GoodsCats');
            $this->assign('goodsCatsList', $m->queryByList());
            // 获取商家商品分类
            $m = D('Admin/ShopsCats');
            $this->assign('shopCatsList', $m->queryByList($object['shopId'], 0));
        } else {
            die("商品不存在!");
        }
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->view->display('/group/view_pendding');
    }

    /**
     * 分页查询已审核团购活动
     */
    public function index()
    {
        $this->changeGroupStatus();
        $this->isLogin();
        $this->checkPrivelege('splb_00');
        // 获取地区信息
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        // 获取商品分类信息
        $m = D('Admin/GoodsCats');
        $this->assign('goodsCatsList', $m->queryByList());
        $m = D('Admin/Group');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('goodsName', I('goodsName'));
        $this->assign('groupStatus', I('groupStatus', - 1));
        $this->assign('areaId1', I('areaId1', 0));
        $this->assign('areaId2', I('areaId2', 0));
        $this->assign('goodsCatId1', I('goodsCatId1', 0));
        $this->assign('goodsCatId2', I('goodsCatId2', 0));
        $this->assign('goodsCatId3', I('goodsCatId3', 0));
        $this->assign('isAdminBest', I('isAdminBest', - 1));
        $this->assign('isAdminRecom', I('isAdminRecom', - 1));
        $this->display("/group/list");
    }

    /**
     * 分页查询待审核团购活动
     */
    public function queryPenddingByPage()
    {
        $this->changeGroupStatus();
        $this->isLogin();
        $this->checkPrivelege('spsh_00');
        // 获取地区信息
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        // 获取商品分类信息
        $m = D('Admin/GoodsCats');
        $this->assign('goodsCatsList', $m->queryByList());
        $m = D('Admin/Group');
        $page = $m->queryPenddingByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $pager->setConfig('header', '个会员');
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('goodsName', I('goodsName'));
        $this->assign('groupStatus', I('groupStatus', - 1));
        $this->assign('areaId1', I('areaId1', 0));
        $this->assign('areaId2', I('areaId2', 0));
        $this->assign('goodsCatId1', I('goodsCatId1', 0));
        $this->assign('goodsCatId2', I('goodsCatId2', 0));
        $this->assign('goodsCatId3', I('goodsCatId3', 0));
        $this->display("/group/list_pendding");
    }

    /**
     * 修改待审核团购商品状态
     */
    public function changePenddingGoodsStatus()
    {
        $this->changeGroupStatus();
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('spsh_04');
        $m = D('Admin/Group');
        $rs = $m->changeGoodsStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 团购商品禁售
     */
    public function changeGoodsStatus()
    {
        $this->changeGroupStatus();
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Group');
        $rs = $m->changeGoodsStatus();
        $this->ajaxReturn($rs);
    }
}
;
?>