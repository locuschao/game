<?php
namespace Admin\Model;

/**
 * 店铺分类服务类
 */
class ShopsCatsModel extends BaseModel
{

    /**
     * 获取列表
     */
    public function queryByList($shopId, $parentId)
    {
        $m = M('shops_cats');
        return $m->where('shopId=' . (int) $shopId . ' and catFlag=1 and parentId=' . (int) $parentId)->select();
    }
}
;
?>