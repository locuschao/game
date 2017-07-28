<?php
/**
 * @author peng	
 * @date 2017-01-03
 * @descreption 代理推广模块
 */
namespace Admin\Controller;

class PlateformAgentController extends BaseController
{
    public function index(){
        $this->assign('data',M('profit_setting')->order('tg_level,gm_level')->select());
    	$this->display();
    }
    public function setting(){
        $post=I('post.');
        if($post['return_profit']<0){
            $this->error('金额不能是负数');
        }
        if(!is_numeric($post['return_profit'])){
            $this->error('金额必须是数字');
        }
        if($post['return_profit']<0.01){
            $this->error('金额必须大于0.01');
        }
    	if($post['id']){
    	   if(M('profit_setting')->where(['id'=>$post['id']])->save(['return_profit'=>$post['return_profit']])){
    	       $this->success('设置成功!');
    	   }else{
    	       $this->error('操作失败！');
    	   }
    	}
    }
    public function orderIndex()
    {
        $this->isLogin();
        // $this->checkAjaxPrivelege('ddlb_00');
        
        $Agent = D('Admin/Agent');
        
        $tempreslut = $Agent->orderCheckStatus();
        $page['root'] = $tempreslut;
        
        $pager = new \Think\Page(count($page), 20);
        //echo '<pre>';
//        var_dump($page);
//        echo '</pre>';
//        exit;
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        
        $this->display("PlateformAgent/orderList");
    }
    public function orderAction()
    {
        $this->isLogin();
        $re = D('Admin/Orders')->fenchengHandle(intval(I('post.orderId')));
        if($re['status'] == 1){
            $this->success('操作成功！');
        } else {
            $this->error($re['info']);
        }

        
    }
}