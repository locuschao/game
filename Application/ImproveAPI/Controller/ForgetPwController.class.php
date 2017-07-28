<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
class ForgetPwController extends BaseController
{   
    /**
     * @api {post} ForgetPw/sendCode 找回密码发送手机验证码
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
        $post = getData();
        if(requestHeader()['token'] || $post['code_token']) $this->error('请求数据异常');
        if(!isMobile($post['phone'])){
            $this->error('手机格式不正确');
        }
        if(!M('users')->where([
            'userPhone'=>$post['phone']
        ])->find()){
            $this->error('手机不存在');
        }
        $res = D('Base')->sendPhoneCode($post['phone'],'找回密码');
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
     * @api {post} ForgetPw/checkCode 忘记密码 ：验证手机验证码接口
     * @apiParam {Number} code_token 手机验证码token
     * @apiParam {Number} code 验证码
     * @apiParam {Number} phone 手机号码
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": 验证成功,
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function checkCode() {
        
        $this->ajaxReturn(D('Base')->checkRegisterCode());
    }
    
    
    /**
     * @api {post} ForgetPw/editpw 忘记密码，修改密码
     * @apiParam {string} code_token 手机验证码token
     * @apiParam {Number} code 验证码
     * @apiParam {Number} loginPwd 修改密码
     * @apiParam {Number} phone 手机号
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": 密码已经重置,
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
    public function editpw() {
        $post = getData();
        
        $info = D('Base')->checkRegisterCode();
        if($info['status'] !=1) $this->ajaxReturn($info);
        
        $userinfo = M('users')->where(array(
            'userPhone' => $post['phone'],
            'userFlag' => 1
        ))->find();
        if(!$userinfo) $this->error('手机不存在');
        if(md5($post['loginPwd'] . $userinfo['loginSecret']) == $userinfo['loginPwd']){
            $this->error('不能与原密码一样');
        }
        
        if(strlen($post['loginPwd']) < 6){
            $this->error('密码要大于6位数字');
        }
        if(!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,18}$/',$post['loginPwd'],$match)){
            $this->error('要包含字母和数字');
        }
        
        $data['lastIP'] = get_client_ip();
        
        $data['loginPwd'] = md5($post['loginPwd'] . $userinfo['loginSecret']);
        
        if (M('users')->where(array(
            'userPhone' => $userinfo['userPhone'],
            'userFlag' => 1,
            'userType' => 0
        ))->save($data)) {
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '密码已经重置'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '重置密码失败'
            ));
        }
    }
    
    
    
}
