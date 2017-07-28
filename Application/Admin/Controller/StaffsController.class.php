<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 职员控制器
 */
class StaffsController extends BaseController
{

    /**
     * 跳到新增/编辑页面
     */
    public function toEdit()
    {
        $this->isLogin();
        $m = D('Admin/Staffs');
        $r = D('Admin/Roles');
        $object = array();
        if (I('id', 0) > 0) {
            $this->checkPrivelege('zylb_02');
            $object = $m->get();
        } else {
            $this->checkPrivelege('zylb_03');
            $object = $m->getModel();
            $object['staffStatus'] = 1;
        }
        $this->assign('roleList', $r->queryByList());
        $this->assign('object', $object);
        $this->view->display('/staffs/edit');
    }

    /**
     * 新增/修改操作
     */
    public function edit()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Staffs');
        $rs = array();
        if (I('id', 0) > 0) {
            $this->checkAjaxPrivelege('zylb_02');
            $rs = $m->edit();
        } else {
            $this->checkAjaxPrivelege('zylb_01');
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
        $this->checkAjaxPrivelege('zylb_03');
        $m = D('Admin/Staffs');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 查看
     */
    public function toView()
    {
        $this->isLogin();
        $this->checkPrivelege('zylb_00');
        $m = D('Admin/Staffs');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
        }
        $this->view->display('/staffs/view');
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('zylb_00');
        $m = D('Admin/Staffs');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('loginUserId', 1);
        $this->assign('staffName', I('staffName'));
        $this->assign('loginName', I('loginName'));
        $this->display("/staffs/list");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出职员管理数据
     */
    public function staffsExp()
    {
        $this->isLogin();
        $this->checkPrivelege('zylb_00');
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == ''|| $end == ''|| $start >= $end)
        {
            $this->assign('msg','<font color=red>时间未选择或终始时间相同');
            $this->index();
            die;
        }
        $xlsCell = array(
            array('num','序号'),
            array('loginName','账号'),
            array('staffqq','QQ'),
            array('staffName','姓名'),
            array('roleName','角色'),
            array('staffNo','编号'),
            array('workStatus','工作状态'),
            array('lastTime','最后登录时间'),
            array('lastIP','最后登录IP'),
            array('staffStatus','状态'),
        );
        $xlsModel = M('staffs');
        $sql = " select s.loginName,s.staffName,s.staffqq,s.staffNo,s.workStatus,s.lastTime,s.lastIP,s.staffStatus,r.roleName from __PREFIX__staffs s left join  
              __PREFIX__roles r on s.staffRoleId=r.roleId where staffFlag=1 and  UNIX_TIMESTAMP(lastTime) >= $start and UNIX_TIMESTAMP(lastTime) <= $end  order  by s.staffId desc";
        $xlsData = $xlsModel->query($sql);
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->index();
            die;
        }
        foreach ($xlsData as $key => $value)
        {
            $xlsData[$key]['staffNo'] =$value['staffNo'].' ';
            $xlsData[$key]['num'] = $key + 1;
            if($value['workStatus'] == 1)
            {
                $xlsData[$key]['workStatus'] = '在职';
            }else if($value['workStatus'] == 0){
                $xlsData[$key]['workStatus'] = '离职';
            }
            if($value['staffStatus'] == 0)
            {
                $xlsData[$key]['staffStatus'] = '停用';
            }else{
                $xlsData[$key]['staffStatus'] = '启用';
            }
        }
//        de($xlsData);
        $xlsName = 'staffs';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    /**
     * 查询用户账号
     */
    public function checkLoginKey()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Staffs');
        $rs = $m->checkLoginKey();
        $this->ajaxReturn($rs);
    }

    /**
     * 显示职员账号是否启用/停用
     */
    public function editStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('zylb_03');
        $m = D('Admin/Staffs');
        $rs = $m->editStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 跳到修改职员密码
     */
    public function toEditPass()
    {
        $this->isLogin();
        $this->display("/edit_pass");
    }

    /**
     * 修改职员密码
     */
    public function editPass()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Staffs');
        $rs = $m->editPass(session('WST_STAFF.staffId'));
        $this->ajaxReturn($rs);
    }
}
;
?>