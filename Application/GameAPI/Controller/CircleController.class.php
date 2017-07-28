<?php
namespace GameAPI\Controller;

use Think\Controller;
use Think\Model;

class CircleController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $m = D('Wx/Circle');
        
        if (IS_AJAX) {
            $data = $m->listData();
            $this->ajaxReturn($data);
        }
        
        $data = $m->listData();
        
        $this->assign('list', $data);
        $this->display();
    }

    public function Detail()
    {
        $m = D('Wx/Circle');
        if (IS_AJAX) {
            $data = $m->Detail();
            $this->ajaxReturn($data);
        }
        
        $data = $m->Detail();
        $this->assign('data', $data);
        
        // dump($data['likeInfo']);
        $this->display();
    }

    public function like()
    {
        if (IS_AJAX) {
            $data = D('Wx/Circle')->likes();
            
            $this->ajaxReturn($data);
        }
    }
}
