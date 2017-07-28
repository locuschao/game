<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 登录日志控制器
 */
class LogLoginsController extends BaseController
{

    /**
     * 查看
     */
    public function toView()
    {
        $this->isLogin();
        $this->checkPrivelege('dlrz_00');
        $m = D('Admin/LogLogins');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
        }
        $this->view->display('/loglogins/view');
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('dlrz_00');
        $m = D('Admin/LogLogins');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('startDate', I('startDate', date('Y-m-d', strtotime('-30 days'))));
        $this->assign('endDate', I('endDate', date('Y-m-d')));
        $this->display("/loglogins/list");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出登录日记
     * 
     */
    public function logLoginsExp()
    {
        $this->isLogin();
        $this->checkPrivelege('dlrz_00');
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == '' || $end == '' || $start >= $end)
        {
            $this->assign('msg','<font color=red>时间未选择或终始时间相同</font>');
            $this->index();
            die;
        }
        $xlsCell = array(
            array('num','序号'),
            array('loginName','账号'),
            array('staffName','姓名'),
            array('loginTime','登录时间'),
            array('loginIp','登录IP'),
        );
        $xlsModel = M('log_logins');
        $sql = "select loginName,staffName,loginTime,loginIp from __PREFIX__log_staff_logins l,__PREFIX__staffs s where l.staffId=s.staffId and UNIX_TIMESTAMP(loginTime) >= $start
                and UNIX_TIMESTAMP(loginTime) <= $end order by loginId desc";
        $xlsData = $xlsModel->query($sql);
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->index();
            die;
        }
        foreach ($xlsData as $key => $value)
        {
            $xlsData[$key]['num'] = $key + 1;
        }
        $xlsName = 'logLoginList';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
        
    }
}
;
?>