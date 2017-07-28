<?php
namespace GameAPI\Controller;

use Think\Controller;
use Think\Model;

class AgentController extends BaseController
{

    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = D('GameAPI/Agent');
    }

    public function checkLogin()
    {
        if (! $_SESSION['oto_mall']['oto_userInfo']) {
            $this->redirect("Wx/Login/Login");
        } else {}
    }
    
    // 分销说明
    public function explain()
    {
        $this->display();
    }

    public function index()
    {
        $userinfo = $this->model->userAgentPrice();
        $fansInfo = $this->model->fansCount();
        
        $level['one'] = $this->model->enCodeString(1);
        $level['two'] = $this->model->enCodeString(2);
        $level['three'] = $this->model->enCodeString(3);
        $selfId = $this->model->enCodeString($userinfo['userId']);
        $userinfo['selfId'] = $selfId;
        
        $arr = array();
        $arr['level'] = $level;
        $arr['userinfo'] = $userinfo;
        $arr['fansInfo'] = $fansInfo;
        $this->returnJson($arr);
    }
    
    // 提现页面数据
    public function postapply()
    {
        $data = $this->model->applyBankInfo();
        $this->returnJson($data);
    }
    
    // 申请提现
    public function apply()
    {
        $data = $this->model->checkApply();
        $this->returnJson($data);
    }

    public function allFansinfo()
    {
        $level = I('level', 0, 'intval');
        $title = $this->model->fanslevel($level);
        $data = $this->model->allFansinfo($level);
        $this->returnJson($data);
    }

    public function allFansOrder()
    {
        $loginName = I('cid');
        $page = I('page', 0, 'intval');
        $loginName = $this->model->enCodeString($loginName, 'DECODE');
        $data = $this->model->thisFansOrder($loginName,$page);
        $this->returnJson($data);
    }

    public function deleteBank()
    {
        $userId = authCode(I('userId'));
        $res = $this->model->deleteBank($userId);
        $this->returnJson($res);
    }
}
