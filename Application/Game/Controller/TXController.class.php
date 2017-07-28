<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class TXController extends BaseController
{

    public function _initialize()
    {
        parent::isLogin();
    }

    public function index()
    {
        $this->userMoney = M('users')->where(array(
            'userId' => session('oto_userId')
        ))->getField('userMoney');
        $this->bankList = M('banks')->where(array(
            'bankFlag' => 1
        ))->select();
        $this->display();
    }

    public function tixian()
    {
        $m = D('Game/TX');
        $data = $m->applyBankInfo();
        $this->assign('list', $data);
        $this->display();
    }

    public function tixianHandle()
    {
        $m = D('Game/TX');
        $rs = $m->tixianHandle();
        $this->ajaxReturn($rs);
    }
}