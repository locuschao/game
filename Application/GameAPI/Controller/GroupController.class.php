<?php
namespace GameAPI\Controller;

use Think\Controller;

class GroupController extends Controller
{

    public function group()
    {
        $this->display();
    }

    public function search()
    {
        $star = I('get.star');
        $end = I('get.end');
        $sorts = I('get.sorts'); // 排序
        $page = I('get.page');
        $brand = I('get.brand');
        $cates = I('get.cates');
        $order = "";
        switch ($sorts) {
            // 智能排序
            case 'smart':
                $order = " order by gg.endTime ASC ";
                break;
            // 距离最近
            case 'lately':
                $order = " order by gg.endTime ASC ";
                break;
            // 起送价最低
            case 'freight':
                $order = " order by ss.deliveryStartMoney ASC ";
                break;
            // 销量最高
            case 'volume':
                $order = "order by monthSale DESC ";
                break;
        }
        $where = "";
        if ($brand) {
            $where .= " and go.brandId= $brand";
        }
        if ($cates) {
            $where .= " and go.goodsCatId3= $cates";
        }
        if ($star && $end) {
            if ($end > $star) {
                $where = " and  gg.groupPrice between $star and $end ";
            } else {
                $where = " and   gg.groupPrice >= $star ";
            }
        } else 
            if ($star && ! $end) {
                $where = " and   gg.groupPrice >= $star ";
            } else 
                if (! $star && $end) {
                    $where = "  and  gg.groupPrice <=$end ";
                }
        
        $limit = 10;
        $step = $limit * $page;
        $sql = <<<Eof
       SELECT
	gg.*, go.goodsName,
	go.shopPrice,
	go.goodsThums,
           ss.deliveryStartMoney,
	(
		SELECT
			COUNT(og.goodsId)
		FROM
			oto_order_goods AS og
		JOIN oto_orders AS od ON od.orderId = og.orderId
		AND od.orderType = 3
		WHERE
			og.goodsId = gg.goodsId
	) AS monthSale
FROM
	oto_goods_group AS gg
LEFT JOIN oto_goods AS go ON gg.goodsId = go.goodsId  left join oto_shops as ss on ss.shopId=go.shopId
WHERE
	unix_timestamp(now()) BETWEEN gg.startTime
AND gg.endTime and gg.goodsGroupStatus=1 and gg.groupStatus=1 $where $order limit $step,$limit
Eof;
        $info = M()->query($sql);
        $this->info = $info;
        $cateSql = <<<Catesql
        SELECT
	go.goodsCatId3
FROM
	oto_goods_group AS gg
LEFT JOIN oto_goods AS go ON gg.goodsId = go.goodsId
WHERE
	unix_timestamp(now()) BETWEEN gg.startTime
AND gg.endTime and gg.goodsGroupStatus=1 and gg.groupStatus=1   group by  goodsCatId3
Catesql;
        $cate3 = M()->query($cateSql);
        $ids = '';
        foreach ($cate3 as $k => $v) {
            $ids .= $v['goodsCatId3'] . ',';
        }
        // 三级分类
        $ids3 = trim($ids, ',');
        // 店铺分类
        $cat3 = M('goods_cats')->where(array(
            'isShow' => 1,
            'catFlag' => 1,
            'catId' => array(
                'in',
                $ids3
            )
        ))
            ->order('catSort ASC')
            ->select();
        // 二级分类
        $ids2 = '';
        foreach ($cat3 as $k => $v) {
            $ids2 .= $v['parentId'] . ',';
        }
        $ids2 = trim($ids2, ',');
        $ids2 = explode(',', $ids2);
        $ids2 = array_unique($ids2);
        $ids2 = implode(',', $ids2);
        $cat2 = M('goods_cats')->where(array(
            'isShow' => 1,
            'catFlag' => 1,
            'catId' => array(
                'in',
                $ids2
            )
        ))
            ->order('catSort ASC')
            ->select();
        // 一级分类
        $ids1 = '';
        foreach ($cat2 as $k => $v) {
            $ids1 .= $v['parentId'] . ',';
        }
        $ids1 = trim($ids1, ',');
        $ids1 = explode(',', $ids1);
        $ids1 = array_unique($ids1);
        $ids1 = implode(',', $ids1);
        $cat1 = M('goods_cats')->where(array(
            'isShow' => 1,
            'catFlag' => 1,
            'catId' => array(
                'in',
                $ids1
            )
        ))
            ->order('catSort ASC')
            ->select();
        
        $arr = array_merge($cat1, $cat2, $cat3);
        
        $res = $this->arrayPidProcess($arr);
        
        $this->assign('cate', $res);
        
        // 品牌
        $brandSql = <<<Eof
SELECT
	go.brandId,
	br.brandName,
br.brandIco
FROM
	oto_goods_group AS gg
LEFT JOIN oto_goods AS go ON gg.goodsId = go.goodsId
LEFT JOIN oto_brands AS br ON br.brandId = go.brandId
WHERE
	unix_timestamp(now()) BETWEEN gg.startTime 
AND gg.endTime
AND gg.goodsGroupStatus = 1
AND gg.groupStatus = 1
and br.brandFlag=1
GROUP BY
	go.brandId
Eof;
        $this->brandInfo = M()->query($brandSql);
        
        $this->display();
    }
    
    // 递归
    public function arrayPidProcess($data, $res = array(), $pid = '0')
    {
        foreach ($data as $k => $v) {
            if ($v['parentId'] == $pid && $v['isShow'] == 1 && $v['catFlag'] == 1) {
                $res[$v['catId']]['info'] = $v;
                $res[$v['catId']]['child'] = $this->arrayPidProcess($data, array(), $v['catId']);
            }
        }
        return $res;
    }
}
