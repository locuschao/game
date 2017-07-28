<?php
namespace GameAPI\Controller;

use Think\Controller;
use Think\Model;

class TXController extends BaseController
{

    public function _initialize()
    {}

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
        $userId = authCode(I('userId'));
        $m = D('GameAPI/TX');
        $data = $m->applyBankInfo($userId);
        $this->returnJson($data);
    }

    public function bankList()
    {
        $userId = authCode(I('userId'));
        $m = D('GameAPI/TX');
        $data = $m->applyBankInfo($userId);
        $info = $data['self'];
        $this->returnJson($info);
    }

    public function tixianHandle()
    {
        $m = D('GameAPI/TX');
        $userId = authCode(I('userId'));
        $rs = $m->tixianHandle($userId);
        $this->returnJson($rs);
    }
}