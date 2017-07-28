<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 店铺控制器
 */
class CustomerServerController extends BaseController
{

    /**
     * 跳到新增/编辑页面
     */
    public function toEdit()
    {
        $this->isLogin();
        // 获取商品分类信息
        $m = D('Admin/CustomerServer');
        if (I('id', 0) > 0) {
            $this->checkPrivelege('setQQ_03');
            $object = $m->get();
        } else {
            $this->checkPrivelege('setQQ_01');
            $object = $m->getModel();
        }
        
        $this->assign('object', $object);
        $this->assign('src', I('src', 'index'));
        $this->view->display('CustormerServer/edit');
    }
    
    // 新增
    public function add()
    {
        $where['staffFlag'] = 1;
        $where['workStatus'] = 1;
        $this->list = M('staffs')->where($where)->select();
        $this->display('CustormerServer/add');
    }
    
    // 处理新增
    public function addHandle()
    {
        $this->isLogin();
        $this->checkPrivelege('setQQ_03');
        $staffId = I('staffId', 0);
        $isReception = I('isReception', 0);
        $isOnline = I('isOnline', 0);
        $qq = I('qq');
        if (! $staffId) {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
        $staffInfo = M('staffs')->where(array(
            'staffId' => $staffId
        ))->find();
        
        // 判断 QQ号是否已经存在
        $isExistsQQ = M('qq')->where(array(
            'qq' => $qq
        ))->find();
        if ($isExistsQQ) {
            $this->ajaxReturn(array(
                'status' => - 2
            ));
            exit();
        }
        
        $data = array();
        $data['cName'] = $staffInfo['staffName'];
        $data['qq'] = $qq;
        $data['createTime'] = date('Y-m-d H:i:s');
        $data['isOnline'] = $isOnline;
        $data['isReception'] = $isReception;
        $rs = M('qq')->add($data);
        if ($rs) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }

    public function ajaxEdit()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('setQQ_03');
        $m = D('Admin/CustomerServer');
        $rs = $m->ajaxEdit();
        $this->ajaxReturn($rs);
    }

    /**
     * 热门店铺
     */
    public function hotShop()
    {
        $m = D('Admin/Shops');
        $page = $m->getHotShop();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('params', $_POST);
        $this->assign('src', I('src', 'index'));
        $this->display();
    }
    
    // 设置热门店铺
    public function setHot()
    {
        $m = D('Admin/Shops');
        $rs = $m->setHot();
        $this->ajaxReturn($rs);
    }
    // 设置热门店铺
    public function setSort()
    {
        $m = D('Admin/CustomerServer');
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
            $this->checkAjaxPrivelege('setQQ_03');
            if (I('shopStatus', 0) <= - 1) {
                $rs = $m->reject();
            } else {
                $rs = $m->edit();
            }
        } else {
            $this->checkAjaxPrivelege('setQQ_01');
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
        $this->checkAjaxPrivelege('setQQ_02');
        $m = D('Admin/CustomerServer');
        $rs = $m->del();
        $this->ajaxReturn($rs);
    }

    /**
     * 查看
     */
    public function toView()
    {
        $this->isLogin();
        // $this->checkPrivelege('ppgl_00');
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
        $this->checkPrivelege('setQQ_00');
        $m = D('Admin/CustomerServer');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/CustormerServer/list");
    }
    
    // 客服订单 2016.7.5
    public function orders()
    {
        $this->isLogin();
        $this->checkPrivelege('kfdd_00');
        $m = D('Admin/CustomerServer');
        $page = $m->queryByPageOrders();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/CustormerServer/orders");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出客服订单数据
     */
    public function cusSerExp()
    {
        $this->isLogin();
        $this->checkPrivelege('kfdd_00');
        $xlsCell = array(
            array('num','序号'),
            array('kfQQ','序号'),
            array('orderNo','序号'),
        );
        $xlsModel = M('qq');
        $sql = "select o.orderNo,o.kfQQ from  __PREFIX__orders as o left join  __PREFIX__qq  as q on q.qq=o.kfQQ  where o.kfQQ>10  order by orderId desc";
        $xlsData = $xlsModel->query($sql);
        foreach ($xlsData as $key => $value)
        {
            $xlsData[$key]['num'] = $key + 1;
        }
        $xlsName = 'cusSer';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
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