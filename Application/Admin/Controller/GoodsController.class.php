<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 商品控制器
 */
class GoodsController extends BaseController
{

    /**
     * 查看
     */
    public function toView()
    {
        $this->isLogin();
        $m = D('Admin/Goods');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
        } else {
            die("商品不存在!");
        }
        $this->view->display('/goods/view');
    }

    /**
     * 查看
     */
    public function toPenddingView()
    {
        $this->isLogin();
        $m = D('Admin/Goods');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
            // 获取商品分类信息
            $m = D('Admin/GoodsCats');
            $this->assign('goodsCatsList', $m->queryByList());
            // 获取商家商品分类
            $m = D('Admin/ShopsCats');
            $this->assign('shopCatsList', $m->queryByList($object['shopId'], 0));
        } else {
            die("商品不存在!");
        }
        $this->view->display('/goods/view_pendding');
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $m = D('Admin/Goods');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('goodsSn', I('goodsSn'));
        $this->assign('starDay', I('starDay'));
        $this->assign('scope', I('scope'));
        $this->assign('endDay', I('endDay'));
        $this->assign('gameName', I('gameName'));
        $this->assign('vName', I('vName'));
        $this->assign('goodsName', I('goodsName'));
        $this->assign('isAdminRecom', I('isAdminRecom', - 1));
        /**
         * @author peng	
         * @date 2017-01
         * @descreption 平台商品
         */
        $is_pt=I('is_pt');
        if($is_pt){
            $this->assign('is_pingtai_goods', $is_pt);
            
            $this->assign('search_key', [
                'isSale'=>isset($_GET['isSale']) && $_GET['isSale']!=''?$_GET['isSale']:-1,
                'member_rank'=>I('member_rank')
            ]);
        }
        
         
        $this->display("/goods/list");
    }
    /*
     * 魏永就
     * 导出 excel表格数据
     */
    public function dataExp()
    {
        $this->isLogin();
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        $type = I('post.type');
        if($start == ''||$end == ''|| $start >= $end || $type =='')
        {
            $this->assign('msg','<font color=red>信息未选择完整或终始时间相同</font>');
            $this->index();
            die;
        }
        $xlsName  = "Users";
        $xlsCell  = array(
            array('goodsSn','商品编号'),
            array('goodsName','商品名称'),
            array('shopId','商家ID'),
            array('gameName','商家名称'),
            array('versions','游戏类型'),
            array('versions','版本'),
            array('scopeType','商品类型'),
            array('shopPrice','充值面额'),
            array('lowPrice','最低售价'),
            array('zhekou','最低折扣'),
            array('upTime','上架时间'),
            array('isHot','是否热门'),
            array('isSale','是否上架'),
            array('isAdminRecom','是否推荐'),
            array('isMiao','是否秒充'),
            array('createTime','创建时间'),

        );
        $xlsModel = D('Admin/Goods');         //获取用户表对象
      
//        $xlsData = $xlsModel->query("select g.isMiao,g.goodsThums,g.goodsImg,g.goodsType,g.isHot,g.isSale,
//                                g.upTime,g.goodsId,g.saleCount,g.goodsSn,g.goodsName,s.shopId,
//                                g.shopPrice,
//                                g.isAdminRecom,g.createTime,s.shopName,ga.gameName
//                                from oto_goods as g left join oto_shops as s
//                                on g.shopId=s.shopId  left join  oto_game as ga on ga.id=g.gameId
//                                left join oto_goods_versions as gv on gv.goodsId=g.goodsId
//                                left join oto_versions as vv on gv.versionsId=vv.id
//                                where g.goodsFlag=1 and g.goodsStatus=1 order by g.goodsId desc");
        $xlsData = $xlsModel->queryByPage('dataExp',I('post.type'),$start,$end);
        if(!$xlsData['root'])
        {
            $this->assign('msg','<font color=red>没数据符合您选择的时间</font>');
            $this->index();
            die;
        }
        $xlsData = $xlsData['root'];
        foreach($xlsData as$key => $value)
        {
            if($value['isHot'] == 1)
                $xlsData[$key]['isHot'] = '是';
            else
                $xlsData[$key]['isHot'] = '否';
            if($value['isSale'] == 1)
                $xlsData[$key]['isSale'] = '是';
            else
                $xlsData[$key]['isSale'] = '否';
            if($value['isAdminRecom'] == 1)
                $xlsData[$key]['isAdminRecom'] = '是';
            else
                $xlsData[$key]['isAdminRecom'] = '否';
            if($value['isMiao'] == 1)
                $xlsData[$key]['isMiao'] = '是';
            else
                $xlsData[$key]['isMiao'] = '否';
        }

        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);      //导出数据
    }

    /**
     * 分页查询
     */
    public function queryPenddingByPage()
    {
        $this->isLogin();
        $this->checkPrivelege('spsh_00');
        // 获取地区信息
        
        $m = D('Admin/Goods');
        $page = $m->queryPenddingByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $pager->setConfig('header', '个会员');
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('goodsSn', I('goodsSn'));
        $this->assign('starDay', I('starDay'));
        $this->assign('endDay', I('endDay'));
        $this->assign('gameName', I('gameName'));
        $this->assign('vName', I('vName'));
        $this->assign('goodsName', I('goodsName'));
        $this->assign('isAdminRecom', I('isAdminRecom', - 1));
        $this->display("/goods/list_pendding");
    }

    /**
     * 列表查询
     */
    public function queryByList()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Goods');
        $list = $m->queryByList();
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }

    /**
     * 列表查询[获取启用的区域信息]
     */
    public function queryShowByList()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Goods');
        $list = $m->queryShowByList();
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }

    /**
     * 修改待审核商品状态
     */
    public function changePenddingGoodsStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('spsh_04');
        $m = D('Admin/Goods');
        $rs = $m->changeBethGoodsStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 修改商品状态
     */
    public function changeGoodsStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeGoodsStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 修改商品上下架状态
     */
    public function changeGoodsSaleStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeGoodsSaleStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 修改商品热门状态
     */
    public function changeGoodsHotStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeGoodsHotStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 获取待审核的商品数量
     */
    public function queryPenddingGoodsNum()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Goods');
        $rs = $m->queryPenddingGoodsNum();
        $this->ajaxReturn($rs);
    }

    /**
     * 批量修改上下架
     */
    public function changeUpDownStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeUpDownStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 批量修改为推荐
     */
    public function changeAdminRecomStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeAdminRecomStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 单品修改热门
     */
    public function changeHotStatus()
    {
        $this->isAjaxLogin();
        // $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeHotStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 单品修改为推荐
     */
    public function changeRecomStatus()
    {
        $this->isAjaxLogin();
        // $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeRecomStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 单品修改为秒充
     */
    public function changeMiaoStatus()
    {
        $this->isAjaxLogin();
        // $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeMiaoStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 批量通过审核
     */
    public function changeBethGoodsStatus()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('splb_04');
        $m = D('Admin/Goods');
        $rs = $m->changeBethGoodsStatus();
        $this->ajaxReturn($rs);
    }
}
;
?>