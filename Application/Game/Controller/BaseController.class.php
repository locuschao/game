<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class BaseController extends Controller
{

    public function _initialize()
    {}

    public function isLogin()
    {
        if (! session('oto_userId')) {
            $this->redirect(U('Login/login', '', '', 0));
        }
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
}

