<?php
/**
 * @author peng	
 * @date 2016-12-28
 * @descreption 一个计划任务
 */
namespace Api\Controller;
use Think\Controller;
class StatisticsCronController extends Controller {
    
    #每天凌晨开始统计昨天的支付总额和成功总额
    public function countPayAmount(){
        #目前此方案作废
        exit();
        $this->checkAuth();
        D('Api/Util')->_countPayAmount();
    }
    
    public function checkAuth(){
        $get=I('get.');
        if(date('YmdH',$get['timestamp'])!=date('YmdH'))
        exit('timestamp wrong');
        if(D('Common/Util')->createSignture($get['nonce'],$get['timestamp'])!=$get['sign'])
        exit('sign wrong');
    	
    }
}