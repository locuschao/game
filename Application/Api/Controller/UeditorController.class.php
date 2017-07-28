<?php
namespace Api\Controller;

use Think\Controller;
class UeditorController extends Controller {

    //BaiDu Editor
    public function Ueditor(){
        $data = new \Org\Util\Ueditor();
        echo $data->output();

    }
    public function enCodeString_app()
    {
        $user_id=$_REQUEST['user_id'];
        $data[0]['user_id']=$this->enCodeString($user_id);
        echo json_encode($data);
    }
    public function enCodeString($string,$mode='ENCODE'){
        return   _encrypt($string,$mode);
    }
}