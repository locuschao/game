<?php
namespace Admin\Model;

/**
 * 订单服务类
 */
class OrdersModel extends BaseModel
{

    /**
     * 获取订单详细信息
     */
    public function getDetail()
    {
        $m = M('orders');
        $id = (int) I('id', 0);
        $orderId = $id;
        $data = array();
        $where = " where o.orderId = $orderId ";
        
        $sql = "select o.shopId,gg.goodsType,o.payType, o.orderType,o.userAddress,o.roleName,o.profession,o.userPhone,o.orderNo,o.qq,og.goodsNums,o.userName,o.orderStatus,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums
		from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og
		on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid left join __PREFIX__goods as gg on gg.goodsId=og.goodsId
		left join __PREFIX__versions as v on v.id=og.vid $where order by orderId desc ";
        
        $order = $this->queryRow($sql);
        if ($order['orderType'] == 1 || $order['orderType'] == 2) {
            $data['fahuo'] = M('fahuo')->where(array(
                'orderId' => $orderId
            ))->select();
        }
        
        $data["order"] = $order;
        $sql = "SELECT * FROM __PREFIX__log_orders WHERE orderId = $orderId ";
        $logs = $this->query($sql);
        $data["logs"] = $logs;
        
        return $data;
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
     * 二次开发
     * 编写者 魏永就
     * $msg 不为空 表示该函数是在导出数据时调用的
     */
    public function queryByPage($msg='',$start='',$end='')
    {
        $m = M('goods');
        $shopName = I('shopName');
        $orderNo = I('orderNo');
        $type = I('type');
        $version = I('version');
        $gameName = I('get.gameName');
        $timeS = I('get.timeS');
        $timeE = I('get.timeE');
        $orderStatus = I('get.orderStatus');
        
        /**
         * @author peng
         * @date 2017-02
         * @descreption 删除的订单也显示出来方便对账
         */
        //$where = " where o.orderFlag=1 ";
        $where = " where o.orderId>0";
        
        if ($shopName) {
            $where .= " and s.shopName like '%$shopName%' ";
        }
        if ($orderNo) {
            $where .= " and o.orderNo=$orderNo ";
        }
        /**
         * @author 魏永就
         * @date 2017-2-8
         * @description 分类查询
         */
        if($type)
        {
            switch ($type)
            {
                case 1:
                    $where .= " and o.orderType = 1 and gg.goodsType != 1";
                    break;
                case 2:
                    $where .= " and o.orderType = 1 and gg.goodsType = 1";
                    break;
                case 3:
                    $where .=" and o.orderType = 2 and gg.goodsType != 1";
                    break;
                case 4:
                    $where .= " and o.orderType = 2 and gg.goodsType = 1";
            }
        }
        if($version)
        {
            $where .= " and v.id = $version";
        }
        if($gameName)
        {
            $where .= " and ga.gameName = '$gameName'";
        }
        if($timeS && $timeE)
        {
            $timeS = strtotime($timeS);
            $timeE = strtotime($timeE);
            $where .= " and UNIX_TIMESTAMP(o.createTime) >= $timeS and  UNIX_TIMESTAMP(o.createTime) <= $timeE ";
        }
        if($orderStatus)
        {
            $where .= " and o.orderStatus = $orderStatus";
        }
        /**
         * @author peng
         * @date 2017-01
         * @descreption 不包括平台订单
         */
        $where .= " and o.shopId>0 ";
        
        $sql = "select o.fahuoTime,o.fahuoType,gg.goodsType,o.orderType,o.payType,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums,og.goodsNums,gg.marketPrice
     	    from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og 
     	    on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid  left join __PREFIX__goods as gg on gg.goodsId=og.goodsId
     	    left join __PREFIX__versions as v on v.id=og.vid $where order by orderId desc ";
           
        if($msg) //导出数据
        {
            $where .= " and UNIX_TIMESTAMP(o.createTime) >= $start and  UNIX_TIMESTAMP(o.createTime) <= $end ";
            $sql = "select o.fahuoTime,o.fahuoType,gg.goodsType,o.orderType,o.payType,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums,og.goodsNums,gg.marketPrice
     	    from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og 
     	    on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid  left join __PREFIX__goods as gg on gg.goodsId=og.goodsId
     	    left join __PREFIX__versions as v on v.id=og.vid $where order by orderId desc ";
            $page['root'] = $m->query($sql);
        }else{
            
            $page = $m->pageQuery($sql);
        }
        foreach ($page['root'] as $k => $v) {
            if ($v['orderType'] == 1) {
                $page['root'][$k]['scope'] = '首充号';
                if($v['goodsType']==1){
                    $page['root'][$k]['scope'] = '会员首充';
                }
            } else 
                if ($v['orderType'] == 2) {
                    $page['root'][$k]['scope'] = '首充号代充';
                    if($v['goodsType']==1){
                        $page['root'][$k]['scope'] = '会员首代';
                    }
                }
            $page['root'][$k]['amount'] = $v['goodsNums']*$v['marketPrice'];
            $page['root'][$k]['orderNo'] = ' '.$v['orderNo'];
        }
        // 获取涉及的订单及商品
        return $page;
    }

    /**
     * 获取退款列表
     */
    public function queryRefundByPage()
    {
        $m = M('goods');
        $shopName = I('shopName');
        $orderNo = I('orderNo');
        $isRefund = (int) I('isRefund', - 1);
        $areaId1 = (int) I('areaId1', 0);
        $areaId2 = (int) I('areaId2', 0);
        $areaId3 = (int) I('areaId3', 0);
        $sql = "select o.orderId,o.orderNo,o.totalMoney,o.orderStatus,o.isRefund,o.deliverMoney,o.payType,o.createTime,s.shopName,o.userName from __PREFIX__orders o
	 	         left join __PREFIX__shops s on o.shopId=s.shopId  where o.orderFlag=1 and o.orderStatus in (-1,-4) and payType=1 and isPay=1 ";
        if ($areaId1 > 0)
            $sql .= " and s.areaId1=" . $areaId1;
        if ($areaId2 > 0)
            $sql .= " and s.areaId2=" . $areaId2;
        if ($areaId3 > 0)
            $sql .= " and s.areaId3=" . $areaId3;
        if ($isRefund > - 1)
            $sql .= " and o.isRefund=" . $isRefund;
        if ($shopName != '')
            $sql .= " and (s.shopName like '%" . $shopName . "%' or s.shopSn like '%" . $shopName . "%')";
        if ($orderNo != '')
            $sql .= " and o.orderNo like '%" . $orderNo . "%' ";
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
     * 退款
     */
    public function refund()
    {
        $rd = array(
            'status' => - 1
        );
        $m = M('orders');
        $rs = $m->where('isRefund=0 and orderFlag=1 and orderStatus in (-1,-4,-6,-7) and payType=1 and isPay=1 and orderId=' . I('id'))->find();
        if ($rs['orderId'] != '') {
            $data = array();
            $data['isRefund'] = 1;
            $data['refundRemark'] = I('content');
            $rss = $m->where("orderId=" . (int) I('id', 0))->save($data);
            // 修改退款表
            $data = array();
            $data['pf_status'] = 1; // 平台退款状态
            $data['actual_money'] = $rs['needPay']; // 实际退款金额
            $data['pf_time'] = time(); // 平台处理时间
            $data['way'] = I('way'); // 退款方式
            $m = M('Refund');
            $m->where('orderid =' . I('id'))->save($data);
            // 退款到余额
            if (I('way') == '0') {
                $m = M('Users');
                $m->where('userId =' . $rs['userId'])->setField('userMoney', $data['actual_money']);
            }
            if (false !== $rs) {
                $rd['status'] = 1;
            } else {
                $rd['status'] = - 2;
            }
        }
        return $rd;
    }
    /**
     * @author peng
     * @date 2017-03
     * @descreption 处理分销订单的分成操作
     */
    public function fenchengHandle($orderId,$is_batch=false){
        $orderInfo=M('orders')->find($orderId);
        
        if($is_batch === false){
            if($orderInfo['orderStatus'] != 3) {
                return [
                    'status'=>0,
                    'info'=>'订单未完成'
                ];
            }
            if($orderInfo['is_fencheng'] != 1) {
                return [
                    'status'=>0,
                    'info'=>'不是分成订单'
                ];
            }
            if($orderInfo['isAgent']==1){
                return [
                    'status'=>0,
                    'info'=>'该订单已经分成!'
                ];
                
            }
        }
        
        $buyerInfo=M('users')->where([
            'userId'=>M('orders')->find($orderId)['userId']
        
        ])->find();
        if($buyerInfo['partnerId']==0){
            return [
                'status'=>0,
                'info'=>'没有推荐!'
            ];
        }
        $tgPersonInfo=M('users')->where([
            'userId'=>$buyerInfo['partnerId']
        
        ])->find();
        
        $profit=M('profit_setting')->where([
            'tg_level'=>$tgPersonInfo['rank'],
            'gm_level'=>$buyerInfo['rank']
        ])->find()['return_profit'];
       
        $profit_money=floatval($profit);
        
        if($profit_money==0){
            return [
                'status'=>0,
                'info'=>'请设置分成金额'
            ];
            
        }
        $rank_lang=[
            1=>'高级',
            2=>'中级',
            3=>'低级'
        ];
        if($profit_money>floatval($orderInfo['needPay'])){
            $this->error($rank_lang[$tgPersonInfo['rank']].'对'.$rank_lang[$buyerInfo['rank']].'的分成金额大于订单金额！');
        }
        
        M()->startTrans();
    
        $sql='update __PREFIX__users set agentBalance=agentBalance+'.$profit_money.',agentTotalPrice=agentTotalPrice+'.$profit_money.' where userId='.$tgPersonInfo['userId'];
        $re1=M('users')->query($sql);
        
        $re2=M('data_tmp')->where(['key'=>'platform_money'])->setDec('value',$profit_money);
        $re3=M('orders')->where(['orderId'=>$orderId])->setField('isAgent',1);
        $re4= M('percentage_log')->add([
            'orderId'=>$orderId,
            'time'=>time(),
            'gain_price'=>$profit_money,
            'userId'=>$buyerInfo['userId']
        ]);
        
        $re=M('platfrom_account')->add([
            'orderId'=>$orderId,
            'time'=>time(),
            'income'=>0-$profit_money,
            'remark'=>'分成金额',
            'orderNo'=>$orderInfo['orderNo'],
            //'userId'=>$goods_info['userId']
        ]);
        
        if($re1!==false && $re2 && $re3 && $re4 && $re){
            M()->commit();
            return [
                'status'=>1
            ];
        } else {
            M()->rollback();
            return [
                'status'=>0,
                'info'=>'提交失败'
            ];;
        }    
    }
    /**
     * @author peng
     * @date 2017-03
     * @descreption 对所有已经是完成状态但未分成的订单进行分成
     */
    public function fenchengBatch(){
        foreach(M('orders')->where([
            'is_fencheng'=>1,
            'isAgent'=>0,
            'orderStatus'=>3
        ])
        ->select() as $row) {
            $this->fenchengHandle($row['orderId'],true);
        }
    }
    
}   
    
?>