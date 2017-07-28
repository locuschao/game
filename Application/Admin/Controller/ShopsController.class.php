<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 店铺控制器
 */
class ShopsController extends BaseController
{

    /**
     * 跳到新增/编辑页面
     */
    public function toEdit()
    {
        $this->isLogin();
        // 获取商品分类信息
        $m = D('Admin/GoodsCats');
        $this->assign('goodsCatsList', $m->queryByList());
        // 获取地区信息
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        // 获取银行列表
        $m = D('Admin/Banks');
        $this->assign('bankList', $m->queryByList(0));
        // 获取商品信息
        $m = D('Admin/Shops');
        $object = array();
        if (I('id', 0) > 0) {
            $this->checkPrivelege('dplb_02');
            $object = $m->get();
        } else {
            $this->checkPrivelege('dplb_01');
            $object = $m->getModel();
        }
        
        $this->assign('object', $object);
        $this->assign('src', I('src', 'index'));
        $this->view->display('/shops/edit');
    }

    /**
     * 热门店铺
     */
    public function hotShop()
    {
        $this->isLogin();
        $this->checkPrivelege('ymdp_00');
        $m = D('Admin/Shops');
        $page = $m->getHotShop();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('params', $_POST);
        $this->assign('src', I('src', 'index'));
        $this->display('shops/hotShop');
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出热门店铺数据设置数据
     */
    public function hotShopExp()
    {
        $this->isLogin();
        $this->checkPrivelege('ymdp_00');
        $xlsCell = array(
            array('shopId','商铺ID'),
            array('shopName','商铺名称'),
            array('sort','序号'),
            array('isAdminRecom','是否是热门店铺'),
        );
        $sql = "select shopId,shopName,sort,isAdminRecom from __PREFIX__shops where shopFlag=1 order by sort ASC";
        $xlsModel = M('shops');
        $xlsData = $xlsModel->query($sql);
        foreach ($xlsData as $key => $value)
        {
            if($value['isAdminRecom'] ==1)
                $xlsData[$key]['isAdminRecom'] = '是';
            else
                $xlsData[$key]['isAdminRecom'] = '否';
        }
        $xlsName = 'hotShop';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }
    
    // 设置热门店铺
    public function setHot()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('ymdp_01');
        $m = D('Admin/Shops');
        $rs = $m->setHot();
        $this->ajaxReturn($rs);
    }
    // 设置热门店铺
    public function setSort()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('ymdp_01');
        $m = D('Admin/Shops');
        $rs = $m->setSort();
        $this->ajaxReturn($rs);
    }

    /**
     * 新增/修改操作
     */
    public function edit()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Shops');
        $rs = array();
        if (I('id', 0) > 0) {
            $this->checkAjaxPrivelege('dplb_02');
            if (I('shopStatus', 0) <= - 1) {
                $rs = $m->reject();
            } else {
                $rs = $m->edit();
            }
        } else {
            $this->checkAjaxPrivelege('dplb_01');
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
        $this->checkAjaxPrivelege('dplb_03');
        $m = D('Admin/Shops');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 查看
     */
    public function toView()
    {
        $this->isLogin();
        $this->checkPrivelege('ppgl_00');
        $m = D('Admin/Shops');
        if (I('id') > 0) {
            $object = $m->get();
            $this->assign('object', $object);
        }
        $this->view->display('/shops/view');
    }

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('dplb_00');
        // 获取地区信息
        
        $m = D('Admin/Shops');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('shopSn', I('shopSn'));
        $this->assign('areaId1', I('areaId1', 0));
        $this->assign('areaId2', I('areaId2', 0));
        $this->display("/shops/list");
    }
    /*
     * 编写者  魏永就
     * 导出excel形式的数据
     */
    public function dataExp()
    {
        $this->isLogin();
        $this->checkPrivelege('dplb_00');
        
        $xlsName  = "shops";
        $xlsCell  = array(
            array('shopid','店铺id'),
            array('shopname','店铺名称'),
            array('username','店主'),
            array('serviceTime','营业时间'),
            array('scopeName','经营范围'),
        );
        $xlsModel = M('Users');         //获取用户表对象

        $xlsData = $xlsModel->query("SELECT shopid,shopname,serviceStartTime,serviceEndTime,scope,u.username FROM  oto_shops s,oto_users u  
         where  s.userId=u.userId and shopStatus=1 and shopFlag=1 order by shopid DESC");
        foreach ($xlsData as $key => $value)
        {
            $temp = explode(',', $value['scope']);
            $str = '';
            foreach ($temp as $vv) {
                if ($vv == 1) {
                    $str .= '首充号' . ',';
                }
                if ($vv == 2) {
                    $str .= '首充号代充' . ',';
                }
                if ($vv == 3) {
                    $str .= '会员首充号' . ',';
                }
                if ($vv == 4) {
                    $str .= '会员首充号代充' . ',';
                }
            }
            $str = rtrim($str, ',');
            $xlsData[$key]['scopeName'] = $str;
            $xlsData[$key]['serviceTime'] = $xlsData[$key]['serviceStartTime'].'-'.$xlsData[$key]['serviceEndTime'];
        }
        foreach ($xlsData as $key => $value)
        {
            if($value['userstatus'] == 1)
                $xlsData[$key]['userstatus'] = '启用';
            else
                $xlsData[$key]['userstatus'] = '停用';
        }
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);      //导出数据
    }
    /**
     * 分页查询[待审核列表]
     */
    public function queryPeddingByPage()
    {
        $this->isLogin();
        $this->checkPrivelege('dpsh_00');
        // 获取地区信息
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        $m = D('Admin/Shops');
        $page = $m->queryPeddingByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $pager->setConfig('header', '');
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('shopSn', I('shopSn'));
        $this->assign('shopStatus', I('shopStatus', - 999));
        $this->assign('areaId1', I('areaId1', 0));
        $this->assign('areaId2', I('areaId2', 0));
        $this->display("/shops/list_pendding");
    }

    /**
     * 列表查询
     */
    public function queryByList()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Shops');
        $list = $m->queryList();
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }

    /**
     * 获取待审核的店铺数量
     */
    public function queryPenddingGoodsNum()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Shops');
        $rs = $m->queryPenddingShopsNum();
        $this->ajaxReturn($rs);
    }
}
;
?>