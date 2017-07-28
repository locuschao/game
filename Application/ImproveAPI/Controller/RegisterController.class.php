<?php
namespace ImproveAPI\Controller;
use Think\Controller;
class RegisterController extends BaseController{
    /**
     * @api {post} Register/sendCode 快捷登录的发送手机验证码
     * @apiName 快捷登录的发送手机验证码
     * @apiParam {Number} phone 手机号
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": 短信发送成功,
     *        "code_token": ""
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": 短信发送失败,
     *      }
     */
    public function sendCode() {
        $post = getData();
        if(requestHeader()['token'] || $post['code_token']) $this->error('请求数据异常');
        if(!isMobile($post['phone'])){
            $this->error('手机格式不正确');
        }
        $res = D('Base')->sendPhoneCode($post['phone'],'快捷登录');
        if ($res['status'] == 1) {
            $this->ajaxReturn([
                'status' => 1,
                'info' => '短信发送成功',
                'code_token' => $res['code_token']
            ]);
        } else {
            $this->ajaxReturn([
                'status' => 0,
                'info' => '短信发送失败'
            ]);
        }
    }
    
    
    /**
     * @api {post} Register/quickLogin 用户快捷登录
     * @apiParam {Number} phone 手机号
     * @apiParam {Number} imei 手机imei
     * @apiParam {Number} code 手机验证码
     * @apiParam {Number} code_token 手机验证码token
     * @apiSuccess {object} result 
     *      有密码: {
     *       'status':1,
     *       'token':,
     *       'info':登录成功,
     *        ''''''
     *       }
     *  没有密码 :{object} result 
     *       {
     *       'status':-1,
     *       'token':,
     *       'info':没设置初始密码,
     *        ''''''
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info":验证码错误 ,
     *      }
     *      {
     *        "status": 0,
     *        "info":注册失败 ,
     *      }
     */
    public function quickLogin()
    {
        $post = getData();
        $info = D('Base')->checkRegisterCode();
        if($info['status'] !=1) $this->error($info['info']);
        $isExists = M('users')->where(array(
            'userPhone' => $post['phone'],
            'userFlag' => 1
        ))->find();
        
        if ($isExists['loginPwd']) {
            $client_ip = get_client_ip();
            $data['lastTime'] = date('Y-m-d H:i:s');
            $data['lastIP'] = $client_ip;
            $data['IMEI']= $post['imei'];
            $m = M('users');
            $m->where(" userId=" . $isExists['userId'])
                ->data($data)
                ->save();
            D('Login')->setLoginSession($isExists['userId']);
            $uinfo['ip'] = $client_ip;
            $uinfo['info'] = '登录成功!';
            $uinfo['status'] = 1;
            $uinfo['isAgent'] = $isExists['isAgent'];
            $uinfo['userPhoto'] = $isExists['userPhoto'];
            $uinfo['payPwd'] = empty($isExists['payPwd']) ? 0 : 1;
            $uinfo['userPhone'] = $isExists['userPhone'];
            
            $uinfo['token'] = session_id();
            
            $this->ajaxReturn($uinfo);
        }else{
            if(!$post['loginPwd']) {
                $this->ajaxReturn([
                    'status'=>-1,
                    '没初始密码'
                ]);
            }
        }
        
        
    }
    
    /**
     * @api {post} Register/setPw 快捷登录的设置初始密码
     * @apiParam {Number} phone 手机号
     * @apiParam {Number} imei 手机imei
     * @apiParam {Number} code 手机验证码
     * @apiParam {Number} code_token 手机验证码token
     * @apiParam {string} loginPwd 设置登录密码
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info":注册成功,
     *        'token':
     *       }
     * @apiError {object} error  
     *      {
     *        "status": -1,
     *        "info": 验证码过期,
     *      }
            {
     *        "status": 0,
     *        "info": 验证码错误,
     *      }
     *       {
     *        "status": -2,
     *        "info": 手机不匹配,
     *      }
     */
    public function setPw() {
        $post = getData();
        $info = D('Base')->checkRegisterCode();
        if($info['status'] !=1) $this->ajaxReturn($info);
        
        if (M('users')->where(array(
            'userPhone' => $post['phone'],
            'userFlag' => 1
        ))->find()['loginPwd']) {
            $this->error('用户密码已存在');
        }
        
        if(!isMobile($post['phone'])){
            $this->error('手机格式不正确');
        }
        
        if(strlen($post['loginPwd']) < 6){
            $this->error('密码要大于6位数字');
        }
        if(!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,18}$/',$post['loginPwd'],$match)){
            $this->error('要包含字母和数字');
        }
        
        $data['createTime'] = date('Y-m-d H:i:s', time());
        $data['lastIP'] = get_client_ip();
        $data['userStatus'] = 1;
        $data['userFlag'] = 1;
        $data['loginName'] = $post['phone'];
        $data['userPhone'] = $post['phone'];
        $data['loginSecret'] = mt_rand(1000, 9999);
        $data['loginPwd'] = md5($post['loginPwd'] . $data['loginSecret']);
        $data['userType'] = 0;
        $data['IMEI']=I('imei');
        $data['uniqueId']=D('User')->createUserId();
        if ($user_id = M('users')->add($data)) {
            $res['status'] = 1;
            $res['info'] = '注册成功！';
            $res['userPhone'] = $post['phone'];
            
            $_SESSION['userid'] = $user_id;
            $res['token'] = session_id();
            $this->ajaxReturn($res);
        } else {
            // 注册失败
            $this->ajaxReturn([
                'status' => 0,
                'info' => '注册失败'
            ]);
        }
    }
    
    
}