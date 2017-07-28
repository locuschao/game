<?php
namespace GameAPI\Controller;

use Think\Controller;
use Think\Model;

class LoginController extends BaseController
{

    public function Login()
    {
        $this->display();
    }
    
    // 判断是否已经登录
    public function isAjaxLogin()
    {
        if (session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
    
    // 登录
    public function loginHandle()
    {
        // 默认状态为
        $res['status'] = - 1;
        $res['msg'] = '登录密码错误！';
        $uname = I('userName');
        $upwd = I('passWord');
        $imei=I('IMEI');
        if (! $uname || ! $upwd) {
            print_r(json_encode($res));
            return;
        }
     /*    $map['loginName|userEmail|userPhone'] = array(
            'eq',
            $uname
        ); */
        $map['userPhone']=$uname;
        $map['userFlag'] = 1;
        $map['userStatus'] = 1;
        $isExists = M('users')->where($map)->find();
        if ($isExists) {
            if ($isExists['loginPwd'] == md5($upwd . $isExists['loginSecret'])) {
                // 更新一下用户最后一次登录的信息
                $data = array();
                $data['lastTime'] = date('Y-m-d H:i:s');
                $data['lastIP'] = get_client_ip();
                $data['IMEI']=$imei;
                $m = M('users');
                $m->where(" userId=" . $isExists['userId'])
                    ->data($data)
                    ->save();
                // 返回数据
                $uinfo['ip'] = get_client_ip();
                $uinfo['msg'] = '登录成功!';
                $uinfo['status'] = 0;
                $uinfo['isAgent'] =$isExists['isAgent'];
                $uinfo['userPhoto'] = $isExists['userPhoto'];
                $uinfo['loginPwd'] = empty($isExists['loginPwd']) ? 0 : 1;
                $uinfo['payPwd'] = empty($isExists['payPwd']) ? 0 : 1;
                $uinfo['userPhone'] = $isExists['userPhone'];
                $uinfo['userId'] = authcode($isExists['userId'], 'ENCODE');
                $this->returnJson($uinfo);
            } else {
                $this->returnJson($res);
            }
        } else {
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '用户不存在'
            ));
        }
    }
    
    
    
