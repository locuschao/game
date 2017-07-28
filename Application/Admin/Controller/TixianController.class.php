<?php
namespace Admin\Controller;

use Lib\Exp\DataExp;

class TixianController extends BaseController
{

    public function psTixianList()
    {
        $this->isLogin();
        $this->checkPrivelege('grtx_00');
        $m = D('Admin/Tixian');
        $page = $m->getPsBizTixianList();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('userName', I('userName'));
        $this->assign('txID', I('txID'));
        $this->assign('starDay', I('starDay'));
        $this->assign('endDay', I('endDay'));
        $this->assign('txStatus', I('txStatus', 0));
        $this->display('psTixianList');
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出个人提现数据
     */
    public function psExp()
    {
        $this->isLogin();
        $this->checkPrivelege('grtx_00');
        $start  = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        $this->isLogin();
        if($start == '' || $end == '' || $start >= $end)
        {
            $this->assign('msg','<font color=red>选择的时间未完整或终始时间相同</font>');
            $this->psTixianList();
        }else{
            $sql = "select t.id,t.bankNo,t.bankName,t.txTime,t.txStatus,u.userName ,u.userPhone from __PREFIX__ps_tixian as t  left  join  __PREFIX__users as u on u.userId=t.userId  where u.userFlag=1 and UNIX_TIMESTAMP(t.txTime) >= $start and UNIX_TIMESTAMP(t.txTime) <= $end order by t.id DESC";
            $xlsModel = M('tixian');
            $xlsData = $xlsModel->query($sql);
            if(!$xlsData)
            {
                $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
                $this->psTixianList();
                die;
            }
            $xlsCell = array(
                array('id','提现ID'),
                array('userPhone','手机号'),
                array('userName','提现人姓名'),
                array('bankNo','卡号'),
                array('bankName','所属银行'),
                array('txTime','提现金额'),
                array('txStatus','提现时间'),
            );
            foreach ($xlsData as $key => $value)
            {
                if($value['txStatus'] == 0)
                {
                    $xlsData[$key]['txStatus'] = '待处理';
                }else if($value['txStatus'] == 1){
                    $xlsData[$key]['txStatus'] = '已同意';
                }else{
                    $xlsData[$key]['txStatus'] ='已拒绝';
                }
            }
            $xlsName = 'psTixian';
            $dataExp = new DataExp();
            $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
        }
    }

    public function bizTixianList()
    {
        $this->isLogin();
        $this->checkPrivelege('sjtx_00');
        $m = D('Admin/Tixian');
        $page = $m->getBizTixianList();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('txID', I('txID'));
        $this->assign('starDay', I('starDay'));
        $this->assign('endDay', I('endDay'));
        $this->assign('txStatus', I('txStatus', 0));
        $this->display('bizTixianList');
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 商家提现列表
     */
    public function bizExp()
    {
        $this->isLogin();
        $this->checkPrivelege('sjtx_00');
        $start  = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        $this->isLogin();
       if($start == '' || $end == ''|| $start >= $end)
       {
            $this->assign('msg','<font color=red>选择的时间未完整或终始时间相同</font>');
            $this->bizTixianList();
       }else{
            $sql = "select t.id,t.bankNo,t.bankName,t.txMoney,t.txTime,t.txStatus,s.shopName,u.userName from __PREFIX__tixian as t left join __PREFIX__shops as s  on s.shopId=t.shopId left join 
          __PREFIX__users as u on u.userId=s.userId where UNIX_TIMESTAMP(t.txTime) >= $start and UNIX_TIMESTAMP(t.txTime) <= $end and  s.shopFlag=1 order by t.id DESC";
           $xlsModel = M('tixian');
           $xlsData = $xlsModel->query($sql);
           if(!$xlsData)
           {
               $this->assign('msg','<font color=red>没有数据符合你选择的时间</font>');
               $this->bizTixianList();
               die;
           }
           $xlsCell = array(
               array('id','提现ID'),
               array('shopName','所属店铺'),
               array('userName','提现人姓名'),
               array('bankNo','卡号'),
               array('bankName','所属银行'),
               array('txMoney','提现金额'),
               array('txTime','提现时间'),
               array('txStatus','提现状态'),
           );
            foreach ($xlsData as $key => $value)
            {
                if($value['txStatus'] == 0)
                {
                    $xlsData[$key]['txStatus'] = '待处理';
                }else if($xlsData == 1)
                {
                    $xlsData[$key]['txStatus'] = '已同意';
                }else{
                    $xlsData[$key]['txStatus'] = '已拒绝';
                }
            }
           $xlsName = 'bizTx';
           $dataExp = new DataExp();
           $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
       }
    }
    // 商家提现处理
    public function bizTixianHandle()
    {
        $this->checkAjaxPrivelege('sjtx_01');
        $m = D('Admin/Tixian');
        $rs = $m->bizTixianHandle();
        $this->ajaxReturn($rs);
    }
    // 个人提现处理
    public function psTixianHandle()
    {
        $this->checkAjaxPrivelege('grtx_01');
        $m = D('Admin/Tixian');
        $rs = $m->psTixianHandle();
        $this->ajaxReturn($rs);
    }
}
?>