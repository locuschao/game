<?php
namespace Native\Controller;

use Think\Controller;
use Think\Model;

class RegisterController extends BaseController
{

    public function _initialize()
    {
        @header('Content-type: text/html;charset=UTF-8');
    }

    public function register()
    {
        $partnerId = I('get.partner');
        
       
        $status = 0;
        if ($partnerId) {
            $status = true;
            if (! _getcookie('partnerId')) {
                if (! $_SESSION['oto_mall']) {
                    _setcookie('partnerId', $partnerId, 3600 * 24);
                }
            }
        }
        if (! $_SESSION['oto_mall']['oto_userInfo']) {
            $status = 2;
        }
        $this->assign('status', $status);
        $this->display();
    }
    
    // 重新获取验证码
    public function repeatGetCode()
    {
        $phone = I('userPhone');
        preg_match_all("/^1[34578]\d{9}$/", $phone, $mobiles);
        if (! $phone || ! $mobiles[0]) {
            // 用户名密码必填
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '短信发送失败'
            ));
            return;
        }
        $code = rand(100000, 999999);
        import('Vendor.yunsms');
        $a = new \yunsms();
        $res = $a->sendMsg($phone, $code . '耀玩平台验证码,五分钟内有效【耀玩平台】');
        if ($res['status'] == 1) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '短信发送成功',
                'code' => $code,
                'phone' => $phone
            ));
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '短信发送失败'
            ));
        }
    }
    
    // 获取重置密码验证码
    public function resetPwdGetCode()
    {
        $phone = I('userPhone');
        preg_match_all("/^1[34578]\d{9}$/", $phone, $mobiles);
        if (! $phone || ! $mobiles[0]) {
            // 用户名密码必填
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '短信发送失败'
            ));
            return;
        }
        $isExists = M('users')->where(array(
            'userPhone' => $phone,
            'userFlag' => 1,
            'userStatus' => 1
        ))->find();
        if (! $isExists) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '用户不存在'
            ));
        }
        $code = rand(100000, 999999);
        import('Vendor.yunsms');
        $a = new \yunsms();
        $res = $a->sendMsg($phone, $code . '耀玩平台验证码,五分钟内有效【耀玩平台】');
        if ($res['status'] == 1) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '短信发送成功',
                'code' => $code,
                'phone' => $phone
            ));
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '短信发送失败'
            ));
        }
    }
    
    //获取找回支付密码验证码
    public function resetPayPwdGetCode()
    {
        $userId=authCode(I('userId'));
        $phone = I('userPhone');
        preg_match_all("/^1[34578]\d{9}$/", $phone, $mobiles);
        if (! $phone || ! $mobiles[0]) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '短信发送失败'
            ));
            return;
        }
        $isExists = M('users')->where(array('userId'=>$userId))->find();
        if (! $isExists) {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '用户不存在'
            ));
        }
        if($isExists['userPhone']!=$phone){
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '非当前用户手机号！'
            ));
        }
        $code = rand(100000, 999999);
        import('Vendor.yunsms');
        $a = new \yunsms();
        $res = $a->sendMsg($phone, $code . '耀玩平台验证码,五分钟内有效【耀玩平台】');
        if ($res['status'] == 1) {
            $this->returnJson(array(
                'status' => 0,
                'msg' => '短信发送成功',
                'code' => $code,
                'phone' => $phone
            ));
        } else {
            $this->returnJson(array(
                'status' => - 1,
                'msg' => '短信发送失败'
            ));
        }
    }
    
    
    
    // 检测手机号是否已经注册，没注册则发送短信
    public function checkPhone()
    {
        $phone = I('userPhone');
        $data['userPhone'] = I('userPhone');
        preg_match_all("/^1[34578]\d{9}$/", $phone, $mobiles);
        if (! $phone || ! $mobiles[0]) {
            // 用户名密码必填
            print_r(json_encode(array(
                'status' => - 1,
                'msg' => '请输入合法手机号'
            )));
            return;
        }
        $isExistsPhone = M('users')->where(array(
            'userPhone' => $data['userPhone'],
            'userFlag' => 1
        ))->find();
        if ($isExistsPhone) {
            print_r(json_encode(array(
                'status' => - 2,
                'msg' => '手机号码已经注册'
            )));
            return;
        }
        $code = rand(100000, 999999);
        // 发送验证码
        import('Vendor.yunsms');
        $a = new \yunsms();
        $res = $a->sendMsg($phone, $code . '耀玩平台验证码,五分钟内有效【耀玩平台】');
        if ($res['status'] == 1) {
            print_r(json_encode(array(
                'status' => 0,
                'msg' => '短信发送成功',
                'code' => $code,
                'phone' => $phone
            )));
        } else {
            print_r(json_encode(array(
                'status' => - 5,
                'msg' => '短信发送失败'
            )));
        }
    }
    
    // 发送验证码后
    public function register1()
    {
        if (! session('registerPhone')) {
            $this->redirect(U('Login/login', '', 0, ''));
            exit();
        }
        $phone = session('registerPhone');
        $this->phone = substr($phone, 0, 3) . str_repeat('*', 5) . substr($phone, 8, 3);
        $this->display();
    }
    // 发送验证码后
    public function register2()
    {
        if (! session('registerPhone')) {
            $this->redirect(U('Login/login', '', 0, ''));
            exit();
        }
        $this->display();
    }

    public function testMes()
    {
        // 发送验证码
        import('Vendor.yunsms');
        $a = new \yunsms();
        $res = $a->sendMsg(15815890001, '891292耀玩平台验证码,五分钟内有效【吉祥夺宝】');
        print_r($res);
    }
    
    // 用户注册
    public function registerHandle()
    {
        // 默认用户名或者密码为空
        $res['status'] = - 1;
        $res['meg'] = '注册失败';
        $data['userPhone'] = I('userPhone');
        $data['loginPwd'] = I('password');
        if (! $data['userPhone'] || ! $data['loginPwd']) {
            // 用户名密码必填
            print_r(json_encode($res));
            return;
        }
        $isExistsPhone = M('users')->where(array(
            'userPhone' => $data['userPhone'],
            'userFlag' => 1
        ))->find();
        if ($isExistsPhone) {
            // 用户已经存在
            print_r(json_encode(array(
                'status' => - 2,
                'msg' => '用户已经存在'
            )));
            return;
        }
        $partnerId = _encrypt(_getcookie('partnerId'), 'DECODE');
        $data['partnerId'] = isset($partnerId) ? $partnerId : 0;
        $data['createTime'] = date('Y-m-d H:i:s', time());
        $data['lastIP'] = get_client_ip();
        $data['userStatus'] = 1;
        $data['userFlag'] = 1;
        $data['loginName'] = I('userPhone');
        $data['loginSecret'] = mt_rand(1000, 9999);
        $data['loginPwd'] = md5($data['loginPwd'] . $data['loginSecret']);
        $data['userType'] = 0;
        $data['IMEI']=I('imei');
        $db = M('users');
        $r = $db->add($data);
        if ($r) {
            $res['status'] = 0;
            $res['msg'] = '注册成功！';
            $res['userPhone'] = I('userPhone');
            $res['userId'] = authCode($r, 'ENCODE');
            print_r(json_encode($res));
        } else {
            // 注册失败
            print_r(json_encode(array(
                'status' => - 4,
                'msg' => '注册失败'
            )));
        }
    }

    public function checkRegisterCode()
    {
        $code = I('code');
        if ($code == session('registerCode')) {
            $this->ajaxReturn(array(
                'status' => 0,
                'msg' => '验证码通过'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => - 1,
                'msg' => '验证码不正确'
            ));
        }
    }
}
