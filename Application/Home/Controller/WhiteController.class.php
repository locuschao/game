<?php
namespace Home\Controller;

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
        // $this->checkAjaxPrivelege('gggl_03');
        $m = D('Home/White');
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
        $m = D('Home/White');
        $object = array();
        if (I('id', 0) > 0) {
            // $this->checkPrivelege('gggl_02');
            $object = $m->get();
        }
        $gameList = $m->gameList();
        $shopId = session('WST_USER.shopId');
        $this->assign('shopId', $shopId);
        $this->assign('gameList', $gameList);
        $versionsList = $m->versionsList();
        $this->assign('versionsList', $versionsList);
        $this->assign('object', $object);
        $this->view->display('shops/White/edit');
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $m = D('Home/White');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('shopId', I('shopId'));
        $this->assign('gameName', I('gameName'));
        $this->display("shops/White/index");
    }
    
    // 添加白名单
    public function whiteEdit()
    {
        $m = D('Home/White');
        $rs = $m->whiteEdit();
        $this->ajaxReturn($rs);
    }
}
;
?>