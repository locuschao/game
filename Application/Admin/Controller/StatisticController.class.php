<?php
/**
 * @author peng	
 * @date 2016-12-28
 * @descreption 总后台统计模块
 */
namespace Admin\Controller;
class StatisticController extends BaseController{
    /**
     * @author peng
     * @date 2017-01
     * @descreption 支付成功或失败的每天统计
     */
    public function index(){
        #默认时间区间是最新一个月的情况
        $get=I('get.');
        
        $init_date=[
            'start'=>date('Y-m-d',strtotime('-1 month',time())),
            'end'=>date('Y-m-d')
        ];
        $search_key['end_date']=$get['end_date']?:$init_date['end'];
        $search_key['start_date']=$get['start_date']?:$init_date['start'];
        $condition=[
            'paytime'=>[['lt',strtotime('+1 day',strtotime($search_key['end_date']))],['egt',strtotime($search_key['start_date'])]]
        ];
        
        $orders=M('orders');
        
        $table_header=[
        'count_day'=>'统计时间',
        'pay_amount'=>'支付总额',
        'success_amount'=>'成功支付总额',
        'fail_amount'=>'失败支付总额'
        ];
        
        $page = new \Think\Page($orders->table($orders->where($condition)->group("FROM_UNIXTIME(paytime, '%Y%m%d')")->select(false).' a')->count(), 10); // 实例化分页类 传入总记录数和每页显示的记录数
        
        $orders->field("FROM_UNIXTIME(paytime, '%Y-%m-%d') count_day,
        sum(needPay) pay_amount,
    	sum(
            if (orderStatus in (-4,-7), needPay,0)
    	) fail_amount,
    	sum(
            if (orderStatus not in (-4,-7),needPay,0)
    	) success_amount")
        ->where($condition)
        ->group("FROM_UNIXTIME(paytime, '%Y%m%d')")
        ->order('paytime desc');
        
        if($get['export']){
            
            if(!$data=$orders->select()) $this->error('没有可导出数据');
            
            D('Admin/Statistic')->exportExcel([
                'header'=>$table_header,
                'data'=>$data,
                'file_name'=>'支付成功失败统计_'.date('Y-m-d')
            ]);
            exit;
        }
        
        $this->assign('data',$orders->limit($page->firstRow,$page->listRows)->select());
        
        $this->assign('table_header',$table_header);
        $this->assign('init_date', $init_date);
        $this->assign('search_key', $search_key);
        $this->assign('page', $page->show());
    	$this->display();                
        
    }
    /**
     * @author peng
     * @date 2017-01
     * @descreption 各种方式的每天统计
     */
    public function paymentCount(){
        
        #默认时间区间是最新一个月的情况
        $get=I('get.');
        
        $init_date=[
            'start'=>date('Y-m-d',strtotime('-1 month',time())),
            'end'=>date('Y-m-d')
        ];
        $search_key['end_date']=$get['end_date']?:$init_date['end'];
        $search_key['start_date']=$get['start_date']?:$init_date['start'];
        $condition=[
            'paytime'=>[['lt',strtotime('+1 day',strtotime($search_key['end_date']))],['egt',strtotime($search_key['start_date'])]]
        ];
        
        $orders=M('orders');
        
        $table_header=[
            'count_day'=>'统计时间',
            'pay_amount'=>'支付总额',
            'alipay'=>'支付宝支付',
            'weixin_pay'=>'微信支付',
            'balance_pay'=>'余额支付'
        ];
        
        $page = new \Think\Page($orders->table($orders->where($condition)->group("FROM_UNIXTIME(paytime, '%Y%m%d')")->select(false).' a')->count(), 10); // 实例化分页类 传入总记录数和每页显示的记录数
        
        $orders->field("FROM_UNIXTIME(paytime, '%Y-%m-%d') count_day,
       	sum(needPay) pay_amount,
        sum(
           if (payType = 1, needPay,0)
           ) alipay,
        sum(
           if (payType = 2, needPay,0)
           ) weixin_pay,
        sum(
           if (payType = 3, needPay,0)
           ) balance_pay")
        ->where($condition)
        ->group("FROM_UNIXTIME(paytime, '%Y%m%d')")
        ->order('paytime desc');
        
        
        if($get['export']){
            
            if(!$data=$orders->select()) $this->error('没有可导出数据');
            
            D('Admin/Statistic')->exportExcel([
                'header'=>$table_header,
                'data'=>$data,
                'file_name'=>'各种支付方式统计_'.date('Y-m-d')
                
            ]);
            exit;
        }
        $this->assign('data',$orders->limit($page->firstRow,$page->listRows)->select());
        
        $this->assign('table_header',$table_header);
        $this->assign('init_date', $init_date);
        $this->assign('search_key', $search_key);
        $this->assign('page', $page->show());
    	$this->display();
    }
    /**
     * @author peng
     * @date 2017-01
     * @descreption 综合统计
     */
    public function countByPayType(){
        
    	$data=M('orders')->where(['paytime'=>['gt',0]])
        ->field('payType,sum(needPay) payAmount')
        ->group('payType')
        ->select();
        foreach(M('money_record')
            ->field('payWay,sum(money) money')
            ->where([
            'type'=>3,
            'time'=>['egt',strtotime('2017-01-24')]
            ])
            ->group('payWay')
            ->select() as $row){
            $deposit[$row['payWay']]=$row['money'];//充值的
        }
        
        $sum=0;
        foreach($data as $k=>$row){
            $data[$k]['payAmount']+=$deposit[$row['payType']];
            $sum+=$data[$k]['payAmount'];
            $data[$k]['payAmount']=number_format($data[$k]['payAmount'],2);
            
        }
        
        $this->assign('sum1',number_format($sum,2));
        $this->assign('data',$data);
        
        $tixian=M('tixian')->sum('txMoney');
        $ps_tixian=M('ps_tixian')->sum('txMoney');
        
        $this->assign('txMoneyAmount',[
            'user'=>number_format($tixian,2),
            'shop'=>number_format($ps_tixian,2),
            'sum'=>number_format($tixian+$ps_tixian,2)
        ]);
        $userMoney_user=M('users')->sum('userMoney');
        $bizMoney_shop=M('shops')->sum('bizMoney');
        $plateForm_money = M('data_tmp')->where(['key'=>'platform_money'])->find()['value'];#平台余额
        $this->assign('bizMoney',[
            'user'=>number_format($userMoney_user,2),
            'shop'=>number_format($bizMoney_shop,2),
            'plateForm_money'=>number_format($plateForm_money,2),
            'sum'=>number_format($userMoney_user+$bizMoney_shop+$plateForm_money,2)
        ]);
        $this->assign('pay_type_text',['货到付款','支付宝','微信','余额','积分']);
        $this->display();
        
    }
    
}