    //第三方QQ登录
    public function QQLogin(){
        $openid=I('openid');
        if(empty($openid)){
            return;
        }
        $nickname=I('nickname');
        $touxian=I('touxian');
        $sex=I('sex');
        $imei=I('imei');
        $map['qqLogin']=$openid;
        $map['userStatus']=1;
        $map['userFlag']=1;
        $info=M('users')->where($map)->find();
        if($info){
            $data ['lastTime'] = date ( 'Y-m-d H:i:s' );
            $data ['userPhoto'] = $touxian;
            $data ['lastIP'] = get_client_ip ();
            $data['userName']=$nickname;
            $data ['userSex'] = $sex;
            $data ['IMEI'] =$imei;
            $m = M ( 'users' );
            $m->where ( " userId=" . $info ['userId'] )->data ( $data )->save ();
            $uinfo['ip'] = get_client_ip();
            $uinfo['msg'] = '登录成功!';
            $uinfo['status'] = 0;
            $uinfo['isAgent'] =$info['isAgent'];
            $uinfo['userPhoto'] = $info['userPhoto'];
            $uinfo['loginPwd'] = empty($info['loginPwd']) ? 0 : 1;
            $uinfo['payPwd'] = empty($info['payPwd']) ? 0 : 1;
            $uinfo['userPhone'] = $info['userPhone'];
            $uinfo['userId'] = authcode($info['userId'], 'ENCODE');
            $this->returnJson($uinfo);
        }else{
            $data ['createTime'] = date ( 'Y-m-d H:i:s', time () );
            $data ['lastIP'] = get_client_ip ();
            $data ['userName']=$nickname;
            $data ['userStatus'] = 1;
            $data ['userFlag'] = 1;
            $data ['qqLogin'] = $openid;
            $data ['userType'] = 0;
            $data ['IMEI'] = $imei;
            $data ['userPhoto'] = $touxian;
            $data ['userSex'] = $sex;
            $db = M ( 'users' );
            if ($db->create ( $data )) {
                $r = $db->add ();
                if ($r) {
                    $res = $db->where ( array ('userId' => $r) )->field('userPhone,loginPwd,userPhoto,payPwd,userMoney,isAgent,userName,IMEI')->find ();
                    $uinfo['ip'] = get_client_ip();
                    $uinfo['msg'] = '登录成功!';
                    $uinfo['status'] = 0;
                    $uinfo['isAgent'] =$res['isAgent'];
                    $uinfo['userPhoto'] = $res['userPhoto'];
                    $uinfo['loginPwd'] = empty($res['loginPwd']) ? 0 : 1;
                    $uinfo['payPwd'] = empty($res['payPwd']) ? 0 : 1;
                    $uinfo['userPhone'] = $res['userPhone'];
                    $uinfo['userId'] = authcode($res['userId'], 'ENCODE');
                    $this->returnJson($uinfo);
                } else {
                    // 注册失败
                     $this->returnJson(array('status'=>-1,'msg'=>'登录失败！'));
                }
            } else {
                // 注册失败
                  $this->returnJson(array('status'=>-2,'msg'=>'登录失败！'));
            }
        }
    }
    
    
    //第三方微信登录
    public function WXLogin(){
        $openid=I('openid');
        if(empty($openid)){
            return;
        }
        $nickname=I('nickname');
        $touxian=I('touxian');
        $sex=I('sex');
        $imei=I('imei');
        $map['wxLogin']=$openid;
        $map['userStatus']=1;
        $map['userFlag']=1;
        $info=M('users')->where($map)->find();
        if($info){
            $data ['lastTime'] = date ( 'Y-m-d H:i:s' );
            $data ['userPhoto'] = $touxian;
            $data ['lastIP'] = get_client_ip ();
            $data['userName']=$nickname;
            $data ['userSex'] = $sex;
            $data ['IMEI'] =$imei;
            $m = M ( 'users' );
            $m->where ( " userId=" . $info ['userId'] )->data ( $data )->save ();
            $uinfo['ip'] = get_client_ip();
            $uinfo['msg'] = '登录成功!';
            $uinfo['status'] = 0;
            $uinfo['isAgent'] =$info['isAgent'];
            $uinfo['userPhoto'] = $info['userPhoto'];
            $uinfo['loginPwd'] = empty($info['loginPwd']) ? 0 : 1;
            $uinfo['payPwd'] = empty($info['payPwd']) ? 0 : 1;
            $uinfo['userPhone'] = $info['userPhone'];
            $uinfo['userId'] = authcode($info['userId'], 'ENCODE');
            $this->returnJson($uinfo);
        }else{
            $data ['createTime'] = date ( 'Y-m-d H:i:s', time () );
            $data ['lastIP'] = get_client_ip ();
            $data['userName']=$nickname;
            $data ['userStatus'] = 1;
            $data ['userFlag'] = 1;
            $data ['wxLogin'] = $openid;
            $data ['userType'] = 0;
            $data ['IMEI'] = $imei;
            $data ['userPhoto'] = $touxian;
            $data ['userSex'] = $sex;
            $db = M ( 'users' );
            if ($db->create ( $data )) {
                $r = $db->add ();
                if ($r) {
                    $res = $db->where ( array ('userId' => $r) )->field('userPhone,loginPwd,userPhoto,payPwd,userMoney,isAgent,userName,IMEI')->find ();
                    $uinfo['ip'] = get_client_ip();
                    $uinfo['msg'] = '登录成功!';
                    $uinfo['status'] = 0;
                    $uinfo['isAgent'] =$res['isAgent'];
                    $uinfo['userPhoto'] = $res['userPhoto'];
                    $uinfo['loginPwd'] = empty($res['loginPwd']) ? 0 : 1;
                    $uinfo['payPwd'] = empty($res['payPwd']) ? 0 : 1;
                    $uinfo['userPhone'] = $res['userPhone'];
                    $uinfo['userId'] = authcode($res['userId'], 'ENCODE');
                    $this->returnJson($uinfo);
                    
                } else {
                    // 注册失败
                    $this->returnJson(array('status'=>-1,'msg'=>'登录失败！'));
                }
            } else {
                // 注册失败
                $this->returnJson(array('status'=>-2,'msg'=>'登录失败！'));
            }
        }
    }
    
    
    
    
    // 找回密码发送验码
    public function resetPwdGetCode()
    {
        $phone = I('phone');
        if (! preg_match('/^1[3|7|4|5|8]{1}\d{9}$/', $phone)) {
            $this->ajaxReturn(array(
                'status' => - 1,
                'msg' => '手机号码格式不正确'
            ));
            exit();
        }
        // 判断此手机是否已经注册
        $r = M('users')->where(array(
            'userPhone' => $phone,
            'userFlag' => 1
        ))->find();
        if (! $r) {
            $this->ajaxReturn(array(
                'status' => - 3,
                'msg' => '手机号码不存在'
            ));
            exit();
        }
        $resetPwdCode = rand(100000, 999999);
        cookie('resetPwdCode', $resetPwdCode, 360); // 指定cookie保存时间
        cookie('resetPwdPhone', $phone, 360); // 指定cookie保存时间
        import('Vendor.yunsms');
        $a = new \yunsms();
        $res = $a->sendMsg($phone, $resetPwdCode . '耀玩平台找回密码验证码,五分钟内有效【耀玩平台】');
        if ($res['status'] == 1) {
            $this->ajaxReturn(array(
                'status' => 0,
                'msg' => '短信发送成功'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1,
                'msg' => '短信发送失败'
            ));
        }
    }
    
