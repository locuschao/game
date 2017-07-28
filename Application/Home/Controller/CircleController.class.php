<?php
namespace Home\Controller;

/**
 * 圈子控制器
 */
class CircleController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->model = D('Home/Circle');
    }

    public function index()
    {
        $list = $this->model->index();
        
        $this->assign('umark', 'Circle');
        $this->assign('Circle', json_encode($list));
        // dump($list);
        $this->display("Circle/index");
    }

    public function modify()
    {
        if (IS_AJAX) {
            
            $response = $this->model->action();
            $this->ajaxReturn($response);
        }
        
        $Circle = $this->model->getGoods();
        
        $this->assign('Circle', $Circle);
        $this->assign('umark', 'Circle');
        $this->display("Circle/modify");
    }

    public function look()
    {
        $id = I('get.circleId');
        if (! $id) {
            $this->error('非法参数');
        }
        $data = $this->model->look();
        
        $this->assign('look', $data);
        $this->display('Circle/look');
    }
}
?>