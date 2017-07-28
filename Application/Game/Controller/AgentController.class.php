<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class AgentController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->checkLogin();
        
        $this->model = D('Game/Agent');
    }

    public function checkLogin()
    {
       // if (! $_SESSION['oto_mall']['oto_userInfo']) {
//            $this->redirect("Game/Login/login");
//        } else {}
        if (! $_SESSION['oto_mall']['oto_userId']) {
            $this->redirect("Game/Login/login");
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
        
        //$userinfo['agentTotalPrice']=M('percentage_log')->where(['userId'=>session('oto_userId')])->sum('gain_price');
        $fansInfo = $this->model->fansCount();
        
        $level['one'] = $this->model->enCodeString(1);
        $level['two'] = $this->model->enCodeString(2);
        $level['three'] = $this->model->enCodeString(3);
        $selfId = authCode($userinfo['userId'],'ENCODE');
        
        //$userinfo['selfId'] = $selfId;
        $userinfo['selfId'] = urlencode($selfId);
        
        _setcookie('tmp_partnerId', $userinfo['selfId']);
        
        $this->assign("level", $level);
        $this->assign("userinfo", $userinfo);
        $this->assign("fansInfo", $fansInfo);
        
        $this->display();
    }

    public function postapply()
    {
        $data = $this->model->applyBankInfo();
        
        $this->assign('list', $data);
        $this->display();
    }

    public function allFansinfo()
    {
        $level = I('get.level');
        $level = $this->model->enCodeString($level, 'DECODE');
        $title = $this->model->fanslevel($level);
        $data = $this->model->allFansinfo($level);
        if (IS_AJAX) {
            foreach(M('users')->where(['partnerId'=>session('oto_userId')])->select() as $row){
                //$data['name']=
            }
            $this->ajaxReturn($data);
        }
        $this->assign('list', $data);
        $this->assign('title', $title);
        $this->assign('level', I('get.level'));
        $this->display();
    }

    public function allFansOrder()
    {
        $loginName = I('get.userId'); // 我的下级的userId
        
        $loginName = $this->model->enCodeString($loginName, 'DECODE');
        
        if (IS_AJAX) {
            $data = $this->model->thisFansOrder($loginName);
            $this->ajaxReturn($data);
        }
        
        $this->assign('title', $loginName[0]);
        
        $this->assign('userId', I('get.userId'));
        $this->display();
    }

    public function deleteBank()
    {
        $res = $this->model->deleteBank();
        
        $this->ajaxReturn($res);
    }
    /**
     * @author peng	
     * @date 2017-01-07
     * @descreption 
     */
     public function getFenchengList(){
        $l = I('get.limit');
        $limit = isset($l) ? I('get.limit') : 0;
        
        $loginName = I('get.userId'); // 我的下级的userId
        $data=M('orders')->where([
            'is_fencheng'=>1,
            'o.userId'=> $this->model->enCodeString($loginName, 'DECODE')
         ])->join('o inner join '.C('DB_PREFIX').'percentage_log p on o.orderId=p.orderId')
         ->limit($limit, 10)
         ->select();
         
        $data['limit'] = $limit + 10;
        if(IS_AJAX){
            $this->ajaxReturn($data);
        }
        
        
     	
     }
}
