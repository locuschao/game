<?php
namespace ImproveAPI\Model;
use Think\Model;
class MyOrderModel extends Model
{
    public function getOrder() {
        $post = getData();
        $_GET['p'] = $post['p'];
        $condition = [
            'o.userId'=>$_SESSION['userId'],
            'o.orderFlag'=>1,
            'o.isClosed'=>0
        ];

        $type_asoc_key = [
            2=>- 2,// 待付款
            3=>1,// 待发货
            4=>2,// 已发货
            5=>3,// 已完成
            6=>[-3,-8],// 退款中
            7=>[-9,-7]// 取消或无效
        ];
        
        $cond_type = $type_asoc_key[$post['type']];
        if(is_array($cond_type)){
            $condition['o.orderStatus'] = ['in',$cond_type] ;
        }else if($cond_type){
            $condition['o.orderStatus'] = $cond_type;
        }
        $join_str = 'as o left join oto_order_goods  as g on o.orderId=g.orderId
        left join oto_versions as v on g.vid=v.id
        left join  oto_game as ga on ga.id=g.gid
        ';
        $page = new \Think\Page(M('orders')->join($join_str)->where($condition)->count(), 15);
        
        $field = "o.totalMoney,o.orderNo,o.signTime,o.orderStatus,o.shopId,o.needPay,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $data = M('orders')->join($join_str)
            ->where($condition)
            ->order('o.lastMessTime DESC')
            ->field($field)
            ->limit($page->firstRow,$page->listRows)
            ->select();
        
        foreach ($data as $k => $row) {
            if($row['orderType'] == 1) $data[$k]['type'] = '首充号';
            else if($row['orderType'] == 2) $data[$k]['type'] = '首充号代充';
            $data[$k]['goodsThums'] = C('RESOURCE_URL').$row['goodsThums'];
        }
        return $data;
    }
    
    
    public function orderDetail($arr) {
        $post = $arr['post'];
        $orderNo = $post['orderId'];
        $map['o.isClosed'] = 0;
        $map['o.orderFlag'] = 1;
        //$map['o.userId'] = $_SESSION['userId'];
        $map['o.orderNo'] = $orderNo;
        $field = "o.account,o.pwd,o.totalMoney,o.kfQQ,o.orderNo,o.orderStatus,o.orderId,o.shopId,o.needPay,o.fahuoTime,o.roleName,o.profession,o.paytime,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,g.vid,g.gid,o.orderType,v.vName,ga.gameName";
        $orderInfo = M('orders')->join('as o left join oto_order_goods  as g on o.orderId=g.orderId')
            ->join('left join oto_versions as v on g.vid=v.id')
            ->join('left join  oto_game as ga on ga.id=g.gid')
            ->where($map)
            ->order('o.orderId DESC')
            ->field($field)
            ->find();
        $orderInfo['goodsThums'] = C('RESOURCE_URL').$orderInfo['goodsThums'];
        if($row['orderType'] == 1) $orderInfo['type'] = '首充号';
        else if($row['orderType'] == 2) $orderInfo['type'] = '首充号代充';
        if (($orderInfo['orderStatus'] == 3 || $orderInfo['orderStatus'] == - 6) && (time() - strtotime($orderInfo['fahuoTime'])) < C('cancelOrderTime')) {
            // 7天内的订单才可能投诉
            // 检查是否已经投诉过
            if (! M('complain')->where(['orderNo'=>$orderNo])->find()) {
                $orderInfo['complain'] = 1;
            }
        }
        if($orderInfo['orderType'] == 1){
            $orderInfo['desc'] = '账号:'. $orderInfo['account'] . ' 密码:'.$orderInfo['pwd'];
        }else if($orderInfo['orderType'] == 2){
            $orderInfo['desc'] = '账号:'. $orderInfo['account'];
        }
        unset($orderInfo['account']);
        unset($orderInfo['pwd']);
        return $orderInfo;
    }
}