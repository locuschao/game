<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class PayPwController extends BaseController
{  
    public function __contruct() {
        parent::__construct();
        parent::isLogin();
    }
    /**
     * @api {post} PayPw/setPayPwd 设置支付密码
     * @apiParam {Number} pwd 支付密码
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function setPayPwd()
    {
        $post = getData();
        $pwd = $post['pwd'];
        $this->checkPwd($pwd);
        $map['userId'] = $_SESSION['userId'];
        $loginSecret = M('users')->where($map)->getField('loginSecret');
        $newPwd = md5($pwd . $loginSecret);
        $res = M('users')->where($map)->save(array(
            'payPwd' => $newPwd
        ));
        if ($res) {
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '支付密码设置成功'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '请稍候重试'
            ));
        }
    }
    
    public function checkPwd($pwd) {
         if (!is_numeric($pwd) || strlen($pwd) != 6) {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '密码必须是6位数字'
            ));
        }
    }
    
    
    /**
     * @api {post} PayPw/editPayPw 修改支付密码
     * @apiParam {Number} resoucePwd 原支付密码
     * @apiParam {Number} passwd 支付密码
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
   function editPayPw() {
        $post = getData();
        $db = M('users');
        $userId = $_SESSION['userId'];
        // 判断 是否是当前手机与登录手机一致
        $userInfo = $db->where(array('userId'=>$userId))->find();
        $resoucePwd = trim($post['resoucePwd']);
        $passwd = trim($post['passwd']);
        $this->checkPwd($passwd);
        
        if (md5($resoucePwd . $userInfo['loginSecret'] ) != $userInfo['payPwd']) {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '支付密码不正确'
            ));
        }
        if(md5($post['payPwd'] . $r['loginSecret']) == $r['loginPwd']) $this->error('支付密码不能和登录密码一样');
        $data['lastIP'] = get_client_ip();
        $data['payPwd'] = md5($passwd . $userInfo['loginSecret']);
        
        if ($db->where(array('userId'=>$userId))->save($data)) {
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '支付密码已经重置'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '支付重置密码失败'
            ));
        }
    }
    
    /**
     * @api {post} PayPw/sendCode 找回支付密码发送手机验证码
     * @apiParam {Number} phone 手机号
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": 短信发送成功,
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": 手机不存在,
     *      }
     *      {
     *        "status": 0,
     *        "info": 短信发送失败,
     *      }
     */
    public function sendCode() {
        parent::isLogin();
        $post = getData();
        if(!isMobile($post['phone'])){
            $this->error('手机格式不正确');
        }
        if(!M('users')->where([
            'userPhone'=>$post['phone']
        ])->find()){
            $this->error('手机不存在');
        }
        $res = D('Base')->sendPhoneCode($post['phone'],'找回支付密码');
        if ($res['status'] == 1) {
            $this->ajaxReturn([
                'status' => 1,
                'info' => '短信发送成功',
             ]);
        } else {
            $this->ajaxReturn([
                'status' => 0,
                'info' => '短信发送失败'
            ]);
        }
    }
    
    
    /**
     * @api {post} PayPw/resetPwHandle 找回支付密码的重置密码流程
     * @apiParam {Number} code 验证码
     * @apiParam {Number} payPwd 支付密码
     * @apiParam {Number} phone 手机号
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": 支付密码已经重置,
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": 验证码错误,
     *      }
     *      {
     *        "status": 0,
     *        "info": 重置密码失败,
     *      }
     */
    public function resetPwHandle() {
        $post = getData();
        $info = D('Base')->checkRegisterCode();
        if($info['status'] !=1) $this->ajaxReturn($info);
        $db = M('users');
        $userId = $_SESSION['userId'];
        // 判断 是否是当前手机与登录手机一致
        $r = $db->where(array('userId'=>$userId))->find();
        if(md5($post['payPwd'] . $r['loginSecret']) == $r['loginPwd']) $this->error('支付密码不能和登录密码一样');
        if ($r['userPhone']!=$post['phone']) {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '当前手机与登录的不一致'
            ));
        }
        $this->checkPwd($post['payPwd']);
        $data['lastIP'] = get_client_ip();
        $data['payPwd'] = md5($post['payPwd'] . $r['loginSecret']);
        if ($db->where(array('userId'=>$userId))->save($data)) {
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '支付密码已经重置'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '支付重置密码失败'
            ));
        }
    }
    
    
    
}