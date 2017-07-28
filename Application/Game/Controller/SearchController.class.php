<?php
namespace Game\Controller;

use Think\Controller;
use Think\Model;

class SearchController extends BaseController
{

    public function _initialize()
    {}
    
    // 搜索页面
    public function search()
    {
        $brand = I('bid');
        $page = I('get.page');
        $sort = I('get.sorts');
        $type = I('get.type');
        $sales = I('get.sales');
        $star = I('get.star');
        $end = I('get.end');
        $key = I('get.key');
        
        $limit = 10;
        $step = $page * $page;
        
        if (! is_numeric($star)) {
            $star = 0;
        }
        if (! is_numeric($end)) {
            $end = 0;
        }
        // 搜索商品
        if ($type == 'goods' && ! empty($key)) {
            $order = "";
            $sale = "";
            if ($sales) {
                $sale = 'monthCount DESC , ';
            }
            switch ($sort) {
                // 智能
                case 'smart':
                    $order = "$sale goodsId DESC";
                    break;
                // 配送费
                case 'freight':
                    $order = "$sale deliveryStartMoney ASC";
                    break;
                // 销量
                case 'volume':
                    $order = "monthCount DESC";
                    break;
                default:
                    $order = "goodsId DESC";
            }
            $where = " where g.goodsName like '%$key%' ";
            if ($brand) {
                $where = " where g.goodsName like '%$key%' and g.brandId = $brand ";
            }
            
            $where .= " and goodsFlag=1 and goodsStatus=1";
            if ($star && $end) {
                if ($end > $star) {
                    $having = " HAVING  g.shopPrice between $star and $end ";
                } else {
                    $having = " HAVING   g.shopPrice >= $star ";
                }
            } else 
                if ($star && ! $end) {
                    $having = " HAVING   g.shopPrice >= $star ";
                } else 
                    if (! $star && $end) {
                        $having = "  HAVING  g.shopPrice <=$end ";
                    }
            
            $sql = <<<Eof
SELECT
	g.goodsId,
	g.goodsName,
	g.goodsThums,
	g.marketPrice,
	g.shopPrice,
	s.deliveryStartMoney,
	(
		SELECT
			count(goodsId)
		FROM
			oto_order_goods
		JOIN oto_orders ON oto_order_goods.orderId = oto_orders.orderId
		WHERE
			(
				oto_orders.isPay = 1
				OR oto_orders.payType = 0
			)
		AND oto_orders.isClosed = 0
		AND oto_orders.isRefund = 0
		AND date_sub(curdate(), INTERVAL 30 DAY) <= date(oto_orders.signTime)
		AND oto_order_goods.goodsId = g.goodsId
	) AS monthCount
FROM
	oto_goods AS g
LEFT JOIN oto_order_goods AS og ON g.goodsId = og.goodsId
LEFT JOIN oto_shops AS s ON s.shopId = g.shopId
$where 
GROUP BY
	og.goodsId
	$having 
ORDER BY
	$order
limit $step,$limit
Eof;
            
            $csql = <<<Eof
SELECT
	g.goodsId,
	g.goodsName,
	g.goodsThums,
	g.marketPrice,
	g.shopPrice,
	s.deliveryStartMoney,
	(
		SELECT
			count(goodsId)
		FROM
			oto_order_goods
		JOIN oto_orders ON oto_order_goods.orderId = oto_orders.orderId
		WHERE
			(
				oto_orders.isPay = 1
				OR oto_orders.payType = 0
			)
		AND oto_orders.isClosed = 0
		AND oto_orders.isRefund = 0
		AND date_sub(curdate(), INTERVAL 30 DAY) <= date(oto_orders.signTime)
		AND oto_order_goods.goodsId = g.goodsId
	) AS monthCount
FROM
	oto_goods AS g
LEFT JOIN oto_order_goods AS og ON g.goodsId = og.goodsId
LEFT JOIN oto_shops AS s ON s.shopId = g.shopId
$where
GROUP BY
	og.goodsId
	$having
ORDER BY
	$order
Eof;
            $count = M()->query($csql);
            $this->count = count($count);
            $info = M()->query($sql);
            $this->info = $info;
        } else 
            // 搜索店铺
            if (! empty($key) && $brand) {
                $page = I('get.page');
                $sort = I('get.sorts');
                $sales = I('get.sales');
                $key = I('get.key');
                $limit = 10;
                $step = $page * $page;
                $order = "";
                $sale = "";
                if ($sales) {
                    $sale = 'monthCount DESC , ';
                }
                switch ($sort) {
                    // 智能
                    case 'smart':
                        $order = "$sale shopId DESC";
                        break;
                    // 配送费
                    case 'freight':
                        $order = "$sale deliveryStartMoney ASC";
                        break;
                    // 销量
                    case 'volume':
                        $order = "monthCount DESC";
                        break;
                    default:
                        $order = "shopId DESC";
                }
                $shopWhere = " where s.shopName LIKE '%$key%'";
                // 如果存在品牌
                if ($brand) {
                    $shopWhere = " where  s.shopName LIKE '%$key%' and exists(select 1 from oto_goods where oto_goods.shopId=s.shopId and oto_goods.brandId=$brand)";
                }
                $shopWhere .= "and s.shopStatus=1 and s.shopFlag=1";
                $shop_sql = <<<Eof
            SELECT
	s.shopId,
	s.shopName,
	s.shopImg,
	s.serviceStartTime,
	s.serviceEndTime,
	s.deliveryStartMoney,
    s.deliveryFreeMoney,
                s.avgeCostMoney,
	(
		SELECT
			count(oto_orders.orderId)
		FROM
			oto_order_goods
		JOIN oto_orders ON oto_order_goods.orderId = oto_orders.orderId
		WHERE
			(
				oto_orders.isPay = 1
				OR oto_orders.payType = 0
			)
		AND oto_orders.isClosed = 0
		AND oto_orders.isRefund = 0
		AND date_sub(curdate(), INTERVAL 30 DAY) <= date(oto_orders.signTime)
		AND oto_orders.shopId = s.shopId
	) AS monthCount
FROM
	oto_shops AS s
LEFT JOIN oto_orders AS o ON o.shopId = s.shopId
LEFT JOIN oto_order_goods AS og ON o.orderId = og.orderId
 $shopWhere 
GROUP BY
	s.shopId
ORDER BY
	$sale $order 
Eof;
                $this->shopInfo = M()->query($shop_sql);
                $this->countShop = count($this->shopInfo);
            }
        
        // 推荐一些品牌
        $this->brand = M('brands')->where(array(
            'brandFlag' => 1
        ))
            ->order('brandId DESC')
            ->limit(12)
            ->select();
        $this->display();
    }
}