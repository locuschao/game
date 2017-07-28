<?php
namespace Home\Controller;

/**
 * 物流管理控制器
 */
class ExpressController extends BaseController
{

    /**
     * 跳到新增/编辑页面
     */
    public function toEdit()
    {
        $this->isShopLogin();
        $m = D('Home/Express');
        $object = array();
        if ((int) I('id', 0) > 0) {
            $object = $m->getOne();
        } else {
            $object = $m->getModel();
        }
        $this->assign('object', $object);
        $this->assign('umark', "express");
        $this->view->display('Shops/express/edit');
    }

    /**
     * 新增/修改操作
     */
    public function edit()
    {
        $this->isShopAjaxLogin();
        $m = D('Home/Express');
        $rs = array();
        if ((int) I('id', 0) > 0) {
            $rs = $m->edit();
        } else {
            $rs = $m->insert();
        }
        $this->ajaxReturn($rs);
    }

    /**
     * 删除操作
     */
    public function del()
    {
        $this->isShopAjaxLogin();
        $m = D('Home/Express');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isShopLogin();
        $m = D('Home/Express');
        $list = $m->queryByList();
        $this->assign('List', $list);
        $this->assign('umark', "express");
        $this->display("Shops/express/list");
    }

    /*
     * 修改物流启用状态
     */
    public function changeExpressStatus()
    {
        $this->isShopAjaxLogin();
        $m = D('Home/Express');
        $rs = $m->changeExpressStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 获取商户配送物流
     */
    public function get()
    {
        $this->isShopAjaxLogin();
        $m = D('Home/Express');
        $rs = $m->get();
        if ($rs == false) {
            $rs = 0;
        }
        $this->ajaxReturn($rs);
    }
}
;
?>