<?php
namespace Admin\Controller;
use Lib\Exp\DataExp;

/**
 * 游戏投诉管理
 */
class GameComplainController extends BaseController
{

    /**
     * 分页查询
     */
    public function index()
    {
        $this->isLogin();
        $this->checkPrivelege('gglb_00');
        $m = D('Admin/GameComplain');
        $page = $m->queryByPage();
        $pager = new \Think\Page($page['total'], $page['pageSize']); // 实例化分页类 传入总记录数和每页显示的记录数
        $page['pager'] = $pager->show();
        $this->assign('Page', $page);
        $this->display("/gamecomplain/list");
    }
    
}
;
?>