<?php
namespace Native\Controller;
use Think\Controller;
class TestController extends Controller{
    public function index(){
        $info=M('users')->select();
        $charset[1] = substr($info, 0, 1);
        $charset[2] = substr($info, 1, 1);
        $charset[3] = substr($info, 2, 1);
        if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
                $rest = substr($info, 3);
        }
        echo (json_encode($info));
       
       
    }
}
?>