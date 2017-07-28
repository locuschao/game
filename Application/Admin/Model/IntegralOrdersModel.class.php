<?php
namespace Admin\Model;

/**
 * 订单服务类
 */
class IntegralOrdersModel extends BaseModel
{

    /**
     * 获取订单详细信息
     */
    public function getDetail()
    {
        $m = M('orders');
        $id = (int) I('id', 0);
        $sql = "select o.* from __PREFIX__orders o
	 	         where o.orderFlag=1 and o.orderId=" . $id;
        $rs = $this->queryRow($sql);
        // 获取用户详细地址
        $sql = 'select communityName,a1.areaName areaName1,a2.areaName areaName2,a3.areaName areaName3 from __PREFIX__communitys c 
		        left join __PREFIX__areas a1 on a1.areaId=c.areaId1 
		        left join __PREFIX__areas a2 on a2.areaId=c.areaId2
		        left join __PREFIX__areas a3 on a3.areaId=c.areaId3
		        where c.communityId=' . $rs['communityId'];
        $cRs = $this->queryRow($sql);
        $rs['userAddress'] = $cRs['areaName1'] . $cRs['areaName2'] . $cRs['areaName3'] . $cRs['communityName'] . $rs['userAddress'];
        // 获取日志信息
        $m = M('log_orders');
        $sql = "select lo.*,u.loginName,u.userType,s.shopName from __PREFIX__log_orders lo
		         left join __PREFIX__users u on lo.logUserId = u.userId
		         left join __PREFIX__shops s on u.userType!=0 and s.userId=u.userId
		         where orderId=" . $id . "  order by lo.logTime asc";
        $rs['log'] = $this->query($sql);
        foreach ($rs['log'] as $k => $v) {
            if ($v['loginName'] != true) {
                $sql = "select loginName from __PREFIX__staffs where staffId = " . $v['logUserId'];
                $loginName = $this->query($sql)[0]['loginName'];
                $rs['log'][$k]['loginName'] = $loginName;
                $rs['log'][$k]['shopName'] = '在线商城';
            }
        }
        
        // 获取相关商品
        $sql = "select og.* from __PREFIX__order_goods og
			        left join __PREFIX__goods g on og.goodsId=g.goodsId
			        where og.orderId = " . $id;
        $rs['goodslist'] = $this->query($sql);
        // 获取物流信息
        $sql = "select oe.*,e.* from __PREFIX__order_express oe left join __PREFIX__express e on oe.exId = e.id where orderId = " . $id;
        $rs['express'] = $this->query($sql);
        return $rs;
    }

    /**
     * 获取订单信息
     */
    public function get()
    {
        $m = M('orders');
        return $m->where('isRefund=0 and payType=1 and isPay=1 and orderFlag=1 and orderStatus in (-1,-4,-6,-7) and orderId=' . (int) I('id'))->find();
    }

    /**
     * 订单分页列表
     */
    public function queryByPage()
    {
        $m = M('goods');
        $orderNo = I('orderNo');
        $orderStatus = (int) I('orderStatus', - 9999);
        $sql = "select o.orderId,o.orderNo,o.totalMoney,o.orderStatus,o.deliverMoney,o.deliverType,o.payType,o.createTime,o.userName from __PREFIX__orders o where o.orderFlag=1 and o.shopId=0 and o.orderStatus in(-1,0,1,3,4)";
        if ($orderNo != '')
            $sql .= " and o.orderNo like '%" . $orderNo . "%' ";
        if ($orderStatus != - 9999 && $orderStatus != - 100)
            $sql .= " and o.orderStatus=" . $orderStatus;
        if ($orderStatus == - 100)
            $sql .= " and o.orderStatus in(-6,-7)";
        $sql .= " order by orderId desc";
        $page = $m->pageQuery($sql);
        // 获取涉及的订单及商品
        if (count($page['root']) > 0) {
            $orderIds = array();
            foreach ($page['root'] as $key => $v) {
                $orderIds[] = $v['orderId'];
            }
            $sql = "select og.orderId,og.goodsThums,og.goodsName,og.goodsId from __PREFIX__order_goods og
			        where og.orderId in(" . implode(',', $orderIds) . ")";
            $rs = $this->query($sql);
            $goodslist = array();
            foreach ($rs as $key => $v) {
                $goodslist[$v['orderId']][] = $v;
            }
            foreach ($page['root'] as $key => $v) {
                $page['root'][$key]['goodslist'] = $goodslist[$v['orderId']];
            }
        }
        return $page;
    }

    /**
     * 确认积分商城订单
     */
    public function changeOrderStatus()
    {
        $rs = 0;
        $m = M('orders');
        $id = (int) I('id', 0);
        $status = (int) I('status', 0);
        $orders = $m->where('orderFlag=1 and shopId=0 and orderId=' . $id)->setField('orderStatus', $status);
        if ($orders) {
            // 订单操作日志
            $data = array();
            $m = M('log_orders');
            $data["orderId"] = $id;
            if ($status == 1) {
                $data["logContent"] = "商家已受理订单";
            } elseif ($status == 3) {
                $data["logContent"] = "商家已发货";
            }
            $data["logUserId"] = (int) session('WST_STAFF.staffId');
            $data["logType"] = 0;
            $data["logTime"] = date('Y-m-d H:i:s');
            $log = $m->add($data);
            if ($log) {
                $rs = 1;
            }
        }
        return $rs;
    }

    /**
     * 增加快递信息
     */
    public function toExpress()
    {
        $rs = 0;
        $data['exId'] = (int) I('expressId', 0);
        $data['orderId'] = (int) I('orderId', 0);
        $data['trackNumber'] = (int) I('trackNumber', 0);
        $data['deliveryTime'] = time();
        $ordersExModel = M('OrderExpress');
        $expressRs = $ordersExModel->add($data);
        if ($expressRs) {
            $ordersModel = M('Orders');
            $arr['orderStatus'] = 3; // 已发货状态
            $arr['deliverType'] = 1; // 物流配送
            $orders = $ordersModel->where('orderFlag = 1 and shopId = 0 and orderId=' . $data['orderId'])->setField($arr);
            if ($orders) {
                // 订单操作日志
                $data = array();
                $m = M('log_orders');
                $data["orderId"] = (int) I('orderId', 0);
                $data["logContent"] = "商家已发货";
                $data["logUserId"] = (int) session('WST_STAFF.staffId');
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                $log = $m->add($data);
                if ($log) {
                    $rs = 1;
                }
            }
        }
        return $rs;
    }

    /*
     * 查看物流跟踪信息
     */
    public function follow()
    {
        $orderId = (int) I('id', 0);
        // 获取物流公司拼音
        $oeModel = M('OrderExpress');
        $orderExpress = $oeModel->where('orderId = ' . $orderId)->find();
        $expressModel = M('Express');
        $express = $expressModel->where('id = ' . $orderExpress['exId'])->find();
        $type = $express['pinyin'];
        $postid = $orderExpress['trackNumber'];
        // 发送快递跟踪请求
        $url = "http://www.kuaidi100.com/query?type=$type&postid=$postid";
        $html = json_decode(file_get_contents($url));
        // 将得到的数据转换成数组
        $arr = array();
        foreach ($html->data as $k => $v) {
            $arr[$k]['time'] = $v->time;
            $arr[$k]['context'] = $v->context;
            $arr[$k]['ftime'] = $v->ftime;
        }
        krsort($arr);
        $data = array();
        $data['arr'] = $arr;
        $data['express'] = $express;
        $data['orderEx'] = $orderExpress;
        return $data;
    }
}
;
?>