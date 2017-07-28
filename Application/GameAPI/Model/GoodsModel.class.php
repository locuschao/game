<?php
namespace GameAPI\Model;

use Think\Model;

class GoodsModel extends Model
{
    /**
     * @author peng
     * @date 2017-05
     * @descreption 获取商店销量
     */
    public function getShopNum($shopId) {
        return M('order_goods')->where(array(
            'o.orderStatus' => array(
                'in',
                '3,-6,-8'
            ),
            'o.shopId' => $shopId
        ))->join('as g join oto_orders as o on o.orderId=g.orderId')
          ->SUM('goodsNums')+(($shopId==18)?30000:0);
    }
}