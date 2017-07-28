<?php
namespace Home\Controller;

/**
 * 数据导入控制器
 */
class ImportsController extends BaseController
{

    /**
     * 数据导入首页
     */
    public function index()
    {
        $this->isShopLogin();
        $this->display('Shops/import');
    }
}
;
?>