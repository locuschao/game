<?php
namespace Game\Controller;

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
        
        /**
         * @author peng
         * @date 2017-01
         * @descreption 
         */
        //$nonce=I('get.nonce');
//        if(M('users')->where(['nonce'=>$nonce])->find()){
//            $this->redirect('/Game/index');
//        }
//        if($partnerId && !$nonce) exit('地址异常！');
//        _setcookie('nonce', $nonce);
        
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
        $phone = session('registerPhone');
        session('codeTime',time());
        $code = rand(100000, 999999);
        $code = "$code";
        
        session('registerCode',$code);
        vendor('Alidayu.Alidayu');
        $sendMsg = new \Alidayu();
        $result = $sendMsg->sendMsg($code,$phone);
        if($result->sub_msg)
        {
            $this->ajaxReturn(array(
                'status' => - 5,
                'msg' => '短信发送失败'
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0,
                'msg' => '短信发送成功'
            ));
        }
    }
    /*
     * 二次开发
     * 将短信发送方式改为 阿里大鱼
     */
    // 检测手机号是否已经注册，没注册则发送短信
    public function checkPhone($flag = '')      //  如果$flag有值的话，则表示是qq绑定手机号码，否则是测试注册手机号码
    {
        $data['userPhone'] = I('phone');
        $phone = I('phone');
        preg_match_all("/^1[34578]\d{9}$/", $phone, $mobiles);
        if (! $data['userPhone'] || ! $mobiles[0]) {
            // 用户名密码必填
            $this->ajaxReturn(array(
                'status' => - 1,
                'msg' => '请输入合法手机号'
            ));
            return;
        }
        
        $isExistsPhone = M('users')->where(array(
            'userPhone' => $data['userPhone'],
            'userFlag' => 1
        ))->find();
        if(!$flag)
        {
            if ($isExistsPhone) {
                $this->ajaxReturn(array(
                    'status' => - 2,
                    'msg' => '手机号码已经注册'
                ));
                return;
            }
        }
        if($isExistsPhone)          //魏永就   如果qq或微信id存在，则不能再用该电话号码绑定
        {
            if($isExistsPhone['wxLogin'] || $isExistsPhone['qqLogin'])
            {
                $this->ajaxReturn(array(
                    'status'=>-2,
                    'msg'=>'该手机号已经绑定qq或微信'
                ));
            }
        }
        $phone = "$phone";
        $code = rand(100000, 999999);
        $code = "$code";
        session('registerCode',$code);
        session('registerPhone', $phone);
        session('codeTime',time());    //获取验证时的时间戳
        // 发送验证码
        
        vendor('Alidayu.Alidayu');
        $sendMsg = new \Alidayu();
        if($flag)
        {
            $result = $sendMsg->sendMsg(session('registerCode'),$phone,'绑定qq或微信');
        }else{
            $result = $sendMsg->sendMsg(session('registerCode'),$phone,'注册');
        }
        
        if($result->sub_msg)
        {
            $this->ajaxReturn(array(
                'status' => - 5,
                'msg' => '短信发送失败'
            ));
        }else{
            $this->ajaxReturn(array(
                'status' => 0,
                'msg' => '短信发送成功'
            ));
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
        $data['userPhone'] = session('registerPhone');
        $data['loginPwd'] = I('pwd');
        $code = I('code');
        if (! $data['userPhone'] || ! $data['loginPwd']) {
            // 用户名密码必填
            $this->ajaxReturn($res);
            return;
        }
        $isExistsPhone = M('users')->where(array(
            'userPhone' => $data['userPhone'],
            'userFlag' => 1
        ))->find();
        if ($isExistsPhone) {
            // 用户已经存在
            $this->ajaxReturn(array(
                'status' => - 2
            ));
            return;
        }
        $partnerId = authCode(_getcookie('partnerId'), 'DECODE');
        
        //判断用户是否已购买过会员商品或是否已成为代理，如果isAgent为0，则推广的不做记录
        //$iAgent=M('users')->where(array('userId'=>$partnerId))->getField('isAgent');
        //$iAgent=$iAgent==1?$iAgent:0;
        /**
         * @author peng	
         * @date 2017-01-10
         * @descreption 如果用户已经成为了会员则推广有效
         */
        if(M('users')->find($partnerId)['rank']){
            $data['partnerId'] = $partnerId;
        }else{
            $data['partnerId'] = 0;
        }
        
        
        $data['createTime'] = date('Y-m-d H:i:s', time());
        $data['lastIP'] = get_client_ip();
        $data['userStatus'] = 1;
        $data['userFlag'] = 1;
        $data['loginSecret'] = mt_rand(1000, 9999);
        $data['loginPwd'] = md5($data['loginPwd'] . $data['loginSecret']);
        $data['userType'] = 0;
        #$data['nonce']=_getcookie('nonce');
        $db = M('users');
        $r = $db->add($data);
        if ($r) {
            /**
             * @author peng
             * @date 2017-01
             * @descreption 
             */
            $res['return_back_uri']=_getcookie('ref')?:U('Login/login',array('r'=>'my'));
            _setcookie('ref', null);
            
            session('oto_userId', $r);
            $res['status'] = 0;
            _setcookie('partnerId', null);
            session('registerPhone', null);
            session('registerCode', null);
            #_setcookie('nonce', null);
            /**
             * @author peng
             * @date 2017-01
             * @descreption peng
             */
            $_SESSION['oto_userId']=$r;
            $this->ajaxReturn($res);
        } else {
            // 注册失败
            $this->ajaxReturn(array(
                'status' => - 4
            ));
        }
    }

    public function checkRegisterCode()
    {
        $code = I('code');
        if(time() - session('codeTime') > 300)
        {
            $this->ajaxReturn(array(
                'status' => -2,
                'msg' => '验证码失效'
            ));
        }else if ($code == session('registerCode')) {
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
    /**
     * @author 魏永就
     * @function share
     * @description 分享页面
     *
     */
    public function share()
    {
        $parseUrl = parse_url($_GET['url']);
         
        $url = 'http://'.$parseUrl['host'].$parseUrl['path'].'?partner='._getcookie('tmp_partnerId');
        
        $this->assign('url',$url);
        $this->display();
    }
}
