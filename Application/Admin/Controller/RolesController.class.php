<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 权限控制器
 */
class RolesController extends BaseController
{

    /**
     * 跳到新增/编辑页面
     */
    public function toEdit()
    {
        $this->isLogin();
        $m = D('Admin/Roles');
        $object = array();
        if (I('id', 0) > 0) {
            $this->checkPrivelege('jsgl_02');
            $object = $m->get();
        } else {
            $this->checkPrivelege('jsgl_01');
            $object = $m->getModel();
        }
        $this->assign('object', $object);
        $this->view->display('/roles/edit');
    }

    /**
     * 新增/修改操作
     */
    public function edit()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Roles');
        $rs = array();
        if (I('id', 0) > 0) {
            $this->checkAjaxPrivelege('jsgl_02');
            $rs = $m->edit();
        } else {
            $this->checkAjaxPrivelege('jsgl_01');
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
        $this->checkAjaxPrivelege('jsgl_03');
        $m = D('Admin/Roles');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('jsgl_00');
        $m = D('Admin/Roles');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/roles/list");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出 角色数据
     */
    public function  roleExp()
    {
        $this->isLogin();
        $this->checkPrivelege('jsgl_00');
        $xlsCell = array(
            array('num','序号'),
            array('roleName','角色名称'),
        );
        $sql = "select roleName from __PREFIX__roles order by roleId desc";
        $xlsModel = M('roles');
        $xlsData = $xlsModel->query($sql);
        foreach ($xlsData as $key => $value)
        {
            $xlsData[$key]['num'] = $key + 1;
        }
        $xlsName = 'role';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    /**
     * 列表查询
     */
    public function queryByList()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Roles');
        $list = $m->queryList();
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }
}
;
?>