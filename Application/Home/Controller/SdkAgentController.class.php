<?php
namespace Home\Controller;

/**
 * @author peng
 * @copyright 2016
 * @remark 商家绑定代理
 */
class SdkAgentController extends BaseController
{
    
    public function index(){
        $this->isLogin();
        $this->assign('bind_info',M('shop_agent')->where(['shop_id'=>session('WST_USER.shopId')])->find());
    	$this->display("shops/sdkAgent/index");
    }
    public function doBind(){
        $this->isLogin();
        
        $username=I('username');
        if(!$username){
            $this->error('用户名不能空');
        }
        if(!I('password')){
            $this->error('密码不能空');
        }
        if(!I('code')){
            $this->error('验证码不能空');
        }
        if(!$this->checkCodeVerify(false)){
            $this->error('验证码错误');
        }
    	$result_info=D('Home/SdkAgent')->checkAgent([
                //业务参数
                //'username'=>I('username'),
                'username'=>$username,
                'password'=>I('password')
                
                
        ]);
        
        if($result_info['error']==0){
            //执行绑定
            $shop_id=session('WST_USER.shopId');
            //$post=I('post.');
            $post['shop_id']=$shop_id;
            $post['agent_id']=$result_info['msg']['id'];
            $post['agentname']=$username;
            
            if(!$post['shop_id']){
                $this->error('店铺id不能空');
            }
            
             //解除绑定
            if ($bind_id=M('shop_agent')->where(['shop_id'=>$shop_id,'agentname'=>$username])->getField('id')){
                if (M('shop_agent')->delete($bind_id))
                $this->success('解除绑定成功');
                else
                $this->success('解除绑定失败');
            }else{
                if (M('shop_agent')->add($post)) {
                    $this->success('成功绑定');
                }else{
                    $this->error('绑定失败');
                }
            }
            
            
            
        }else{
            $this->error('验证失败');
        }
        
        
       
    }
    
}