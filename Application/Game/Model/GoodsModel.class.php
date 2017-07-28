<?php
namespace Game\Model;
/**
 * @author peng	
 * @date 2017-01-07
 * @descreption 
 */
class GoodsModel
{
    public function getRightPrice(&$goodsAttr){
    	foreach($goodsAttr as $k=>$row){
            #低级会员价格
            $goodsAttr[$k]['lmprice']=$row['low_member_price']?:$row['attrPrice'];
            #中级会员价
            $goodsAttr[$k]['mmprice']=($row['mid_member_price']?:$row['low_member_price'])?:$row['attrPrice'];
            #会员最低价也就是高级会员价格
            $goodsAttr[$k]['lowest_price']=min([$row['low_member_price'],$row['mid_member_price'],$row['heigh_member_price']]);
        
        }
    }
    
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