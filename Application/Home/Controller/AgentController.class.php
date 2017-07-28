<?php
namespace Home\Controller;

/**
 * 分销模块
 */
class AgentController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function queryFans()
    {
        $USER = session('WST_USER');
        $this->assign("agent", $USER['checkagent']);
        $this->assign("umark", "queryFans");
        
        $this->display("Agent/list_fans");
    }

    public function fansinfo()
    {
        $userId = $_SESSION['oto_mall']['WST_USER']['userId'];
        
        $m = D('Home/Agent');
        $data = $m->fansInfo($userId);
        // dump($data);
        
        echo json_encode($data);
    }

    public function index()
    {
        $this->isUserLogin();
        $USER = session('WST_USER');
        session('WST_USER.loginTarget', 'User');
        $m = D('Home/Agent');
        $selftinfo = $m->selfInfo();
        
        $applydata = $m->agentPriceLog();
        $applyLogdata = $m->checkApplylog();
        $checkApplyTime = $m->checkTime();
        
        $this->assign('checkApplyTime', $checkApplyTime);
        $this->assign('applyLog', json_encode($applyLogdata));
        $this->assign('apply', json_encode($applydata));
        $this->assign('info', $selftinfo);
        $this->assign("umark", "agent");
        $this->assign("agent", $USER['checkagent']);
        $this->display("Agent/list_agent");
    }

    public function logInfo()
    {
        $m = D('Home/Agent');
        $re = $m->info();
        
        echo json_encode($re);
    }

    public function payview()
    {
        $m = D('Home/Agent');
        $info = $m->selfInfo();
        $this->assign('user', $info);
        $this->display("Agent/work");
    }

    public function text()
    {
        $data['time'] = time();
        
        $data['userId'] = '1';
        $data['userType'] = '1';
        $data['loginName'] = 'shop2282862';
        $data['applyPrice'] = 100.00;
        $data['bankName'] = '中国人寿';
        $data['bankAccesss'] = '广州白云区';
        $data['remark'] = '快递';
        $data['password'] = '123456';
        $data['captcha'] = '123456';
        
        $data = M('agentApply')->data($data)->add();
        // $sql= "INSERT INTO `oto_agent_apply` ( `userId`, `loginName`, `userType`, `tel`, `captcha`, `applyPrice`, `bankUserName`, `bankName`, `bankAccesss`, `status`, `bankNum`, `remark`, `time`) VALUES ( '1', 'goog', '1', '18218602662', '231', '12.00', '周某某', '中国银行', '广州百华', '0', '45454123', '好的', '12312321');";
        // $status= M()->query($sql);
        echo $data;
    }

    public function apply()
    {
        $m = D('Home/Agent');
        $data = $m->checkApply();
        
        $this->ajaxReturn($data);
    }

    public function Balance()
    {
        $m = D('Home/Agent');
        $data = $m->checkBalance();
        
        echo (json_encode($data));
    }

    public function applyPs()
    {
        $m = D('Home/Agent');
        $data = $m->CheckApplyPassword();
        
        echo (json_encode($data));
    }

    public function applyLog()
    {
        $m = D('Home/Agent');
        
        $data = $m->checkApplylog();
        
        echo (json_encode($data));
    }
}