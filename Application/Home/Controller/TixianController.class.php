<?php
namespace Home\Controller;

/**
 * 分销模块
 */
class TixianController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function tixianList()
    {
        $oDB = D('Home/Tixian');
        $page = $oDB->getTixianList();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopMoney', $oDB->shopMoney());
        $this->display('Tixian/tixianList');
    }

    public function historyTixian()
    {
        $oDB = D('Home/Tixian');
        $page = $oDB->historyTixian();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display('Tixian/historyTixian');
    }

    public function orderList()
    {
        $oDB = D('Home/Tixian');
        $page = $oDB->orderList();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display('Tixian/orderList');
    }

    public function tixian()
    {
        $shopId = session('WST_USER.shopId');
        $this->info = M('shops as s')->where(array(
            'shopId' => $shopId
        ))
            ->field('s.bankNo,u.userName,b.bankName,s.bizMoney')
            ->join('left join oto_users as u on s.userId=u.userId left join oto_banks as b on b.bankId=s.bankId')
            ->find();
        $this->display('Tixian/tixian');
    }
    
    // 提现处理
    public function tixianHandle()
    {
        // money:money,bankName:bankName,bankNo:bankNo,userName:userName
        $shopId = session('WST_USER.shopId');
        if (IS_AJAX) {
            $bankName = I('bankName');
            $bankNo = I('bankNo');
            $userName = I('userName');
            if (empty($bankName) || empty($bankNo) || empty($userName) || ! is_numeric($bankNo)) {
                $this->ajaxReturn(array(
                    'status' => - 1
                ));
                exit();
            }
            if (! $shopId) {
                $this->ajaxReturn(array(
                    'status' => - 1
                ));
                exit();
            }
            //已成功的订单及拒绝退款的订单
            $order = M('orders')->where(array(
                'o.orderStatus' => array('in','3,-6,-8'),
                'o.isTixian' => 0,
                'o.shopId'=>$shopId
            ))->join('as o left join oto_autocomfirm as a on a.orderId=o.orderId')
                ->field('o.orderId,o.needPay,a.bizIncome')
                ->select();
            
            $ids = '';
            
            //计算商家总收入
            $totalMoney = 0;
            foreach ($order as $k => $v) {
                $ids .= $v['orderId'] . ',';
                $totalMoney += $v['bizIncome'];
            }
            $ids = trim($ids, ',');
            M()->startTrans();
            $A = M('orders')->where(array(
                'orderId' => array(
                    'in',
                    $ids
                )
            ))->setField(array(
                'isTixian' => 1
            ));
            
            $bizMoney=M('shops')->where(array('shopId'=>$shopId))->getField('bizMoney');
            
            if((float)trim($bizMoney)!=(float)trim($totalMoney)){
                $this->ajaxReturn(array(
                    'status' => - 2,'msg'=>'部分订单还在处理中'
                ));
                return;
            }
            
            
            /**
             * @author peng	
             * @date 2016-12-19
             * @descreption 订单状态是完成的实际收入+拒绝退款的金额
             */
            /*$totalMoney=M('orders')->where([
            'shopId'=>$shopId,
            'orderStatus'=>-6
            ])->sum('needPay')+$totalMoney;
          
            if($bizMoney!=$totalMoney){
                $this->ajaxReturn(array(
                    'status' => - 2,'msg'=>'部分订单还在处理中'
                ));
                return;
            }*/
            $txData['shopId'] = $shopId;
            $txData['txTime'] = date('Y-m-d H:i:s');
            $txData['txMoney'] = $totalMoney;
            $txData['orderIds'] = $ids;
            $txData['orderIds'] = $ids;
            $txData['txStatus'] = 0;
            $txData['trueName'] = $userName;
            $txData['bankNo'] = $bankNo;
            $txData['bankName'] = $bankName;
            $B = M('tixian')->add($txData);
            $C = M('shops')->where(array(
                'shopId' => $shopId
            ))->setDec('bizMoney', $totalMoney);
            if ($A != false && $B && $C != false) {
                M()->commit();
                $this->ajaxReturn(array(
                    'status' => 0,'msg'=>'提成操作成功！'
                ));
                exit();
            } else {
                M()->rollback();
                $this->ajaxReturn(array(
                    'status' => - 1,'msg'=>'提现操作失败'
                ));
                exit();
            }
        }
    }
}