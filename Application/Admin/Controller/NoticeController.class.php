<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 商品控制器
 */
class NoticeController extends BaseController
{

    /**
     * 删除操作
     */
    public function del()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('gglb_03');
        $m = D('Admin/Notice');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 跳到新增/编辑页面
     */
    public function toEdit()
    {
        $this->isLogin();
        // 获取地区信息
        $m = D('Admin/Notice');
        $object = array();
        if (I('id', 0) > 0) {
            $this->checkPrivelege('gglb_02');
            $object = $m->get();
            $this->assign('object', $object);
        }
        $this->view->display('/notice/edit');
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('gglb_00');
        $m = D('Admin/Notice');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/notice/list");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出公告管理数据
     */
    public function noticeExp()
    {
        $this->isLogin();
        $this->checkPrivelege('gglb_00');
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == '' || $end == '' || $start >= $end)
        {
            $this->assign('msg','<font color=red>时间未选择或终始相同</font>');
            $this->index();
            die;
        }
        $xlsCell = array(
            array('id','编号'),
            array('title','标题'),
            array('createTime','日期'),
        );
        $xlsModel = M('notice');
        $sql = " select id,title,createTime from __PREFIX__notice where  UNIX_TIMESTAMP(createTime) >= $start and UNIX_TIMESTAMP(createTime) <= $end order by id desc ";
        $xlsData = $xlsModel->query($sql);
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->index();
            die;
        }
        $xlsName = 'notice';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }
    //
    public function edit()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('gglb_02');
        $m = D('Admin/Notice');
        $rs = $m->edit();
        $this->ajaxReturn($rs);
    }
}
;
?>