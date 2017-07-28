<?php
namespace Game\Controller;
use Think\Controller;
use Think\Model;

class MessController extends Controller
{

    public function _initialize()
    {   
        /**
         * @author peng
         * @copyright 2016
         * @remark 判断是否登录
         */
        if (! $_SESSION['oto_mall']['oto_userId']) {
            $this->redirect("Game/Login/login");
            exit;
        }
        @header('Content-type: text/html;charset=UTF-8');
    }

    public function mess($page = 0, $limit = 100)
    {
        $userid = session('oto_userId');
        if ($userid) {
            
            $this->assign('notRead',M('orders')->where([
            'userId'=>$userid,
            'isRead'=>0,
            'o.orderFlag'=>1//一旦相关订单删除了，则显示
            ])->count());
            
        }
        
        $this->display();
    }
    public function messageList() {
        $page = I('page');
        $limit = 10;
        $userid = session('oto_userId');
        if ($userid) {
            $map['o.userId'] = $userid;
            $map['o.orderFlag'] = 1;//一旦相关订单删除了，则显示
            //$map['o.isRead'] = 0;
            $field = "o.isRead,o.orderType,o.orderStatus,o.orderId,o.shopId,o.needPay,o.roleName,o.profession,o.createTime,g.goodsName,g.goodsNums,g.goodsPrice,g.goodsAttrName,g.goodsId,g.goodsThums,o.paytime,g.vid,g.gid,v.vName,ga.gameName";
            $waitPay = M('orders as o')
                ->join(' join oto_order_goods  as g on o.orderId=g.orderId')
                ->join('join oto_game as ga on ga.id=g.gid')
                ->join('join oto_versions as v on v.id=g.vid')
                ->where($map)
                ->order('o.isRead,o.lastMessTime DESC')
                ->field($field)
                ->limit($page * $limit, $limit)
                ->select();
            $notRead = 0;
            foreach ($waitPay as $k => $v) {
                if($v['isRead'] == 0)
                {
                    $notRead++;
                }
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
                    }
                $waitPay[$k]['type'] = $type;
            }
            $this->messInfo = $waitPay;
            $this->display();
        }
    }
}