<?php

namespace Wx\Controller;

use Think\Controller;
use Think\Model;
class LoginController extends BaseController {
    public function Login(){
        $this->display();
    }
    
    public function test(){
        $orderids="1000,1001";
        $saveData['isPay']=1;
        $saveData['orderStatus']=0;
        $saveData['paytime']=time();
        $saveData['payType']=2; //微信支付
        $res=M('orders')->where(array('orderId'=>array('in',"$orderids")))->save($saveData);
        echo M('orders')->_sql();
    }
    public function register(){

        $partnerId =  I('get.partner');
        $status=false;
        if($partnerId){
            $status=true;
            if(!_getcookie('partnerId')){

                if(!$_SESSION['oto_mall']){
                    _setcookie('partnerId',$partnerId,3600*24);
                }
            }
        }
        if(!$_SESSION['oto_mall']['oto_userInfo']){
            $status=2;
        }

        $this->assign('status',$status);
        $this->display();
    }
    
    
    //判断是否已经登录
    public function isAjaxLogin(){
        if(session('oto_userId')){
            $this->ajaxReturn(array('status'=>0));
        }else{
            $this->ajaxReturn(array('status'=>-1));
        }
    }
    
    // 登录
    public function loginHandle() {
        // 默认状态为
        $res ['status'] = - 1;
        $uname = I ( 'phone' );
        $upwd = I ( 'pwd' );
        if (!$uname  || !$upwd) {
            $this->ajaxReturn($res);
        }
        $map ['loginName|userEmail|userPhone'] = array ( 'eq',$uname);
        $map ['userFlag'] = 1;
        $map ['userStatus'] = 1;
//        $map ['userType'] = 0; // 0为用户
        $isExists = M ( 'users' )->where ( $map )->find ();
//        echo M()->getLastSql();
        if ($isExists) {
            if ($isExists ['loginPwd'] == md5 ( $upwd . $isExists ['loginSecret'] )) {
                // 更新一下用户最后一次登录的信息
                $data = array ();
                $data ['lastTime'] = date ( 'Y-m-d H:i:s' );
                $data ['lastIP'] = get_client_ip ();
                $m = M ( 'users' );
                $m->where ( " userId=" . $isExists ['userId'] )->data ( $data )->save ();
                // 返回数据
                $isExists ['status'] = 0;
                $isExists ['ip'] = get_client_ip ();
                session('oto_userInfo',$isExists);
                session('oto_userId',$isExists['userId']);
                $isExists['loginPwd']=1;
                $isExists['payPwd']=0;
                unset($isExists['loginSecret']);
                $this->ajaxReturn($isExists);
            } else {
                $this->ajaxReturn($res);
            }
        } else {
            $this->ajaxReturn(array ( 'status' => - 2) );
        }
    }
    // 用户注册
    public function registerHandle() {
        // 默认用户名或者密码为空
        $res ['status'] = - 1;
        $data ['userPhone'] = I ( 'phone' );
        $data ['loginPwd'] = I ( 'pwd' );
        $code = I ( 'code' );
        if (! $data ['userPhone'] || ! $data ['loginPwd']) {
            // 用户名密码必填
            $this->ajaxReturn($res);
            return;
        }
//        if($code!=session('authCode')['register_code']||$data['userPhone']!=session('authCode')['register_phone']){
        if($code!=$_SESSION['oto_mall']['authCode']['register_code']||$data['userPhone']!=$_SESSION['oto_mall']['authCode']['register_phone']){
              //不是发送验证码的手机
              $this->ajaxReturn(array('status'=>-3));
              return; 
         }
        $isExistsPhone = M ( 'users' )->where ( array ('userPhone' => $data ['userPhone'],'userFlag' => 1 ) )->find ();
        if ($isExistsPhone) {
            // 用户已经存在
            $this->ajaxReturn(array('status'=>-2));
            return;
        }
        $partnerId = _encrypt(_getcookie('partnerId'),'DECODE');
        $data['partnerId'] = isset($partnerId)?$partnerId:'';
        $data ['createTime'] = date ( 'Y-m-d H:i:s', time () );
        $data ['lastIP'] = get_client_ip ();
        $data ['userStatus'] = 1;
        $data ['userFlag'] = 1;
        $data ['loginSecret'] = mt_rand ( 1000, 9999 );
        $data ['loginPwd'] = md5 ( $data ['loginPwd'] . $data ['loginSecret'] );
        $data ['userType'] = 0;

        $db = M ( 'users' );
        if ($db->create ( $data )) {
            $r = $db->add ();
            if ($r) {
                session('oto_userId',$r);
                $res ['status'] = 0;
                _setcookie('partnerId',null);
                $this->ajaxReturn($res);
            } else {
                // 注册失败
                $this->ajaxReturn(array('status'=>-4));
            }
        } else {
        // 注册失败
        $this->ajaxReturn(array('status'=>-4));
        }
    }
    // 发送手机验证码
    public function getCode() {
        $type = I ( 'type' ) ? I ( 'type' ) : 2; // 如果 为1则为用户注册申请,2为找回密码，3绑定手机，4修改绑定手机
        $phone = trim ( I ( 'phone' ) );

        if (! preg_match ( '/^1[3|7|4|5|8]{1}\d{9}$/', $phone )) {
            // 手机号码不正确
            $this->ajaxReturn(array('status'=>-1));
            return;
        }
        // 判断此手机是否已经注册
        $r = M ( 'users' )->where ( array ('userPhone' => $phone,'userFlag' => 1 ) )->find ();
        if ($r) {
            // 手机号码已经注册
                if($type==1){
                    $this->ajaxReturn(array('status'=>-3));return;
                    exit();
                }
                
        }else{
            if($type==2){
                //重置密码时，号码不存在
                $this->ajaxReturn(array('status'=>-5));return;
                exit();
            }
        }
        $map ['phone'] = $phone;
        $map['type']=$type;
        $map['_string']='to_days(time)=to_days(now())';
        $exists = M ( 'sendmes' )->where ( $map )->count ();
        if ($exists >= C('SEND_MES_COUNT')) {
            // 每天获取的验证码数大于3次限制当天不能再发送
            $this->ajaxReturn(array('status'=>-2));
            exit();
        }
        $code = mt_rand ( 100000, 999999 );
        session ( array (
            'name' => 'session_id',
            'expire' => 1200
        ) );
        $sessionInfo=array(
            'register_code'=>$code,
            'register_phone'=>$phone,
            'type'=>$type
        );
        session('authCode',$sessionInfo);

//        $cont = session('authCode')['register_code'] . C('SEND_MES_TXT');
        $cont = $_SESSION['oto_mall']['authCode']['register_code']. C('SEND_MES_TXT');
        $text = $cont;
        $cont = urlencode ( $cont );
        $data = array (
            'phone' => $phone,
            'ip' => get_client_ip(),
            'info' => $text,
            'type'=>$type
        );
       $url = C('SEND_MES_URL');
       $url =  str_replace('{$userName}', C('SEND_MES_USER'), $url);
       $url = str_replace('{$userPass}', C('SEND_MES_PWD'), $url);
       $url = str_replace('{$phone}', $phone, $url);
       $url =  str_replace('{$content}', $cont, $url);
        $result = $this->Get ( $url );
        if ($result == 0) {
            // 短信已经下发
            M ( 'sendmes' )->data ( $data )->add ();
            print_r ( json_encode ( array (
                'status' => 0,
//                'register_code' => session('authCode')['register_code'],
                'register_code' => $_SESSION['oto_mall']['authCode']['register_code'],
//                'register_phone' => session('authCode')['register_phone']
                'register_phone' => $_SESSION['oto_mall']['authCode']['register_phone']
            ) ) );
            return;
        } else {
            // 短信发送失败
            $this->ajaxReturn(array('status'=>-1));
            return;
        }
    }
    
