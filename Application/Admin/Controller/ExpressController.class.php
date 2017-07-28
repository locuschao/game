<?php
namespace Admin\Controller;

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
        $this->isLogin();
        $m = D('Admin/Express');
        $object = array();
        if ((int) I('id', 0) > 0) {
            $object = $m->get();
        } else {
            $object = $m->getModel();
        }
        $this->assign('object', $object);
        $this->assign('umark', "express");
        $this->view->display('/express/edit');
    }

    /**
     * 新增/修改操作
     */
    public function edit()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Express');
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
        $this->isAjaxLogin();
        $m = D('Admin/Express');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $m = D('Express');
        $list = $m->queryByList();
        $pager = new \Think\Page($list['total'], $list['pageSize']);
        $pager->setConfig('header', '个会员');
        $list['pager'] = $pager->show();
        $this->assign('List', $list);
        $this->display("/express/list");
    }

    /**
     * 获取商户配送物流
     */
    public function getModel()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Express');
        $rs = $m->getAll();
        $this->ajaxReturn($rs);
    }

    /**
     * 修改物流名称
     */
    public function editName()
    {
        $this->isAjaxLogin();
        $rd = array(
            'status' => - 1
        );
        $id = (int) I("id", 0);
        $data = array();
        $expressCompany = I("expressCompany");
        if ($expressCompany) {
            $m = M('Express');
            $rs = $m->where("expressFlag=1 and shopId=0 and id=" . (int) I('id'))->setField('expressCompany', $expressCompany);
            if (false !== $rs) {
                $rd['status'] = 1;
            }
        }
        $this->ajaxReturn($rd);
    }

    /**
     * 启用/禁用物流
     */
    public function editiIsShow()
    {
        $this->isAjaxLogin();
        $rd = array(
            'status' => - 1
        );
        $m = D('Express');
        $rs = $m->editiIsShow();
        if ($rs != false) {
            $rd['status'] = 1;
        }
        $this->ajaxReturn($rd);
    }

    /**
     * 显示/隐藏物流
     */
    public function editiIsEnable()
    {
        $this->isAjaxLogin();
        $rd = array(
            'status' => - 1
        );
        $m = D('Express');
        $rs = $m->editiIsEnable();
        if ($rs != false) {
            $rd['status'] = 1;
        }
        $this->ajaxReturn($rd);
    }
}
;
?>