<?php
namespace Admin\Controller;

use Lib\Exp\DataExp;
use Think\Model;

/**
 * 区域控制器
 */
class RefundController extends BaseController
{
    // 退款列表
    public function refundList()
    {
        $this->checkPrivelege('tk_00');
        $searchKey = I('get.searchKey');
        $biz_status = I('biz_status'); // 商家处理状态
        $pf_status = I('pf_status');
        $type = I('type'); // 今后类型，取消订单和售后退款
        $sday = I('start_day');
        $eday = I('end_day');
        $where = "  o.orderStatus in (-3,-4,-5,-6) ";
        if ($sday && $eday) {
            $where .= " and  r.refundTime between '$sday' and '$eday' ";
        } else 
            if (! $sday && $eday) {
                $where .= " and  r.refundTime <= '$eday' ";
            } else 
                if ($sday && ! $eday) {
                    $where .= " and r.refundTime>='$sday' ";
                }
        
        if (is_numeric($biz_status) && $biz_status >= 0) {
            $where .= " and r.biz_status=$biz_status ";
        }
        if (is_numeric($type) && $type >= 0) {
            $where .= " and r.type=$type ";
        }
        if (is_numeric($pf_status) && $pf_status >= 0) {
            if ($pf_status == 0) {
                $where .= " and r.pf_status=$pf_status and r.biz_status!=2 ";
            } else {
                $where .= " and r.pf_status=$pf_status ";
            }
        }
        if ($searchKey) {
            $where .= " and o.orderNo=$searchKey";
        }
        
        // 存搜索条件
        cookie('searchCondition', serialize($where));
        $field = "r.*,o.orderNo";
        $db = M('refund');
        // 分页
        $data['count'] = $db->join("as r left join oto_orders as o on o.orderId=r.orderId")
            ->where($where)
            ->count();
        $Page = new \Think\Page($data['count'], 20);
        $data['show'] = $Page->show();
        $data['list'] = $db->field($field)
            ->join("as r left join oto_orders as o on o.orderId=r.orderId")
            ->where($where)
            ->order('id desc')
            ->limit($Page->firstRow, $Page->listRows)
            ->select();
        // 分页结束
        $this->assign('res', $data);
        $this->display('refund/list');
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出数据
     */
    public function dataExp()
    {
        $this->checkPrivelege('tk_00');
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == '' || $end == ''||$start >= $end)
        {
            $this->assign('Msg','<font color=red>信息不完整或终始时间相同</font>');
            $this->refundList();
        }else {
            $xlsName = 'Refund';
            $xlsModel = M('Refund');
            $field = "r.*,o.orderNo";
            $where = "  o.orderStatus in (-3,-4,-5,-6) and UNIX_TIMESTAMP(r.refundTime) >= $start and UNIX_TIMESTAMP(r.refundTime) <= $end ";
            $xlsData = $xlsModel->join("as r left join oto_orders as o on o.orderId=r.orderId")->field($field)->where($where)->order('id desc')->select();
            if(!$xlsData)
            {
                $this->assign('Msg','<font color=red>没有数据符合您选择的时间</font>');
                $this->refundList();
                die;
            }
           $xlsCell = array(
               array('id','序号'),
               array('orderNo','订单编号'),
               array('refundType','退款类型'),
               array('apply_money','退款金额'),
               array('pf_status','平台处理'),
               array('dealWith','商家处理'),
               array('refundTime','申时间请'),
           );
            foreach ($xlsData as $key => $value) {
                if ($value['type'] == 1) {
                    $xlsData[$key]['refundType'] = '取消订单';
                } else {
                    $xlsData[$key]['refundType'] = '售后退款';
                }
                switch ($value['pf_status']) {
                    case 0: {
                        if ($value['biz_status'] == 2) {
                            $xlsData[$key]['pf_status'] = '商家拒绝';
                        } else {
                            $xlsData[$key]['pf_status'] = '待处理';
                        }
                        break;
                    }
                    case 1:
                        $xlsData[$key]['pf_status'] = '已退款';
                        break;
                    case 2:
                        $xlsData[$key]['pf_status'] = '已拒绝';
                        break;
                    default:
                }
                if ($value['type'] == 1)
                {
                    $xlsData[$key]['dealWith'] = '订单取消';
                }else{
                    switch ($value['biz_status'])
                    {
                        case 0:
                            $xlsData[$key]['dealWith'] = '待处理';
                            break;
                        case 1:
                            $xlsData[$key]['dealWith'] = '已同意';
                            break;
                        case 2:
                            $xlsData[$key]['dealWith'] = '已拒绝';
                            break;
                        case 3:
                            $xlsData[$key]['dealWith'] = '商家已拒绝，但平台强制退款';
                        default:
                    }
                }
            }
            $dataExp = new DataExp();
            $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
        }
    }
    // 退款处理
    public function agressRefund()
    {
        $this->checkAjaxPrivelege('tk_04');
        $id = I('id');
        $payWay = I('payWay', 0);
        $money = I('money');
        $agree = I('agree', 1);
        $isExis = M('refund')->where(array(
            'id' => $id
        ))->find();
        
        if ($isExis['pf_status'] > 0) {
            $this->ajaxReturn(array(
                'status' => 0
            )); // 已经操作过了！
            return;
        }
        if ($isExis['biz_status'] == 2) {
            $this->ajaxReturn(array(
                'status' => 0
            )); // 商家已经拒绝了此订单
            return;
        }
        if (! $id || ! is_numeric($agree)) {
            $this->ajaxReturn(array(
                'status' => 0
            )); // 非法请求
            return;
        } else {
            $field = "o.orderId,o.orderNo,o.shopId,o.userId,o.needPay,o.voucherIds,o.createTime,u.userMoney,u.userScore";
            $info = M('orders')->field($field)
                ->where(array(
                'orderId' => $isExis['orderid']
            ))
                ->join('as o left join oto_users as u on o.userId=u.userId')
                ->find();
            
            if ($agree == 1) { // 同意退款
                M()->startTrans();
                $refundInfo = $isExis;
                $a = M('refund')->where(array(
                    'id' => $id
                ))->setField(array(
                    'pf_status' => 1,
                    'actual_money' => $money,
                    'pf_time' => date('Y-m-d H:i:s'),
                    'way' => $payWay
                ));
                
                // 更新退款状态
                $b = M('orders')->where(array(
                    'orderId' => $refundInfo['orderid']
                ))->setField(array(
                    'orderStatus' => '-5',
                    'isRead'=>0,
                    'lastMessTime'=>time()
                ));
                // 退款日志表
                $refundLog['orderId'] = $isExis['orderid'];
                $refundLog['userId'] = $refundInfo['userId'];
                $refundLog['mess'] = '平台已同意退款';
                $refundLog['time'] = date('Y-m-d H:i:s');
                $c = M('refund_log')->add($refundLog);
                
                // 订单最新日志
                $data = array();
                $data["orderId"] = $isExis['orderid'];
                $data["logContent"] = "平台已同意退款";
                $data["logUserId"] = $isExis['userId'];
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                M('log_orders')->add($data);
                
                // 添加余额变动记录
                if ($payWay == 0) {
                    // 退还余额时进行余额变动记录操作
                    $this->OperationMoneyRecord(2, $money, $info['orderNo'], 1, $info['userId'], $info['userMoney'] + $money, '', 0);
                    $m = M('users')->where(array(
                        'userId' => $info['userId']
                    ))->setInc('userMoney', $money);

                            //添加平台金额流水记录
                    $platRes = M('platfrom_account')->add(array(
                       'orderId'    =>  $info['orderId'],
                        'time'      =>  time(),
                        'income'    =>  -$info['needPay'],
                        'remark'    =>  '买家申请退款，商家和平台都同意',
                        'orderNo'   =>   $info['orderNo']
                    ));
                    //更新平台暂时的金额
                    $dataRes = M('data_tmp')->where('id=1')->setDec('value',$info['needPay']);
                    //2017.7.19 修正优惠券信息
                    if($info['voucherIds']){
                        $uservoucher_s = \Org\Order\Order::getInstance()->VoucherUser($info['voucherIds'],$info['createTime']);
                    }else{
                        $uservoucher_s = true;
                    }
                    if ($a && $b && $c && $m && $platRes && $dataRes && $uservoucher_s) {
                        M()->commit();
                        //添加消息  peng
                        D('ImproveAPI/Msg')->addMsgHook([
                            'moduleId'=>2,
                            'info'=>$info,
                            'changeType'=>-5
                        ]);
                        
                        // 退款后扣掉订单积分
                        $this->checkStock($info['orderId'], 0, 1); // 还原库存
                        $this->OperationScoreRecord(2, $info['shopId'], $money, $info['orderId'], 0, $info['userId']);
                        $this->ajaxReturn(array(
                            'status' => 1
                        ));
                    } else {
                        M()->rollback();
                        $this->ajaxReturn(array(
                            'status' => 0
                        ));
                    }
                } else {
                    M()->rollback();
                    $this->ajaxReturn(array(
                        'status' => 0
                    ));
                }
            } else {
                // 拒绝退款
                // 更新退款状态
                $b = M('orders')->where(array(
                    'orderId' => $isExis['orderid'],
                ))->setField(array(
                    'orderStatus' => -8,
                    'isRead'=>0,
                    'lastMessTime'=>time()
                ));
                
                // 更新商家收入
                $a = M('shops')->where(array(
                    'shopId' => $isExis['shopId']
                ))->setInc('bizMoney', $info['needPay']); // 收入
                                                                                                               
                // 退款日志表
                $refundLog['orderId'] = $isExis['orderid'];
                $refundLog['userId'] = $isExis['userId'];
                $refundLog['mess'] = '平台已拒绝了你的退款';
                $refundLog['time'] = date('Y-m-d H:i:s');
                $c = M('refund_log')->add($refundLog);
                
                // 订单最新日志
                $data = array();
                $data["orderId"] = $isExis['orderid'];
                $data["logContent"] = "平台已拒绝了退款";
                $data["logUserId"] = $isExis['userId'];
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                M('log_orders')->add($data);

                if ($a && $b && $c) {
                    M()->commit();
                    $this->ajaxReturn(array(
                        'status' => 1
                    ));
                } else {
                    M()->rollback();
                    $this->ajaxReturn(array(
                        'status' => 0
                    ));
                }
            }
        }
    }
    /**
     * 强制退款 2017.7.19
     * 修正fRefund() 请求功能
     */
    public function fcRefund(){
        $this->checkAjaxPrivelege('tk_04');
        $id = I('id');
        $result = \Org\Order\Order::getInstance()->PlatformCompelRefund($id,'平台已强制商家退款给买家！');
        if($result){
            $this->ajaxReturn(array(
                'status' => 1
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0
            ));
        }
    }
    /*
     * 魏永就
     * 16-12-18
     * 超管强制退款的后台程序
     */
    public function fRefund()
    {  
        $this->checkAjaxPrivelege('tk_04');
        $id = I('id');
        $payWay = I('payWay', 0);
        $money = I('money');
        $agree = I('agree', 1);
        $isExis = M('refund')->where(array(         //对表 refund 查询
            'orderid' => $id
        ))->find();

//        print_r($isExis);
//        die;
        if ($isExis['pf_status'] > 0) {
            $this->ajaxReturn(array(
                'status' => 0
            )); // 已经操作过了！
            return;
        }

        if ($isExis['biz_status'] != 2) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
            return;
        }
        if (! $id || ! is_numeric($agree)) {
            $this->ajaxReturn(array(
                'status' => 0
            )); // 非法请求
            return;
        } else {
            $field = "o.orderId,o.orderNo,o.shopId,o.userId,o.needPay,u.userMoney,u.userScore";
            $info = M('orders')->field($field)
                ->where(array(
                    'orderId' => $isExis['orderid']
                ))
                ->join('as o left join oto_users as u on o.userId=u.userId')
                ->find();
            if ($agree == 1) { // 同意退款
                M()->startTrans();
                $refundInfo = $isExis;
                $a = M('refund')->where(array(
                    'orderid' => $id
                ))->setField(array(
                    'pf_status' => 1,
                    'biz_status' => 3,          // biz 为 3 表示商家拒绝买家退款，而平台强制商家退款
                    'actual_money' => $money,
                    'pf_time' => date('Y-m-d H:i:s'),
                    'way' => $payWay
                ));
                // 更新退款状态
                $b = M('orders')->where(array(
                    'orderId' => $refundInfo['orderid']
                ))->setField(array(
                    'orderStatus' => '-5',
                    'isRead'=>0,
                    'lastMessTime'=>time()
                ));
                $complainRes = M('complain')->where(array(          //将投诉改为 “已处理状态"
                    'orderId' => $id
                ))->setField(array(
                    'isHandle'=> 1
                ));
                /**
                 * 魏永就
                 * 将商家的金额减回原来的
                 */
//                $info = M('orders')->where(array(
//                    'orderId' => $id
//                ))
//                    ->field('needPay,shopId')
//                    ->find();
//                print_r($info);
//                die;
//                $needPay = $info['needPay'];
//
                /**
                 * @author peng	
                 * @date 2016-12-20
                 * @descreption 判断拒绝退款是否已经确认，确认方可减掉商家退款余额
                 */
                if ($comfirm_info=M('autocomfirm')->where(['orderId'=>$id])->find()){
                    $e = M('shops')->where(array(
                    'shopId' => $info['shopId']
                    ))->setDec('bizMoney', $comfirm_info['bizIncome']);
                    if($e) M('autocomfirm')->delete($comfirm_info['id']);
                }else{
                    $e=true;
                }
                

                // 退款日志表
                $refundLog['orderId'] = $isExis['orderid'];
                $refundLog['userId'] = $refundInfo['userId'];
                $refundLog['mess'] = '平台已强制商家退款给买家';
                $refundLog['time'] = date('Y-m-d H:i:s');
                $c = M('refund_log')->add($refundLog);

                // 订单最新日志
                $data = array();
                $data["orderId"] = $isExis['orderid'];
                $data["logContent"] = "平台已强制商家退款给买家";
                $data["logUserId"] = $isExis['userId'];
                $data["logType"] = 0;
                $data["logTime"] = date('Y-m-d H:i:s');
                M('log_orders')->add($data);
                // 添加余额变动记录
                if ($payWay == 0) {
                    // 退还余额时进行余额变动记录操作
                    $this->OperationMoneyRecord(2, $money, $info['orderNo'], 1, $info['userId'], $info['userMoney'] + $money, '', 0);
                    $m = M('users')->where(array(
                        'userId' => $info['userId']
                    ))->setInc('userMoney', $money);
                    $platRes = M('platfrom_account')->add(array(        //添加平台流水
                       'orderId'=>$isExis['orderid'],
                        'time'=>time(),
                        'income'=> -$money,
                        'remark'=>'商家拒绝退款，平台强制退款给买家',
                        'orderNo'=>$info['orderNo']
                    ));
                    if($money)
                    {
                        $dataRes = M('data_tmp')->where('id=1')->setDec('value',$money);
                    }else{
                        $dataRes = true;
                    }
                    if ($a && $b && $c && $m && $e && $complainRes && $dataRes && $platRes) {

                        M()->commit();
                        // 退款后扣掉订单积分
                        $this->checkStock($info['orderId'], 0, 1); // 还原库存
                        $this->OperationScoreRecord(2, $info['shopId'], $money, $info['orderId'], 0, $info['userId']);

                        $this->ajaxReturn(array(
                            'status' => 1
                        ));
                    } else {
                        M()->rollback();
                        $this->ajaxReturn(array(
                            'status' => 0
                        ));
                    }
                } else {
                    M()->rollback();
                    $this->ajaxReturn(array(
                        'status' => 0
                    ));
                }
            }
        }
    }
    public function detail()
    {
        $id = I('id');
        $refundInfo = M('refund')->where(array(
            'id' => $id
        ))->find();
        $orderInfo = M('orders')->field('o.*,p.out_trade_no')
            ->where(array(
            'o.orderId' => $refundInfo['orderid']
        ))
            ->join('as o left join oto_pay_record as p on p.orderNo=o.orderNo')
            ->find();
        
        $goodInfo = M('order_goods')->where(array(
            'orderId' => $refundInfo['orderid']
        ))->select();
        
        foreach ($goodInfo as $k => $v) {
            $goodInfo[$k]['singleTotal'] = $v['goodsPrice'] * $v['goodsNums'];
        }
        
        $this->assign('refundInfo', $refundInfo);
        $this->assign('orderInfo', $orderInfo);
        $this->assign('goodInfo', $goodInfo);
        $this->display('refund/detail');
    }

    /**
     * 处理退款页面
     */
    public function toEditAccount()
    {
        $this->checkPrivelege('tk_04');
        $id = I('id');
        $this->isLogin();
        $refundInfo = M('refund')->where(array(
            'id' => $id
        ))->find(); // 还款信息
        $field = "o.*,p.out_trade_no";
        $orderInfo = M('orders')->field($field)
            ->where(array(
            'o.orderId' => $refundInfo['orderid']
        ))
            ->join('as o left join oto_pay_record as p on o.orderNo=p.orderNo ')
            ->find();
        $this->assign('orderInfo', $orderInfo);
        $this->assign('refund', $refundInfo);
        $this->gameName = M('orders as o')->where(array(
            'o.orderId' => $orderInfo['orderId']
        ))
            ->join('oto_order_goods as og on og.orderId=o.orderId')
            ->join('oto_game as g on g.id=og.gid')
            ->getField('g.gameName');
        $this->display("/refund/edit_account");
    }

    /*
     * 魏永就
     * 添加超管强制退款页面
     */
    public function forceRefund()
    {
        $this->checkPrivelege('tk_04');
        $id = I('id');
        $this->isLogin();
//        $refundInfo = M('refund')->where(array(
//            'orderid' => $id
//        ))->find(); // 还款信息  对表 refund 查询
        $field = "o.*,p.out_trade_no";
        $orderInfo = M('orders')->field($field)
            ->where(array(
                'o.orderId' =>$id
            ))
            ->join('as o left join oto_pay_record as p on o.orderNo=p.orderNo ')
            ->find();
        $this->assign('orderInfo', $orderInfo);
//        $this->assign('refund', $refundInfo);
        $this->gameName = M('orders as o')->where(array(
            'o.orderId' => $orderInfo['orderId']
        ))
            ->join('oto_order_goods as og on og.orderId=o.orderId')
            ->join('oto_game as g on g.id=og.gid')
            ->getField('g.gameName');
        $this->display("/refund/forceRefund");
    }
    // 金额操作记录
    /**
     * 构造函数
     *
     * @param $type 操作类型,1下单，2取消订单，3充值，4提现,
     *            5订单无效, 6贷款成功，7还款，8减少金额
     * @param $money 金额            
     * @param $orderid 订单ID或者充值ID            
     * @param $IncDec 余额变动
     *            0为减，1加
     * @param $userid 用户ID            
     * @param $balance 余额            
     */
    public function OperationMoneyRecord($type = '', $money = 0, $orderid = '', $IncDec = '', $userid = 0, $balance = 0, $remark = '', $payWay = 0)
    {
        $db = M('money_record');
        $data['type'] = $type;
        $data['money'] = $money;
        $data['time'] = time();
        $data['ip'] = get_client_ip();
        $data['orderNo'] = $orderid;
        $data['IncDec'] = $IncDec;
        $data['userid'] = $userid;
        $data['balance'] = $balance;
        $data['remark'] = $remark;
        $data['payWay'] = $payWay;
        $res = $db->add($data);
        return $res;
    }
    
    // 导出excel
    public function exprotExcel()
    {
        vendor('PHPExcel');
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle('Sheet1');
        $objActSheet->getDefaultStyle()
            ->getAlignment()
            ->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 设置excel文件默认水平垂直方向居中
        $objActSheet->getDefaultStyle()
            ->getFont()
            ->setSize(14)
            ->setName("微软雅黑"); // 设置默认字体大小和格式
        $objActSheet->getDefaultRowDimension()->setRowHeight(30); // 设置默认行高
        $objPHPExcel->getActiveSheet()
            ->getStyle('G')
            ->getNumberFormat()
            ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->getColumnDimension('G')->setWidth(35);
        $map = unserialize(cookie('searchCondition'));
        $field = "o.orderNo,r.money,r.pfstatus,r.status,r.time,o.payType,p.tradeNo";
        $db = M('refund');
        $info = $db->field($field)
            ->join("as r left join oto_orders as o on o.orderId=r.orderid left join oto_pay_record as p on p.orderNo=o.orderNo")
            ->where($map)
            ->select();
        $array = array(
            '订单号',
            '退款金额',
            '平台处理',
            '商家处理',
            '申请时间',
            '支付方式',
            '支付流水号'
        );
        $arr = $this->merge();
        for ($i = 1; $i <= count($arr); $i ++) {
            $objActSheet->setCellValue($arr[$i - 1] . '1', $array[$i - 1]);
        }
        foreach ($info as $k => $v) {
            switch ($v['status']) {
                case 0:
                    $info[$k]['status'] = '待处理';
                    break;
                case 1:
                    $info[$k]['status'] = '已同意';
                    break;
                case 2:
                    $info[$k]['status'] = '已拒绝';
                    break;
            }
            switch ($v['status']) {
                case 0:
                    $info[$k]['pfstatus'] = '待处理';
                    break;
                case 1:
                    $info[$k]['pfstatus'] = '已退款';
                    break;
                case 2:
                    $info[$k]['pfstatus'] = '已拒绝';
                    break;
            }
            switch ($v['payType']) {
                case 0:
                    $info[$k]['payType'] = '余额支付';
                    break;
                case 1:
                    $info[$k]['payType'] = '支付宝支付';
                    break;
                case 2:
                    $info[$k]['payType'] = '微信支付';
                    break;
            }
        }
        $count = 1;
        $num = 0;
        foreach ($info as $key => $val) {
            foreach ($val as $k => $v) {
                $num ++;
                $number = $arr[$num - 1];
                $font = $count + 1;
                
                if ($arr[$num - 1] == 'G') {
                    $objActSheet->setCellValue($number . $font, ' ' . $v);
                } else {
                    $objActSheet->setCellValue($number . $font, $v);
                }
                if ($number == 'E') {
                    $objActSheet->setCellValue($number . $font, date("Y-m-d H:i:s", $v));
                }
            }
            $num = 0;
            $count ++;
        }
        $filename = "退款记录";
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        header("Content-Type: application/vnd.ms-excel;");
        header("Content-Disposition:attachment;filename={$filename}" . date('Y-m-d', mktime()) . ".xls");
        header("Pragma:no-cache");
        header("Expires:0");
        $objWriter->save('php://output');
    }

    /**
     * 生成A-Z之间的所有英文字母
     * 
     * @return array
     */
    public function merge()
    {
        $arr = range('A', 'Z');
        return $arr;
    }
    
    // 积分操作记录
    /**
     * 构造函数
     *
     * @param $type 1购物，2取消订单，3充值，4订单无效，5活动,6评价订单
     *            ，6评价订单+1分
     * @param $score 积分            
     * @param $shopid 店铺ID            
     * @param $orderid 订单ID或者充值ID            
     * @param $IncDec 积分变动
     *            0为减，1加
     * @param $userid 用户ID            
     *
     */
    public function OperationScoreRecord($type = '', $shopid, $payMoney = 0, $orderid = '', $IncDec = '', $userid = 0)
    {
        $score = floor($payMoney);
        if ($score <= 0) {
            return;
        }
        $fen = 0;
        $userinfo = M('users')->where(array(
            'userId' => $userid
        ))->find();
        $scoreSetting = M('score_setting')->find(); // 查询积分配置
        if ($type == 1) {
            if ($scoreSetting['xiaofei'] == - 1) {
                $fen = $score;
            } else {
                $fen = $scoreSetting['xiaofei'];
            }
        } elseif ($type == 3) {
            if ($scoreSetting['chongzi'] == - 1) {
                $fen = $score;
            } else {
                $fen = $scoreSetting['chongzi'];
            }
        } elseif ($type == 6) {
            if ($scoreSetting['pingjia'] == - 1) {
                $fen = $score;
            } else {
                $fen = $scoreSetting['pingjia'];
            }
        } else 
            if ($type == 2) {
                if ($scoreSetting['xiaofei'] == - 1) {
                    $fen = $score;
                } else {
                    $fen = $scoreSetting['xiaofei'];
                }
            }
        if ($IncDec == 0) {
            $totalscore = $userinfo['userScore'] - $fen;
            M('users')->where(array(
                'userId' => $userid
            ))->setDec('userScore', $fen); // 更新积分
            M('users')->where(array(
                'userId' => $userid
            ))->setDec('userTotalScore', $fen); // 更新总积分
        } else {
            $totalscore = $userinfo['userScore'] + $fen;
            M('users')->where(array(
                'userId' => $userid
            ))->setInc('userScore', $fen); // 更新积分
            M('users')->where(array(
                'userId' => $userid
            ))->setInc('userTotalScore', $fen); // 更新总积分
        }
        $db = M('score_record');
        $data['score'] = $fen;
        $data['type'] = $type;
        $data['time'] = time();
        $data['ip'] = get_client_ip();
        $data['orderNo'] = $orderid;
        $data['IncDec'] = $IncDec;
        $data['userid'] = $userid;
        $data['totalscore'] = $totalscore;
        $res = $db->add($data);
        return $res;
    }
    
    // 付款成功后操作库存
    // $type操作1为加库存，0为减库存
    /**
     * 构造函数
     * 
     * @param $type 操作1为加库存，0为减库存            
     */
    public function checkStock($orderID = 0, $orderNo = 0, $type = 0)
    {
        $id = 0;
        if ($orderID) {
            $id = $orderID;
        } else {
            $map['orderNo'] = $orderNo;
            $id = M('orders')->where($map)->getField('orderId');
        }
        $info = M('order_goods')->where(array(
            'orderId' => $id
        ))
            ->field('goodsId,goodsNums')
            ->select();
        $db = M('goods');
        if ($type == 1) {
            foreach ($info as $k => $v) {
                $db->where(array(
                    'goodsId' => $v['goodsId']
                ))->setInc('goodsStock', $v['goodsNums']);
            }
        } else {
            foreach ($info as $k => $v) {
                $db->where(array(
                    'goodsId' => $v['goodsId']
                ))->setDec('goodsStock', $v['goodsNums']);
            }
        }
    }
}