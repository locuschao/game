<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/10
 * Time: 16:40
 */
namespace Home\Controller;

class GroupOrdersController extends BaseController
{

    /*
     * ��ת������ȷ��ҳ��
     */
    public function checkOrder()
    {
        $this->isUserLogin();
        $mareas = D('Home/Areas');
        $morders = D('Home/GroupOrders');
        $mgoods = D('Home/Goods');
        $maddress = D('Home/UserAddress');
        $gtotalMoney = 0; // ��Ʒ�ܼۣ�ȥ�����ͷѣ�
        $totalMoney = 0; // ��Ʒ�ܼۣ������ͷѣ�
        $totalCnt = 0;
        $shopColleges = array();
        $startTime = 0;
        $endTime = 24;
        $paygoods = array();
        session('WST_PAY_GOODS', $paygoods);
        $USER = session('WST_USER');
        // ��ȡ��ַ�б�
        $areaId2 = $this->getDefaultCity();
        $addressList = $maddress->queryByUserAndCity($USER['userId'], $areaId2);
        $this->assign("addressList", $addressList);
        $this->assign("areaId2", $areaId2);
        // ֧����ʽ
        $pm = D('Home/Payments');
        $payments = $pm->getList();
        $this->assign("payments", $payments);
        
        // ��ȡ��ǰ�е�����
        $m = D('Home/Areas');
        $areaList2 = $m->getDistricts($areaId2);
        $this->assign("areaList2", $areaList2);
        if ($endTime == 0) {
            $endTime = 24;
            $cstartTime = (floor($startTime)) * 4;
            $cendTime = (floor($endTime)) * 4;
        } else {
            $cstartTime = (floor($startTime) + 1) * 4;
            $cendTime = (floor($endTime) + 1) * 4;
        }
        if (floor($startTime) < $startTime) {
            $cstartTime = $cstartTime + 2;
        }
        if (floor($endTime) < $endTime) {
            $cendTime = $cendTime + 2;
        }
        
        $this->assign("startTime", $cstartTime);
        $this->assign("endTime", $cendTime);
        $this->assign("shopColleges", $shopColleges);
        $this->assign("catgoods", $catgoods);
        $this->assign("gtotalMoney", $gtotalMoney);
        $this->assign("totalMoney", $totalMoney);
        $this->display('Group/orders/check_order');
    }
}
?>