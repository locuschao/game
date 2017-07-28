<?php
namespace Admin\Controller;

/**
 * 店铺分类控制器
 */
class ShopsCatsController extends BaseController
{

    /**
     * 列表查询
     */
    public function queryByList()
    {
        $m = D('Admin/ShopsCats');
        $list = $m->queryByList(I('shopId', 0), I('id', 0));
        $rs = array();
        $rs['status'] = 1;
        $rs['list'] = $list;
        $this->ajaxReturn($rs);
    }
}
;
?>