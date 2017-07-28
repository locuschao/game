<?php
namespace Admin\Model;

/**
 * 提现
 */
class TixianModel extends BaseModel
{
    
    // 商家提现
    public function getBizTixianList()
    {
        $m = M('tixian');
        $shopName = I('shopName');
        $txID = I('txID');
        $starDay = I('starDay');
        $endDay = I('endDay');
        $txStatus = I('txStatus', 0);
        $where = " where s.shopFlag=1 ";
        
        if ($shopName) {
            $where .= " and s.shopName like '%$shopName%' ";
        }
        if ($txID) {
            $where .= " and t.id=$txID ";
        }
        if (isset($txStatus)) {
            $where .= " and t.txStatus=$txStatus ";
        }
        if ($starDay && $endDay) {
            $where .= "  and t.txTime between '$starDay' and '$endDay' ";
        } else 
            if ($starDay && ! $endDay) {
                $where .= " and t.txTime >='$starDay' ";
            } else 
                if (! $starDay && $endDay) {
                    $where .= " and t.txTime <='$endDay' ";
                }
        
        $sql = "select t.*,s.shopName,u.userName from __PREFIX__tixian as t left join __PREFIX__shops as s  on s.shopId=t.shopId left join __PREFIX__users as u on u.userId=s.userId $where order by t.id DESC";
        
        $page = $m->pageQuery($sql);
        
        // 获取涉及的订单及商品
        return $page;
    }
    // 个人提现
    public function getPsBizTixianList()
    {
        $m = M('tixian');
        $userName = I('userName');
        $txID = I('txID');
        $starDay = I('starDay');
        $endDay = I('endDay');
        $txStatus = I('txStatus', 0);
        $where = " where u.userFlag=1 ";
        
        if ($userName) {
            $where .= " and u.userName like '%$userName%' ";
        }
        if ($txID) {
            $where .= " and t.id=$txID ";
        }
        if (isset($txStatus)) {
            $where .= " and t.txStatus=$txStatus ";
        }
        if ($starDay && $endDay) {
            $where .= "  and t.txTime between '$starDay' and '$endDay' ";
        } else 
            if ($starDay && ! $endDay) {
                $where .= " and t.txTime >='$starDay' ";
            } else 
                if (! $starDay && $endDay) {
                    $where .= " and t.txTime <='$endDay' ";
                }
        
        $sql = "select t.*,u.userName ,u.userPhone from __PREFIX__ps_tixian as t  left  join  __PREFIX__users as u on u.userId=t.userId $where order by t.id DESC";
        
        $page = $m->pageQuery($sql);
        
        // 获取涉及的订单及商品
        return $page;
    }
    
    // 商家处理提现
    public function bizTixianHandle()
    {
        $id = I('id', 0);
        if (! $id) {
            return array(
                'status' => - 1
            );
        }
        $rs = M('tixian')->where(array(
            'id' => $id
        ))->setField(array(
            'txStatus' => 1,
            'handleTime' => date('Y-m-d H:i:s')
        ));
        if ($rs != false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => - 1
            );
        }
    }
    // 个人提现处理
    public function psTixianHandle()
    {
        $id = I('id', 0);
        if (! $id) {
            return array(
                'status' => - 1
            );
        }
        $rs = M('ps_tixian')->where(array(
            'id' => $id
        ))->setField(array(
            'txStatus' => 1,
            'handleTime' => date('Y-m-d H:i:s')
        ));
        if ($rs != false) {
            return array(
                'status' => 0
            );
        } else {
            return array(
                'status' => - 1
            );
        }
    }
}
?>