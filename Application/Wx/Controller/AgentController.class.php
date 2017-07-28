<?php

namespace Wx\Controller;

use Think\Controller;
use Think\Model;
class AgentController extends Controller {

    public function __construct(){

        parent::__construct();
        $this->checkLogin();
        $this->model = D('Wx/Agent');

    }

    public function checkLogin(){

        if(!$_SESSION['oto_mall']['oto_userInfo']){
            $this->redirect("Wx/Login/Login");
        }else{

        }

    }

    public function index(){


        $userinfo = $this->model->userAgentPrice();
        $fansInfo=   $this->model->fansCount();

        $level['one']      =     $this->model->enCodeString(1);
        $level['two']      =     $this->model->enCodeString(2);
        $level['three']      =     $this->model->enCodeString(3);
        $selfId = $this->model->enCodeString($userinfo['userId']);

        $userinfo['selfId'] = $selfId;



        $this->assign("level",$level);
        $this->assign("userinfo",$userinfo);
        $this->assign("fansInfo",$fansInfo);


        $this->display();
    }



    public function postapply(){
        $data = $this->model->applyBankInfo();


        $this->assign('list',$data);
        $this->display();
    }


    public function allFansinfo(){

        $level=I('get.level');

      $level   =  $this->model->enCodeString($level,'DECODE');
       $title    =$this->model->fanslevel($level);

        if(IS_AJAX){

            $data =    $this->model->allFansinfo($level);
            $this->ajaxReturn($data);
        }





        $this->assign('list',$data);
        $this->assign('title',$title);
        $this->assign('level',I('get.level'));
        $this->display();
    }

    public function allFansOrder(){

        $loginName = I('get.userId');

        $loginName = $this->model->enCodeString($loginName,'DECODE');
        $loginName = explode('|',$loginName);
        if(IS_AJAX){
          $data      =  $this->model->thisFansOrder($loginName[1]);
          $this->ajaxReturn($data);
        }



        $this->assign('title',$loginName[0]);

        $this->assign('userId',I('get.userId'));
        $this->display();
    }

    public function deleteBank(){

      $res = $this->model->deleteBank();

        $this->ajaxReturn($res);
    }



}