    // 发送手机验证码
    public function getCode()
    {
        $type = I('type') ? I('type') : 2; // 如果 为1则为用户注册申请,2为找回密码，3绑定手机，4修改绑定手机
        $phone = trim(I('phone'));
        
        if (! preg_match('/^1[3|7|4|5|8]{1}\d{9}$/', $phone)) {
            // 手机号码不正确
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            return;
        }
        // 判断此手机是否已经注册
        $r = M('users')->where(array(
            'userPhone' => $phone,
            'userFlag' => 1
        ))->find();
        if ($r) {
            // 手机号码已经注册
            if ($type == 1) {
                $this->ajaxReturn(array(
                    'status' => - 3
                ));
                return;
                exit();
            }
        } else {
            if ($type == 2) {
                // 重置密码时，号码不存在
                $this->ajaxReturn(array(
                    'status' => - 5
                ));
                return;
                exit();
            }
        }
        $map['phone'] = $phone;
        $map['type'] = $type;
        $map['_string'] = 'to_days(time)=to_days(now())';
        $exists = M('sendmes')->where($map)->count();
        if ($exists >= C('SEND_MES_COUNT')) {
            // 每天获取的验证码数大于3次限制当天不能再发送
            $this->ajaxReturn(array(
                'status' => - 2
            ));
            exit();
        }
        $code = mt_rand(100000, 999999);
        session(array(
            'name' => 'session_id',
            'expire' => 1200
        ));
        $sessionInfo = array(
            'register_code' => $code,
            'register_phone' => $phone,
            'type' => $type
        );
        session('authCode', $sessionInfo);
        
        // $cont = session('authCode')['register_code'] . C('SEND_MES_TXT');
        $cont = $_SESSION['oto_mall']['authCode']['register_code'] . C('SEND_MES_TXT');
        $text = $cont;
        $cont = urlencode($cont);
        $data = array(
            'phone' => $phone,
            'ip' => get_client_ip(),
            'info' => $text,
            'type' => $type
        );
        $url = C('SEND_MES_URL');
        $url = str_replace('{$userName}', C('SEND_MES_USER'), $url);
        $url = str_replace('{$userPass}', C('SEND_MES_PWD'), $url);
        $url = str_replace('{$phone}', $phone, $url);
        $url = str_replace('{$content}', $cont, $url);
        $result = $this->Get($url);
        if ($result == 0) {
            // 短信已经下发
            M('sendmes')->data($data)->add();
            print_r(json_encode(array(
                'status' => 0,
                // 'register_code' => session('authCode')['register_code'],
                'register_code' => $_SESSION['oto_mall']['authCode']['register_code'],
                // 'register_phone' => session('authCode')['register_phone']
                'register_phone' => $_SESSION['oto_mall']['authCode']['register_phone']
            )));
            return;
        } else {
            // 短信发送失败
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            return;
        }
    }

