<?php
namespace ImproveAPI\Model;
use Think\Model;
class LoginModel extends Model
{
    public function setLoginSession($userid) {
        $_SESSION['userId'] = $userid;
        $_SESSION['login_expire'] = time()+3600*24*10;//10天有效
    }
    
    public function isLogin() {
        if(time()>$_SESSION['login_expire']){
            return [
                'status'=>0,
                'info'=>'token过期'
            ];
        }else if(!$_SESSION['userId']){
           return [
                'status'=>0,
                'info'=>'token无效'
            ];
        }else{
            return [
                'status'=>1,
                'info'=>'登录'
            ];
        }
        
    }
}