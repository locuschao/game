<?php
namespace ImproveAPI\Controller;
use Think\Controller;
class UserController extends BaseController{
    /**
     * @api {post} User/isLogin 验证登录状态
     * @apiParam {string} token
     * @apiSuccess {object} result 
     *       {
     *        "status": 1,
     *        "info"登录: ,
     *       }
     * @apiError {object} error  
     *      {
     *        "status": 0,
     *        "info": 过期或者无效,
     *      }
     */
    
   public function isLogin(){
        $this->ajaxReturn(D('Login')->isLogin());
   }
    
    /**
     * @api {post} User/userInfo 用户信息
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
    public function userInfo() {
        parent::isLogin();
        $userInfo = M('users')
        ->field('payPwd,userPhone,userPhoto,userName,userMoney,rank,userScore,personalizedSignature')
        ->where([
        'userId'=>$_SESSION['userId']
        ])
        ->find();
        $userInfo['payPwd'] = $userInfo['payPwd']?1:0;
        $userInfo['userPhoto'] = $userInfo['userPhoto']?C('RESOURCE_URL').$userInfo['userPhoto']:'';
        $userInfo['userPhone'] = hidePhone($userInfo['userPhone']);
        $userInfo['rank'] = D('User')->rank_text[$userInfo['rank']]?:'成为会员'; 
        if($userInfo) $this->ajaxReturn([
            'status'=>1,
            'userinfo'=>$userInfo
        ]);
        else $this->error('没有数据');
    }
}