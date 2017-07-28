<?php
namespace Home\Model;

/**
 * 积分上传模型类
 */
class IntegralModel extends BaseModel
{

    /**
     * 显示所有积分商品分类
     */
    public function getCats()
    {
        $m = M('IntegralCats');
        $cats = $m->where('isShow=1 and catFlag=1')
            ->order('catSort asc')
            ->select();
        return $cats;
    }

    /**
     * 分页列表查看积分商品
     */
    public function queryByPage()
    {
        $catId = I("catId", 0);
        $prices = I("prices");
        $pcurr = (int) I("pcurr");
        if ($prices != "") {
            $pricelist = explode("_", $prices);
        }
        $msort = (int) I("msort", 0); // 排序标识
        $m = M('Integral');
        $sql = "select i.* from __PREFIX__integral as i left join __PREFIX__integral_cats as ic ";
        $sql .= "ON i.goodsCatId = ic.catId where i.goodsFlag=1  AND i.isSale = 1 AND ic.isShow = 1 ";
        // 礼品筛选--礼品分类
        if ($catId > 0) {
            $sql .= "  AND i.goodsCatId = $catId ";
        }
        // 礼品筛选--积分范围
        if ($prices != "" && $pricelist[0] >= 0 && $pricelist[1] >= 0) {
            $sql .= " AND (i.shopPrice BETWEEN  " . (int) $pricelist[0] . " AND " . (int) $pricelist[1] . ") ";
        }
        // 排序
        if ($msort == 1) { // 综合
            $sql .= " ORDER BY i.saleCount DESC ";
        } else 
            if ($msort == 7) { // 兑换排行
                $sql .= " ORDER BY i.saleCount DESC ";
            } else 
                if ($msort == 8) { // 价格
                    $sql .= " ORDER BY i.shopPrice ASC ";
                } else 
                    if ($msort == 9) { // 价格
                        $sql .= " ORDER BY i.shopPrice DESC ";
                    } else 
                        if ($msort == 10) { // 好评
                        } else 
                            if ($msort == 11) { // 上架时间
                                $sql .= " ORDER BY i.createTime DESC ";
                            }
        return $m->pageQuery($sql, $pcurr, 30);
        // dump($this->_sql());die;
    }

    /**
     * 获取积分商品详情
     */
    public function getGoodsDetails($id)
    {
        $m = M('Integral');
        $goods = $m->where('goodsFlag=1 and goodsId=' . $id)->find();
        $userId = (int) session('WST_USER.userId');
        // 当日凌晨和子时时间
        $startTime = strtotime(date('Y-m-d'));
        $endTime = strtotime(date('Y-m-d', strtotime('+1 day')));
        // 当日订单
        $orderModel = M('Orders');
        $orders = $orderModel->where('orderFlag = 1 and unix_timestamp(createTime)>' . $startTime . ' and unix_timestamp(createTime)<' . $endTime . ' and userId =' . $userId)
            ->field('orderId')
            ->select();
        $orderGoodsModel = M('OrderGoods');
        foreach ($orders as $k => $v) {
            $goodsId = $orderGoodsModel->where('orderId =' . $v['orderId'])->getField('goodsId');
            if ($goodsId == $id) {
                $goods['already'] = 1;
            }
        }
        $m = M('GoodsGallerys');
        $goods['gallerys'] = $m->where('goodsId=' . $id)->select();
        return $goods;
    }

    /**
     * 获取指定id商品分类
     */
    public function getCat($id)
    {
        $m = M('IntegralCats');
        $cat = $m->where('catId=' . $id)->find();
        return $cat;
    }

    /**
     * 商品价格区间
     */
    public function getMaxPrice()
    {
        $c1Id = (int) I("c1Id");
        $c2Id = (int) I("c2Id");
        $c3Id = (int) I("c3Id");
        
        $keyWords = urldecode(I("keyWords"));
        $words = array();
        if ($keyWords != "") {
            $words = explode(" ", $keyWords);
        }
        
        $sql = "SELECT i.* FROM __PREFIX__integral i left join __PREFIX__integral_cats ic on i.goodsCatId = ic.catId where i.goodsFlag = 1 AND  i.isSale = 1 AND ic.isShow = 1";
        
        if ($c1Id > 0) {
            $sql .= " AND g.goodsCatId1 = $c1Id";
        }
        if ($c2Id > 0) {
            $sql .= " AND g.goodsCatId2 = $c2Id";
        }
        if ($c3Id > 0) {
            $sql .= " AND g.goodsCatId3 = $c3Id";
        }
        if (! empty($words)) {
            $sarr = array();
            foreach ($words as $key => $word) {
                if ($word != "") {
                    $sarr[] = "g.goodsName LIKE '%$word%'";
                }
            }
            $sql .= " AND (" . implode(" or ", $sarr) . ")";
        }
        $glist = $this->query($sql);
        
        $maxPrice = 0;
        for ($i = 0; $i < count($glist); $i ++) {
            $goods = $glist[$i];
            if ($goods["shopPrice"] > $maxPrice) {
                $maxPrice = $goods["shopPrice"];
            }
        }
        
        return $maxPrice;
    }

    /*
     * 核对商品信息
     */
    public function checkGoodsStock()
    {
        $goodsId = (int) I('goodsId', 0);
        $sql = 'select i.goodsStock,i.isSale,i.goodsName from __PREFIX__integral as i left join __PREFIX__integral_cats as ic on i.goodsCatId = ic.catId where ic.isShow = 1 and goodsId = ' . $goodsId;
        return $this->query($sql);
    }

    /*
     * 获取积分兑换记录
     */
    public function exchangeRecord($obj)
    {
        $lastTime = $obj['lastTime'];
        if ($lastTime == 1) {
            $time = time() - 182 * 24 * 3600;
        } elseif ($lastTime == 2) {
            $time = time() - 365 * 24 * 3600;
        } else {
            $time = time() - 30 * 24 * 3600;
        }
        $userId = $obj['userId'];
        $pcurr = (int) I("pcurr");
        $sql = "select * from __PREFIX__score_record where userId = " . $userId . " and time > " . $time;
        if ($obj['pay'] != 0) {
            $sql .= " and IncDec = " . $obj['pay'];
        }
        $sql .= " order by time desc";
        return $this->pageQuery($sql, $pcurr, 10);
    }
}
;
?>