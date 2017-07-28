<?php
namespace ImproveAPI\Controller;
use Think\Controller;
/**
 * @author peng
 * @date 2017-06
 * @descreption 登录
 */
class LoginController extends BaseController{
    
    /**
     * @api {post} Login/loginHander 密码登录
     * @apiName 快捷登录的发送手机验证码
     * @apiParam {string} userName 用户名
     * @apiParam {string} passWord 登录密码
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info": ,
     *        
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": ,
     *      }
     */
    public function loginHander() {
        $post = getData();
        $uname = $post['userName'];
        $upwd = trim($post['passWord']);
        $imei=I('IMEI');
        if (! $uname || ! $upwd) {
            $this->error('账号或密码不能为空');
        }
        $map['userPhone'] = $uname;
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
                $uinfo['info'] = '登录成功!';
                $uinfo['status'] = 1;
                $uinfo['isAgent'] =$isExists['isAgent'];
                $uinfo['userPhoto'] = $isExists['userPhoto'];
                //$uinfo['loginPwd'] = empty($isExists['loginPwd']) ? 0 : 1;
                $uinfo['payPwd'] = empty($isExists['payPwd']) ? 0 : 1;
                //$uinfo['userPhone'] = $isExists['userPhone'];
                //$uinfo['userId'] = authcode($isExists['userId'], 'ENCODE');
                D('Login')->setLoginSession($isExists['userId']);
                $uinfo['token'] = session_id();
                $this->ajaxReturn($uinfo);
            } else {
                $this->ajaxReturn([
                    'status'=>0,
                    'info'=>'登录密码错误！'
                ]);
            }
        } else {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '用户不存在'
            ));
        }
    }
    
    /**
     * @api {post} Login/editLoginPw 修改登录密码
     * @apiParam {Number} resoucePwd 原登录密码
     * @apiParam {Number} passwd 登录密码
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
   public function editLoginPw() {
        parent::isLogin();
        $post = getData();
        $db = M('users');
        $userId = $_SESSION['userId'];
        // 判断 是否是当前手机与登录手机一致
        $userInfo = $db->where(array('userId'=>$userId))->find();
        $resoucePwd = trim($post['resoucePwd']);
        $passwd = trim($post['passwd']);
        if(strlen($passwd) < 6){
            $this->error('密码要大于6位数字');
        }
        if(!preg_match('/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{6,18}$/',$passwd,$match)){
            $this->error('要包含字母和数字');
        }
        
        if (md5($resoucePwd . $userInfo['loginSecret']) != $userInfo['loginPwd']) {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '原登录密码不正确'
            ));
        }
      
        $data['lastIP'] = get_client_ip();
        $data['loginPwd'] = md5($passwd . $userInfo['loginSecret']);
        
        if ($db->where(array('userId'=>$userId))->save($data)) {
            $this->ajaxReturn(array(
                'status' => 1,
                'info' => '登录修改成功'
            ));
        } else {
            $this->ajaxReturn(array(
                'status' => 0,
                'info' => '登录修改失败'
            ));
        }
    }
    
    /**
     * @api {post} Login/loginOut 退出登录
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
    public function loginOut() {
        unset($_SESSION);
        if(!$_SESSION['userId'])
        $this->ajaxReturn([
        'status'=>1,
        'info'=>'退出成功'
        ]);
        else $this->error('退出失败');
    }
    
}