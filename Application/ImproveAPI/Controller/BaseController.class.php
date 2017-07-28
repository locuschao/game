<?php
namespace ImproveAPI\Controller;

use Think\Controller;
use Think\Model;

class BaseController extends Controller
{   
    private $token;
    
    public function isLogin() {
        $isLogin = D('Login')->isLogin();
        if($isLogin['status'] == 0){
            $this->ajaxReturn([
                'status'=>1001, //不是登录状态
                'info'=>$isLogin['info']
            ]);
        }
    }
    public function _initialize(){
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers:x-requested-with,content-type,token,version,shopId,client'); 
        $this->initSession();
    }
    
    //author: peng descreption:
    public function initSession() {
        $rqh = requestHeader();
        $data = getData();
        if($rqh['token']){
            //登录状态的初始化
            session_id($rqh['token']);
        }else if($data['code_token']){
            //手机验证码初始化
            session_id($data['code_token']);
        }
        session_start();
    }
    
    public function Get($url)
    {
        if (function_exists('file_get_contents')) {
            $file_contents = file_get_contents($url);
        } else {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }
        return $file_contents;
    }

    public function returnJson($arr)
    {
        header('Content-Type:application/json; charset=utf-8');
        $str = json_encode($arr);
        exit($str);
    }
    
}