    public function Get($url) {
        if (function_exists ( 'file_get_contents' )) {
            $file_contents = file_get_contents ( $url );
        } else {
            $ch = curl_init ();
            $timeout = 5;
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );
            $file_contents = curl_exec ( $ch );
            curl_close ( $ch );
        }
        return $file_contents;
    }
    //重置登录密码
    public function resetLoginPwd(){
        $this->display();
    }
    //
    public function resetLoginPwdHandle(){
        // 默认用户名或者密码为空
        $res ['status'] = - 1;
        $data ['userPhone'] = I ( 'phone' );
        $data ['loginPwd'] = I ( 'pwd' );
        $code = I ( 'code' );
        if (! $data ['userPhone'] || ! $data ['loginPwd']) {
            // 用户名密码必填
            $this->ajaxReturn($res);
            return;
        }
//        if($code!=session('authCode')['register_code']||$data['userPhone']!=session('authCode')['register_phone']){
        if($code!=$_SESSION['oto_mall']['authCode']['register_code']||$data['userPhone']!=$_SESSION['oto_mall']['authCode']['register_phone']){
            //不是发送验证码的手机
            $this->ajaxReturn(array('status'=>-3));
            return;
        }
        $isExistsPhone = M ( 'users' )->where ( array ('userPhone' => $data ['userPhone'],'userFlag' => 1 ) )->find ();
        if (!$isExistsPhone) {
            // 用户不存在
            $this->ajaxReturn(array('status'=>-2));
            return;
        }
        $data ['lastIP'] = get_client_ip ();
        $data ['loginSecret'] = mt_rand ( 1000, 9999 );
        $data ['loginPwd'] = md5 ( $data ['loginPwd'] . $data ['loginSecret'] );
        $db = M ( 'users' );
        if ($db->where(array('userPhone'=>$data ['userPhone'],'userFlag'=>1,'userType'=>0))->save($data)) {
                $res ['status'] = 0;
                $this->ajaxReturn($res);
        } else {
            // 注册失败
            $this->ajaxReturn(array('status'=>-4));
        }
    }
    //个人信息页
    public function userInfo(){
        parent::isLogin();
        $this->uinfo=M('users')->where(array('userId'=>session('oto_userId')))->field('userPhone,userPhoto')->find();
        $this->display();
    }
    //上传头像
    public  function uploadImg(){
        if(!session('oto_userId')){
            echo "文件上传失败";
            return;
        }
        import('ORG.Net.UploadFile');
        $upload = new \UploadFile();
        $upload->autoSub = true;
        $upload->subType = 'custom';
        $data=date('Y-m',time());
        if ($upload->upload('./upload/users/'.$data.'/')){
            $info = $upload->getUploadFileInfo();
        }
        $file_newname = $info['0']['savename'];
        $MAX_SIZE = 20000000;
        if($info['0']['type'] !='image/jpeg' && $info['0']['type'] !='image/jpg' && $info['0']['type'] !='image/pjpeg' && $info['0']['type'] != 'image/png' && $info['0']['type'] != 'image/x-png'){
            echo "2";exit;
        }
        if($info['0']['size']>$MAX_SIZE)
            echo "上传的文件大小超过了规定大小";
         
        if($info['0']['size'] == 0)
            echo "请选择上传的文件";
        switch($info['0']['error'])
        {
            case 0:
                $res=M('users')->where(array('userId'=>session('oto_userId')))->save(array('userPhoto'=>'upload/users/'.$data.'/'.$file_newname));
                if($res){
                    echo 'upload/users/'.$data.'/'.$file_newname;
                }else{
                    echo "文件上传失败";
                }
                break;
            case 1:
                echo "上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值";
                break;
            case 2:
                echo "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
                break;
            case 3:
                echo "文件只有部分被上传";
                break;
            case 4:
                echo "没有文件被上传";
                break;
        }
    }
    //昵称
    public function nickname(){
        $this->nickname=M('users')->where(array('userId'=>session('oto_userId')))->getField('userName');
        $this->display();
    }
    
    public function nicknameHandle(){
        if(!session('oto_userId')){
            $this->ajaxReturn(array('status'=>-1));
            return;
        }
        $nickName=I('nickname');
        $res=M('users')->where(array('userId'=>session('oto_userId')))->setField(array('userName'=>$nickName));
        if($res){
            $this->ajaxReturn(array('status'=>0));
        }else{
            $this->ajaxReturn(array('status'=>-1));
        }
    }
    //绑定手机
    public function bindPhone(){
        parent::isLogin();
        $phone=M('users')->where(array('userId'=>session('oto_userId')))->getField('userPhone');
        if($phone){
            //已存在的则跳到修改页面
            $this->redirect(U('Login/changePhone','','',0));
            return;
        }
        $this->display();
    }
    
    //绑定手机处理
    public  function bindPhoneHandle(){
        if(!session('oto_userId')){
            $this->ajaxReturn(array('status'=>-1));
            return;
        }
        $phone = I ( 'phone' );
        $code = I ( 'code' );
        if (! $phone) {
            //手机必填
           $this->ajaxReturn(array('status'=>-1));
            return;
        }
//        if($code!=session('authCode')['register_code']||$phone!=session('authCode')['register_phone']){
        if($code!=$_SESSION['oto_mall']['authCode']['register_code']||$phone!=$_SESSION['oto_mall']['authCode']['register_phone']){
            //不是发送验证码的手机
            $this->ajaxReturn(array('status'=>-3));
            return;
        }
        $res=M('users')->where(array('userId'=>session('oto_userId')))->setField(array('userPhone'=>$phone));
        if($res){
            $this->ajaxReturn(array('status'=>0));
        }else{
            $this->ajaxReturn(array('status'=>-1));
        }
    }
    
    public function changePhone(){
        parent::isLogin();
        $this->phone=M('users')->where(array('userId'=>session('oto_userId')))->getField('userPhone');
        $this->display();
    }
    //更改绑定手机处理
    public function changePhoneHandle(){
        if(!session('oto_userId')){
            $this->ajaxReturn(array('status'=>-1));
            return;
        }
        // 默认用户名或者密码为空
        $oldphone=I('oldphone');
        $oldcode=I('oldcode');
        $newphone = I ( 'newphone' );
        $newcode = I ( 'newcode' );
        if (! $newphone) {
            //手机必填
            $this->ajaxReturn(array('status'=>-1));
            return;
        }
        if($oldcode!=session('oldCode')||$oldphone!=session('oldPhone')||$newcode!=session('newCode')||$newphone!=session('newPhone')){
            //不是发送验证码的手机
            $this->ajaxReturn(array('status'=>-3));
            return;
        }
        $res=M('users')->where(array('userId'=>session('oto_userId')))->setField(array('userPhone'=>$newphone));
        if($res){
            $this->ajaxReturn(array('status'=>0));
        }else{
            $this->ajaxReturn(array('status'=>-1));
        }
    }
    //更改手机时获取 验证码
    public function getChanPhoneCode(){
        $type = I ( 'type' ); //  $type 0为旧手机 1为新手机号
        $phone = trim ( I ( 'phone' ) );
        if (! preg_match ( '/^1[3|7|4|5|8]{1}\d{9}$/', $phone )) {
            // 手机号码不正确
            $this->ajaxReturn(array('status'=>-1));
            return;
        }
        // 判断此手机是否已经注册
        $r = M ( 'users' )->where ( array ('userPhone' => $phone,'userFlag' => 1 ) )->find ();
        if ($r) {
            //新手机号被占用
            if($type==1){
                $this->ajaxReturn(array('status'=>-5));return;
            }
        }else{
            //旧手机都不存在，非法请求
            if($type==0){
                $this->ajaxReturn(array('status'=>-4));return;
            }
        }
        $map ['phone'] = $phone;
        $map['type']=4;//更改绑定手机
        $map['_string']='to_days(time)=to_days(now())';
        $exists = M ( 'sendmes' )->where ( $map )->count ();
        if ($exists >= C('SEND_MES_COUNT')) {
            // 每天获取的验证码数大于3次限制当天不能再发送
            $this->ajaxReturn(array('status'=>-2));
            return;
        }
        $code = mt_rand ( 100000, 999999 );
        session ( array (
            'name' => 'session_id',
            'expire' => 1200
        ) );
        if($type==0){
            session('oldCode',$code);
            session('oldPhone',$phone);
        }else{
            session('newCode',$code);
            session('newPhone',$phone);
        }
        $cont = $code . C('SEND_MES_TXT');
        $text = $cont;
        $cont = urlencode ( $cont );
        $data = array (
            'phone' => $phone,
            'ip' => get_client_ip(),
            'info' => $text,
            'type'=>4
        );
        $url = C('SEND_MES_URL');
        $url =  str_replace('{$userName}', C('SEND_MES_USER'), $url);
        $url = str_replace('{$userPass}', C('SEND_MES_PWD'), $url);
        $url = str_replace('{$phone}', $phone, $url);
        $url =  str_replace('{$content}', $cont, $url);
        $result = $this->Get ( $url );
        if ($result == 0) {
            // 短信已经下发
            M ( 'sendmes' )->data ( $data )->add ();
                $this->ajaxReturn(
                    array(
                        'status' => 0,
                        'register_code' => $code,
                        'register_phone' =>$phone
                    )
                );
            return;
        } else {
            // 短信发送失败
            $this->ajaxReturn(array('status'=>-1));
            return;
        }
    }
    
    //登录密码
    public function setLoginPwd(){
        parent::isLogin();
       $isExists=M('users')->where(array('userId'=>session('oto_userId')))->getField('loginPwd');
       if($isExists){
           //已有密码直接跳转到修改页面
           $this->redirect(U('Login/changeLoginPwd','','',0));
           return;
       }
        $this->display();
    }
   //处理更改登录密码
   public function changeLoginPwdHandle(){
       $oldpwd=I('oldpwd');
       $newpwd=I('newpwd');
       $isExists=M('users')->where(array('userId'=>session('oto_userId')))->field('loginSecret,loginPwd')->find();
       if(md5($oldpwd.$isExists['loginSecret'])!=$isExists['loginPwd']){
           //旧密码不正确
           $this->ajaxReturn(array('status'=>-2));
           return;
       }
       $data ['loginSecret'] = mt_rand ( 1000, 9999 );
       $data ['loginPwd'] = md5 ( $newpwd . $data ['loginSecret'] );
       $data['userId']=session('oto_userId');
       $res=M('users')->save($data);
       if($res){
         $this->ajaxReturn(array('status'=>0));
       }else{
           $this->ajaxReturn(array('status'=>-1));
       }
   }
   //设置登录密码
   public function setLoginPwdHandle(){
       $isExists=M('users')->where(array('userId'=>session('oto_userId')))->field('loginSecret,loginPwd')->find();
       if($isExists['loginPwd']){
           //已经有密码
           $this->ajaxReturn(array('status'=>-2));
           return;
       }
       $newpwd=I('newpwd');
       $data ['loginSecret'] = mt_rand ( 1000, 9999 );
       $data ['loginPwd'] = md5 ( $newpwd . $data ['loginSecret'] );
       $data['userId']=session('oto_userId');
       $res=M('users')->save($data);
       if($res){
         $this->ajaxReturn(array('status'=>0));
       }else{
           $this->ajaxReturn(array('status'=>-1));
       }
   }
   
   //支付密码
   public function payPwd(){
       parent::isLogin();
       $isExists=M('users')->where(array('userId'=>session('oto_userId')))->field('loginSecret,payPwd')->find();
       if($isExists['payPwd']){
           $this->redirect(U('Login/changePayPwd','','',0));
           return;
       }
       $this->display();
   }
   //设置支付密码
   public function setPayPwd(){
       if(!session('oto_userId')){
           $this->ajaxReturn(array('status'=>-3));
           return;
       }
       $userid=session('oto_userId');
       $pwd=I('pwd');
       if(!is_numeric($pwd)||strlen($pwd)!=6){
           $this->ajaxReturn(array('status'=>-1,'msg'=>'密码必须是6位数字'));
           return;
       }
       $map['userId']=$userid;
       $loginSecret=M('users')->where($map)->getField('loginSecret');
       $newPwd=md5($pwd.$loginSecret);
       $res=M('users')->where($map)->save(array('payPwd'=>$newPwd));
       if($res){
           $this->ajaxReturn(array('status'=>0,'msg'=>'支付密码设置成功'));
       }else{
           $this->ajaxReturn(array('status'=>-2,'msg'=>'请稍候重试'));
       }
   }
   //修改支付密码
   public function changePayPwdHandle(){
       $oldpwd=I('oldpwd');
       $newpwd=I('newpwd');
       $isExists=M('users')->where(array('userId'=>session('oto_userId')))->field('loginSecret,payPwd')->find();
       if(md5($oldpwd.$isExists['loginSecret'])!=$isExists['payPwd']){
           //旧密码不正确
           $this->ajaxReturn(array('status'=>-2));
           return;
       }
       $data ['payPwd'] = md5 ( $newpwd . $isExists ['loginSecret'] );
       $data['userId']=session('oto_userId');
       $res=M('users')->save($data);
       if($res){
           $this->ajaxReturn(array('status'=>0));
       }else{
           $this->ajaxReturn(array('status'=>-1));
       }
   }
   
   //退出登录
   public function logOut(){
       session('oto_userId',null);
       session_destroy();
       $this->ajaxReturn(array('status'=>0));
   }
   
   //收货地址
   public function myAddr(){
      $addr=M('user_address')->where(array('addressFlag'=>1,'userId'=>session('oto_userId')))->select();
      $ares=M('areas')->select();
       foreach ($addr as $k=>$v){
           foreach ($ares as $a=>$av){
               if($v['areaId1']==$av['areaId']){
                   $addr[$k]['province']=$av['areaName'];
               }
               if($v['areaId2']==$av['areaId']){
                   $addr[$k]['city']=$av['areaName'];
               }
               if($v['areaId3']==$av['areaId']){
                   $addr[$k]['area']=$av['areaName'];
               }
           }
       }
       $this->addr=$addr;
       $this->display();
   }
   //设置为默认地址
   public function setDefaultAddr(){
       if(!session('oto_userId')){
           $this->ajaxReturn(array('status'=>-3));
           return;
       }
       $id=I('addrid');
       M()->startTrans();
       $A=M('user_address')->where(array('userId'=>session('oto_userId')))->setField(array('isDefault'=>0));
       $B=M('user_address')->where(array('userId'=>session('oto_userId'),'addressId'=>$id))->setField(array('isDefault'=>1));
       if($A!==false&&$B!==false){
           M()->commit();
           $this->ajaxReturn(array('status'=>-0));
       }else{
           $this->ajaxReturn(array('status'=>-1));
           M()->rollback();
       }
   }
   //删除地址
   public function delAddr(){
       if(!session('oto_userId')){
           $this->ajaxReturn(array('status'=>-3));
           return;
       }
       $id=I('addrid');
       $A=M('user_address')->where(array('userId'=>session('oto_userId'),'addressId'=>$id))->delete();
       if($A){
           $this->ajaxReturn(array('status'=>-0));
       }else{
           $this->ajaxReturn(array('status'=>-1));
       }
   }
   //添加地址
   public function addAddr(){
       parent::isLogin();
       $this->province=$this->getCity();
       $this->display();
   }
   //添加地址处理
   public function addAddrHandle(){
       if(!session('oto_userId')){
           $this->ajaxReturn(array('status'=>-3));
           return;
       }
       $map['userId']=session('oto_userId');
       $map['userName']=I('userName');
       $map['userPhone']=I('userPhone');
       $map['areaId1']=I('areaId1');
       $map['areaId2']=I('areaId2');
       $map['areaId3']=I('areaId3');
       $map['address']=I('address');
       $map['createTime']=date('Y-m-d H:i:s',time());
       $r=M('user_address')->add($map);
       if($r){
           $this->ajaxReturn(array('status'=>0));
       }else{
           $this->ajaxReturn(array('status'=>-1));
       }
   }
   public function editAddr(){
       parent::isLogin();
       $id=I('id');
       $addr=M('user_address')->where(array('addressId'=>$id))->find();
       $this->addr=$addr;
       $this->province=$this->getCity();
       $B=$this->getCity($addr['areaId1']);
       $this->assign('city',$B);
       $this->area=$this->getCity($addr['areaId2']);
       $this->display();
   }
   //保存编辑地址
   public  function editAddrHandle(){
       if(!session('oto_userId')){
           $this->ajaxReturn(array('status'=>-3));
           return;
       }
       $map['userId']=session('oto_userId');
       $map['addressId']=I('addrid');
       $map['userName']=I('userName');
       $map['userPhone']=I('userPhone');
       $map['areaId1']=I('areaId1');
       $map['areaId2']=I('areaId2');
       $map['areaId3']=I('areaId3');
       $map['address']=I('address');
       $r=M('user_address')->save($map);
       if($r){
           $this->ajaxReturn(array('status'=>0));
       }else{
           $this->ajaxReturn(array('status'=>-1));
       }
   }
   // 获取省
   public function getCity($parentid=0) {
       $m = M ( 'areas' );
       $I_parentId=I ( 'parentId' );
       if(isset($parentid)&&!$I_parentId){
           $pid=$parentid;
       }else{
           $pid = I ( 'parentId' ) ? I ( 'parentId' ) : 0;
       }
       $map ['areaFlag'] = 1;
       //$map['isShow']=1;
       $field = "areaId,areaName";
       $map ['parentId'] = $pid;
       $res = $m->where ( $map )->field ( $field )->select ();
       if(IS_AJAX){
           $this->ajaxReturn($res);
       }else{
           return $res;
       }
   }
}