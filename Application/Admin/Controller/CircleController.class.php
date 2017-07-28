<?php
namespace Admin\Controller;

/**
 * 圈子模块控制器
 */
class CircleController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $m = D('Admin/Circle');
        
        $list = $m->index();
        // dump(json_encode($list));
        if (IS_AJAX) {
            $re = $m->change();
            
            $this->ajaxReturn($re);
        }
        
        $this->assign('Circle', json_encode($list));
        $this->display("circle/index");
    }

    public function look()
    {
        $id = (int) I('get.circleId');
        if (! $id) {
            $this->error('非法参数');
        }
        $data = D('Admin/Circle')->look();
        $this->assign('look', $data);
        $this->display('circle/look');
    }
}
;
?>
