<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 分销模块控制器
 */
class AgentController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function historyOrderIndex()
    {
        $this->isLogin();
        $m = D('Admin/agent');
        $page = $m->historyOrderqueryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        $this->assign('orderNo', I('orderNo'));
        
        $this->assign('orderStatus', I('orderStatus', - 9999));
        
        $this->display("agent/historyOrderList");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 订单数据导出
     * 历史订单导出
     */
    public function historyOrderExp()
    {
        $this->isLogin();
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == ''||$end == ''|| $start >= $end)
        {
            $this->assign('msg','<font color=red>时间未选择或终始时间相同</font>');
            $this->historyOrderIndex();
            die;
        }
        $xlsCell = array(
            array('num','序号'),
            array('orderNo','订单'),
            array('shopName','店铺'),
            array('scope','类型-游戏名-版本'),
            array('needPay','需要付款金额'),
            array('payType','付款类型'),
            array('createTime','订单时间'),
            array('orderS','状态'),
        );
        $where = " and UNIX_TIMESTAMP(o.createTime) >= $start and UNIX_TIMESTAMP(o.createTime) <= $end";
        $xlsModel = M('goods');
        $sql = "select o.signTime,o.orderType,o.payType,o.needPay,o.orderId,o.orderNo,og.goodsName,ga.gameName,v.vName,o.createTime,s.shopName,o.orderStatus,og.goodsThums
        from __PREFIX__orders as o left join __PREFIX__shops as s on o.shopId=s.shopId left join __PREFIX__order_goods as og
        on og.orderId=o.orderId  left join __PREFIX__game as ga on ga.id=og.gid
        left join __PREFIX__versions as v on v.id=og.vid where  o.isAgent=1  $where   GROUP BY o.orderId   order by o.orderId desc ";
//        de($sql);
        $xlsData = $xlsModel->query($sql);
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->historyOrderIndex();
            die;
        }
        foreach ($xlsData as $key => $value)
        {
            $xlsData[$key]['num'] = $key + 1;
            switch ($value['orderType'])
            {
                case 1:
                    $xlsData[$key]['scope'] = '首充号-'.$value['gameName'].'-'.$value['vName'];
                    break;
                case 2:
                    $xlsData[$key]['scope'] = '首充号代充-'.$value['gameName'].'-'.$value['vName'];
                    break;
                case 3:
                    $xlsData[$key]['scope'] = '首充号分销-'.$value['gameName'].'-'.$value['vName'];
                    break;
                case 4:
                    $xlsData[$key]['scope'] = '自主充值-'.$value['gameName'].'-'.$value['vName'];
                    break;
            }
            if($value['orderStatus'] > 0)
            {
                if($value['payType'] == 2)
                {
                    $xlsData[$key]['payType'] = '微信支付';
                }else{
                    $xlsData[$key]['payType'] = '余额支付';
                }
            }else{
                $xlsData[$key]['payType'] = '待付款';
            }
            switch ($value['orderStatus'])
            {
                case 1:
                    $xlsData[$key]['orderS'] = '等待发货';
                    break;
                case -2:
                    $xlsData[$key]['orderS'] = '待付款';
                    break;
                case 2:
                    $xlsData[$key]['orderS'] = '已发货';
                    break;
                case 3:
                    $xlsData[$key]['orderS'] = '已完成';
                    break;
            }
        }
        $xlsName = 'historyOrders';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    public function historyOrderView()
    {
        $this->isLogin();
        $m = D('Admin/agent');
        if (I('id') > 0) {
            $object = $m->historyOrderViewGetDetail();
            $this->assign('object', $object);
        }
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        
        $this->display("agent/historyOrderView");
    }

    public function usersIndex()
    {
        $this->isLogin();
        $this->checkPrivelege('hylb_00');
        $m = D('Admin/agent');
        $page = $m->usersQueryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        $this->assign('loginName', I('loginName'));
        $this->assign('userPhone', I('userPhone'));
        $this->assign('userEmail', I('userEmail'));
        $this->assign('userType', I('userType', - 1));
        $this->assign('Page', $page);
        
        $this->display("agent/usersList");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 导出分销会员数据
     */
    public function userListExp()
    {
        $this->isLogin();
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == ''||$end == ''|| $start >= $end)
        {
            $this->assign('msg','<font color=red>时间未选择或终始时间相同</font>');
            $this->usersIndex();
            die;
        }
        $xlsCell = array(
            array('userId','用户ID'),
            array('loginName','账号'),
            array('userName','用户名'),
            array('userPhone','手机号码'),
            array('fansCount','粉丝分量'),
            array('partnerId','推荐人'),
            array('agentTotalPrice','总佣金'),
            array('agentBalance','可提现'),
            array('agentWaitPrice','申请中'),
            array('agentPayPrice','已提现'),
            array('createTime','注册时间'),
            array('userStatus','状态'),
        );
        $xlsModel = D('Admin/agent');
        $xlsData = $xlsModel->dataSelect($start,$end);
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->usersIndex();
            die;
        }
        $xlsName = 'userList';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);

    }

    public function usersEdit()
    {
        $this->isLogin();
        $this->checkPrivelege('hylb_00');
        $m = D('Admin/agent');
        
        if (IS_POST) {
            $result = $m->usersToEdit();
            $this->ajaxReturn($result);
        } else {
            if (I('id') > 0) {
                $object = $m->usersGet();
                $this->assign('object', $object);
            }
        }
        
        $this->display('agent/usersEdit');
    }

    public function usersMoreInfo()
    {
        $m = D('Admin/agent');
        
        $results = $m->usersMoreResult();
        
        $this->assign('results', json_encode($results));
        
        $this->display('agent/usersMoreInfoList');
    }

    /**
     * ***********************agent_order_log_controller_star****************************
     */
    public function revenueLogIndex()
    {
        $this->isLogin();
        $this->checkAjaxPrivelege('ddlb_00');
        
        $m = D('Admin/Agent');
        $page = $m->revenueQueryByPage();
        
        $pager = new \Think\Page($page['total'], $page['pageSize']);
        $page['pager'] = $pager->show();
        // dump($page);
        
        $this->assign('Page', $page);
        $this->assign('shopName', I('shopName'));
        
        $this->display("agent/revenueLogList");
    }

    /**
     * 查看订单详情
     */
    public function revenueToView()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $m = D('Admin/Orders');
        if (I('id') > 0) {
            $object = $m->getDetail();
            $this->assign('object', $object);
        }
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->display("/orders/view");
    }

    /**
     * ***********************agent_order_log_controller_end****************************
     */
    
    /**
     * ******************agent_order_controller_star*************************
     */
    public function orderIndex()
    {
        $this->isLogin();
        // $this->checkAjaxPrivelege('ddlb_00');
        
        $Agent = D('Admin/Agent');
        
        $tempreslut = $Agent->orderCheckStatus();
        $page['root'] = $tempreslut;
        
        $pager = new \Think\Page(count($page), 20);
        
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        
        $this->display("agent/orderList");
    }

    public function orderAction()
    {
        $this->isLogin();
        
        $Agent = D('Admin/Agent');
        
        $rs = $Agent->orderActionReturnMoney();
//        de($rs);
        $this->ajaxReturn($rs);
    }

    /**
     * 查看订单详情
     */
    public function orderToView()
    {
        $this->isLogin();
        $this->checkPrivelege('ddlb_00');
        $m = D('Admin/Agent');
        if (I('id') > 0) {
            $object = $m->orderGetDetail();
            
            $this->assign('object', $object);
        }
        $this->assign('referer', $_SERVER['HTTP_REFERER']);
        $this->display("/agent/orderView");
    }

    public function text()
    {
        $uid = '123';
        $ee = _encrypt($uid);
        dump($ee);
        
        $rrr = _encrypt($ee, 'DECODE');
        dump($rrr);
        
        // _setcookie('agent',null);
        // echo _getcookie('agent');
    }

    public function textfun()
    {
        $re = D('Admin/Agent')->orderCheckopen();
        
        if ($re['status'] == '1') {
            
            $rs = 0;
            if ((int) I('id', 0) > 0) {
                $orderId = (int) I('id', 0);
                $rs = D('Admin/Agent')->orderAgentAction($orderId);
            }
        } else {
            $rs = - 1;
        }
        // $this->display();
        $this->ajaxReturn($rs);
    }
    
    // 计算出来的商品数量*商品价格 不能转换成 浮点型
    public function toAgentOrdersAction($orderId = 25)
    {
        $list = array();
        echo '<meta charset="UTF-8">';
        $order = D('Admin/Agent')->orderCountPrice($orderId);
        
        $orderInfo = D('Admin/Agent')->orderInfo($orderId);
        $agentset = D('Admin/Agent')->orderAgentSetInfo();
        $var = explode("|", $agentset['agentProportion']);
        
        // dump($order);
        
        $user = D('Admin/Agent')->orderUserInfo($orderInfo['partnerId']);
        // $star=M()->startTrans;
        foreach ($order as $key2 => $value2) {
            foreach ($user as $key => $value) {
                $list[$key2][$key] = $value2;
                $list[$key2][$key] = array_merge($list[$key2][$key], $value);
                $addtime['addTime'] = time();
                $list[$key2][$key] = array_merge($list[$key2][$key], $addtime);
                
                $agentCount['agentCount'] = (string) (($var[$key] * 0.01) * $list[$key2][$key]['agentPrice']);
                $list[$key2][$key] = array_merge($list[$key2][$key], $agentCount);
                
                $agentAdminProportion['agentAdminProportion'] = $var[$key];
                
                $list[$key2][$key] = array_merge($list[$key2][$key], $agentAdminProportion);
                
                $whatOk = M('agentRevenueLog')->add($list[$key2][$key]);
                
                if ($whatOk) {
                    
                    M('users')->where(array(
                        'userId' => $list[$key2][$key]['userId']
                    ))->setInc('agentTotalPrice', $agentCount['agentCount']);
                    // echo M()->_sql();
                } else {
                    M()->rollback();
                    return false;
                }
            }
        }
        
        return true;
    }

    private function getMenuTree($arrCat, $parent_id = 0, $level = 0, $agentLevel = 0)
    {
        static $arrTree = array(); // 使用static代替global
        if (empty($arrCat))
            return FALSE;
        $level ++;
        foreach ($arrCat as $key => $value) {
            if ($value['partnerId'] == $parent_id) {
                $value['level'] = $level;
                $arrTree[] = $value;
                unset($arrCat[$key]); // 注销当前节点数据，减少已无用的遍历
                if ($level < $agentLevel) {
                    self::getMenuTree($arrCat, $value['userId'], $level, $agentLevel);
                }
            }
        }
        
        return $arrTree;
    }

    /**
     * ******************agent_order_controller_end*************************
     */
    
    /**
     * ************agent_apply_controller_star*******************
     */
    // 分销模块申请提现列表
    public function applyIndex()
    {
        $m = D('Admin/Agent');
        $applyData = $m->applyList();
        $this->assign('applyLog', json_encode($applyData));
        // $this->display("agentapply/list");
        $this->display("agent/applyList");
    }
    /*
     * 二次开发
     * 编写者 魏永就
     * 提现数据导出
     */
    public function applyExp()
    {
        $start = strtotime(I('post.timeStart'));
        $end = strtotime(I('post.timeEnd'));
        if($start == ''|| $end == '' || $start >= $end)
        {
            $this->assign('msg','<font color=red>时间未选择或终始时间相同</font>');
            $this->applyIndex();
            die;
        }
        $where = " time >= $start and time <= $end";
        $xlsModel = M('agentApply');
        $xlsData = $xlsModel->field('userId,applyType,applyPrice,bankName,bankNum,bankUserName,tel,FROM_UNIXTIME(time),status')->where($where)->select();
        if(!$xlsData)
        {
            $this->assign('msg','<font color=red>没有数据符合您选择的时间</font>');
            $this->applyIndex();
            die;
        }
        $xlsCell = array(
            array('num','序号'),
            array('userId','用户ID'),
            array('applyType','申请类型'),
            array('applyPrice','申请金额'),
            array('bankName','银行名称'),
            array('bankNum','银行卡号'),
            array('bankUserName','姓名'),
            array('tel','电话'),
            array('FROM_UNIXTIME(time)','申请时间'),
            array('status','状态'),
        );
        foreach ($xlsData as $key => $value)
        {
            switch ($value['status'])
            {
                case 0;
                    $xlsData[$key]['status'] = '待处理';
                    break;
                case 1:
                    $xlsData[$key]['status'] = '处理中';
                    break;
                case 2:
                    $xlsData[$key]['status'] = '通过';
                    break;
                case 3:
                    $xlsData[$key]['status'] = '不通过';
                    break;
                default:
                    break;
            }
            switch ($value['applyType'])
            {
                case 0:
                    $xlsData[$key]['applyType'] = '银行转账';
                    break;
                case 1:
                    $xlsData[$key]['applyType'] = '充值金额';
                    break;
                default:
                    break;
            }
            $xlsData[$key]['num'] = $key + 1;
        }
        $xlsName = 'apply';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }
    
    // 分销模块申请提现操作
    public function applyEdit()
    {
        $m = D('Admin/Agent');
        
        $data = $m->applyCheckEdit();
        
        $this->ajaxReturn($data);
    }

    /**
     * ************agent_apply_controller_end*******************
     */
    
    /* agent_setting_controller_star */
    // 分销设置列表
    public function settingIndex()
    {
        $this->isLogin();
        $this->checkPrivelege('fxsz_00');
        // 获取地区信息
        $m = D('Admin/Agent');
        $page = $m->settingQueryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/agent/settingList");
    }
    /*
     *二次开发
     * 编写者 魏永就
     * 导出分销set数据
     */
    public function settingExp()
    {
        $this->isLogin();
        $this->checkPrivelege('fxsz_00');
        $xlsModel = M('agentset');
        $sql = "select * from oto_agentset";
        $xlsData = $xlsModel->query($sql);
        $xlsCell = array(
            array('num','序号'),
            array('agentLevel','返利级数'),
            array('agentProportion','返利比例'),
            array('minApplyPrice','提现最小金额'),
            array('maxApplyPrice','提现最大金额'),
            array('applyDay','申请间隔天数'),
            array('applyPw','提现密码'),
            array('status','状态'),
        );
        foreach ($xlsData as $key => $value )
        {
            if($value['applyPw'] == 1)
            {
                $xlsData[$key]['applyPw'] = '需要';
            }else{
                $xlsData[$key]['applyPw'] = '不需要';
            }
            if($value['status'] == 1)
            {
                $xlsData[$key]['status'] = '开启';
            }else{
                $xlsData[$key]['status'] = '关闭';
            }
            $xlsData[$key]['num'] = $key + 1;
        }
        $xlsName = 'setting';
        $dataExp = new DataExp();
        $dataExp->exportExcel($xlsName,$xlsCell,$xlsData);
    }

    /**
     * 显示分销模块是否显示/隐藏
     */
    public function settingEditIsShow()
    {
        $this->isAjaxLogin();
        $this->checkAjaxPrivelege('fxsz_00');
        $m = D('Admin/Agent');
        $rs = $m->settingEditIsStatus();
        $this->ajaxReturn($rs);
    }

    /**
     * 新增/修改操作
     */
    public function settingEdit()
    {
        $this->isAjaxLogin();
        $m = D('Admin/Agent');
        
        $this->checkAjaxPrivelege('fxsz_00');
        $rs = $m->settingEdit();
        $this->ajaxReturn($rs);
    }

    /**
     * 跳到新增/编辑页面
     */
    public function settingtoEdit()
    {
        $this->isLogin();
        $m = D('Admin/Agent');
        $object = array();
        
        $this->checkPrivelege('fxsz_00');
        $object = $m->settingGet();
        
        $this->assign('object', $object);
        $this->view->display('/agent/settingEdit');
    }
    /* agent_setting_controller_end */
}
;
?>
