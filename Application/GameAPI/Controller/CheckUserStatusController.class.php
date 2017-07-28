<?php
namespace GameAPI\Controller;

use Think\Controller;
use Think\Model;

class CheckUserStatusController extends BaseController
{

    public $userid;

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }

    //判断个人同一帐号只能一台手机登录
    public function multiUserLogin(){
        $imei=I('imei');
        $userId=I('userId');
        $userId=authCode($userId);
        $map ['userFlag'] = 1;
        $map ['userStatus'] = 1;
        $map ['userId'] = $userId;
        $oldImei=M('users')->where($map)->getField('peImei');
        if($imei!=$oldImei){
            //在不同设备登录了
            $this->ajaxReturn(array('status'=>0));
        }else{
            $this->ajaxReturn(array('status'=>1));
        }
    }
    
    //判断用户状态
    public  function userStatus(){
        $userId=authCode(I('userId'));
        $imei=I('IMEI');
        if(!$userId){
            $this->returnJson(array('msg'=>'非法用户！','status'=>'-1'));exit();
        }
        $map ['userFlag'] = 1;
        $map ['userStatus'] = 1;
        $map ['userId'] = $userId;
        $userInfo=M('users')->where($map)->field('userPhone,loginPwd,userPhoto,payPwd,userMoney,isAgent,userName,IMEI')->find();
        if($userInfo){
            
            if($imei&&$imei!='undefined'){
                if($imei!=$userInfo['IMEI']){
                    $this->returnJson(array('msg'=>'用户已在其它手机登录','status'=>'-3'));exit();
                }
            }
            
            $userInfo['IP']=get_client_ip();
            $userInfo['status']=0;
            $userInfo['loginPwd']= $userInfo['loginPwd']?1:0;
            $userInfo['payPwd']= $userInfo['payPwd']?1:0;
            $this->returnJson($userInfo);
        }else{
            $this->returnJson(array('msg'=>'用户状态异常！','status'=>'-2'));exit();
        }
    }
}