    public function Get($url)
    {
        if (function_exists('file_get_contents')) {
            $file_contents = file_get_contents($url);
        } else {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }
        return $file_contents;
    }
    // 重置登录密码
    public function resetLoginPwd()
    {
        $this->display();
    }
    //重置登录密码
    public function resetLoginPwdHandle()
    {
        // 默认用户名或者密码为空
        $data['userPhone'] = I('userPhone');
        $phone = I('userPhone');
        $data['loginPwd'] = I('password');
        if (! $data['userPhone'] || ! $data['loginPwd']) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '手机号或密码不能为空'
            ));
            return;
        }
        if (! preg_match('/^1[3|7|4|5|8]{1}\d{9}$/', $phone)) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '手机号码格式不正确'
            ));
            exit();
        }
        // 判断此手机是否已经注册
        $r = M('users')->where(array(
            'userPhone' => $phone,
            'userFlag' => 1
        ))->find();
        if (! $r) {
            $this->returnJson(array(
                'status' => - 3,
                'msg' => '手机号码不存在'
            ));
            exit();
        }
        $data['lastIP'] = get_client_ip();
        $data['loginSecret'] = $r['loginSecret'];
        $data['loginPwd'] = md5($data['loginPwd'] . $data['loginSecret']);
        $db = M('users');
        if ($db->where(array(
            'userPhone' => $data['userPhone'],
            'userFlag' => 1,
            'userType' => 0
        ))->save($data)) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '密码已经重置'
            ));
        } else {
            $this->returnJson(array(
                'status' => - 5,
                'msg' => '重置密码失败'
            ));
        }
    }
    
   //找回支付密码
    //重置登录密码
    public function resetPayPwdHandle()
    {
        $userId=authCode(I('userId'));
        // 默认用户名或者密码为空
        $data['userPhone'] = I('userPhone');
        $phone = I('userPhone');
        $data['payPwd'] = I('password');
        if (! $data['userPhone'] || ! $data['payPwd']) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '手机号或密码不能为空'
            ));
            return;
        }
        if (! preg_match('/^1[3|7|4|5|8]{1}\d{9}$/', $phone)) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '手机号码格式不正确'
            ));
            exit();
        }
        // 判断此手机是否已经注册
        $r = M('users')->where(array('userId'=>$userId))->find();
        if (! $r) {
            $this->returnJson(array(
                'status' => - 3,
                'msg' => '用户状态异常！'
            ));
            exit();
        }
        
        if($r['userPhone']!=$phone){
            $this->returnJson(array(
                'status' => - 3,
                'msg' => '非当前用户手机号！'
            ));
            exit();
        }
        $data['lastIP'] = get_client_ip();
        $data['loginSecret'] =$r['loginSecret'];
        $data['payPwd'] = md5($data['payPwd'] . $data['loginSecret']);
        $db = M('users');
        if ($db->where(array('userId'=>$userId))->save($data)) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '密码已经重置'
            ));
        } else {
            $this->returnJson(array(
                'status' => - 5,
                'msg' => '重置密码失败'
            ));
        }
    }
    
    // 个人信息页
    public function userInfo()
    {
        parent::isLogin();
        $this->uinfo = M('users')->where(array(
            'userId' => session('oto_userId')
        ))
            ->field('userPhone,userPhoto')
            ->find();
        $this->display();
    }
    // 上传头像
    public function uploadImg()
    {
        $userId = I('userId');
        $userId = authCode($userId);
        if (! $userId) {
            echo "文件上传失败";
            return;
        }
        import('ORG.Net.UploadFile');
        $upload = new \UploadFile();
        $upload->autoSub = true;
        $upload->subType = 'custom';
        $data = date('Y-m', time());
        if ($upload->upload('./upload/users/' . $data . '/')) {
            $info = $upload->getUploadFileInfo();
        }
        $file_newname = $info['0']['savename'];
        $MAX_SIZE = 20000000;
        if ($info['0']['type'] != 'image/jpeg' && $info['0']['type'] != 'image/jpg' && $info['0']['type'] != 'image/pjpeg' && $info['0']['type'] != 'image/png' && $info['0']['type'] != 'image/x-png') {
            echo "2";
            exit();
        }
        if ($info['0']['size'] > $MAX_SIZE)
            echo "上传的文件大小超过了规定大小";
        
        if ($info['0']['size'] == 0)
            echo "请选择上传的文件";
        switch ($info['0']['error']) {
            case 0:
                $res = M('users')->where(array(
                    'userId' => $userId
                ))->save(array(
                    'userPhoto' => '/upload/users/' . $data . '/' . $file_newname
                ));
                if ($res) {
                    echo '/upload/users/' . $data . '/' . $file_newname;
                } else {
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
    // 昵称
    public function nickname()
    {
        $this->nickname = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->getField('userName');
        $this->display();
    }

    public function nicknameHandle()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            return;
        }
        $nickName = I('nickname');
        $res = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->setField(array(
            'userName' => $nickName
        ));
        if ($res) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
    // 绑定手机
    public function bindPhone()
    {
        parent::isLogin();
        $phone = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->getField('userPhone');
        if ($phone) {
            // 已存在的则跳到修改页面
            $this->redirect(U('Login/changePhone', '', '', 0));
            return;
        }
        $this->display();
    }
    
    // 绑定手机处理
    public function bindPhoneHandle()
    {
        
        $userId=authCode(I('userId'));
        
        if (! $userId){
            $this->returnJson(array(
                'status' => - 1,'msg'=>'请先登录！'
            ));
            return;
        }
        
        $userInfo=M('users')->where(array('userId'=>$userId))->find();
        
        if(!$userInfo){
            $this->returnJson(array(
                'status' => - 2,'msg'=>'用户状态异常！'
            )); return;
        }
        
        if($userInfo['userPhone']){
            $this->returnJson(array(
                'status' => - 3,'msg'=>'用户已经绑定过手机！'
            )); return;
        }
        
        $phone = I('phone');
        if (! $phone) {
            // 手机必填
            $this->returnJson(array(
                'status' => - 4,'msg'=>'手机号不能为空！'
            ));
            return;
        }
        
        if (! preg_match('/^1[3|7|4|5|8]{1}\d{9}$/', $phone)) {
            // 手机号码不正确
            $this->returnJson(array(
                'status' => - 5,'msg'=>'手机号格式不正确'
            ));
            return;
        }

        $res = M('users')->where(array(
            'userId' => $userId
        ))->setField(array(
            'userPhone' => $phone
        ));
        
        if ($res) {
            $this->returnJson(array(
                'status' => 0,'msg'=>'绑定手机成功！'
            ));
        } else {
            $this->returnJson(array(
                'status' => - 1,'msg'=>'绑定手机失败'
            ));
        }
    }

    public function changePhone()
    {
        parent::isLogin();
        $this->phone = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->getField('userPhone');
        $this->display();
    }
    // 更改绑定手机处理
    public function changePhoneHandle()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            return;
        }
        // 默认用户名或者密码为空
        $oldphone = I('oldphone');
        $oldcode = I('oldcode');
        $newphone = I('newphone');
        $newcode = I('newcode');
        if (! $newphone) {
            // 手机必填
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            return;
        }
        if ($oldcode != session('oldCode') || $oldphone != session('oldPhone') || $newcode != session('newCode') || $newphone != session('newPhone')) {
            // 不是发送验证码的手机
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $res = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->setField(array(
            'userPhone' => $newphone
        ));
        if ($res) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
    // 更改手机时获取 验证码
    public function getChanPhoneCode()
    {
        $type = I('type'); // $type 0为旧手机 1为新手机号
        $phone = trim(I('phone'));
        if (! preg_match('/^1[3|7|4|5|8]{1}\d{9}$/', $phone)) {
            // 手机号码不正确
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            return;
        }
        // 判断此手机是否已经注册
        $r = M('users')->where(array(
            'userPhone' => $phone,
            'userFlag' => 1
        ))->find();
        if ($r) {
            // 新手机号被占用
            if ($type == 1) {
                $this->ajaxReturn(array(
                    'status' => - 5
                ));
                return;
            }
        } else {
            // 旧手机都不存在，非法请求
            if ($type == 0) {
                $this->ajaxReturn(array(
                    'status' => - 4
                ));
                return;
            }
        }
        $map['phone'] = $phone;
        $map['type'] = 4; // 更改绑定手机
        $map['_string'] = 'to_days(time)=to_days(now())';
        $exists = M('sendmes')->where($map)->count();
        if ($exists >= C('SEND_MES_COUNT')) {
            // 每天获取的验证码数大于3次限制当天不能再发送
            $this->ajaxReturn(array(
                'status' => - 2
            ));
            return;
        }
        $code = mt_rand(100000, 999999);
        session(array(
            'name' => 'session_id',
            'expire' => 1200
        ));
        if ($type == 0) {
            session('oldCode', $code);
            session('oldPhone', $phone);
        } else {
            session('newCode', $code);
            session('newPhone', $phone);
        }
        $cont = $code . C('SEND_MES_TXT');
        $text = $cont;
        $cont = urlencode($cont);
        $data = array(
            'phone' => $phone,
            'ip' => get_client_ip(),
            'info' => $text,
            'type' => 4
        );
        $url = C('SEND_MES_URL');
        $url = str_replace('{$userName}', C('SEND_MES_USER'), $url);
        $url = str_replace('{$userPass}', C('SEND_MES_PWD'), $url);
        $url = str_replace('{$phone}', $phone, $url);
        $url = str_replace('{$content}', $cont, $url);
        $result = $this->Get($url);
        if ($result == 0) {
            // 短信已经下发
            M('sendmes')->data($data)->add();
            $this->ajaxReturn(array(
                'status' => 0,
                'register_code' => $code,
                'register_phone' => $phone
            ));
            return;
        } else {
            // 短信发送失败
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            return;
        }
    }
    
    // 登录密码
    public function setLoginPwd()
    {
        parent::isLogin();
        $isExists = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->getField('loginPwd');
        if ($isExists) {
            // 已有密码直接跳转到修改页面
            $this->redirect(U('Login/changeLoginPwd', '', '', 0));
            return;
        }
        $this->display();
    }
    // 处理更改登录密码
    public function changeLoginPwdHandle()
    {
        $userId = I('userId');
        $userId = authCode($userId);
        $oldpwd = I('oldpwd');
        $newpwd = I('newpwd');
        $isExists = M('users')->where(array(
            'userId' => $userId
        ))
            ->field('loginSecret,loginPwd')
            ->find();
        if (md5($oldpwd . $isExists['loginSecret']) != $isExists['loginPwd']) {
            // 旧密码不正确
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '原密码错误！'
            ));
            return;
        }
        $data['loginSecret'] = $isExists['loginSecret'];
        $data['loginPwd'] = md5($newpwd . $data['loginSecret']);
        $data['userId'] = $userId;
        $res = M('users')->save($data);
        if ($res) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '密码修改成功'
            ));
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '密码修改失败'
            ));
        }
    }
    // 设置登录密码
    public function setLoginPwdHandle()
    {
        $userId = I('userId');
        $userId = authCode($userId);
        $isExists = M('users')->where(array(
            'userId' => $userId
        ))
            ->field('loginSecret,loginPwd')
            ->find();
        if ($isExists['loginPwd']) {
            // 已经有密码
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '密码已经设置！'
            ));
        }
        $newpwd = I('newpwd');
        $data['loginSecret'] = mt_rand(1000, 9999);
        $data['loginPwd'] = md5($newpwd . $data['loginSecret']);
        $data['userId'] = $userId;
        $res = M('users')->save($data);
        if ($res) {
            $this->ajaxReturn(array(
                'status' => 0,
                'msg' => '密码设置成功！'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1,
                'msg' => '密码设置失败！'
            ));
        }
    }
    
    // 支付密码
    public function payPwd()
    {
        parent::isLogin();
        $isExists = M('users')->where(array(
            'userId' => session('oto_userId')
        ))
            ->field('loginSecret,payPwd')
            ->find();
        if ($isExists['payPwd']) {
            $this->redirect(U('Login/changePayPwd', '', '', 0));
            return;
        }
        $this->display();
    }
    // 设置支付密码
    public function setPayPwd()
    {
        $userId = I('userId');
        $userId = authCode($userId);
        if (! $userId) {
            $this->returnJson(array(
                'status' => - 3,
                'msg' => '支付密码已经设置'
            ));
        }
        $pwd = I('newpwd');
        if (! is_numeric($pwd) || strlen($pwd) != 6) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '密码必须是6位数字'
            ));
        }
        $map['userId'] = $userId;
        $loginSecret = M('users')->where($map)->getField('loginSecret');
        $newPwd = md5($pwd . $loginSecret);
        $res = M('users')->where($map)->save(array(
            'payPwd' => $newPwd
        ));
        if ($res) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '支付密码设置成功'
            ));
        } else {
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '支付密码设置失败'
            ));
        }
    }
    // 修改支付密码
    public function changePayPwdHandle()
    {
        $userId = I('userId');
        $userId = authCode($userId);
        $oldpwd = I('oldpwd');
        $newpwd = I('newpwd');
        $isExists = M('users')->where(array(
            'userId' => $userId
        ))
            ->field('loginSecret,payPwd')
            ->find();
        if (md5($oldpwd . $isExists['loginSecret']) != $isExists['payPwd']) {
            // 旧密码不正确
            $this->returnJson(array(
                'status' => - 2,
                'msg' => '原支付密码错误！'
            ));
            return;
        }
        $data['payPwd'] = md5($newpwd . $isExists['loginSecret']);
        $data['userId'] = $userId;
        $res = M('users')->save($data);
        if ($res) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '支付密码修改成功'
            ));
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '支付密码修改失败'
            ));
        }
    }
    
    // 退出登录
    public function logOut()
    {
        session('oto_userId', null);
        session_destroy();
        $this->ajaxReturn(array(
            'status' => 0
        ));
    }
    
    // 收货地址
    public function myAddr()
    {
        $addr = M('user_address')->where(array(
            'addressFlag' => 1,
            'userId' => session('oto_userId')
        ))->select();
        $ares = M('areas')->select();
        foreach ($addr as $k => $v) {
            foreach ($ares as $a => $av) {
                if ($v['areaId1'] == $av['areaId']) {
                    $addr[$k]['province'] = $av['areaName'];
                }
                if ($v['areaId2'] == $av['areaId']) {
                    $addr[$k]['city'] = $av['areaName'];
                }
                if ($v['areaId3'] == $av['areaId']) {
                    $addr[$k]['area'] = $av['areaName'];
                }
            }
        }
        $this->addr = $addr;
        $this->display();
    }
    // 设置为默认地址
    public function setDefaultAddr()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $id = I('addrid');
        M()->startTrans();
        $A = M('user_address')->where(array(
            'userId' => session('oto_userId')
        ))->setField(array(
            'isDefault' => 0
        ));
        $B = M('user_address')->where(array(
            'userId' => session('oto_userId'),
            'addressId' => $id
        ))->setField(array(
            'isDefault' => 1
        ));
        if ($A !== false && $B !== false) {
            M()->commit();
            $this->ajaxReturn(array(
                'status' => - 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
            M()->rollback();
        }
    }
    // 删除地址
    public function delAddr()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $id = I('addrid');
        $A = M('user_address')->where(array(
            'userId' => session('oto_userId'),
            'addressId' => $id
        ))->delete();
        if ($A) {
            $this->ajaxReturn(array(
                'status' => - 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
    // 添加地址
    public function addAddr()
    {
        parent::isLogin();
        $this->province = $this->getCity();
        $this->display();
    }
    // 添加地址处理
    public function addAddrHandle()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $map['userId'] = session('oto_userId');
        $map['userName'] = I('userName');
        $map['userPhone'] = I('userPhone');
        $map['areaId1'] = I('areaId1');
        $map['areaId2'] = I('areaId2');
        $map['areaId3'] = I('areaId3');
        $map['address'] = I('address');
        $map['createTime'] = date('Y-m-d H:i:s', time());
        $r = M('user_address')->add($map);
        if ($r) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }

    public function editAddr()
    {
        parent::isLogin();
        $id = I('id');
        $addr = M('user_address')->where(array(
            'addressId' => $id
        ))->find();
        $this->addr = $addr;
        $this->province = $this->getCity();
        $B = $this->getCity($addr['areaId1']);
        $this->assign('city', $B);
        $this->area = $this->getCity($addr['areaId2']);
        $this->display();
    }
    // 保存编辑地址
    public function editAddrHandle()
    {
        if (! session('oto_userId')) {
            $this->ajaxReturn(array(
                'status' => - 3
            ));
            return;
        }
        $map['userId'] = session('oto_userId');
        $map['addressId'] = I('addrid');
        $map['userName'] = I('userName');
        $map['userPhone'] = I('userPhone');
        $map['areaId1'] = I('areaId1');
        $map['areaId2'] = I('areaId2');
        $map['areaId3'] = I('areaId3');
        $map['address'] = I('address');
        $r = M('user_address')->save($map);
        if ($r) {
            $this->ajaxReturn(array(
                'status' => 0
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1
            ));
        }
    }
    // 获取省
    public function getCity($parentid = 0)
    {
        $m = M('areas');
        $I_parentId = I('parentId');
        if (isset($parentid) && ! $I_parentId) {
            $pid = $parentid;
        } else {
            $pid = I('parentId') ? I('parentId') : 0;
        }
        $map['areaFlag'] = 1;
        // $map['isShow']=1;
        $field = "areaId,areaName";
        $map['parentId'] = $pid;
        $res = $m->where($map)
            ->field($field)
            ->select();
        if (IS_AJAX) {
            $this->ajaxReturn($res);
        } else {
            return $res;
        }
    }
}