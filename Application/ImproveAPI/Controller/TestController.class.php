<?php
namespace ImproveAPI\Controller;
use Think\Controller;
use Think\Model;
/**
 * @author peng
 * @date 2017-07
 * @descreption 测试
 */
class TestController extends BaseController
{
    public function index(){
        echo _encrypt('2e17VFMHU1IHB1QFA1cAUg5XB1MGAgoHAgYDAANT','DECODE');
    }
    
}