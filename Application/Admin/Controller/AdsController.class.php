<?php
namespace Admin\Controller;

use Lib\Exp\DataExp;

class AdsController extends BaseController
{

    /**
     * 跳到新增/编辑页面
     */
    public function toEdit()
    {
        $this->isLogin();
        // 获取地区信息
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        // 获取商品分类
        $m = D('Admin/GoodsCats');
        $this->assign('goodsCatList', $m->queryByList(0));
        $m = D('Admin/Ads');
        $object = array();
        if (I('id', 0) > 0) {
            $this->checkPrivelege('gggl_02');
            $object = $m->get();
        } else {
            $this->checkPrivelege('gggl_01');
            $object = $m->getModel();
            $object['adStartDate'] = date('Y-m-d');
            $object['adEndDate'] = date('Y-m-d');
        }
        $this->assign('object', $object);
        $this->view->display('/ads/edit');
    }

    /**
     * 新增/修改操作
     */
    public function edit()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Ads');
        $rs = array();
        if (I('id', 0) > 0) {
            $this->checkAjaxPrivelege('gggl_02');
            $rs = $m->edit();
        } else {
            $this->checkAjaxPrivelege('gggl_01');
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
        $this->checkAjaxPrivelege('gggl_03');
        $m = D('Admin/Ads');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('gggl_00');
        self::WSTAssigns();
        // 获取商品分类
        $m = D('Admin/GoodsCats');
        $this->assign('goodsCatList', $m->queryByList(0));
        $m = D('Admin/Ads');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $pager->setConfig('header', '个会员');
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/ads/list");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出广告管理数据
     */
    public function adsExp()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('gggl_00');
        $type = I('post.type');
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == ''|| $end == ''||$start >= $end || $type == '')
        {
            $this->assign('msg','<font color=red>信息未选择完整或终始时间相同</font>');
            $this->index();
            die;
        }
        if($type == '0')
        {
            $where = " where UNIX_TIMESTAMP(adStartDate) >= $start and UNIX_TIMESTAMP(adStartDate) <= $end";
        }else{
            $where =  " where  UNIX_TIMESTAMP(adEndDate) >= $start and UNIX_TIMESTAMP(adEndDate) <= $end";
        }

        $xlsCell = array(
            array('num','序号'),
            array('adName','广告标题'),
            array('adPositionId','广告位置'),
            array('adURL','广告网址'),
            array('adStartDate','广告开始时间'),
            array('adEndDate','广告结束时间'),
            array('adClickNum','点击数'),
        );
        $xlsModel = M('ads');
        $sql = "select adName,adPositionId,adURL,adStartDate,adEndDate,adClickNum
	 	        from __PREFIX__ads $where
	 	         ";
        $xlsName = 'ads';
        $xlsData = $xlsModel->query($sql);
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->index();
            die;
        }
        foreach ($xlsData as $key => $value) {
            $xlsData[$key]['num'] = $key + 1;
            switch ($value['adPositionId'])
            {
                case -1:
                    $xlsData[$key]['adPositionId'] = '首页主广告';
                    break;
                case -2:
                    $xlsData[$key]['adPositionId'] = '品牌汇广告';
                    break;
                case  -3:
                    $xlsData[$key]['adPositionId'] = '店铺街广告';
            }
        }
        $dataEpx = new DataExp();
        $dataEpx->exportExcel($xlsName,$xlsCell,$xlsData);
    }
    /**
     * 列表查询
     */
    public function queryByList()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Ads');
        $list = $m->queryByList();
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }
}
;
?>