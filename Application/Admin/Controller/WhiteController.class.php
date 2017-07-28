<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 商品控制器
 */
class WhiteController extends BaseController
{

    /**
     * 删除操作
     */
    public function del()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('bmd_03');
        $m = D('Admin/White');
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
        $m = D('Admin/White');
        $object = array();
        if (I('id', 0) > 0) {
            $this->checkPrivelege('bmd_02');
            $object = $m->get();
        }
        $gameList = $m->gameList();
        $this->assign('gameList', $gameList);
        
        $versionsList = $m->versionsList();
        $this->assign('versionsList', $versionsList);
        $this->assign('object', $object);
        $this->view->display('/White/edit');
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('bmd_00');
        $m = D('Admin/White');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('shopId', I('shopId'));
        $this->assign('gameName', I('gameName'));
        $this->display("/White/index");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出首充白名单数据
     */
    public function  whiteListExp()
    {
        $this->isLogin();
        $this->checkPrivelege('bmd_00');
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == '' || $end ==''|| $start >= $end )
        {
            $this->assign('msg','<font color=red>时间未选择或终始时间相同');
            $this->index();
            die;
        }
        $xlsCell = array(
            array('id','编号'),
            array('account','游戏账号'),
            array('gameName','游戏名称'),
            array('vName','版本'),
            array('createTime','创建时间'),
            array('shopName','商铺'),
            array('orderId','订单号'),
        );
        $xlsModel = M('white_list');
        $sql = "select w.id,w.account,w.createTime,w.orderId,s.shopName,ga.gameName,v.vName from __PREFIX__white_list as w left join 
          __PREFIX__shops as s on s.shopId=w.shopId  left join __PREFIX__game as ga on ga.id=w.gid left join __PREFIX__versions as v on v.id=w.vid 
          where w.isDel=0 and  UNIX_TIMESTAMP(w.createTime) >= $start and UNIX_TIMESTAMP(w.createTime) <= $end order by w.id DESC";
        $xlsData = $xlsModel->query($sql);
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->index();
            die;
        }
        $xlsName = 'whiteList';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }
    // 添加白名单
    public function whiteEdit()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('bmd_02');
        $m = D('Admin/White');
        $rs = $m->whiteEdit();
        $this->ajaxReturn($rs);
    }
}
;
?>