<?php
namespace Home\Controller;
/**
 * TT代理
 */
class TTAgentController extends BaseController
{
    public function index(){
        $this->isLogin();
        $condition = [
        'shopId'=>session('WST_USER.shopId'),
        'versionId'=>I('versionId')
        ];
        $bind_info = M('shop_version_agent')
        ->join('sva left join '.C('DB_PREFIX').'agent a on sva.agentId=a.id')->where($condition)->find();
        
        $this->assign('bind_info',$bind_info);
        $condition['status'] = ['neq',4];
        $status = M('update_bind')->where($condition)->find()['status'];
        if($status == 2 && $bind_info) { #可以解除绑定
            $mode = 1;
        }else if(!$bind_info){ #可以执行绑定
            $mode = 2;
        }else{ #可以申请解除绑定
            $mode = 3;
        }     
        $this->assign('mode',$mode);
    	$this->display("shops/TTAgent/index");
    }
    public function doBind(){
        $this->isLogin();
        $post = I('');
        $username=$post['username'];
        if(!$username){
            $this->error('用户名不能空');
        }
        if(!$post['password']){
            $this->error('密码不能空');
        }
        if(!$post['pay_pwd']){
            $this->error('支付密码不能空');
        }
        //if(!I('code')){
//            $this->error('验证码不能空');
//        }
//        if(!$this->checkCodeVerify(false)){
//            $this->error('验证码错误');
//        }
        $sdk_type_arr = [
            39=>1,
            41=>2
        ];
        $version_id = I('versionId');
        $shop_id=session('WST_USER.shopId');
        $condition = [
            'versionId'=>$version_id,
            'shopId'=>$shop_id
        ];
        $condition1 = [
            'status'=>['neq',4],
            'versionId'=>$version_id,
            'shopId'=>$shop_id
        ];
        $apply_info = M('update_bind')->where($condition1)->find();
        
        $autobase = D('Api/AutoBase');
        
        if($cookie_str = D('Api/TcoinBase')->getCookie([
                'username'=>$username,
                'login_pwd'=>$post['password']
         ])){
            if($apply_info['status'] == 2){ #解绑
                if(M('shop_version_agent')->where($condition)->delete()){
                    $condition['status'] = ['neq',4];
                    M('update_bind')->where($condition1)->save([
                        'status'=>4
                    ]);
                    $this->success('解绑成功');
                }else{
                    $this->error('解绑失败');
                }
            }else{  #绑定
                if(M('shop_version_agent')->where($condition)->find()){
                    $this->error('已经绑定代理');
                }
                
                if($agent_info = M('agent')->where([  #已经存在代理
                'username'=>$username,
                'sdk_type'=>$sdk_type_arr[$version_id]
                ])->find()){
                    $agent_id = $agent_info['id'];
                }else{   #添加存在代理
                    
                    if(!$agent_id = M('agent')->add([
                        'username'=>$username,
                        'login_pwd'=>$autobase->encrypt($post['password']),
                        'pay_pwd'=>$autobase->encrypt($post['pay_pwd']),
                        'sdk_type'=>$sdk_type_arr[$version_id],
                        'cookie_str'=>$cookie_str
                    ])){
                        $this->error('添加代理失败');
                    }
                    
                }
                if(M('shop_version_agent')->add([
                    'shopId'=>$shop_id,
                    'versionId'=>$version_id,
                    'agentId'=>$agent_id,
                    
                ])){
                    $this->success('成功绑定');
                }else{
                    $this->error('绑定失败');
                }
            }
            
         }else{
            
            $this->error('验证失败');
        }
        
     }
     public function applyList() {
         $this->display("shops/TTAgent/applyList");  
     }
     public function applyUpdate() {
        $re = D('Home/TTAgent')->applyUpdate();
        if($re['status'] == 1){
            $this->success($re['info']);
        }else{
            $this->error($re['info']);
        }
     }
     
}