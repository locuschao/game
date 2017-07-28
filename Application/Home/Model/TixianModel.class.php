<?php
namespace Home\Model;

/**
 * 提现
 */
class TixianModel extends BaseModel
{

    public function getTixianList()
    {
        $shopId = session('WST_USER.shopId');
        if (! $shopId) {
            return array();
        }
        $field = "o.orderId,o.orderNo,needPay,o.createTime,o.userId,u.userPhone,a.bizIncome,a.agentMoney";
        $sql = "SELECT  $field FROM __PREFIX__orders as o left join __PREFIX__users as u on u.userId=o.userId left join __PREFIX__autocomfirm as a on a.orderId=o.orderId where  o.orderStatus =3 and o.isTixian=0  and o.shopId=$shopId order by o.orderId desc";
        return M()->pageQuery($sql);
    }

    public function historyTixian()
    {
        $shopId = session('WST_USER.shopId');
        if (! $shopId) {
            return array();
        }
        $sql = "SELECT  * FROM __PREFIX__tixian where shopId=$shopId order by id desc";
        return M()->pageQuery($sql);
    }

    public function orderList()
    {
        $shopId = session('WST_USER.shopId');
        $txId = I('id');
        $ids = M('tixian')->where(array(
            'id' => $txId
        ))->getField('orderIds');
        if (! $shopId) {
            return array();
        }
        $field = "o.orderId,o.orderNo,needPay,o.createTime,o.userId,u.userPhone,a.bizIncome,a.agentMoney";
        $sql = "SELECT  $field FROM __PREFIX__orders as o left join __PREFIX__users as u on u.userId=o.userId left join __PREFIX__autocomfirm as a on a.orderId=o.orderId where  o.orderId in ($ids)  and o.shopId=$shopId order by o.orderId desc";
        
        $info = M()->pageQuery($sql);
        $totalMoney = 0;
        foreach ($info['root'] as $k => $v) {
            $totalMoney += $v['bizIncome'];
        }
        $info['totalMoney'] = $totalMoney;
        return $info;
    }

    public function shopMoney()
    {
        $shopId = session('WST_USER.shopId');
        if (! $shopId) {
            return array(
                'balanceMoney' => 0,
                'txMoney' => 0
            );
        }
        $money = M('shops')->where(array(
            'shopId' => $shopId
        ))->getField('bizMoney');
        $tixianMoney = M('tixian')->where(array(
            'shopId' => $shopId
        ))->sum('txMoney'); // ,'txStatus'=>1
        return array(
            'balanceMoney' => $money,
            'txMoney' => $tixianMoney
        );
    }
}