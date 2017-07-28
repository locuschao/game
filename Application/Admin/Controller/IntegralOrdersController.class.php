<?php
namespace Admin\Controller;

/**
 * 订单控制器
 */
class IntegralOrdersController extends BaseController
{

    /**
     * 分页查询积分商城订单
     */
    public function index()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('ddlb_00');
        $m = D('Admin/IntegralOrders');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        // 强制积分值为int
        foreach ($page['root'] as $k => $v) {
            if ($v['payType'] == '4') {
                $page['root'][$k]['totalMoney'] = (int) $v['totalMoney'];
            }
        }
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('orderNo', I('orderNo'));
        $this->assign('areaId1', I('areaId1', 0));
        $this->assign('areaId2', I('areaId2', 0));
        $this->assign('areaId3', I('areaId3', 0));
        $this->assign('orderStatus', I('orderStatus', - 9999));
        $this->display("/integralgoods/orders/orders_list");
    }

    /**
     * 查看积分商城订单详情
     */
    public function toView()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $m = D('Admin/IntegralOrders');
        $object = $m->getDetail();
        // 强制积分值为int
        foreach ($object['goodslist'] as $k => $v) {
            $object['goodslist'][$k]['goodsPrice'] = (int) $v['goodsPrice'];
        }
        $object['order']['totalMoney'] = (int) $object['order']['totalMoney'];
        $this->assign('object', $object);
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->display("/integralgoods/orders/view");
    }

    /**
     * 确认积分商城订单
     */
    public function changeOrderStatus()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $m = D('Admin/IntegralOrders');
        $rs['status'] = $m->changeOrderStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 跳转到编辑快递信息页
     */
    public function editExpress()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $orderId = (int) I('id');
        $this->assign('orderId', $orderId);
        // 获取物流信息
        $expressModel = D('Admin/Express');
        $express = $expressModel->getAll();
        $this->assign('express', $express);
        // 获取订单状态,保存后跳转到相应状态
        $ordersModel = M('Orders');
        $orderStatus = $ordersModel->where('orderId =' . $orderId)->getField('orderStatus');
        $this->assign('orderStatus', $orderStatus);
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->display('/integralgoods/orders/orders_express');
    }

    /**
     * 增加快递信息
     */
    public function toExpress()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $ordersModel = D('Admin/IntegralOrders');
        $rs['status'] = $ordersModel->toExpress();
        $this->ajaxReturn($rs);
    }

    /**
     * 跟踪快递信息
     */
    public function follow()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $m = D('Admin/IntegralOrders');
        $object = $m->follow();
        $this->assign('object', $object);
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->display('/integralgoods/orders/orders_follow');
    }
}
;
?>