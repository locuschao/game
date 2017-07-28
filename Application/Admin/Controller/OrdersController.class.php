<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 订单控制器
 */
class OrdersController extends BaseController
{

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $m = D('Admin/Orders');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('orderNo', I('orderNo'));
        $versionArr = M('versions')->field('id,vName')->select();
        $this->assign('versions',$versionArr);
        $this->assign('todayStart',date('Y-m-d 00:00:00',time()));
        $this->assign('todayEnd',date('Y-m-d 23:59:59'),time());
        $this->display("/orders/list");
    }
    /*二次开发
     * 编写者 魏永就
     * 导出数据
     */
    public function dataExp()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        
        if($start == '' || $end == ''||$start >= $end)
        {
            $this->assign('Msg','<font color=red>信息不完整或终始时间相同</font>');
            $this->index();
        }else {
            
            $xlsModel = D('Admin/Orders');
            $xlsData = $xlsModel->queryByPage('dataExp', $start, $end);
            
            if (!$xlsData['root'])
            {
                $this->assign('Msg','<font color=red>没有数据符合您选择的时间</font>');
                $this->index();
                die;
            }
            $xlsName = 'Orders';
            $xlsCell = array(
                array('num','序号'),
                array('orderNo','订单'),
                array('shopName','店铺'),
                array('scope','类型'),
                array('gameName','游戏'),
                array('vName','版本'),
                array('needPay','需要付款金额'),
                array('payType','付款类型'),
                array('marketPrice','原价'),
                array('goodsNums','数量'),
                array('amount','总价(原价的)'),
                array('createTime','下单时间'),
                array('fahuoTime','发货时间'),
                array('fahuoType','发货方式'),
                array('status','交易状态'),
            );
            $xlsData = $xlsData['root'];
            
            foreach ($xlsData as $key => $value)
            {
                $xlsData[$key]['num'] = $key + 1;
                switch ($value['orderStatus'])
                {
                    case 1:
                        $xlsData[$key]['status'] = '等待发货';
                        break;
                    case -2:
                        $xlsData[$key]['status'] = '待付款';
                        break;
                    case 2:
                        $xlsData[$key]['status'] = '已发货';
                        break;
                    case -3:
                        $xlsData[$key]['status'] = '退款中';
                        break;
                    case -4:
                        $xlsData[$key]['status'] = '订单取消';
                        break;
                    case -5:
                        $xlsData[$key]['status'] = '退款成功';
                        break;
                    case -6:
                        $xlsData[$key]['status'] = '拒绝退款';
                        break;
                    case 3:
                        $xlsData[$key]['status'] = '已经完成';
                        break;
                }
                if($value['orderStatus'] > 0 )
                {
                   switch ($value['payType'])
                   {
                       case 1:
                           $xlsData[$key]['payType'] = '支付宝支付';
                           break;
                       case 2:
                           $xlsData[$key]['payType'] = '微信支付';
                           break;
                       default:
                           $xlsData[$key]['payType'] = '余额支付';
                           break;
                   }
                }else{
                    $xlsData[$key]['payType'] = '待付款';
                }
                /**
                 * @author peng
                 * @date 2017-02
                 * @descreption 发货方式
                 */
                 if($value['fahuoTime']>0) {
                    if($value['fahuoType']==0){
                        $xlsData[$key]['fahuoType'] = '手动';
                    }else if(in_array($value['fahuoType'],[1,2])){
                        $xlsData[$key]['fahuoType'] = '自动';
                    }
                       
                 }else{
                    $xlsData[$key]['fahuoType'] = '';
                 }
            }
            $dataExp = new DataExp();
            ob_clean();
            $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
        }
    }

    /**
     * 退款分页查询
     */
    public function queryRefundByPage()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('tk_00');
        // 获取地区信息
        $m = D('Admin/Areas');
        $this->assign('areaList', $m->queryShowByList(0));
        $m = D('Admin/Orders');
        $page = $m->queryRefundByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $pager->setConfig('header', '件商品');
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('orderNo', I('orderNo'));
        $this->assign('isRefund', I('isRefund', - 1));
        $this->assign('areaId1', I('areaId1', 0));
        $this->assign('areaId2', I('areaId2', 0));
        $this->assign('areaId3', I('areaId3', 0));
        $this->display("/orders/list_refund");
    }

    /**
     * 查看订单详情
     */
    public function toView()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $m = D('Admin/Orders');
        if (I('id') > 0) {
            $object = $m->getDetail();
            $this->assign('object', $object);
        }
        // 判断是不是客服
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->display("/orders/view");
    }

    /**
     * 查看订单详情
     */
    public function toRefundView()
    {
        $this->isLogin();
        $this->checkPrivelege('tk_00');
        $m = D('Admin/Orders');
        if (I('id') > 0) {
            $object = $m->getDetail();
            $this->assign('object', $object);
        }
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->display("/orders/view");
    }

    /**
     * 跳到退款页面
     */
    public function toRefund()
    {
        $this->isLogin();
        $this->checkPrivelege('tk_04');
        $m = D('Admin/Orders');
        if (I('id') > 0) {
            $object = $m->get();
            
            $this->assign('object', $object);
        }
        $this->display("/orders/refund");
    }

    /**
     * 退款
     */
    public function refund()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('tk_04');
        $m = D('Admin/Orders');
        $rs = $m->refund();
        $this->ajaxReturn($rs);
    }
}
;
?>