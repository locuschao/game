<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;

class MessController extends BaseController
{

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }

    public function mess()
    {
        $userid = I('userId');
        $userid = authCode($userid);
        $page = I('page', 0);
        $limit = 20;
        if ($userid) {
            $field = "m.*,";
            $messList = M('mess as m')->field($field)
                ->join()
                ->select();
            $map['m.userId'] = $userid;
            $map['m.type'] = 1;
            $field = "o.orderType,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,o.paytime,g.vid,g.gid,v.vName,ga.gameName";
            $waitPay = M('mess as m')->join('oto_orders as o  on o.orderId=m.orderid')
                ->join(' join oto_order_goods  as g on o.orderId=g.orderId')
                ->join('join oto_game as ga on ga.id=g.gid')
                ->join('join oto_versions as v on v.id=g.vid')
                ->where($map)
                ->order('o.orderId DESC')
                ->field($field)
                ->limit($page * $limit, $limit)
                ->select();
            foreach ($waitPay as $k => $v) {
                $type = '首充号';
                if ($v['orderType'] == 1) {
                    $type = '首充号';
                    // 查询发货帐号及密码
                    $account = M('fahuo')->where(array(
                        'orderId' => $v['orderId']
                    ))->select();
                    $waitPay[$k]['fahuo'] = $account;
                } else 
                    if ($v['orderType'] == 2) {
                        $type = '首充号代充';
                    } else 
                        if ($v['orderType'] == 3) {
                            // 查询发货帐号及密码
                            $account = M('fahuo')->where(array(
                                'orderId' => $v['orderId']
                            ))->select();
                            $waitPay[$k]['fahuo'] = $account;
                            $type = '首充号分销';
                        } else 
                            if ($v['orderType'] == 4) {
                                $type = '自主充值';
                                // 查询发货帐号及密码
                                $account = M('fahuo')->where(array(
                                    'orderId' => $v['orderId']
                                ))->select();
                                $waitPay[$k]['fahuo'] = $account;
                            }
                $waitPay[$k]['type'] = $type;
            }
            $this->returnJson($waitPay);
        }
    }
}